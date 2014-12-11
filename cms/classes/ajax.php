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
    	'submitForm',
    	'saveSetting',
        'updateProfile',
        'updateCMS',
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
            $this->log('Exit', array('module'=>'logout'));
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
    
    protected function updateCMS() {
        $params = array(
            'control' => 'update',
            'ver' => $this->data->cmsSettings['version'],
        );
        
        return $this->update($params);
    }
    
    protected function saveSetting($data) {
        if (substr($data['param'],0,6) == 'module') {
            $data['param'] = str_replace('module-','',$data['param']);
            $q = Db::query('SELECT * FROM `tm_modules` WHERE `name` = "'.Db::escape($data['param']).'" LIMIT 1');
            if ($q->num_rows == 0) {
                return '0;Module setting not found';
            }
            
            Db::query('UPDATE `tm_modules` '.
                'SET `level` = "'.Db::escape($data['value']).'" '.
                'WHERE `name` = "'.Db::escape($data['param']).'" '.
                'LIMIT 1'
            );
            
            return '1;Module setting updated successfully';
        }
        else {
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
    }
    
    protected function submitForm($data) {
        $className = ucfirst($data['module']);
        
        $data['system'] = $this;
        
        if (file_exists(_cfg('cmsmodules').'/cms-modules/'.$data['module'].'/source.php')) {
            require_once _cfg('cmsmodules').'/cms-modules/'.$data['module'].'/source.php';
            
            $module = new $className($data);
        }
        else if (file_exists(_cfg('cmsmodules').'/'.$data['module'].'/source.php')) {
        	require_once _cfg('cmsmodules').'/'.$data['module'].'/source.php';
        
        	$module = new $className($data);
        }
        else {
            return '0;<p>Source file for module '.$data['module'].'/source.php not found</p>';
        }
        
        if (method_exists($module, $data['action'])) {
        	if (!isset($data['form'])) {
        		$data['form'] = NULL;
        	}
            return $module->$data['action']($data['form']);
        }
        else {
            return '0;Method <u>'.$data['action'].'</u> in class <u>'.$className.'</u> does not exist';
        }
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
    	 
    	Db::query('UPDATE `tm_admins` '.
    	'SET `email` = "'.Db::escape($email).'" '.
    	'WHERE `id` = '.intval($this->user->id).' '.
    	'LIMIT 1');
    	
    	$this->log('Changes email to <b>'.$email.'</b>', array('module'=>'dashboard', 'type'=>'email'));
    	 
    	return '1;'.at('admin_email_success');
    }
    
    protected function updateProfile($data) {
    	$newPassword = trim($data['password']);
        $oldPassword = trim($data['currentPassword']);
        $newPassLen = strlen($newPassword);
        $email = trim($data['email']);
        $passwordChange = '';
        if (trim($data['lang'])=='') {
    		$data['lang']='en';
    	}
        
        $row = Db::fetchRow('SELECT `language` FROM `tm_admins` '.
            'WHERE `id` = '.intval($this->user->id).' AND '.
            '`password` = "'.sha1(Db::escape($oldPassword)._cfg('salt')).'" '.
            'LIMIT 1'
        );
        
        if (!$oldPassword) {
            $this->log('Admin profile update <b>'.at('current_password_empty').'</b>', array('module'=>'dashboard', 'type'=>'error'));
    		return '0;'.at('current_password_empty');
        }
        else if (!$row) {
            $this->log('Admin profile update <b>'.at('current_password_incorrect').'</b>', array('module'=>'dashboard', 'type'=>'error'));
    		return '0;'.at('current_password_incorrect');
        }
        else if (!$email) {
    		$this->log('Admin profile update <b>'.at('email_empty').'</b>', array('module'=>'dashboard', 'type'=>'error'));
    		return '0;'.at('email_empty');
    	}
    	else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    		$this->log('Admin profile update <b>'.at('email_incorrect').'</b> ('.$email.')', array('module'=>'dashboard', 'type'=>'error'));
    		return '0;'.at('email_incorrect');
    	}
        
        //Only checking if user trying to change password
        if ($newPassword) {
            if ($newPassLen < 6 || $newPassLen > 30) {
                $this->log('Admin profile update <b>'.at('password_incorrect').'</b>', array('module'=>'dashboard', 'type'=>'error'));
                return '0;'.at('password_incorrect');
            }
            $passwordChange = ', `password` = "'.sha1($newPassword._cfg('salt')).'" ';
        }
    	 
    	Db::query('UPDATE `tm_admins` SET '.
        '`language` = "'.Db::escape($data['lang']).'", '.
        '`email` = "'.Db::escape($email).'", '.
        '`editRedirect` = '.(int)$data['editRedirect'].' '.
    	$passwordChange.
    	'WHERE `id` = '.intval($this->user->id).' '.
    	'LIMIT 1');
    	
    	$this->log('Admin profile update', array('module'=>'dashboard', 'type'=>'success'));
        
        if ($row->language != $data['lang']) {
            $answer = '2';
        }
        else {
            $answer = '1';
        }

    	return $answer.';'.at('admin_profile_update_success');
    }
    
    private function showPage($data) {
        $breakdown = explode('/', $data['page']);
        $data['page'] = substr($breakdown[0], 1);
        
        $templateClass = new Template();
        $html = $templateClass->loadModule($data);
        
		return $html;
    }
}
