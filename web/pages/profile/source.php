<?php

class profile extends System
{
    public $additional;
    
	public function __construct($params = array()) {
		parent::__construct();
        
        if ($this->logged_in == 0 && $_GET['val2'] != 'error') {
            go(_cfg('site'));
            exit();
        }
	}
    
    public function errorPage() {
        if (!isset($_SESSION['errors']) || !$_SESSION['errors']) {
            go(_cfg('site'));
            exit();
        }
        $messages = $_SESSION['errors'];
        unset($_SESSION['errors']);
		include_once _cfg('pages').'/'.get_class().'/error.tpl';
	}
    
    public function getProfileAdditionalPage($page) {
        if (!file_exists(_cfg('pages').'/'.get_class().'/'.$page.'.tpl')) {
            return false;
        }
        
        if ($page == 'streamers-list') {
            $this->additional = $this->streamersList();
        }
        else if ($page == 'summoners') {
            $this->additional = $this->summonersList();
        }
        
        include_once _cfg('pages').'/'.get_class().'/'.$page.'.tpl';
        
        return true;
    }
    
	public function getProfilePage() {
		if ($_SESSION['registration'] == 1) {
            unset($_SESSION['registration']);
            $regComplete = 1;
        }
        
        $userTimeZone = $this->data->user->timezone / 60;
        if ($this->data->user->timezone && $userTimeZone <= 720 && $userTimeZone >= -720) {
            $pickedTimezone = $userTimeZone;
        }
        else {
            $pickedTimezone = 0;
        }
        
        $timezoneSelector = array(
            '-720' => 'GMT-12 International Date Line West',
            '-660' => 'GMT-11 Midway Island, Samoa',
            '-600' => 'GMT-10 Hawaii',
            '-540' => 'GMT-9 Alaska',
            '-480' => 'GMT-8 Pacific Time (US & Canada), Tijuana, Baja California',
            '-420' => 'GMT-7 Arizona, Mountain Time (US & Canada), Chihuahua, La Paz, Mazatlan',
            '-360' => 'GMT-6 Central America, Central Time (US & Canada)',
            '-300' => 'GMT-5 Eastern Time (US & Canada), Indiana (East)',
            '-240' => 'GMT-4 Atlantic Time (Canada)',
            '-180' => 'GMT-3 Greenland',
            '-120' => 'GMT-2 Mid-Atlantic',
            '-60' => 'GMT-1 Cape Verde Is.',
            '0' => 'GMT-0/UTC Greenwich Mean Time, London, Dublin',
            '60' => 'GMT+1 Amsterdam, Berlin, Rome, Stockholm, Vienna',
            '120' => 'GMT+2 Riga, Tallinn, Vilnius, Athens, Bucharest, Istanbul',
            '180' => 'GMT+3 Moscow, St. Petersburg, Volgograd',
            '240' => 'GMT+4 Baku, Yerevan',
            '300' => 'GMT+5 Yekaterinburg, Tashkent',
            '360' => 'GMT+6 Almaty, Novosibirsk, Astana',
            '420' => 'GMT+7 Bangkok, Krasnoyarsk',
            '480' => 'GMT+8 Hong Kong, Irkutsk, Taipei',
            '540' => 'GMT+9 Osaka, Tokyo, Seoul, Yakutsk',
            '600' => 'GMT+10 Brisbane, Melbourne, Sydney, Vladivostok',
            '660' => 'GMT+11 Magadan, New Caledonia',
            '720' => 'GMT+12 Auckland, Wellington',
        );
        
        $avatars = $this->getAvatarList();
        
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'Profile';
		
		return $seo;
	}
	
	public function showTemplate() {
		if (isset($_GET['val2']) && $_GET['val2'] == 'error') {
			$this->errorPage();
		}
		else {
            if (isset($_GET['val2']) && $_GET['val2'] != 'profile') {
                if ($this->getProfileAdditionalPage($_GET['val2']) === true) {
                    return true;
                }
            }
            
            $this->getProfilePage();
		}
	}
    
    protected function summonersList() {
        $return = new stdClass();
        
        $return->summoners = Db::fetchRows(
            'SELECT * FROM `summoners` '.
            'WHERE `user_id` = '.(int)$this->data->user->id.' '.
            'ORDER BY `id`, `approved` '
        );
        
        if ($return->summoners) {
            foreach($return->summoners as &$v) {
                foreach(_cfg('lolRegions') as $k => $lr) {
                    if ($k == $v->region) {
                        $v->regionName = $lr;
                    }
                }
            }
            unset($v);
        }
        
        return $return;
    }
    
    protected function getAvatarList() {
        $directory = _cfg('dir').'/static/images/avatar';
        $avatars = array();
        
        if (!file_exists($directory) && !is_dir($directory)) {
            exit('Directory does not exists');
        }
        
        $fileList = array();
        $handler = opendir($directory);
        $ignoreFiles = array('.svn');
        while($file = readdir($handler)) {
            //Checking if not hidden files
            if ($file != "." && $file != "..") {
                //Checking if file ignoring is required
                if(!in_array($file, $ignoreFiles)) {
                    $avatars[] = $file;
                }
            }
        }
        closedir($handler);
        
        return $avatars;
    }
    
    protected function streamersList() {
        $return = new stdClass();
        
        $return->streams = Db::fetchRows(
            'SELECT * FROM `streams` '.
            'WHERE `user_id` = '.(int)$this->data->user->id.' '
        );
        
        return $return;
    }
}