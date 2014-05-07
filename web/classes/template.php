<?php
class Template extends System
{
    public $title = '';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function parse() {
    	$this->getSeo();
    	
    	$this->getMainTemplate('head');
    	
    	$this->loadPage($this);
    	
        $this->getMainTemplate('sidebar');
        $this->getMainTemplate('footer');
        
        return true;
    }
    
    public function getSeo() {
    	
    	if (file_exists(_cfg('pages').'/'.$this->page.'/source.php')) {
    		require_once _cfg('pages').'/'.$this->page.'/source.php';

    		$seoPage = new $this->page();
    		if (method_exists($seoPage, 'getSeo')) {
    			$seoPageData = $seoPage::getSeo();
    		}
    	}
    	
    	if ($this->page != 'home') {
    		if (isset($seoPageData)) {
    			$title = str_replace('-', ' ', ucfirst($seoPageData->title));
    		}
    		else {
    			$title = str_replace('-', ' ', ucfirst($this->page));
    		}
    		$this->title .= $title;
    		$this->title .= ' | ';
    	}
    	
    }
    
    public function loadPage($data) {
    	/*if (isset($this->data->settings[$data['page']]) && $this->user->level < $this->data->settings[$data['page']]) {
    		return at('denied_access_level');
    	}*/

   		if (file_exists(_cfg('pages').'/'.$data->page.'/source.php')) {
   			require_once _cfg('pages').'/'.$data->page.'/source.php';
   			
   			$page = new $data->page($data);
   			$page->showTemplate();
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