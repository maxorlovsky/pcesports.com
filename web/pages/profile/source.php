<?php

class profile extends System
{
	public function __construct($params = array()) {
		parent::__construct();
        
        if ($this->logged_in == 0 && $_GET['val2'] != 'error') {
            header('Location: '._cfg('site'));
            exit();
        }
	}
    
    public function errorPage() {
		$messages = $_SESSION['errors'];
		include_once _cfg('pages').'/'.get_class().'/error.tpl';
	}
    
	public function getProfilePage() {
		if ($_SESSION['registration'] == 1) {
            unset($_SESSION['registration']);
            $regComplete = 1;
        }
        
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
			$this->getProfilePage();
		}
	}
}