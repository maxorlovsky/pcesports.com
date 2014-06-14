<?php

class System
{
    public $data;
    public $page;
    public $user;
    public $logged_in;
    public $links;
    public $serverTimes = array();
    protected $userClass;
    
    public function __construct() {
    	if (!$this->data) {
    		$this->data = new stdClass();
    	}
    	
    	$this->loadClasses();

        //Making a connection
        Db::connect();
        
        $this->fetchParams();
    }
    
    public function run() {
        $this->checkGetData();
        $this->getStrings();
        
        $template = new Template();
        $template->parse();
    }
    
    public function fetchParams() {
        global $cfg;
        
        $this->data->settings = array();
        $this->data->links = new stdClass();
        
        $data = array_merge($_GET, $_POST, $_SESSION);
         
        if (!isset($data['val1'])) {
        	$data['val1'] = false;
        }
        if (!isset($data['token'])) {
        	$data['token'] = false;
        }
        
        $rows = Db::fetchRows('SELECT * FROM `tm_settings`');
        if ($rows) {
        	foreach($rows as $v) {
        		$this->data->settings[$v->setting] = $v->value;
        	}
        }
        
        $rows = Db::fetchRows('SELECT * FROM `tm_links` '.
            'WHERE `able` = 1 '.
            'ORDER BY `position` '
        );
        
        if ($rows) {
        	$this->data->links = $rows;
        }
        
        if (!$this->data->langugePicker && _cfg('language') != 'Config not found') {
            $languageRows = Db::fetchRows('SELECT `title`, `flag` FROM `tm_languages`');
            foreach($languageRows as $v) {
                if ($v->flag != _cfg('language')) {
                    $this->data->langugePicker[] = $v;
                }
                else {
                    $this->data->langugePicker['picked'] = $v;
                }
            }
        }
        
      	if ($data['val1']) {
        	$this->page = $data['val1'];
        }
        else {
        	$this->page = 'home';
        }
        
        $rows = Db::fetchRows('SELECT * FROM `tournaments` '.
            'ORDER BY `id` DESC'
        );
        foreach($rows as $v) {
            $time = strtotime($v->dates.' '.$v->time);
            
            if ($time > (time() - 86400)) {
                $statusString = str_replace(' ', '_', strtolower($v->status));
                $this->serverTimes[$time] = array(
                    'id'	=> $v->name,
                    'name' 	=> ($v->game=='lol'?'League of Legends':'Hearthstone'),
                    'status'=> $statusString,
                    'time' 	=> $time,
                );
            }
        }
        ksort($this->serverTimes);

        $this->logged_in = 0;
        
        if (isset($_SESSION['participant']) && $_SESSION['participant']->id) {
            
        }
    }
    
    public function ajax($data) {
    	$this->checkGetData();
    	$this->getStrings();
    	
        $ajax = new Ajax();
        $ajax->ajaxRun($data);
    }
    
    public function cleanData() {
    	unset($_SESSION['token']);
    	$this->logged_in = 0;
    	$this->user = array();
    	go(_cfg('site'));
    }
    
    public function runChallongeAPI($apiAdditionalData, $apiArray = array(), $apiGetUrl = '') {
    	$startTime = microtime(true);
    	$error = '';
    
    	$apiUrl = 'https://api.challonge.com/v1/';
    	$apiUrl .= $apiAdditionalData;
    	$apiUrl .= '?api_key=5Md6xHmc7hXIEpn87nf6z13pIik1FRJY7DpOSoYa';
    	if ($apiGetUrl) {
    		$apiUrl .= '&'.$apiGetUrl;
    	}
    
    	$apiUrlLog = $apiUrl;
    	if ($apiArray) {
    		foreach($apiArray as $k => $v) {
    			$apiUrlLog .= '&'.$k.'='.$v;
    		}
    	}
    
    	Db::query(
    		'INSERT INTO `challonge_requests` SET '.
    		' `timestamp` = NOW(), '.
    		' `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
    		' `data` = "'.$apiUrlLog.'"'
		);
    
    	$lastId = Db::lastId();
    
    	$ch = curl_init();
    
    	//---
    	curl_setopt($ch, CURLOPT_URL, $apiUrl); // set url to post to
    	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
    	curl_setopt($ch, CURLOPT_TIMEOUT, 60); // times out after 119s
    	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    	if ($apiArray) {
    		curl_setopt($ch, CURLOPT_POST, 1); //POST
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $apiArray); // add POST fields
    	}
    	else {
    		curl_setopt($ch, CURLOPT_POST, 0); //GET
    	}
    
    	$response = curl_exec($ch); // run the whole process
    	//dump(curl_error($ch));
    	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    	curl_close($ch);
    
    	if ($http_status == 401) {
    		$error = 'Invalid API key';
    	}
    	else if ($http_status == 404 ) {
    		$error = 'Object not found within your account scope';
    	}
    	else if ($http_status == 422) {
    		$error = 'Validation error(s) for create or update method';
    	}
    
    	$endTime = microtime(true);
    	$duration = $endTime - $startTime; //calculates total time taken
    
    	if ($apiArray) {
    		$response = 'POST';
    	}
    
    	Db::query(
	    	'UPDATE `challonge_requests` SET '.
	    	' `response` = "'.($error?$error:Db::escape($response)).'", '.
	    	' `time` = "'.(float)$duration.'" '.
	    	' WHERE id='.$lastId
    	);
    
    	if ( $error )
    	{
    		return false;
    	}
    
    	if ($response == 'POST') {
    		return true;
    	}
    
    	$response = json_decode($response);
    
    	return $response;
    }
	
	public function runAPI($apiAdditionalData, $fullReturn = false) {
		$startTime = microtime(true);
		
		$apiUrl = 'http://euw.api.pvp.net/api/lol';
		$apiUrl .= $apiAdditionalData;
		$apiUrl .= '?api_key=d8339ebc-91ea-49d3-809d-abcb42df872a';
		
		
		Db::query('INSERT INTO `riot_requests` SET '.
			'`timestamp` = NOW(), '.
			'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
			'`data` = "'.$apiUrl.'"'
		);
		
		$lastId = Db::lastId();
		
		$ch = curl_init();
		
		//---
		curl_setopt($ch, CURLOPT_URL, $apiUrl); // set url to post to
		curl_setopt($ch, CURLOPT_FAILONERROR, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
		curl_setopt($ch, CURLOPT_TIMEOUT, 60); // times out after 119s
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST, 0); // set POST method
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $apiArray); // add POST fields
		
		$response = curl_exec($ch); // run the whole process 
		
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close($ch);
		
		if ($http_status == 400) {
			//$error = curl_error($ch);
			$error = 'Bad request';
		}
		else if ($http_status == 503) {
			$error = 'Service unavailable';
		}
		else if ($http_status == 500) {
			$error = 'Internal server error';
		}
		else if ($http_status == 401) {
			$error = 'Unauthorized';
		}
		else if ($http_status == 404) {
			$error = 'Not found';
		}
		
		$endTime = microtime(true);
		$duration = $endTime - $startTime; //calculates total time taken
		
		Db::query('UPDATE `riot_requests` SET '.
			'`response` = "'.($error?$error:Db::escape($response)).'", '.
			'`time` = "'.(float)$duration.'" '.
			'WHERE `id` = '.$lastId.' '
		);
		
		if ( $error ) {
			return false;
		}
		
		if ($fullReturn === false) {
			$response = (array)json_decode($response);
			$response = array_values($response);
			$response = $response[0];
		}
		else {
			$response = json_decode($response);
		}
		
		return (object)$response;
	}
    
    public function sendMail($email, $subject, $msg) {
    	if(!_cfg('smtpMailName') || !_cfg('smtpMailPass')) return false;
    	
        $mailData = 'Date: '.date('D, d M Y H:i:s')." UT\r\n";
        $mailData .= 'Subject: =?UTF-8?B?'.base64_encode($subject). "=?=\r\n";
        $mailData .= 'Reply-To: '._cfg('smtpMailFrom'). "\r\n";
        $mailData .= 'MIME-Version: 1.0'."\r\n";
        $mailData .= 'Content-Type: text/html; charset="UTF-8"'."\r\n";
        $mailData .= 'Content-Transfer-Encoding: 8bit'."\r\n";
        $mailData .= 'From: "Pentaclick eSports" <'._cfg('smtpMailFrom').'>'."\r\n";
        $mailData .= 'To: '.$email.' <'.$email.'>'."\r\n";
        $mailData .= 'X-Priority: 3'."\r\n\r\n";
        
        $mailData .= $msg."\r\n";
        
        if(!$socket = fsockopen(_cfg('smtpMailHost'), _cfg('smtpMailPort'), $errno, $errstr, 30)) {
            return $errno."&lt;br&gt;".$errstr;
        }
        if (!$this->serverParse($socket, '220', __LINE__)) return false;
        
        fputs($socket, 'HELO '._cfg('smtpMailHost'). "\r\n");
        if (!$this->serverParse($socket, '250', __LINE__)) return false;
        
        fputs($socket, 'AUTH LOGIN'."\r\n");
        if (!$this->serverParse($socket, '334', __LINE__)) return false;
        
        fputs($socket, base64_encode(_cfg('smtpMailName')) . "\r\n");
        if (!$this->serverParse($socket, '334', __LINE__)) return false;
        
        fputs($socket, base64_encode(_cfg('smtpMailPass')) . "\r\n");
        if (!$this->serverParse($socket, '235', __LINE__)) return false;
        
        fputs($socket, 'MAIL FROM: <'._cfg('smtpMailName').'>'."\r\n");
        if (!$this->serverParse($socket, '250', __LINE__)) return false;
        
        fputs($socket, 'RCPT TO: <'.$email.'>'."\r\n");
        if (!$this->serverParse($socket, '250', __LINE__)) return false;
        
        fputs($socket, 'DATA'."\r\n");
        if (!$this->serverParse($socket, '354', __LINE__)) return false;
        
        fputs($socket, $mailData."\r\n.\r\n");
        if (!$this->serverParse($socket, '250', __LINE__)) return false;
        
        fputs($socket, 'QUIT'."\r\n");
        
        fclose($socket);
        
        return true;
    }
    
    /*Protected functions*/
    protected function loadClasses() {
    	require_once _cfg('cmsclasses').'/db.php';
    	require_once _cfg('classes').'/ajax.php';
        require_once _cfg('classes').'/cron.php';
    	require_once _cfg('classes').'/template.php';
    }
    
    protected function serverParse($socket, $response, $line = __LINE__) {
    	$server_response = '';
        while (substr($server_response, 3, 1) != ' ') {
            if (!($server_response = fgets($socket, 256))) {
                echo 'Error: '.$server_response.', '. $line;
                return false;
            }
        }
        
        if (!(substr($server_response, 0, 3) == $response)) {
            echo 'Error: '.$server_response.', '. $line;
            return false;
        }
        
        return true;
    }
    
    protected function getStrings() {
        global $str;
        
        $rows = Db::fetchRows('SELECT `key`, `'._cfg('fullLanguage').'` AS `value` FROM `tm_strings`');
        if ($rows) {
        	foreach($rows as $v) {
        		$str[$v->key] = $v->value;
        	}
        }
        
        return true;
    }
    
    /*Private functions*/
    private function checkGetData() {
        global $cfg;
    
        if (isset($_GET['language']) && $_GET['language'] == 'run') { //Special RUN command
            if (isset($_GET['val1'])) {
                if ($_GET['val1'] === _cfg('cronjob')) {
                    set_time_limit(300);
                    $cronClass = new Cron();
                    $cronClass->cleanImagesTmp();
                    $cronClass->updateChallongeMatches();
                    $cronClass->sendNotifications();
                }
                else if ($_GET['val1'] == 'riotcode') {
                    /*$callback = '{"version":1,"tournamentMetaData":{"passbackDataPacket":"22325692","passbackUrl":"http://test.pcesports.com/run/riotcode/"},"gameId":856927295,"gameLength":1616,"gameType":"CUSTOM_GAME","ranked":false,"invalid":false,"gameMode":"CLASSIC","teamPlayerParticipantsSummaries":[{"level":30,"teamId":100,"isWinningTeam":true,"leaver":false,"summonerName":"Soldecroix","skinName":"Quinn","profileIconId":583,"botPlayer":false,"spell1Id":21,"spell2Id":4,"statistics":[{"value":411,"statTypeName":"TOTAL_TIME_CROWD_CONTROL_DEALT","m_nDataVersion":0},{"value":24380,"statTypeName":"PHYSICAL_DAMAGE_DEALT_TO_CHAMPIONS","m_nDataVersion":0},{"value":0,"statTypeName":"TRUE_DAMAGE_TAKEN","m_nDataVersion":0},{"value":25117,"statTypeName":"TOTAL_DAMAGE_TAKEN","m_nDataVersion":0},{"value":0,"statTypeName":"NEUTRAL_MINIONS_KILLED_ENEMY_JUNGLE","m_nDataVersion":0},{"value":152,"statTypeName":"MINIONS_KILLED","m_nDataVersion":0},{"value":10139,"statTypeName":"GOLD_EARNED","m_nDataVersion":0},{"value":123122,"statTypeName":"PHYSICAL_DAMAGE_DEALT_PLAYER","m_nDataVersion":0},{"value":1,"statTypeName":"WIN","m_nDataVersion":0},{"value":3340,"statTypeName":"ITEM6","m_nDataVersion":0},{"value":1055,"statTypeName":"ITEM0","m_nDataVersion":0},{"value":3,"statTypeName":"NEUTRAL_MINIONS_KILLED_YOUR_JUNGLE","m_nDataVersion":0},{"value":702,"statTypeName":"TRUE_DAMAGE_DEALT_PLAYER","m_nDataVersion":0},{"value":0,"statTypeName":"MAGIC_DAMAGE_DEALT_PLAYER","m_nDataVersion":0},{"value":157,"statTypeName":"TOTAL_TIME_SPENT_DEAD","m_nDataVersion":0},{"value":0,"statTypeName":"ASSISTS","m_nDataVersion":0},{"value":0,"statTypeName":"MAGIC_DAMAGE_DEALT_TO_CHAMPIONS","m_nDataVersion":0},{"value":1055,"statTypeName":"ITEM5","m_nDataVersion":0},{"value":3072,"statTypeName":"ITEM2","m_nDataVersion":0},{"value":0,"statTypeName":"LARGEST_CRITICAL_STRIKE","m_nDataVersion":0},{"value":12874,"statTypeName":"PHYSICAL_DAMAGE_TAKEN","m_nDataVersion":0},{"value":0,"statTypeName":"SIGHT_WARDS_BOUGHT_IN_GAME","m_nDataVersion":0},{"value":0,"statTypeName":"WARD_KILLED","m_nDataVersion":0},{"value":0,"statTypeName":"WARD_PLACED","m_nDataVersion":0},{"value":1,"statTypeName":"LARGEST_MULTI_KILL","m_nDataVersion":0},{"value":3,"statTypeName":"LARGEST_KILLING_SPREE","m_nDataVersion":0},{"value":16,"statTypeName":"LEVEL","m_nDataVersion":0},{"value":3,"statTypeName":"NEUTRAL_MINIONS_KILLED","m_nDataVersion":0},{"value":1055,"statTypeName":"ITEM1","m_nDataVersion":0},{"value":123824,"statTypeName":"TOTAL_DAMAGE_DEALT","m_nDataVersion":0},{"value":498,"statTypeName":"TRUE_DAMAGE_DEALT_TO_CHAMPIONS","m_nDataVersion":0},{"value":3572,"statTypeName":"TOTAL_HEAL","m_nDataVersion":0},{"value":3022,"statTypeName":"ITEM3","m_nDataVersion":0},{"value":2,"statTypeName":"BARRACKS_KILLED","m_nDataVersion":0},{"value":0,"statTypeName":"VISION_WARDS_BOUGHT_IN_GAME","m_nDataVersion":0},{"value":5,"statTypeName":"NUM_DEATHS","m_nDataVersion":0},{"value":3252,"statTypeName":"ITEM4","m_nDataVersion":0},{"value":24878,"statTypeName":"TOTAL_DAMAGE_DEALT_TO_CHAMPIONS","m_nDataVersion":0},{"value":4,"statTypeName":"TURRETS_KILLED","m_nDataVersion":0},{"value":8,"statTypeName":"CHAMPIONS_KILLED","m_nDataVersion":0},{"value":12242,"statTypeName":"MAGIC_DAMAGE_TAKEN","m_nDataVersion":0}]}],"otherTeamPlayerParticipantsSummaries":[{"level":30,"teamId":200,"isWinningTeam":false,"leaver":false,"summonerName":"Maxtream","skinName":"Nidalee","profileIconId":10,"botPlayer":false,"spell1Id":4,"spell2Id":21,"statistics":[{"value":0,"statTypeName":"NEUTRAL_MINIONS_KILLED_ENEMY_JUNGLE","m_nDataVersion":0},{"value":1026,"statTypeName":"ITEM5","m_nDataVersion":0},{"value":1056,"statTypeName":"ITEM0","m_nDataVersion":0},{"value":2412,"statTypeName":"PHYSICAL_DAMAGE_DEALT_TO_CHAMPIONS","m_nDataVersion":0},{"value":27710,"statTypeName":"PHYSICAL_DAMAGE_TAKEN","m_nDataVersion":0},{"value":3340,"statTypeName":"ITEM6","m_nDataVersion":0},{"value":3,"statTypeName":"NEUTRAL_MINIONS_KILLED_YOUR_JUNGLE","m_nDataVersion":0},{"value":3020,"statTypeName":"ITEM2","m_nDataVersion":0},{"value":20089,"statTypeName":"PHYSICAL_DAMAGE_DEALT_PLAYER","m_nDataVersion":0},{"value":0,"statTypeName":"ASSISTS","m_nDataVersion":0},{"value":0,"statTypeName":"TRUE_DAMAGE_DEALT_PLAYER","m_nDataVersion":0},{"value":0,"statTypeName":"TOTAL_TIME_CROWD_CONTROL_DEALT","m_nDataVersion":0},{"value":0,"statTypeName":"MAGIC_DAMAGE_TAKEN","m_nDataVersion":0},{"value":0,"statTypeName":"LARGEST_CRITICAL_STRIKE","m_nDataVersion":0},{"value":2,"statTypeName":"WARD_PLACED","m_nDataVersion":0},{"value":0,"statTypeName":"SIGHT_WARDS_BOUGHT_IN_GAME","m_nDataVersion":0},{"value":0,"statTypeName":"WARD_KILLED","m_nDataVersion":0},{"value":8,"statTypeName":"NUM_DEATHS","m_nDataVersion":0},{"value":1,"statTypeName":"LARGEST_MULTI_KILL","m_nDataVersion":0},{"value":0,"statTypeName":"BARRACKS_KILLED","m_nDataVersion":0},{"value":3070,"statTypeName":"ITEM4","m_nDataVersion":0},{"value":15,"statTypeName":"LEVEL","m_nDataVersion":0},{"value":3089,"statTypeName":"ITEM3","m_nDataVersion":0},{"value":1,"statTypeName":"LOSE","m_nDataVersion":0},{"value":3,"statTypeName":"NEUTRAL_MINIONS_KILLED","m_nDataVersion":0},{"value":1056,"statTypeName":"ITEM1","m_nDataVersion":0},{"value":0,"statTypeName":"TRUE_DAMAGE_DEALT_TO_CHAMPIONS","m_nDataVersion":0},{"value":250,"statTypeName":"TOTAL_TIME_SPENT_DEAD","m_nDataVersion":0},{"value":115,"statTypeName":"MINIONS_KILLED","m_nDataVersion":0},{"value":0,"statTypeName":"LARGEST_KILLING_SPREE","m_nDataVersion":0},{"value":12242,"statTypeName":"MAGIC_DAMAGE_DEALT_TO_CHAMPIONS","m_nDataVersion":0},{"value":81796,"statTypeName":"TOTAL_DAMAGE_DEALT","m_nDataVersion":0},{"value":61706,"statTypeName":"MAGIC_DAMAGE_DEALT_PLAYER","m_nDataVersion":0},{"value":498,"statTypeName":"TRUE_DAMAGE_TAKEN","m_nDataVersion":0},{"value":7927,"statTypeName":"GOLD_EARNED","m_nDataVersion":0},{"value":1,"statTypeName":"TURRETS_KILLED","m_nDataVersion":0},{"value":28208,"statTypeName":"TOTAL_DAMAGE_TAKEN","m_nDataVersion":0},{"value":4,"statTypeName":"CHAMPIONS_KILLED","m_nDataVersion":0},{"value":0,"statTypeName":"VISION_WARDS_BOUGHT_IN_GAME","m_nDataVersion":0},{"value":5475,"statTypeName":"TOTAL_HEAL","m_nDataVersion":0},{"value":14654,"statTypeName":"TOTAL_DAMAGE_DEALT_TO_CHAMPIONS","m_nDataVersion":0}]}]}';*/
                    
                    if (!is_object(json_decode($callback))) {
                        return false;
                    }
                    
                    //$callback = file_get_contents("php://input");
                    $data = json_decode($callback);
                    Db::query('INSERT INTO `riot_callback` SET '.
                        '`game_id` = '.(int)$data->gameId.', '.
                        '`callback` = "'.Db::escape($callback).'"'
                    );

                    $team = array();
                    $matchId = $data->tournamentMetaData->passbackDataPacket;
                    $matchRow = Db::fetchRow('SELECT `f`.`match_id`, `f`.`player1_id`, `f`.`player2_id`, '.
                        '`t1`.`id` AS `t1id`, '.
                        '`t2`.`id` AS `t2id` '.
                        'FROM `fights` AS `f` '.
                        'LEFT JOIN `teams` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
                        'LEFT JOIN `teams` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
                        'WHERE `f`.`match_id` = '.(int)$matchId.' AND '.
                        '`f`.`done` = 0 AND '.
                        '`t1`.`approved` = 1 AND '.
                        '`t2`.`approved` = 1 AND '.
                        '`t1`.`deleted` = 0 AND '.
                        '`t2`.`deleted` = 0 AND '.
                        '`t1`.`ended` = 0 AND '.
                        '`t2`.`ended` = 0 '
                    );
                    
                    if (!$matchRow) {
                        $txt = 'ERROR!<br />Match ID:'.$matchId;
                        $this->sendMail('max.orlovsky@gmail.com', 'Error processing Riot match', $txt);
                        exit();
                    }
                    
                    //Gathering teams in 1 array
                    $teamsRow = Db::fetchRows('SELECT `t`.`challonge_id`, `p`.`team_id`, `p`.`name`, `p`.`player_num` '.
                        'FROM `players` AS `p` '.
                        'LEFT JOIN `teams` AS `t` ON `p`.`team_id` = `t`.`id` '.
                        'WHERE `p`.`team_id` = '.(int)$matchRow->t1id.' OR `p`.`team_id` = '.(int)$matchRow->t2id.' '
                    );
                    $teams = array();
                    foreach($teamsRow as $v) {
                        $teams[$v->challonge_id][$v->player_num] = $v->name;
                    }
                    
                    //Getting winning team(rTeam) + gathering into small array player names
                    $winningTeam = 0;
                    $persons = array(0,0);
                    foreach($data->teamPlayerParticipantsSummaries as $k => $v) {
                        if ($v->isWinningTeam == 1) {
                            $winningTeam = $v->teamId;
                        }
                        $rTeam[$v->teamId][$k] = $v->summonerName;
                        ++$persons[0];
                    }
                    
                    foreach($data->otherTeamPlayerParticipantsSummaries as $k => $v) {
                        if ($v->isWinningTeam == 1) {
                            $winningTeam = $v->teamId;
                        }
                        $rTeam[$v->teamId][$k] = $v->summonerName;
                        ++$persons[1];
                    }
                    
                    if ($persons[0] != 5 || $persons[1] != 5) {
                        $txt = 'ERROR <b>Persons count</b>!<br />Match ID:'.$matchId;
                        //$this->sendMail('max.orlovsky@gmail.com', 'Error processing Riot match', $txt);
                    }
                    
                    foreach($teams as $k => $v) {
                        foreach($v as $k2 => $v2) {
                            if (in_array($v2, $rTeam[$winningTeam])) {
                                $winner = $k;
                                break;
                            }
                        }
                    }
                    
                    $apiArray = array(
                        '_method' => 'put',
                        'match_id' => $matchId,
                        'match[scores_csv]' => '0-0',
                        'match[winner_id]' => $winner,
                    );
                    
                    if (_cfg('env') == 'prod') {
                        $this->runChallongeAPI('tournaments/pentaclick-lol'.(int)$this->data->settings['lol-current-number'].'/matches/'.$matchId.'.put', $apiArray);
                    }
                    else {
                        $this->runChallongeAPI('tournaments/pentaclick-test1/matches/'.$matchId.'.put', $apiArray);
                    }
                    
                    $this->sendMail('max.orlovsky@gmail.com', 'CHECK GAME!!!', $matchId);
                    /*Db::query('UPDATE `teams` SET `ended` = 1 '.
                        'WHERE `game` = "lol" AND '.
                        '`id` = '.(int)$_SESSION['participant']->id.' '
                    );
                    
                    /*Db::query('UPDATE `fights` SET `done` = 1 '.
                        'WHERE `match_id` = '.(int)$matchId.' '
                    );
                    
                    $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$row->id1.'_vs_'.$row->id2.'.txt';
                        
                    $file = fopen($fileName, 'a');
                    $content = '<p><span id="notice">('.date('H:i:s', time()).')</span> <b>'.$_SESSION['participant']->name.' surrendered</b></p>';
                    fwrite($file, htmlspecialchars($content));
                    fclose($file);*/
                }
                else if ($_GET['val1'] == 'generate') {
					$rows = Db::fetchRows('SELECT * FROM `tm_strings`');
                    $txt = '';
                    foreach($rows as $v) {
                        $txt .= '!'.$v->key.' = '.$v->english;
                        $txt .= "\n\n";
                    }
                    echo '<textarea cols="80" rows="50">'.$txt.'</textarea>';
                }
                else {
                    exit('Run command error');
                }
            }
            
            exit();
        }
    
        $availableLanguages = array();
        $fetchingFullLanguage = array();
        $languageRows = Db::fetchRows('SELECT `title`, `flag` FROM `tm_languages`');
        foreach($languageRows as $v) {
        	$availableLanguages[] = $v->flag;
            $fetchingFullLanguage[$v->flag] = $v->title;
        }
        
        //Setting - Languages
        if (isset($_GET['language']) && $_GET['language'] && in_array($_GET['language'], $availableLanguages)) {
            $cfg['language'] = $_GET['language'];
            setcookie('language', _cfg('language'), time()+7776000, '/', 'pcesports.com');
        }
        else if (isset($_COOKIE['language']) && $_COOKIE['language'] && in_array($_COOKIE['language'], $availableLanguages)) {
            $cfg['language'] = $_COOKIE['language'];
        }
        else {
        	$cfg['language'] = 'en';
        }
        
        $cfg['fullLanguage'] = $fetchingFullLanguage[$cfg['language']];
        
        $cfg['href'] = str_replace('%lang%', $cfg['language'], $cfg['href']);
        $cfg['hssite'] = $cfg['href'].'/hearthstone';
        $cfg['lolsite'] = $cfg['href'].'/leagueoflegends';

        return true;
    }
}