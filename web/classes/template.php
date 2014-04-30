<?php
class Template extends System
{
    
    public function __construct() {
        parent::__construct();
    }
    
    public function parse() {
    	$this->getMainTemplate('head');
    	
    	$this->loadPage($this);
    	
        $this->getMainTemplate('sidebar');
        $this->getMainTemplate('footer');
        
        return true;
    }
    
    public function loadPage($data) {
    	/*if (!$data['page']) {
    		$data['page'] = '404';
    	}
    
    	if (isset($this->data->settings[$data['page']]) && $this->user->level < $this->data->settings[$data['page']]) {
    		return at('denied_access_level');
    	}*/

    	$data->system = $this;
   		if (file_exists(_cfg('pages').'/'.$data->page.'/source.php')) {
   			require_once _cfg('pages').'/'.$data->page.'/source.php';
   			 
   			$page = new $data->page($data);
   		}
   		else if (file_exists(_cfg('pages').'/404/source.php')) {
   			require_once _cfg('pages').'/404/source.php';
   			
   			$page = new errorPage($data);
   		}
   		else {
   			echo '<p>Source file for page '.$data->page.'/source.php not found</p>';
   		}
   		
    	return $page;
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