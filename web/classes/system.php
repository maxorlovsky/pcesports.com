<?php

class System
{
    public $data;
    public $user;
    public $logged_in;
    public $links;
    protected $userClass;
    
    public function __construct() {
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
        
        //$this->data->settings = array();
        
        $data = array_merge($_GET, $_POST, $_SESSION);
         
        if (!isset($data['val1'])) {
        	$data['val1'] = false;
        }
        if (!isset($data['token'])) {
        	$data['token'] = false;
        }
        
        $rows = Db::fetchRows('SELECT * FROM `tm_settings` '.
        	'WHERE `setting` LIKE "site_%"'
        );
        if ($rows) {
        	foreach($rows as $v) {
        		$this->data->settings[$v->setting] = $v->value;
        	}
        }
        
        $rows = Db::fetchRows('SELECT * FROM `tm_links` '.
        		'ORDER BY `position`'
        );
        
        if ($rows) {
        	$this->data->links = $rows;
        }
        
        $this->logged_in = 0;
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
    
    public function sendMail($email, $subject, $msg) {
    	if(!_cfg('smtpMailName') || !_cfg('smtpMailPass')) return false;
    	
        $mailData = 'Date: '.date('D, d M Y H:i:s')." UT\r\n";
        $mailData .= 'Subject: =?UTF-8?B?'.base64_encode($subject). "=?=\r\n";
        $mailData .= 'Reply-To: '._cfg('smtpMailName'). "\r\n";
        $mailData .= 'MIME-Version: 1.0'."\r\n";
        $mailData .= 'Content-Type: text/html; charset="UTF-8"'."\r\n";
        $mailData .= 'Content-Transfer-Encoding: 8bit'."\r\n";
        $mailData .= 'From: "'._cfg('smtpMailFrom').'" <'._cfg('smtpMailName').'>'."\r\n";
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
    	require_once _cfg('classes').'/template.php';
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
        global $str;
        
        $rows = Db::fetchRows('SELECT `key`, `english` AS `value` FROM `tm_strings`');
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
            if ( isset( $_GET['cronjob'] ) ) {
                if ( $_GET['cronjob'] !== _cfg('cronjob') ) {
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
    
        $availableLanguages = array();
        $rows = Db::fetchRows('SELECT `flag` FROM `tm_languages`');
        foreach($rows as $v) {
        	$availableLanguages[] = $v->flag;
        }
        
        //Setting - Languages
        if (!isset($_GET['language']) || !$_GET['language'] || !in_array($_GET['language'], $availableLanguages)) {
            $cfg['language'] = 'en';
        }
        else {
        	$cfg['language'] = $_GET['language'];
        }
        $cfg['href'] = str_replace('%lang%', $cfg['language'], $cfg['href']);
        $cfg['hssite'] = $cfg['href'].'/hearthstone';
        $cfg['lolsite'] = $cfg['href'].'/league-of-legends';

        return true;
    }
}