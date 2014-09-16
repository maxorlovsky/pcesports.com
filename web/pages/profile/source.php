<?php

class profile extends System
{
    public $additional;
    
	public function __construct($params = array()) {
		parent::__construct();
        
        if ($this->logged_in == 0 && $_GET['val2'] != 'error') {
            header('Location: '._cfg('site'));
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
            '-720' => 'GMT-12',
            '-660' => 'GMT-11',
            '-600' => 'GMT-10',
            '-540' => 'GMT-9',
            '-480' => 'GMT-8',
            '-420' => 'GMT-7',
            '-360' => 'GMT-6',
            '-300' => 'GMT-5',
            '-240' => 'GMT-4',
            '-180' => 'GMT-3',
            '-120' => 'GMT-2',
            '-60' => 'GMT-1',
            '0' => 'GMT-0',
            '60' => 'GMT+1',
            '120' => 'GMT+2',
            '180' => 'GMT+3',
            '240' => 'GMT+4',
            '300' => 'GMT+5',
            '360' => 'GMT+6',
            '420' => 'GMT+7',
            '480' => 'GMT+8',
            '540' => 'GMT+9',
            '600' => 'GMT+10',
            '660' => 'GMT+11',
            '720' => 'GMT+12',
        );
        
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
    
    protected function streamersList() {
        $return = new stdClass();
        
        $return->streams = Db::fetchRows(
            'SELECT * FROM `streams` '.
            'WHERE `user_id` = '.(int)$this->data->user->id
        );
        
        return $return;
    }
}