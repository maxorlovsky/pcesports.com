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
    		else if (t('web-page-'.$this->page) != 'web-page-'.$this->page) {
    			$title = str_replace('-', ' ', t('web-page-'.$this->page));
    		}
            else {
                $title = str_replace('-', ' ', t('web-link-'.$this->page));
            }
    		$this->title .= $title;
    		$this->title .= ' | ';
    	}
    	
    }
    
    public function getTxtPages() {
        $rows = Db::fetchRows('SELECT `link` FROM `tm_pages`');
        $pagesList = array();
        foreach($rows as $v) {
            $pagesList[] = $v->link;
        }

        return $pagesList;
    }
    
    public function loadPage($data) {
        $pagesList = $this->getTxtPages();
		
		if ($this->data->settings['maintenance'] == 1 && file_exists(_cfg('pages').'/maintenance/source.php')) {
			require_once _cfg('pages').'/maintenance/source.php';
   			
   			$page = new maintenance($data);
		}
        else if (in_array($data->page, $pagesList)) {
            echo $this->getTxtPage($data->page);
            
            $page = new stdClass();
        }
   		else if (file_exists(_cfg('pages').'/'.$data->page.'/source.php')) {
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
            echo '<p>Text page "'.$data->page.'" not found</p>';
   		}
   		
    	return $page;
    }
    
    public function getTxtPage() {
        $row = Db::fetchRow('SELECT `logged_in`, `value`, `text_'.Db::escape(_cfg('fullLanguage')).'` AS `text` FROM `tm_pages`');
        if ($row->logged_in == 1 && $this->logged_in || $row->logged_in == 0) {
            $html = file_get_contents(_cfg('template').'/page.tpl');
            $html = str_replace(array('%title%', '%text%'), array(t($row->value), $row->text), $html);
            return $html;
        }
        
        require_once _cfg('pages').'/404/source.php';
        return new errorPage($data);
    }
    
    static public function getMailTemplate($page) {
    	if (file_exists(_cfg('template').'/mails/'.$page.'.html')) {
    		return file_get_contents(_cfg('template').'/mails/'.$page.'.html');
    	}
    	
    	return false;
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