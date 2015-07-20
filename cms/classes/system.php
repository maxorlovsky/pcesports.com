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
    
    public function __construct($status = 1) {
    	$this->loadClasses();

        //Run only once!
        if ($status != 1) {
    		//Making a connection
            Db::connect();
    	}
        
        //As soon as DB class is enabled, checking https staru
        $row = Db::fetchRow('SELECT `value` FROM `tm_settings` WHERE `setting` = "https" LIMIT 1');
        //Checking if https always enabled and if user is on http, then redirecting to https
        dump($row->value);
        dump(extension_loaded('openssl'));
        dump($_SERVER);
        if ($row->value == 1 && extension_loaded('openssl')
            && ( (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') || (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https') )
            ) {
            go(str_replace('http', 'https', _cfg('cmssite')));
        }

        if (!$this->data) {
            $this->data = new stdClass();
        }
        $this->defaultPage = 'dashboard';
        $this->fetchParams($status);
    }
    
    public function run() {
        $this->checkGetData();
        $this->getStrings();
        
        $template = new Template();
        $template->parse();
    }
    
    public function fetchParams($status) {
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

        //Checking if upload allowed, if yes checking if folder have permissions
        if (_cfg('allowUpload') == 1 && substr(sprintf('%o', fileperms(_cfg('uploads'))), -3) != '777') {
            $cfg['allowUpload'] = 0;
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
        
        //If there is a token then probably user is already logged in
        if ($data['token'] && !isset($this->logged_in)) {
            $this->user = User::fetchUserByToken($data['token']);
            if ($this->user !== false) {
                $this->logged_in = 1;
            }
            else {
            	User::logout();
            }
        }
        else {
        	$this->logged_in = 0;
        }
        
        if (isset($this->logged_in) && $this->logged_in) {
        	$row = Db::fetchRows('SELECT * FROM `tm_modules`');
            $updatedModulesList = new stdClass();
            foreach($row as $k => $v) {
                $v->displayName = preg_replace('/(?<!\ )[A-Z]/', ' $0', ucfirst($v->name));
                $updatedModulesList->$k = $v;
            }
            $this->data->modules = $updatedModulesList;
        	
        	$this->language = $this->user->language;
        }
        else {
            $this->language = 'en';
        }
        
        //Loggin in admin
        if (isset($data['submit_login']) && $data['submit_login'] && !$data['token'] && $status == 1) {
            $this->logged_in = 0;
            $result = User::login($data);
            
            if (!is_object($result) && substr($result,0,1) == 0) {
                $this->data->login_error = 1;
                $breakDown = explode(';', $result);
                $this->messages['login_error'] = $breakDown[1];
                $this->log('Error login as <b>'.$data['login'].'</b>, ('.$this->messages['login_error'].')', array('module'=>'login', 'type'=>'fail'));
            }
            else if ($result !== false) {
                $this->logged_in = 1;
                $this->user = $result;
            	$this->log('Success login as <b>'.$data['login'].'</b>', array('module'=>'login', 'type'=>'success'));
                go(_cfg('cmssite').'/');
            }
        }
    }
    
    public function ajax($data) {
    	$this->checkGetData();
    	$this->getStrings();
    	
        $ajax = new Ajax();
        $ajax->ajaxRun($data);
    }
    
    public function cleanData() {
    	unset($_SESSION['token'], $_SESSION['recaptcha_login']);
    	$this->logged_in = 0;
    	$this->user = array();
    	go(_cfg('site').'/admin');
    }
    
    public static function errorMail($desc, $cls, $line, $fulldesc) {
        $timestamp = strftime('%Y-%m-%d %H:%M:%S %Z');
        
        $this->sendMail(
            _cfg('adminEmail'),
            'Error '.$desc,
            "Summary: $desc\n" .
            "Time: $timestamp\n" . 
            "Source: $cls:$line\n" .
            'IP: '.$_SERVER['REMOTE_ADDR']."\n" .
            $fulldesc
        );
    }
    
    //@email - Send TO
    //@subject - Subject of email
    //@msg - Body of message (can be html)
    //@file - array, optional, attachment to email, required full link, data in array
    //@file['name'] - name of the file with extension
    //@file['content'] - plain text or plain html, it will be converted into attachment
    public function sendMail($email, $subject, $msg) {
        if(!_cfg('smtpMailName') || !_cfg('smtpMailPass')) {
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
    		'`module` = "'.Db::escape_tags(strtolower($type['module'])).'", '.
	    	'`type` = "'.Db::escape_tags($type['type']).'", '.
	    	'`user_id` = '.intval($userId).', '.
	    	'`date` = NOW(), '.
	    	'`ip` = "'.Db::escape_tags($_SERVER['REMOTE_ADDR']).'", '.
	    	'`info` = "'.Db::escape($text).'"'
    	);
    	
    	if (_cfg('logs') == 1) {
    		//If enabled, sending external logs
    		$array = array(
    			'control' => 'log',
    			'module' => Db::escape_tags(strtolower($type['module'])),
    			'type' => Db::escape_tags($type['type']),
    			'user_id' => intval($userId),
    			'ip' => Db::escape_tags($_SERVER['REMOTE_ADDR']),
    			'info' => Db::escape($text, '<b>'),
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
    
        if (isset($_GET['language']) && isset($_GET['val1']) && $_GET['val1'] == 'run') { //Special RUN command
            if ( isset( $_GET['val2'] ) ) {
                if ( $_GET['val2'] !== _cfg('cronjob') )
                {
                    die('Invalid secret');
                }
                
                set_time_limit(60);
    
                $cronClass = new Cron();
                //SQL involved functions
                $cronClass->sqlCleanUp();
                    
                //Others functions without SQL
                //$cronClass->cleanImagesTmp();
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