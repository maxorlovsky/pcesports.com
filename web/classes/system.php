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
    public $cacheTtl = 600;
    public $apcEnabled = false;
    protected $userClass;
    
    public function __construct() {
        if (isset($this->data->settings) && is_array($this->data->settings)) {
            //Parent class already initialized, data storred, ignoring
            return true;
        }
        
    	if (!$this->data) {
    		$this->data = new stdClass();
    	}
        
        $this->apcEnabled = extension_loaded('apc');
        $this->loadClasses();
        
        //Making a connection
        Db::connect();
        
        $this->fetchParams();
    }
    
    public function run() {
        $this->checkGetData();
        
        $template = new Template();
        if (isset($_GET['language']) && $_GET['language'] == 'widget') { //Special widget command
            $template->parseWidget();
        }
        else {
            $template->parse();
        }        
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
            if ($this->data->user->https == 1 && strpos($cfg['site'], 'https://') === false) {
                go(str_replace('http://', 'https://', $cfg['site'].$_SERVER['REDIRECT_URL']));
                exit();
            }
            else if ($this->data->user->https == 1) {
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
            if ($languageRows) {
                foreach($languageRows as $v) {
                    if ($v->flag != _cfg('language')) {
                        $this->data->langugePicker[] = $v;
                    }
                    else {
                        $this->data->langugePicker['picked'] = $v;
                    }
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
            'GROUP BY `server`, `game` '.
            'ORDER BY `id` DESC '
        );

        if ($rows) {
            foreach($rows as $v) {
                $time = strtotime($v->dates_registration.' '.$v->time);
                
                if ($time > (time() - 86400)) {
                    $statusString = str_replace(' ', '_', strtolower('registration'));
                }
                else {
                    $time = strtotime($v->dates_start.' '.$v->time);
                    $statusString = str_replace(' ', '_', strtolower('start'));
                }

                if ($v->game == 'hs') {
                    $game = 'Hearthstone';
                }
                else if ($v->game == 'lol') {
                    $game = 'League of Legends';
                }
                else if ($v->game == 'smite') {
                    $game = 'Smite';
                }
                else {
                    $game = $v->game;
                }
                
                $this->serverTimes[] = array(
                    'time' 	=> $time,
                    'id'	=> $v->name,
                    'server'=> $v->server,
                    'game'  => $v->game,
                    'name' 	=> $game,
                    'status'=> $statusString,
                );
            }
            asort($this->serverTimes);
        }

        $where = '';
        if ($this->data->settings['tournament-start-hs-s1'] == 1) {
            $where .= '`game` = "hs" AND `tournament_id` = '.(int)$this->data->settings['hs-current-number-s1'].' ';
        }
        if ($this->data->settings['tournament-start-lol-euw'] == 1 || $this->data->settings['tournament-start-lol-eune'] == 1) {
            $where .= '`game` = "lol" AND (`tournament_id` = '.(int)$this->data->settings['lol-current-number-euw'].' OR `tournament_id` = '.(int)$this->data->settings['lol-current-number-eune'].') ';
        }
        if ($this->data->settings['tournament-start-smite-na'] == 1 || $this->data->settings['tournament-start-smite-eu'] == 1) {
            $where .= '`game` = "smite" AND (`tournament_id` = '.(int)$this->data->settings['smite-current-number-na'].' OR `tournament_id` = '.(int)$this->data->settings['smite-current-number-eu'].') ';
        }

        if ($where) {
            $this->streams = Db::fetchRows(
                'SELECT `id`, `name`, `display_name`, `game`, `viewers`, IF(`online` >= '.(time()-360).', 1, 0) AS `onlineStatus`, 1 AS `event`, `name` AS `link` '.
                'FROM `streams_events` '.
                'WHERE '.
                $where.
                'ORDER BY `viewers` DESC '.
                'LIMIT 5'
            );
        }
        else {
            $this->streams = Db::fetchRows('SELECT `id`, `name`, `display_name`, `featured`, `game`, `viewers`, `name` AS `link` FROM `streams` '.
                'WHERE `online` >= '.(time() - 360).' AND '.
                '`approved` = 1 AND '.
                '`game` != "lolcup" AND '.
                '`game` != "smitecup" '.
                'ORDER BY `featured` DESC, `viewers` DESC '.
                'LIMIT 5'
            );
        }
    }
    
    public function ajax($data, $type = '') {
    	$this->checkGetData();
    	
        $ajax = new Ajax();
        $ajax->ajaxRun($data, $type);
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
    
    public function runSmiteAPI($params = array()) {
    	$startTime = microtime(true);
        
        $apiUrl = 'http://api.smitegame.com/smiteapi.svc/'.$params['module'].'json';
        
        //ping command not require any other data, not even developerId and signature
        if ($params['module'] != 'ping') {
            $apiUrl .= '/1307/';
            $signature = md5('1307'.$params['module'].'1148AB9B457A47A8BABF4F41D10C2213'.date('YmdHis'));
            $apiUrl .= $signature;
        }
        
        //create session require timestamp in another place, after signature, in other cases it's goes after signature
        if ($params['module'] == 'createsession') {
            $apiUrl .= '/'.date('YmdHis');
        }
        
        //Adding "main" and additional parameters, to fetch required data
        if (isset($params['command'])) {
            $apiUrl .= '/'.$params['session'].'/'.date('YmdHis');
            $apiUrl .= '/'.$params['command'];
        }
        
        Db::query(
    		'INSERT INTO `smite_requests` SET '.
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
	    	'UPDATE `smite_requests` SET '.
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
	
	public function runRiotAPI($apiAdditionalData, $server, $fullReturn = false) {
        if (!$apiAdditionalData || !in_array($server, array_keys(_cfg('lolRegions')))) {
            return false;
        }
        
		$startTime = microtime(true);
		
		$apiUrl = 'https://'.$server.'.api.pvp.net/api/lol';
		$apiUrl .= $apiAdditionalData;
		$apiUrl .= '?api_key=d8339ebc-91ea-49d3-809d-abcb42df872a';
		
		
		Db::query('INSERT INTO `riot_requests` SET '.
			'`timestamp` = NOW(), '.
			'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
			'`data` = "'.$apiUrl.'"'
		);
		
		$lastId = Db::lastId();
		
		$ch = curl_init();
		
        $curlOptions = array (
            CURLOPT_URL => $apiUrl,
            CURLOPT_FAILONERROR => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_POST => 0,
        );
        curl_setopt_array($ch,$curlOptions);		
		$response = curl_exec($ch); // run the whole process 
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //$error = curl_error($ch);
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

    public function paypalAPI($apiAdditionalData) {
        $startTime = microtime(true);
        
        /*if (_cfg('env') == 'dev') {
            $apiUrl = 'https://';
        }
        else {
            $apiUrl = 'https://';
        }*/
        $apiAdditionalData = 'oauth2/token';

        $apiUrl = 'https://api.sandbox.paypal.com/v1/';
        $apiUrl .= $apiAdditionalData; //payments/payment
        
        Db::query('INSERT INTO `paypal_requests` SET '.
            '`timestamp` = NOW(), '.
            '`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
            '`data` = "'.$apiUrl.'"'
        );
        
        $lastId = Db::lastId();
        
        $ch = curl_init();
        
        $curlOptions = array (
            CURLOPT_URL => $apiUrl,
            CURLOPT_FAILONERROR => 1,
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_SSL_VERIFYHOST => true,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => array(
                'USER' => 'max.orlovsky-facilitator_api1.gmail.com',
                'PWD' => '1362986413',
                'SIGNATURE' => 'At39XbOSsGMwp5m60oESMpE0iOJ4A86HKcWlXKX4PAhhC7EAvBS3oWJQ',
            ),
        );

        curl_setopt_array($ch,$curlOptions);        
        $response = curl_exec($ch); // run the whole process 
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //$error = curl_error($ch);
        curl_close($ch);

        dump($http_status);
        ddump($response);
        
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
    public function sendMail($email, $subject, $msg) {
    	if(!_cfg('smtpMailName') || !_cfg('smtpMailPass') || _cfg('env') == 'dev') {
            return false;
        }
        
        // Connecting
        $transport = Swift_SmtpTransport::newInstance(_cfg('smtpMailHost'), _cfg('smtpMailPort'));
        $transport->setUsername(_cfg('smtpMailName'));
        $transport->setPassword(_cfg('smtpMailPass'));
        
        $message = Swift_Message::newInstance()
        // Give the message a subject
        ->setSubject($subject)
        // Set the From address with an associative array
        ->setFrom(array(_cfg('smtpMailName') => _cfg('smtpMailFrom')))
        // Set the To addresses with an associative array
        ->setTo(array($email))
        // Give it a body
        ->setBody($msg, 'text/html');
        // Optionally add any attachments
        //->attach(Swift_Attachment::fromPath('my-document.pdf'))
        
        //Sending message
        $mailer = Swift_Mailer::newInstance($transport);
        $mailer->send($message, $fails);
        
        if($fails) {
            $_SESSION['mailError'] = $fails;
            return false;
        }
        
        return true;
    }
    
    public function parseText($text) {
        $text = strip_tags($text); //just in case, never know how those fuckers can hack you
        
        $text = str_replace(
            array("\n", "\r"),
            array('<br />', '<br />'),
            $text
        );
        
        $search = array(
            '/\*\*(.*?)\*\*/i',
            '/\*(.*?)\*/i',
            '/\~~(.*?)\~~/i',
            '/\[q](.*?)\[\/q]/i',
            //'/\[(.+?)\]\((.*?)\)/i'
        );
        $replace = array(
            '<b>$1</b>',
            '<i>$1</i>',
            '<s>$1</s>',
            '<blockquote>$1</blockquote>',
            //'<a href="$2" target="_blank">$1</a>'
        );
        $text = preg_replace($search, $replace, $text);
        
        //link regex
        $text = preg_replace_callback(
            '~\[(.*?)]\(([^)]+)\)~',
            function($a) {
                $urlText = ($a[1]?$a[1]:$a[2]);
                $urlText = (strlen($urlText)>50?substr($urlText, 0, 47).'...':$urlText);
                return '<a href="'.$a[2].'" target="_blank" title="'.$a[2].'">'.$urlText.'</a>';
            },
            $text
        );
        
        return $text;
    }
    
    public function convertTime($date, $format = 'd M Y, h:i A') {
        if ($this->data->user->timestyle == 1) {
            $format = 'd M Y, H:i';
        }

        if (!is_numeric($date)) {
            $date = strtotime($date) + _cfg('timeDifference');
        }
        
        if (isset($this->data->user->timezone) && $this->data->user->timezone) {
            if (intval($this->data->user->timezone) > 0) {
                $sign = '+';
            }
            else {
                $timezone = str_replace('-', '', $timezone);
            }
            $return = date($format, $date + $this->data->user->timezone).' (GMT'.$sign.($this->data->user->timezone/3600).')';
        }
        else {
            $return = date($format, $date).' (GMT-0)';
        }
        
        return $return;
    }
    
    public function getAboutTime($interval) {
        if ($interval->y) return $interval->y.' '.t('year_ago');
        else if ($interval->m) return $interval->m.' '.t('months_ago');
        else if ($interval->d) return $interval->d.' '.t('days_ago');
        else if ($interval->h) return $interval->h.' '.t('hours_ago');
        else if ($interval->i) return $interval->i.' '.t('minutes_ago');
        else return $interval->s.' '.t('seconds_ago');
    }

    public function convertDivision($division) {
        if ($division == 'I') return 1;
        else if ($division == 'II') return 2;
        else if ($division == 'III') return 3;
        else if ($division == 'IV') return 4;
        return 5;
    }
    
    public function getCache($key) {
        if ($this->apcEnabled === false) {
            return false;
        }
        
        $resouse = false;
        $data = apc_fetch($key, $resouse);
        return $resouse ? $data : null;
    }
    
    public function setCache($key, $data) {
        if ($this->apcEnabled === false) {
            return false;
        }
        
        return apc_store($key, $data, $this->cacheTtl);
    }

    public function deleteCache($key) {
        if ($this->apcEnabled === false) {
            return false;
        }
        
        return (apc_exists($key)) ? apc_delete($key) : true;
    }
    
    /*Protected functions*/
    protected function loadClasses() {
    	require_once _cfg('cmsclasses').'/db.php';
        require_once _cfg('classes').'/achievements.php';
    	require_once _cfg('classes').'/ajax.php';
        require_once _cfg('classes').'/cron.php';
    	require_once _cfg('classes').'/template.php';
        require_once _cfg('classes').'/social.php';
        require_once _cfg('classes').'/user.php';
    }
    
    protected function getStrings() {
        global $str;
        
        $str = $this->getCache('strings');
        if (is_array($str)) {
            return true;
        }
        
        $rows = Db::fetchRows('SELECT `key`, `'._cfg('fullLanguage').'` AS `value` FROM `tm_strings`');
        if ($rows) {
        	foreach($rows as $v) {
        		$str[$v->key] = $v->value;
        	}
        }
        
        $this->setCache('strings', $str);
        
        return true;
    }
    
    /*Private functions*/
    private function checkGetData() {
        global $cfg;
        
        $availableLanguages = array();
        $fetchingFullLanguage = array();
        $languageRows = Db::fetchRows('SELECT `title`, `flag` FROM `tm_languages`');
        if ($languageRows) {
            foreach($languageRows as $v) {
                $availableLanguages[] = $v->flag;
                $fetchingFullLanguage[$v->flag] = $v->title;
            }
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
                    $cronClass->tournamentsOpenReg();
                    $cronClass->finalizeTournament();
                    $cronClass->sendNotifications();
                    //$cronClass->checkDotaGames();
                    $cronClass->sqlCleanUp();
                    
                    //Others functions without SQL
                    $cronClass->cleanImagesTmp();
                }
                //skillz specific notifications
                else if ($_GET['val1'] == 'notificationzskillz' && $_GET['val2'] === _cfg('cronjob')) {
                    set_time_limit(300);
                    $cronClass = new Cron();
                    $cronClass->sendNotificationsSkillz();
                }
                else if ($_GET['val1'] == 'euw' && $_GET['val2'] === _cfg('cronjob')) {
                    set_time_limit(300);
                    $cronClass = new Cron();
                    $cronClass->checkLolGames('euw');
                }
                else if ($_GET['val1'] == 'eune' && $_GET['val2'] === _cfg('cronjob')) {
                    set_time_limit(300);
                    $cronClass = new Cron();
                    $cronClass->checkLolGames('eune');
                }
                else if ($_GET['val1'] == 'riotcode') {
                    set_time_limit(300);
                    $cronClass = new Cron();
                    $cronClass->checkLolGames('eune');
                }
                else if ($_GET['val1'] == 'na' && $_GET['val2'] === _cfg('cronjob')) {
                    set_time_limit(300);
                    $cronClass = new Cron();
                    $cronClass->checkSmiteGames('na');
                }
                else if ($_GET['val1'] == 'eu' && $_GET['val2'] === _cfg('cronjob')) {
                    set_time_limit(300);
                    $cronClass = new Cron();
                    $cronClass->checkSmiteGames('eu');
                }
                else if ($_GET['val1'] == 'streams' && $_GET['val2'] === _cfg('cronjob')) {
                    set_time_limit(120);
                    $cronClass = new Cron();
                    $cronClass->updateStreamers();
                }
                else if ($_GET['val1'] == 'matches' && $_GET['val2'] === _cfg('cronjob')) {
                    set_time_limit(60);
                    $cronClass = new Cron();
                    $cronClass->updateChallongeMatches();
                }
                else if ($_GET['val1'] == 'summoners' && $_GET['val2'] === _cfg('cronjob')) {
                    set_time_limit(60);
                    $cronClass = new Cron();
                    $cronClass->updateSummoners();
                }
                else if ($_GET['val1'] == 'emails' && $_GET['val2'] === _cfg('cronjob')) {
                    set_time_limit(0);
                    $cronClass = new Cron();
                    $cronClass->emailSender();
                }
                else if ($_GET['val1'] == 'social' && strlen($_GET['val2']) == 2) {
                    unset($_SESSION['errors']);
                    
                    $social = new Social();
                    $answer = $social->Verify($_GET['val2']);
                    
                    if ($answer === false) {
                        header('Location: '._cfg('href').'/profile/error');
                        exit();
                    }
                    
                    if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
                        header('Location: '.$_SERVER['HTTP_REFERER']);
                    }
                    else {
                        header('Location: '._cfg('href').'/profile');
                    }
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