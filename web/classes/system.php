<?php

class System
{
    public $data;
    public $page;
    public $user;
    public $logged_in;
    public $links;
    public $serverTimes = array();
    public $streams = array();
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
        
        $template = new Template();
        $template->parse();
    }
    
    public function fetchParams() {
        global $cfg;
        
        $data = array_merge($_GET, $_POST, $_SESSION);
         
        if (!isset($data['val1'])) {
        	$data['val1'] = false;
        }
        if (!isset($data['token'])) {
        	$data['token'] = false;
        }
        
        if (isset($_SESSION['user']) || isset($_COOKIE['uid']) && $_COOKIE['uid'] && isset($_COOKIE['token']) && $_COOKIE['token']) {
            $checkUser = User::checkUser($_SESSION['user']);
        }
        else {
            $checkUser = false;
        }
        
        if ($checkUser) {
            $this->logged_in = 1;
            $this->data->user = $checkUser;
            if ($this->data->user->https == 1) {
                $cfg['site'] = str_replace('http://', 'https://', $cfg['site']);
                $cfg['href'] = str_replace('http://', 'https://', $cfg['href']);
            }
            //User::token();
        }
        else {
            $this->logged_in = 0;
            $this->data->user = new stdClass();
            if (isset($_SESSION['user']) && $_SESSION['user']) {
                User::logOut();
            }
        }
        
        if (!isset($this->data->settings) && !$this->data->settings) {
            $this->data->settings = array();
            $rows = Db::fetchRows('SELECT * FROM `tm_settings`');
            if ($rows) {
                foreach($rows as $v) {
                    $this->data->settings[$v->setting] = $v->value;
                }
            }
        }
        
        $rows = Db::fetchRows('SELECT * FROM `tm_links` '.
            'WHERE `able` = 1 '.
            'ORDER BY `position` '
        );
        
        if (!isset($this->data->links) && !$this->data->links) {
            $this->data->links = new stdClass();
            if ($rows) {
                foreach($rows as $k => $v) {
                    if ($v->main_link == 0) {
                        $id = $v->id;
                        $this->data->links->$id = $v;
                        $this->data->links->$id->sublinks = new stdClass();
                    }
                }
                
                foreach($rows as $k => $v) {
                    if ($v->main_link != 0) {
                        $mainId = $v->main_link;
                        $id = $v->id;
                        $this->data->links->$mainId->sublinks->$id = $v;
                    }
                }
                
                $this->data->links = $rows;
            }
        }
        
        if (!isset($this->data->langugePicker)) {
            $this->data->langugePicker = array();
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
            'WHERE `status` != "Ended" '.
            'ORDER BY `id` DESC '
        );
        
        foreach($rows as $v) {
            $time = strtotime($v->dates_registration.' '.$v->time);
            
            if ($time > (time() - 86400)) {
                $statusString = str_replace(' ', '_', strtolower('registration'));
            }
            else {
                $time = strtotime($v->dates_start.' '.$v->time);
                $statusString = str_replace(' ', '_', strtolower('start'));
            }
            
            $this->serverTimes[] = array(
                'time' 	=> $time,
                'id'	=> $v->name,
                'server'=> $v->server,
                'game'  => $v->game,
                'name' 	=> ($v->game=='lol'?'League of Legends':'Hearthstone League S1 - '),
                'status'=> $statusString,
            );
        }
        asort($this->serverTimes);
        
        if (_cfg('language') != 'Config not found') {
            $rows = Db::fetchRows('SELECT `id`, `name`, `display_name`, `featured`, `game`, `viewers` FROM `streams` '.
                'WHERE `online` >= '.(time() - 360).' AND '.
                '`approved` = 1 AND '.
                '(`languages` = "'.Db::escape(_cfg('language')).'" OR `languages` = "both") '.
                'ORDER BY `featured` DESC, `viewers` DESC '.
                'LIMIT 5'
            );
            if ($rows) {
                foreach($rows as $v) {
                    $this->streams[$v->id] = (object)array(
                        'name' => $v->name,
                        'display_name' => $v->display_name,
                        'featured' => $v->featured,
                        'game' => $v->game,
                        'viewers' => $v->viewers,
                        'link' => $v->name,
                    );
                }
            }
        }
        
        if ($this->logged_in == 1 && !isset($_SESSION['participant'])) {
            //Check if user is participant
            $row = Db::fetchRow(
                'SELECT * '.
                'FROM `participants` '.
                'WHERE '.
                '`user_id` = '.(int)$this->data->user->id.' AND '.
                '`ended` = 0 AND ' .
                '`deleted` = 0 AND '.
                '(`approved` = 1 OR `game` = "hslan") '.
                'LIMIT 1 '
            );
            
            if ($row) {
                $_SESSION['participant'] = $row;
            }
        }
    }
    
    public function ajax($data) {
    	$this->checkGetData();
    	
        $ajax = new Ajax();
        $ajax->ajaxRun($data);
    }
    
    public function cleanData() {
    	unset($_SESSION['token']);
    	$this->logged_in = 0;
    	$this->user = array();
    	go(_cfg('site'));
    }
    
    public function runDotaAPI($params = array()) {
    	$startTime = microtime(true);
        
        $apiUrl = 'https://api.steampowered.com/'.$params['module'].'/';
        $apiUrl .= '?key=B562BDCF6768E330A9B01B1A016E82E0&';
        $apiUrl .= $params['get'];
        
        Db::query(
    		'INSERT INTO `dota_requests` SET '.
    		' `timestamp` = NOW(), '.
    		' `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
    		' `data` = "'.Db::escape($apiUrl).'" '
		);
        
        $lastId = Db::lastId();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl); // set url to post to
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // times out after 2s
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 0); // set POST method
        $response = curl_exec($ch); // run the whole process 
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
    	$endTime = microtime(true);
    	$duration = $endTime - $startTime; //calculates total time taken
    
    	Db::query(
	    	'UPDATE `dota_requests` SET '.
	    	' `response` = "'.Db::escape($response).'", '.
	    	' `time` = "'.(float)$duration.'" '.
	    	' WHERE id='.$lastId
    	);
    
    	$response = json_decode($response, true);
    
    	return $response;
    }
    
    public function runTwitchAPI($channelName) {
    	$startTime = microtime(true);
        $channelName = strtolower(htmlspecialchars($channelName, ENT_QUOTES));
        
        $apiUrl = 'https://api.twitch.tv/kraken/streams/';
        $apiUrl .= $channelName;
        $apiUrl .= '?client_id='._cfg('social')['tc']['id'];
        
        Db::query(
    		'INSERT INTO `twitch_requests` SET '.
    		' `timestamp` = NOW(), '.
    		' `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
    		' `data` = "'.Db::escape($apiUrl).'" '
		);
        
        $lastId = Db::lastId();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl); // set url to post to
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // times out after 2s
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 0); // set POST method
        $response = curl_exec($ch); // run the whole process 
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
    	$endTime = microtime(true);
    	$duration = $endTime - $startTime; //calculates total time taken
    
    	Db::query(
	    	'UPDATE `twitch_requests` SET '.
	    	' `response` = "'.Db::escape($response).'", '.
	    	' `time` = "'.(float)$duration.'" '.
	    	' WHERE id='.$lastId
    	);
    
    	$response = json_decode($response, true);
    
    	return $response;
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
            $apiArray['api_key'] = '5Md6xHmc7hXIEpn87nf6z13pIik1FRJY7DpOSoYa';
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
	
	public function runAPI($apiAdditionalData, $server, $fullReturn = false) {
        if (!$apiAdditionalData || !in_array($server, array_keys(_cfg('lolRegions')))) {
            return false;
        }
        
		$startTime = microtime(true);
		
        if (_cfg('env') == 'dev') {
            $apiUrl = 'http://';
        }
        else {
            $apiUrl = 'https://';
        }
        
		$apiUrl .= $server.'.api.pvp.net/api/lol';
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
    
    //@email - Send TO
    //@subject - Subject of email
    //@msg - Body of message (can be html)
    //@file - array, optional, attachment to email, required full link, data in array
    //@file['name'] - name of the file with extension
    //@file['content'] - plain text or plain html, it will be converted into attachment
    public function sendMail($email, $subject, $msg, $files = array()) {
    	if(!_cfg('smtpMailName') || !_cfg('smtpMailPass')) {
            return false;
        }
        
        $mime_boundary = '==Multipart_Boundary_x'.md5(time()).'x';

        $mailData = 'MIME-Version: 1.0'."\r\n";
        $mailData .= 'Date: '.date('D, d M Y H:i:s')." UT\r\n";
        $mailData .= 'Subject: '.$subject. "\r\n";
        $mailData .= 'Reply-To: "Pentaclick eSports" <'._cfg('smtpMailFrom').'> '."\r\n";
        $mailData .= 'From: "Pentaclick eSports" <'._cfg('smtpMailFrom').'> '."\r\n";
        $mailData .= 'To: '.$email.''."\r\n";
        $mailData .= 'X-Priority: 3'."\r\n";
        $mailData .= 'Content-Type: multipart/mixed; boundary="'.$mime_boundary.'" '."\r\n"; //
        $mailData .= '--'.$mime_boundary."\r\n";  //
        
        if ($files) {
            foreach($files as $k => $v) {
                if ($v['content']) {
                    $mailData .= 'Content-Type: application/octet-stream; name='.$v['name'].''."\r\n";
                    $mailData .= 'Content-Transfer-Encoding: base64 '."\r\n";
                    $mailData .= 'Content-Disposition: attachment; filename="'.$v['name'].'" '."\r\n";
                    $mailData .= "\r\n".base64_encode($v['content'])."\r\n\r\n";
                    
                    $mailData .= '--'.$mime_boundary."\r\n";
                }
            }
        }
        
        $mailData .= 'Content-Type: text/html; charset="UTF-8"'."\r\n";
        $mailData .= 'Content-Transfer-Encoding: 8bit'."\r\n\r\n";
        $mailData .= $msg;
        
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
        require_once _cfg('classes').'/social.php';
        require_once _cfg('classes').'/user.php';
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
        
        $this->getStrings();
        
        $cfg['href'] = str_replace('%lang%', $cfg['language'], $cfg['href']);
        $cfg['hssite'] = $cfg['href'].'/hearthstone';
        $cfg['lolsite'] = $cfg['href'].'/leagueoflegends';
    
        if (isset($_GET['language']) && $_GET['language'] == 'run') { //Special RUN command
            if (isset($_GET['val1'])) {
                if ($_GET['val1'] === _cfg('cronjob')) {
                    set_time_limit(300);
                    $cronClass = new Cron();
                    
                    //SQL involved functions
                    $cronClass->updateChallongeMatches();
                    $cronClass->tournamentsOpenReg();
                    $cronClass->finalizeTournament();
                    $cronClass->sendNotifications();
                    $cronClass->checkLolGames();
                    $cronClass->checkDotaGames();
                    $cronClass->updateStreamers();
                    $cronClass->sqlCleanUp();
                    
                    //Others functions without SQL
                    $cronClass->cleanImagesTmp();
                }
                else if ($_GET['val1'] == 'riotcode') {
                    set_time_limit(300);
                    $cronClass = new Cron();
                    $cronClass->checkLolGames();
                }
                else if ($_GET['val1'] == 'social' && strlen($_GET['val2']) == 2) {
                    unset($_SESSION['errors']);
                    
                    $social = new Social();
                    $answer = $social->Verify($_GET['val2']);
                    if ($answer === false) {
                        header('Location: '._cfg('href').'/profile/error');
                        exit();
                    }
                    
                    header('Location: '._cfg('href').'/profile');
                }
                else if ($_GET['val1'] == 'logout') {
                    User::logout();
                    header('Location: '._cfg('site'));
                }
                else {
                    exit('Run command error');
                }
            }
            
            exit();
        }

        return true;
    }
}