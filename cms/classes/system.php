<?php

class System
{
    public $data;
    public $user;
    public $logged_in;
    public $messages;
    public $page;
    public $language;
    public $defaultPage;
    public $subPageOpen;
    protected $userClass;
    
    public function __construct($status = 1) {
    	$this->loadClasses();
    	
    	if ($status != 1) {
    		//return false;
    	}

        //Making a connection
        Db::connect();
        
        $this->data = new stdClass();
        $this->userClass = new User();
        $this->defaultPage = 'dashboard';
        
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
        
        $this->data->cmsSettings = array();
        $this->data->settings = array();
        $this->data->pages = array();
        $this->data->subpages = array();
        
        $data = array_merge($_GET, $_POST, $_SESSION);
         
        if (!isset($data['val1'])) {
        	$data['val1'] = false;
        }
        if (!isset($data['token'])) {
        	$data['token'] = false;
        }
        
        $rows = Db::fetchRows('SELECT * FROM `themagescms`');
        if ($rows) {
        	foreach($rows as $v) {
        		$this->data->cmsSettings[$v->setting] = $v->value;
        	}
        }
        
        $rows = Db::fetchRows('SELECT * FROM `tm_settings` '.
            'WHERE `type` = "level" OR `setting` LIKE "site_%" '.
        	'ORDER BY `setting` = "dashboard" DESC'
        );
        if ($rows) {
        	foreach($rows as $v) {
        		$this->data->settings[$v->setting] = $v->value;
        	}
        	 
        	foreach($this->data->settings as $k => $v) {
        		if (substr($k,0,4)!='site') {
        			$this->data->pages[] = array($k, at($k), $v);
                }
        	}
        }
        
        //Loggin in admin
        if (isset($data['submit_login']) && $data['submit_login'] && !$data['token']) {
            $this->logged_in = 0;
            $result = $this->userClass->login($data);
            if (!is_object($result) && substr($result,0,1) == 0) {
                $this->data->login_error = 1;
                $breakDown = explode(';', $result);
                $this->messages['login_error'] = $breakDown[1];
                $this->log('Error login as <b>'.$data['login'].'</b>', array('module'=>'login', 'type'=>'fail'));
            }
            else if ($result !== false) {
                $this->logged_in = 1;
                $this->user = $result;
            	$this->log('Success login as <b>'.$data['login'].'</b>', array('module'=>'login', 'type'=>'success'));
                go(_cfg('cmssite').'/');
            }
        }
        
        //If there is a token then probably user is already logged in
        if ($data['token'] && !isset($this->logged_in)) {
            $this->user = $this->userClass->fetchUserByToken($data['token']);
            if ($this->user !== false) {
                $this->logged_in = 1;
            }
            else {
            	$this->cleanData();
            }
        }
        else {
        	$this->logged_in = 0;
        }
        
        if (isset($this->logged_in) && $this->logged_in) {
        	/*if ($data['val1']) {
        		$this->page = $data['val1'];
        	}
        	else {
        		$this->page = 'dashboard';
        	}*/
        	
        	$this->data->modules = Db::fetchRows('SELECT * FROM `tm_modules`');
        	
        	$this->language = $this->user->language;
        }
        else {
            $this->language = 'en';
        }
    }
    
    public function ajax($data) {
    	$this->checkGetData();
    	$this->getStrings();
    	
        $ajax = new Ajax();
        $ajax->ajaxRun($data);
    }
    
    public function cleanData() {
    	$this->log('Exit', array('module'=>'logout'));
    	unset($_SESSION['token']);
    	$this->logged_in = 0;
    	$this->user = array();
    	go(_cfg('site').'/admin');
    }
    
    public static function errorMail($desc, $cls, $line, $fulldesc) {
        $timestamp = strftime('%Y-%m-%d %H:%M:%S %Z');
        
        /*$this->sendMail(
            _cfg('adminEmail'),
            'Error '.$desc,
            "Summary: $desc\n" .
            "Time: $timestamp\n" . 
            "Source: $cls:$line\n" .
            'IP: '.$_SERVER['REMOTE_ADDR']."\n" .
            $fulldesc
        );*/
    }
    
    public function sendMail($email, $subject, $msg) {
    	if(!_cfg('smtpMailName') || !_cfg('smtpMailPass')) return false;
    	
        $mailData = 'Date: '.date('D, d M Y H:i:s')." UT\r\n";
        $mailData .= 'Subject: =?UTF-8?B?'.base64_encode($subject). "=?=\r\n";
        $mailData .= 'Reply-To: '._cfg('smtpMailFrom'). "\r\n";
        $mailData .= 'MIME-Version: 1.0'."\r\n";
        $mailData .= 'Content-Type: text/html; charset="UTF-8"'."\r\n";
        $mailData .= 'Content-Transfer-Encoding: 8bit'."\r\n";
        $mailData .= 'From: "'._cfg('smtpMailFrom').'" <'._cfg('smtpMailFrom').'>'."\r\n";
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
    
    public function runAPI($apiAdditionalData) {
    	$startTime = microtime(true);
    	
    	$apiData = array(
    		'api_url' 		=> _cfg('apiUrl'),
    		'api_username'  => _cfg('apiUsername'),
    		'api_password'  => _cfg('apiPassword'),
    	);
    	
    	$apiArray = array_merge($apiData, $apiAdditionalData);
    	
    	Db::query('INSERT INTO `tm_api_request` SET '.
    		'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
    		'`request_data` = "'.Db::escape( json_encode($apiArray) ).'"'
    	);
    	
    	$ch = curl_init();
    	$curlOptions = array (
    		CURLOPT_URL => _cfg('apiUrl'),
    		CURLOPT_FAILONERROR => 0,
    		CURLOPT_TIMEOUT => 5, //5s
    		CURLOPT_CONNECTTIMEOUT => 30,
    		CURLOPT_VERBOSE => 1,
    		CURLOPT_SSL_VERIFYPEER => 0,
    		CURLOPT_SSL_VERIFYHOST => FALSE,
    		CURLOPT_POST => 1,
    		CURLOPT_POSTFIELDS => $apiArray,
    		CURLOPT_RETURNTRANSFER => 1,
    	);
    	curl_setopt_array($ch, $curlOptions);
    	$response = curl_exec($ch); // run the whole process
    	
    	if ( $response ) {
    		$rspdata = &$response;
    	}
    	else {
    		$rspdata = curl_error($ch);
    	}
    	
    	curl_close($ch);
    	
    	$endTime = microtime(true);
    	$duration = $endTime - $startTime; //calculates total time taken
    	
    	//---
    	Db::query('UPDATE `tm_api_request` SET '.
    		'`response_data` = "'.Db::escape( $rspdata ).'", '.
    		'`call_time` = '.(float)$duration.' '.
    		' WHERE `id` = '.Db::lastId()
    	);
    	
    	return $rspdata;
    }
    
    public function log($text, $type = array()) {
    	if (isset($this->user->id)) {
    		$userId = $this->user->id;
    	}
    	else {
    		$userId = 0;
    	}
    	
    	if (!isset($type['module'])) {
    		$type['module'] = '';
    	}
    	
    	if (!isset($type['type'])) {
    		$type['type'] = '';
    	}
    	
    	Db::query('INSERT INTO `tm_logs` '.
	    	'SET '.
    		'`module` = "'.Db::escape(strtolower($type['module'])).'", '.
	    	'`type` = "'.Db::escape($type['type']).'", '.
	    	'`user_id` = '.(int)$userId.', '.
	    	'`date` = NOW(), '.
	    	'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
	    	'`info` = "'.Db::escape($text).'"'
    	);
    	
    	if (_cfg('logs') == 1) {
    		//If enabled, sending external logs
    		$array = array(
    			'control' => 'log',
    			'module' => strtolower($type['module']),
    			'type' => $type['type'],
    			'user_id' => $userId,
    			'ip' => $_SERVER['REMOTE_ADDR'],
    			'info' => $text,
    		);
    		$this->runAPI($array);
    	}
    }
    
    /*Protected functions*/
    protected function loadClasses() {
        $directory = _cfg('cmsclasses');
    
        if (!file_exists($directory) && !is_dir($directory)) {
            exit('Directory does not exists');
        }

        $handler = opendir($directory);
        $ignoreFiles = array('system.php', '.svn');
        while($file = readdir($handler)) {
            //Checking if not hidden files
            if ($file != "." && $file != "..") {
                //Checking if file ignoring is required
                if(!in_array($file, $ignoreFiles)) {
                    require_once $directory.'/'.$file;
                }
            }
        }
        closedir($handler);
    }
    
    protected function serverParse($socket, $response, $line = __LINE__) {
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
        global $astr;
        
        if ($this->language) {
        	require_once(_cfg('cmslocale').'/'.$this->language.'.php');
        }
        else {
        	require_once(_cfg('cmslocale').'/'._cfg('defaultLanguage').'.php');
        }
    }
    
    /*Private functions*/
    private function checkGetData() {
        global $cfg;
    
        if (isset($_GET['language']) && $_GET['language'] == 'run') { //Special RUN command
            if ( isset( $_GET['cronjob'] ) ) {
                if ( $_GET['cronjob'] !== _cfg('cronjob') )
                {
                    die('Invalid secret');
                }
    
                $cronClass = new Cron();
                /*$cronClass->cleanSessions();*/
            }
            else {
                exit('Run command error');
            }
    
            exit();
        }
    
        //Setting - Languages
        if (!isset($_GET['language']) || !$_GET['language'] || !in_array($_GET['language'], _cfg('allowedLanguages'))) {
            $cfg['language'] = $cfg['defaultLanguage'];
            $cfg['href'] = $cfg['site'].'/'.$cfg['language'].'/';
        }
        else {
            $cfg['href'] .= $cfg['site'].'/'.$_GET['language'].'/';
            $cfg['language'] = $_GET['language'];
        }
    
        return true;
    }
}