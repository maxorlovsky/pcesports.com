<?php

class System
{
    public $data;
    public $page;
    public $user;
    public $logged_in;
    public $links;
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
        //echo strtotime('02.06.2014 10:00:00');
        $this->serverTimes = array(
        	array(
        		'id'	=> 4,
        		'name' 	=> 'League of Legends',
        		'status'=> t('registration_open'),
        		'time' 	=> '1401692400',
        	),
            array(
            	'id'	=> 4,
            	'name' 	=> 'League of Legends',
            	'status'=> t('start'),
            	'time' 	=> '1402729200',
       		),
        );
        
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
                }
                else if ($_GET['val1'] == 'emails') {
                    /*$rows = Db::fetchRows('SELECT `email` FROM `teams` WHERE `game` = "hs" AND `tournament_id` = 4 AND `approved` = 1');
                    foreach($rows as $v) {
                        Db::query('INSERT IGNORE INTO `subscribe` SET '.
                            '`email` = "'.Db::escape($v->email).'", '.
                            '`unsublink` = "'.sha1(Db::escape($v->email).rand(0,9999).time()).'"'
                        );
                    }*/
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
        $cfg['lolsite'] = $cfg['href'].'/league-of-legends';

        return true;
    }
}