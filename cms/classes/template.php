<?php
class Template extends System
{
    public $cmsModules;
    
    public function __construct() {
        parent::__construct();
        
        $this->cmsModules = array(
            'dashboard',
            'accounts',
            'languages',
            'pages',
            'strings',
            'settings',
        	'links',
        	'logs',
        );
    }
    
    public function parse() {
        $this->getMainTemplate('head');
        
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
        include _cfg('cmstemplate').'/'.$page.'.tpl';
    }
    
    public function loadModule($data) {
        if (!$data['page']) {
    		go(_cfg('cmssite').'/#'.$this->defaultPage);
    	}
        
        if (isset($this->data->settings[$data['page']]) && $this->user->level < $this->data->settings[$data['page']] && $this->user->level != 0) {
            return at('denied_access_level');
        }
        else if ($this->user->level == 0 && 
                $this->user->custom_access->setting->$data['page'] != 1 &&
                $this->user->custom_access->module->$data['page'] != 1) {
                return at('denied_access_level');
        }
        
        $data['system'] = $this;
        
        if (in_array($data['page'], $this->cmsModules)) {
            $answer = $this->loadCmsModule($data);
        }
        else {
            $answer = $this->loadCustomModule($data);
        }
        
        return $answer;
    }
    
    private function loadCmsModule($data) {
        $className = ucfirst($data['page']);
        
        if (!isset($data['var1']) || !$data['var1']) {
            $data['var1'] = 'index';
        }
        
        if (file_exists(_cfg('cmsmodules').'/cms-modules/'.$data['page'].'/source.php')) {
            require_once _cfg('cmsmodules').'/cms-modules/'.$data['page'].'/source.php';
            
            $module = new $className($data);;
        }
        else {
            echo '<p>Source file for module '.$data['page'].'/source.php not found</p>';
        }
        
        if (file_exists(_cfg('cmsmodules').'/cms-modules/'.$data['page'].'/templates/'.$data['var1'].'.tpl')) {
            require_once _cfg('cmsmodules').'/cms-modules/'.$data['page'].'/templates/'.$data['var1'].'.tpl';
        }
        else {
            echo '<p>Template file for module '.$data['page'].'/'.$data['var1'].'.tpl not found</p>';
        }
    }
    
    private function loadCustomModule($data) {
        foreach($this->data->modules as $v) {
            if ($v->name == $data['page'] && $this->user->level < $v->level && $this->user->level != 0) {
                return at('denied_access_level');
            }
            else if ($this->user->level == 0 && 
                $this->user->custom_access->setting->$data['page'] != 1 &&
                $this->user->custom_access->module->$data['page'] != 1) {
                return at('denied_access_level');
            }
        }
        
    	$className = ucfirst($data['page']);
    	
    	if (!isset($data['var1']) || !$data['var1']) {
    		$data['var1'] = 'index';
    	}
    	
    	if (file_exists(_cfg('cmsmodules').'/'.$data['page'].'/source.php')) {
    		require_once _cfg('cmsmodules').'/'.$data['page'].'/source.php';
    	
    		$module = new $className($data);
    	}
    	else {
    		echo '<p>Source file for custom module '.$data['page'].'/source.php not found</p>';
    	}
    	;
    	if (file_exists(_cfg('cmsmodules').'/'.$data['page'].'/templates/'.$data['var1'].'.tpl')) {
    		require_once _cfg('cmsmodules').'/'.$data['page'].'/templates/'.$data['var1'].'.tpl';
    	}
    	else {
    		echo '<p>Template file for custom module '.$data['page'].'/'.$data['var1'].'.tpl not found</p>';
    	}
    }
}