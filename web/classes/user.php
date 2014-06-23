<?php
class User extends System
{
    function __construct() {
		
	}
    
    public static function socialLogin($user) {
        if(!isset($user['social']) || !isset($user['social_uid'])) {
            return false;
        }
    	/*
    	$row = Db::fetchRow('SELECT `s`.`id` AS `sid`, `s`.`social_uid` AS `uid`, `u`.* '.
            'FROM `social` AS `s` '.
            'LEFT JOIN `users` AS `u` ON `s`.`user_id` = `u`.`id` '.
            'WHERE `s`.`social` = "'.Db::escape($user['social']).'" AND '.
            '`s`.`social_uid` = "'.Db::escape($user['social_uid']).'" '
        );
        
        if($err = Db::error()) {
        	if(_cfg('env')=='dev') {
                return $err;
            }
            return false;
        }
        
        if ($row === false || !isset($row->id)) {
        	if(isset($user['email'])) {
        		return $this->socialRegister($user);
        	}
            
        	return array('social_register'=>$user['social']);
        }
        else {
        	$_SESSION['user']  = (array)$row;
        	header('Location: '._cfg('site').'/'._cfg('language'));
        	exit();
        }*/
    }
    
    private function socialRegister($data) {
    	$social = $data['social'];
    	unset($data['social']);
    	
    	$user = $this->register($data);
    	if($user!==true) {
    		return $user;
    	}
    	
    	$row = Db::fetchRow('
    				SELECT * FROM users 
    		         WHERE email="'.Db::escape($data['email']).'"
    		           AND password="'.Db::escape('social_'.$social).'"');
    	
    	if($row==false || empty($row)) return json_encode(array('error'=>array('main'=>'auth error '.__LINE__)));
    	
    	Db::query('
        		INSERT INTO social SET
        		social="'.Db::escape($social).'",
        		social_uid="'.Db::escape($data['social_uid']).'",
        		user_id='.$row->id);
    	
    	$_SESSION['user'] = (array)$row;
    	return 1;
    }
}