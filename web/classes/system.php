<?php

class System
{
    public $data;
    public $page;
    public $user;
    public $logged_in;
    public $links;
    public $boards;
    public $comments;
    public $serverTimes = array();
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
        
        $this->apcEnabled = extension_loaded('apcu');
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
        
        if (isset($_SESSION['user']['token']) || isset($_COOKIE['uid']) && $_COOKIE['uid'] && isset($_COOKIE['token']) && $_COOKIE['token']) {
            $checkUser = User::checkUser();
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
        
        if (!isset($this->data->settings) || !$this->data->settings) {
            $this->data->settings = array();
            $rows = Db::fetchRows('SELECT * FROM `tm_settings`');
            if ($rows) {
                foreach($rows as $v) {
                    $this->data->settings[$v->setting] = $v->value;
                }
            }
        }
        
        if (!isset($this->data->links) || !$this->data->links) {
            $this->data->links = array();

            if ($this->getCache('main-menu')) {
                $this->data->links = $this->getCache('main-menu');
            }
            else {
                $apiMenu = $this->wpApiCall('pce-api/menu');
                
                if ($apiMenu) {
                    foreach($apiMenu as $k => $v) {
                        if ($v['menu_item_parent'] == 0) {
                            $this->data->links[$v['ID']] = array(
                                'title'         => $v['title'],
                                'url'           => str_replace('http://', '', $v['url']),
                                'css_classes'   => implode(' ', $v['classes']),
                                'target'        => $v['target'],
                                'slug'          => $v['post_name'],
                                'sublinks'      => array(),
                            );
                        }
                        else {
                            $this->data->links[$v['menu_item_parent']]['sublinks'][$v['ID']] = array(
                                'title'         => $v['title'],
                                'url'           => str_replace('http://', '', $v['url']),
                                'css_classes'   => $v['classes'],
                                'target'        => $v['target'],
                                'slug'          => $v['post_name'],
                            );
                        }
                    }
                }

                $this->setCache('main-menu', $this->data->links);
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

        //Setting class for body for specific mood
        $this->data->mood = '';

        //Winter/Chrismas/New year mood
        $day = date('d', time());
        $month = date('m', time());
        if (($month == 1 && $day <= 15) || ($month == 12 && $day >= 15)) {
            $this->data->mood = 'winter';
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

    public function wpApiCall($path) {
        $apiUrl = _cfg('site').'/wp/wp-json/';
        $apiUrl .= $path;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl); // set url to post to
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // times out after 4s
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 0); // set POST method
        $response = curl_exec($ch); // run the whole process 
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
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
    		' `ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'", '.
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
			'`ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'", '.
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
    
    public function convertTime($date, $format = 'd M Y, h:i A', $hint = 0) {
        if ($this->data->user->timestyle == 1) {
            $format = 'd M Y, H:i';
        }

        if (!is_numeric($date)) {
            $date = strtotime($date);
        }
        
        $breakdown = explode(',', $format);
        if (isset($breakdown[1]) && $breakdown[1]) {
            $cuttedFormat = $breakdown[1];
        }
        else {
            $cuttedFormat = $format;
        }
        $message = date(trim($cuttedFormat), $date + _cfg('timeDifference')).' (in UK/Portugal)<br />';
        $message .= date(trim($cuttedFormat), $date + 3600 + _cfg('timeDifference')).' (in Germany/Spain/Poland)<br />';
        $message .= date(trim($cuttedFormat), $date + 7200 + _cfg('timeDifference')).' (in Latvia/Bulgaria)<br />';
        if (_cfg('timeDifference') !== 0) {
            $message .= '<i>Daylight Summer Time change already included</i>';
        }
        
        $return = date($format, $date).' (UTC)';
        if ($hint == 1) {
            $return .= '<br />'.$message;
        }
        else {
            $return .= ' <span class="hint timezone-hint" attr-msg="'.$message.'">(?)</span>';
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
        $data = apcu_fetch($key, $resouse);
        return $resouse ? $data : null;
    }
    
    public function setCache($key, $data) {
        if ($this->apcEnabled === false) {
            return false;
        }
        
        return apcu_store($key, $data, $this->cacheTtl);
    }

    public function deleteCache($key) {
        if ($this->apcEnabled === false) {
            return false;
        }
        
        return (apcu_exists($key)) ? apcu_delete($key) : true;
    }
    
    public function errorMessage($error) {
        if (is_array($error)) {
            header('HTTP/1.1 400 Bad request', true, 400);
            return $error;
        }
        else {
            header('HTTP/1.1 400 Bad request('.$error.')', true, 400);
            return array('status' => 400, 'message' => $error);
        }
    }
    public function errorLogin() {
        header('HTTP/1.1 401 '.t('authorization_error'), true, 401);
        return array('status' => 401, 'message' => t('authorization_error'));
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
        
        //Setting - Languages
        $_GET['language'] = 'en';
       	$cfg['language'] = 'en';
        $cfg['fullLanguage'] = 'english';
        
        $this->getStrings();
        
        $cfg['href'] = str_replace('%lang%', '', $cfg['href']);
        $cfg['hssite'] = $cfg['href'].'/hearthstone';
        $cfg['lolsite'] = $cfg['href'].'/leagueoflegends';
    
        if (isset($_GET['language']) && $_GET['language'] == 'run') { //Special RUN command
            if (isset($_GET['val1'])) {
                if ($_GET['val1'] === _cfg('cronjob')) {
                    set_time_limit(300);
                    $cronClass = new Cron();
                    
                    //SQL involved functions
                    $cronClass->tournamentsOpenReg();
                    $cronClass->tournamentStatusUpdate();
                    $cronClass->sqlCleanUp();
                    
                    //Others functions without SQL
                    $cronClass->cleanImagesTmp();
                }
                else if ($_GET['val1'] == 'streams' && $_GET['val2'] === _cfg('cronjob')) {
                    set_time_limit(120);
                    $cronClass = new Cron();
                    $cronClass->updateStreamers();
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
                else if ($_GET['val1'] == 'hslist' && $_GET['val2'] === 'haosdi012') {
                    $ajax = new Ajax();
                    echo $ajax->getHsList();
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
                else if ($_GET['val1'] == 'registration' && $_GET['val2']) {
                    unset($_SESSION['errors']);
                    
                    $user = new User();
                    $answer = $user->completeRegistration($_GET['val2']);
                    
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
                else if ($_GET['val1'] == 'email-change' && $_GET['val2']) {
                    unset($_SESSION['errors']);
                    
                    $user = new User();
                    $answer = $user->completeMailChange($_GET['val2']);
                    
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