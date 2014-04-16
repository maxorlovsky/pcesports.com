<?php
class Ajax extends System
{
    public function __construct() {
        parent::__construct();
    }
    
    private $allowed_ajax_methods = array(
		'showPage',
    	'exit',
    	'cleanData',
    	'setLanguage',
		'setEmail',
        'submitForm',
    	'saveSetting',
	);
	
    public function ajaxRun($data) {
    	//If not logged_in, not allowing anything!
    	if (!$this->logged_in) {
    		$this->cleanData();
    		return false;
    	}
    	
        $controller = $data['control'];
        
        //If exit, exit!
        if (isset($data['page']) && $data['page'] == '#exit') {
        	$this->cleanData();
        	return true;
        }
		
		if ( in_array( $controller, $this->allowed_ajax_methods ) ) {
            echo $this->$controller($data);
            return true;
        }
        else {
            echo '0;'.at('controller_not_exists');
            return false;
        }
    }
    
    protected function saveSetting($data) {
    	$data['param'] = str_replace('setting-','',$data['param']);
    	$q = Db::query('SELECT * FROM `tm_settings` WHERE `setting` = "'.Db::escape($data['param']).'" LIMIT 1');
    	if ($q->num_rows == 0) {
    		return '0;Setting not found';
    	}
    	
    	Db::query('UPDATE `tm_settings` '.
    		'SET `value` = "'.Db::escape($data['value']).'" '.
    		'WHERE `setting` = "'.Db::escape($data['param']).'" '.
    		'LIMIT 1'
    	);
    	
    	return '1;Setting updated successfully';
    }
    
    protected function setEmail($data) {
    	$email = trim($data['mail']);
    	if (!$email) {
    		$this->log('Email change incorrect <b>'.at('email_empty').'</b>', array('module'=>'dashboard', 'type'=>'email'));
    		return '0;'.at('email_empty');
    	}
    	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    		$this->log('Email change incorrect <b>'.at('email_incorrect').'</b>', array('module'=>'dashboard', 'type'=>'email'));
    		return '0;'.at('email_incorrect');
    	}
    	 
    	Db::query('UPDATE `tm_admins`'.
    	'SET `email` = "'.Db::escape($email).'"'.
    	'WHERE `id` = "'.$this->user->id.'"'.
    	'LIMIT 1');
    	
    	$this->log('Changes email to <b>'.$email.'</b>', array('module'=>'dashboard', 'type'=>'email'));
    	 
    	return '1;'.at('admin_email_success');
    }
    
    protected function setLanguage($data) {
    	if (trim($data['lang'])=='') {
    		$data['lang']='en';
    	}
    	
    	Db::query('UPDATE `tm_admins`'.
    	'SET `language` = "'.Db::escape($data['lang']).'"'.
    	'WHERE `id` = "'.$this->user->id.'"'.
    	'LIMIT 1');
    	
    	$this->log('Changes language to <b>'.$data['lang'].'</b>', array('module'=>'dashboard', 'type'=>'language'));
    	
    	return '1;'.at('admin_language_success');
    }
    
    protected function submitForm($data) {
        $className = ucfirst($data['module']);
        
        $data['system'] = $this;
        
        if (file_exists(_cfg('cmsmodules').'/cms-modules/'.$data['module'].'/source.php')) {
            require_once _cfg('cmsmodules').'/cms-modules/'.$data['module'].'/source.php';
            
            $module = new $className($data);
        }
        else {
            return '0;<p>Source file for module '.$data['module'].'/source.php not found</p>';
        }
        
        if (method_exists($module, $data['action'])) {
            return $module->$data['action']($data['form']);
        }
        else {
            return '0;Method <u>'.$data['action'].'</u> in class <u>'.$className.'</u> does not exist';
        }
    }
    
    private function showPage($data) {
        $breakdown = explode('/', $data['page']);
        $data['page'] = substr($breakdown[0], 1);
        
        $templateClass = new Template();
        $html = $templateClass->loadModule($data);
        
		return $html;
    }
}
