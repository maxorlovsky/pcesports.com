<?php
class Template extends System
{
    
    public function __construct() {
        parent::__construct();
    }
    
    public function parse() {
        $this->getMainTemplate('head');
        return true;
        if (!$this->logged_in) {
            $this->getMainTemplate('login');
        }
        else {
            $this->getMainTemplate('head-block');
        }
        
        $this->getMainTemplate('footer');
        
        return true;
    }
    
    public function getMainTemplate($page) {
    	if (file_exists(_cfg('template').'/'.$page.'.tpl')) {
        	include _cfg('template').'/'.$page.'.tpl';
    	}
    	else {
    		echo '<br />Template '.$page.' not found<br />';
    	}
    }
}