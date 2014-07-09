<?php
class User extends System
{
    function __construct() {
		
	}
    
    public static function socialLogin($user) {
        if(!isset($user['social']) || !isset($user['social_uid'])) {
            return false;
        }
        
    	$row = Db::fetchRow('SELECT `s`.`id` AS `sid`, `s`.`social_uid` AS `uid`, `u`.* '.
            'FROM `users_social` AS `s` '.
            'LEFT JOIN `users` AS `u` ON `s`.`user_id` = `u`.`id` '.
            'WHERE `s`.`social` = "'.Db::escape($user['social']).'" AND '.
            '`s`.`social_uid` = "'.Db::escape($user['social_uid']).'" '
        );
        
        if($err = Db::error()) {
            $_SESSION['errors'][] = 'DB error ('.__LINE__.')';
            return false;
        }
        
        //Not reggistered, registering
        if ($row === false || !isset($row->id)) {
            $u = new self;
            return $u->socialRegister($user);
        }
        else {
        	$_SESSION['user'] = (array)$row;
        }
        
        return true;
    }
    
    private function socialRegister($data) {
    	$social = $data['social'];
    	unset($data['social']);
    	
    	$user = $this->register($data);
    	if($user===false) {
    		return false;
    	}

    	$row = Db::fetchRow('SELECT * FROM `users` '.
            'WHERE `email` = "'.Db::escape($data['email']).'" AND '.
            '`password` = "'.Db::escape('social_'.$social).'" '
        );
    	
    	if($row==false || empty($row)) {
            $_SESSION['errors'][] = 'Authorization error ('.__LINE__.')';
            return false;
        }
    	
    	Db::query('INSERT INTO `users_social` SET
            `social` = "'.Db::escape($social).'",
            `social_uid` = "'.Db::escape($data['social_uid']).'",
            `user_id` = '.$user->id
        );
        
        $row = Db::fetchRow('SELECT `s`.`id` AS `sid`, `s`.`social_uid` AS `uid`, `u`.* '.
            'FROM `users_social` AS `s` '.
            'LEFT JOIN `users` AS `u` ON `s`.`user_id` = `u`.`id` '.
            'WHERE `u`.`id` = '.(int)$user->id.' '
        );
    	
    	$_SESSION['user'] = (array)$row;
        
    	return true;
    }
    
    private function register($data) {
    	if(isset($data['social']) && $data['social']!='') {
    		if(!isset($_SESSION['social']) || !isset($_SESSION['social'][$data['social']])) {
                $_SESSION['errors'][] = 'Session expired! Retry!';
    			return false;
    		}
    		
    		$s = new Social();
    		return $s->Verify($data['social']);
    	}
        
        $row = Db::fetchRow('SELECT * FROM `users` WHERE `email` = "'.Db::escape($data['email']).'"');
        if ($row) {
            $_SESSION['errors'][] = 'Email already registered! If you used different social network, just connect it.';
            return false;
        }

        Db::query('INSERT INTO `users` SET '.
            '`name`="'.Db::escape($data['name']).'", '.
            '`email`="'.Db::escape($data['email']).'", '.
            '`password`="'.Db::escape($data['password']).'" '
        );
        $uid = Db::lastId();
        
        return $this->getUser($uid);
    }
    
    public function getUser($id) {
    	$row = Db::fetchRow('SELECT `id`, `email`, `name` FROM `users` WHERE `id` = '.(int)$id);
        if ($row) {
            return $row;
        }
        
        $_SESSION['errors'][] = 'User not exist  ('.__LINE__.')';
        
        return '0;User not exist';
    }
    
    public static function checkUser($user) {
        $row = Db::fetchRow('SELECT * FROM `users` '.
            'WHERE `id` = '.(int)$user['sid'].' AND '.
            '`email` = "'.Db::escape($user['email']).'" AND '.
            '`password` = "'.Db::escape($user['password']).'" '
        );
        
        if (!$row) {
            return false;
        }
        
        return $row;
    }
    
    public static function logOut() {
        unset($_SESSION['user']);
        
        return true;
    }
}