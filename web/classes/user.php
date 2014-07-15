<?php
class User extends System
{
    function __construct() {
		
	}
    
    public static function socialLogin($user) {
        if(!isset($user['social']) || !isset($user['social_uid'])) {
            return false;
        }
        
    	$row = Db::fetchRow('SELECT `u`.`id` AS `id`, `s`.`id` AS `sid`, `s`.`social_uid` AS `uid`, `u`.* '.
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
            if ($_SESSION['user'] && $row->id) {
                $u = new self;
                return $u->socialConnect($user);
            }
            else {
                $u = new self;
                return $u->socialRegister($user);
            }
        }
        else {
        	$_SESSION['user'] = (array)$row;
        }
        
        return true;
    }
    
    private function socialRegister($data) {
    	$social = $data['social'];
    	unset($data['social']);
        
        if (trim($data['email'])) {
            $row = Db::fetchRow(
                'SELECT * FROM `users` '.
                'WHERE `email` = "'.Db::escape($data['email']).'"'
            );
            
            if($row!==false || !empty($row)) {
                $_SESSION['errors'][] = 'Email provided from this social network is already taken. Please contact us if this shouldn\'t happen or don\'t provide email';
                return false;
            }
        }
    	
    	$user = $this->register($data);
    	if($user===false) {
    		return false;
    	}

    	Db::query(
            'INSERT INTO `users_social` SET
            `social` = "'.Db::escape($social).'",
            `social_uid` = "'.Db::escape($data['social_uid']).'",
            `user_id` = '.$user->id
        );
        
        //Getting fresh registered data
        $row = Db::fetchRow('SELECT `u`.`id` AS `id`, `s`.`id` AS `sid`, `s`.`social_uid` AS `uid`, `u`.* '.
            'FROM `users` AS `u` '.
            'LEFT JOIN `users_social` AS `s` ON `s`.`user_id` = `u`.`id` '.
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
        
        //Email not required!
        /*$row = Db::fetchRow('SELECT * FROM `users` WHERE `email` = "'.Db::escape($data['email']).'"');
        if ($row) {
            $_SESSION['errors'][] = 'Email already registered! If you used different social network, just connect it.';
            return false;
        }*/

        Db::query('INSERT INTO `users` SET '.
            '`name` = "'.Db::escape($data['name']).'", '.
            '`email` = "'.Db::escape($data['email']).'", '.
            '`password` = "social" '
        );
        $uid = Db::lastId();
        
        return $this->getUser($uid);
    }
    
    public function getUser($id) {
    	$row = Db::fetchRow('SELECT * FROM `users` WHERE `id` = '.(int)$id);
        if ($row) {
            return $row;
        }
        
        $_SESSION['errors'][] = 'User not exist  ('.__LINE__.')';
        
        return '0;User not exist';
    }
    
    public static function checkUser($user) {
        $userRow = Db::fetchRow(
            'SELECT * FROM `users` '.
            'WHERE `id` = '.(int)$user['id'].' AND '.
            '`email` = "'.Db::escape($user['email']).'" '
        );
        
        if (!$userRow) {
            return false;
        }
        
        $userRow->socials = Db::fetchRows(
            'SELECT * FROM `users_social` '.
            'WHERE `user_id` = '.$userRow->id
        );
        
        return $userRow;
    }
    
    public static function logOut() {
        unset($_SESSION['user']);
        session_destroy();
        
        return true;
    }
    
    /*private function socialConnect($data) {
        Db::query(
            'INSERT INTO `social` SET '.
            '`social` = "'.Db::escape($data['social']).'", '.
            '`social_uid` = "'.Db::escape($data['social_uid']).'", '.
            '`user_id` = '.(int)$_SESSION['user']['id']
        );
        Db::query(
            'UPDATE `users_data` SET '.
            '`social_'.Db::escape($data['social']).'` = 1 WHERE'.
            '`user_id` = '.(int)$_SESSION['user']['id']
        );
        
        exit('<script>window.opener.location.reload(false); window.close()</script>');
        
        return true;
    }
    
    public static function socialDisconnect($data) {
        $u = new self;
        $user = $u->checkUser();
        
        if (!$user->id) {
            return _('not_logged_in');
        }
        
        $row = Db::fetchRow('SELECT COUNT(`id`) AS `count` FROM `social` WHERE `user_id` = '.(int)$user->id);
        
        if ($row->count <= 1) {
        $err = 0;
            //User trying to delete last socials, checking if mail/pass is set
            $msg = _('trying_delete_last_social');
            
            $row = Db::fetchRow('SELECT `password`, `email` FROM `users` WHERE `id` = '.(int)$user->id);
            if (!trim($row->email)) { //if email is not set, it must, because it's a login itself
                $err = 1;
                $msg .= '<br />'._('please_set_email');
            }
            
            if (substr($row->password,0,6) == 'social') { //if password is not set, it must, because it's required to login
                $err = 1;
                $msg .= '<br />'._('please_set_password');
            }
            
            if ($err == 1) {
                return $msg;
            }
        }
        
        Db::query(
            'DELETE FROM `social` WHERE '.
            '`social` = "'.Db::escape($data['provider']).'" AND '.
            '`user_id` = '.(int)$user->id
        );
        Db::query(
            'UPDATE `users_data` SET '.
            '`social_'.Db::escape($data['provider']).'` = 0 WHERE'.
            '`user_id` = '.(int)$user->id
        );
        
        return true;
    }*/
}