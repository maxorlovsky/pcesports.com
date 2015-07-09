<?php

class TournamentLolEuw
{
    public $chats;
    public $system;
    public $server;
    public $config;
    
	function __construct($params = array()) {
		$this->system = $params['system'];
        
        $this->server = 'euw';
        
        $rows = Db::fetchRows('SELECT * '.
			'FROM `tm_settings` '.
			'WHERE `setting` = "tournament-auto-lol-euw" OR '.
            '`setting` = "lol-current-number-euw" '
		);
        
        foreach($rows as $v) {
            $this->config[$v->setting] = $v->value;
        }
        
        $this->server = Db::escape($this->server);
        
		$this->chats = Db::fetchRows('SELECT `f`.`match_id`, `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2`, `t1`.`challonge_id` AS `challongeTeam1`, `t2`.`challonge_id` AS `challongeTeam2` './/, `p1`.`name` AS `playerName1`, `p2`.`name` AS `playerName2`
			'FROM `fights` AS `f` '.
			'LEFT JOIN `participants` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
			'LEFT JOIN `participants` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
            //'JOIN `players` AS `p1` ON `t1`.`cpt_player_id` = `p1`.`player_id` '.
            //'JOIN `players` AS `p2` ON `t2`.`cpt_player_id` = `p2`.`player_id` '.
			'WHERE `f`.`done` = 0 AND '.
            '`t1`.`server` = "'.$this->server.'" '
            //'GROUP BY `f`.`match_id` '
		);

		return $this;
	}
	
	public function fetchChat($form) {
        $return = array();
        
        if ($form) {
            foreach($form as $v) {
                $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$v[1].'.txt';
                $return[$v[0]] = '<p id="notice">Chat is empty</p>';
                if (file_exists($fileName)) {
                    $return[$v[0]] = strip_tags(stripslashes(html_entity_decode(file_get_contents($fileName))), '<div><p><b><a><u><span>');
                }
            }
        }
		
		return json_encode($return);
	}
	
	public function sendChat($form) {
		$fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$form[1].'.txt';
        $file = fopen($fileName, 'a');
        
        $content = '<div class="manager">';
        $content .= '<div class="message">'.$form[0].'</div>';
        $content .= '<span>'.$this->system->user->login.'</span>';
        $content .= '&nbsp;•&nbsp;<span id="notice">'.date('H:i', time()).'</span>';
        $content .= '</div>';
        
        fwrite($file, htmlspecialchars($content));
        fclose($file);
		
		return '1;1';
	}
	
	public function statusCheck($form) {
		$playersStatus = array();
        if ($form) {
            foreach($form as $v) {
                $breakdown = explode('_vs_', $v);
                $rows = Db::fetchRows('SELECT `id`, `online` FROM `participants` '.
                    'WHERE `id` = '.(int)$breakdown[0].' OR `id` = '.(int)$breakdown[1].' '.
                    'LIMIT 2'
                );
                
                foreach($rows as $v2) {
                    if ($v2->online != 0 && $v2->online+30 >= time()) {
                        $playersStatus[$v2->id] = 'online';
                    }
                    else {
                        $playersStatus[$v2->id] = 'offline';
                    }
                }
            }
        }
		
		return json_encode($playersStatus);
	}
    
    public function finishMatch($form) {
        $matchId = (int)$form[0]; //match id
        $server = $form[1]; //lol server
        $scores = $form[2]; //scores as string
        $winner = (int)$form[3]; //winner id
        $loser = (int)$form[4]; //looser id
        
        $row = Db::fetchRow('SELECT `done` FROM `fights` WHERE `match_id` = '.(int)$matchId);
        if ($row->done == 1) {
            return '0;Match already ended';
        }
        
        if (!in_array($server, array('euw','eune'))) {
            return '0;Server error';
        }
        
        $apiArray = array(
            '_method' => 'put',
            'match_id' => $matchId,
            'match[scores_csv]' => $scores,
            'match[winner_id]' => $winner,
        );
        
        if (_cfg('env') == 'prod') {
            $this->runChallongeAPI('tournaments/pentaclick-lol'.$server.$this->config['lol-current-number-'.$server].'/matches/'.$matchId.'.put', $apiArray);
        }
        else {
            $this->runChallongeAPI('tournaments/pentaclick-test1/matches/'.$matchId.'.put', $apiArray);
        }
        
        Db::query('UPDATE `participants` SET `ended` = 1 '.
            'WHERE `game` = "lol" AND '.
            '`server` = "'.$server.'" AND '.
            '`id` = '.(int)$loser.' '
        );
        
        Db::query('UPDATE `fights` SET `done` = 1 '.
            'WHERE `match_id` = '.(int)$matchId.' '
        );
        
        return 1;
    }
    
    protected function runChallongeAPI($apiAdditionalData, $apiArray = array(), $apiGetUrl = '') {
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
}