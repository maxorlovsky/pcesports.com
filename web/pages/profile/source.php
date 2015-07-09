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

    /*
    Function from AJAX class
    */
    public function verifySummoner($data) {
        if (!$this->logged_in) {
            return '0;'.t('error');
        }
        
        $row = Db::fetchRow(
            'SELECT `id`, `region`, `summoner_id`, `masteries` FROM `summoners` WHERE '.
            '`id` = '.(int)$data['id'].' AND '.
            '`user_id`  = '.(int)$this->data->user->id.' '.
            'LIMIT 1 '
        );
        
        if ($row == false) {
            return '0;'.t('summoner_not_found');
        }
        
        $response = $this->runRiotAPI('/'.$row->region.'/v1.4/summoner/'.(int)$row->summoner_id.'/masteries', $row->region);
        foreach($response->pages as $v) {
            if (trim($v->name) == trim($row->masteries)) {
                Db::fetchRow(
                    'UPDATE `summoners` SET '.
                    '`approved` = 1, '.
                    '`masteries` = "" '.
                    'WHERE `id` = '.(int)$row->id.' AND '.
                    '`user_id`  = '.(int)$this->data->user->id.' '.
                    'LIMIT 1 '
                );
                Achievements::give(30);//Summoner of Legends (Add summoner account to your profile)
                return '1;'.$row->id;
            }
        }
        
        return '0;'.str_replace('%code%', $row->masteries, t('mastery_page_not_found'));
    }

    public function removeSummoner($data) {
        if (!$this->logged_in) {
            return '0;'.t('not_logged_in');
        }
        
        $q = Db::query(
            'SELECT * FROM `summoners` WHERE '.
            '`id` = '.(int)$data['id'].' AND '.
            '`user_id`  = '.(int)$this->data->user->id.' '
        );
        
        if ($q->num_rows == 0) {
            return '0;Error';
        }
        
        Db::query(
            'DELETE FROM `summoners` WHERE '.
            '`id` = '.(int)$data['id'].' AND '.
            '`user_id`  = '.(int)$this->data->user->id.' '.
            'LIMIT 1 '
        );
        
        return '1;1';
    }

    public function addSummoner($data) {
        if (!$this->logged_in) {
            return '0;'.t('not_logged_in');
        }
        
        $summoner = new stdClass();
        $name = $data['name'];
        $region = $data['region'];
        
        if (!$name) {
            return '0;'.t('input_name');
        }
        else if (!$region) {
            return '0;Set region';
        }
        $response = $this->runRiotAPI('/'.$region.'/v1.4/summoner/by-name/'.rawurlencode(htmlspecialchars($name)), $region);
        
        if ($response == 404 || !$response) {
            return '0;'.t('summoner_not_found').$response;
        }
        
        $summoner->summoner_id = (int)$response->id;
        $summoner->name = Db::escape($response->name);
        $summoner->region = Db::escape($region);
        
        $summoner->verificationCode = 'PC'.strtoupper(substr(md5(time().$response->name.$response->id), 1, 8));
        
        Db::query(
            'INSERT INTO `summoners` SET '.
            '`user_id`  = '.(int)$this->data->user->id.', '.
            '`region` = "'.$summoner->region.'", '.
            '`summoner_id` = '.$summoner->summoner_id.', '.
            '`name` = "'.$summoner->name.'", '.
            '`masteries` = "'.$summoner->verificationCode.'" '
        );
        
        $summoner->id = Db::lastId();
        
        foreach(_cfg('lolRegions') as $k => $v) {
            if ($k == $region) {
                $summoner->regionName = $v;
            }
        }
        
        return '1;'.json_encode($summoner);
    }
}