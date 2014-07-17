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
            $u = new self;
            if ($_SESSION['user'] && $_SESSION['user']['id']) {
                return $u->socialConnect($user);
            }
            else {
                $u = self;
                return $u->socialRegister($user);
            }
        }
        else {
            if ($_SESSION['user'] && $_SESSION['user']['id']) {
                exit(
                    '<script>'.
                    'alert(\''.str_replace('%social%', $user['social'], t('social_network_already_connected')).'\');'.
                    'window.close();'.
                    '</script>');
            }
            else {
                $_SESSION['user'] = (array)$row;
            }
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
                $_SESSION['errors'][] = t('email_soc_already_taken');
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
    	
        $_SESSION['registration'] = 1;
    	$_SESSION['user'] = (array)$row;
        
    	return true;
    }
    
    private function register($data) {
    	if(isset($data['social']) && $data['social']!='') {
    		if(!isset($_SESSION['social']) || !isset($_SESSION['social'][$data['social']])) {
                $_SESSION['errors'][] = t('session_expired');
    			return false;
    		}
    		
    		$s = new Social();
    		return $s->Verify($data['social']);
    	}
        
        $regName = array(
            'name'          => $data['name'],
            'originalName'  => $data['name'],
        );
        $data['name'] = $this->checkRegName($regName);
        
        Db::query('INSERT INTO `users` SET '.
            '`name` = "'.Db::escape($data['name']).'", '.
            '`email` = "'.Db::escape($data['email']).'", '.
            '`password` = "social" '
        );
        $uid = Db::lastId();
        
        return $this->getUser($uid);
    }
    
    private function checkRegName($regName, $i = 2) {
        $row = Db::fetchRow('SELECT * FROM `users` WHERE `name` = "'.Db::escape($regName['name']).'"');
        if ($row) {
            $regName['name'] = $regName['originalName'].$i;
            $returnName = $this->checkRegName($regName, $i+1);
        }
        else {
            $returnName = $regName['name'];
        }
        
        return $returnName;
    }
    
    public function getUser($id) {
    	$row = Db::fetchRow('SELECT * FROM `users` WHERE `id` = '.(int)$id);
        if ($row) {
            return $row;
        }
        
        $_SESSION['errors'][] = t('user_not_exist').' ('.__LINE__.')';
        
        return '0;'.t('user_not_exist');
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
        $userRow->socials->connected = array();
        foreach($userRow->socials as $v) {
            if ($v->social) {
                $userRow->socials->connected[] = $v->social;
            }
        }
        
        return $userRow;
    }
    
    public static function logOut() {
        unset($_SESSION['user']);
        session_destroy();
        
        return true;
    }
    
    private function socialConnect($data) {
        Db::query(
            'INSERT INTO `users_social` SET
            `social` = "'.Db::escape($data['social']).'",
            `social_uid` = "'.Db::escape($data['social_uid']).'",
            `user_id` = '.(int)$_SESSION['user']['id']
        );
        
        exit('<script>window.opener.location.reload(false); window.close()</script>');
        
        return true;
    }
    
    public static function socialDisconnect($data) {
        $user = self::checkUser($_SESSION['user']);
        
        if (!$user->id) {
            return t('not_logged_in');
        }
        
        $row = Db::fetchRow('SELECT COUNT(`id`) AS `count` FROM `users_social` WHERE `user_id` = '.(int)$user->id);
        
        if ($row->count <= 1) {
            return t('trying_to_delete_last_social');
        }
        
        Db::query(
            'DELETE FROM `users_social` WHERE '.
            '`social` = "'.Db::escape($data['provider']).'" AND '.
            '`user_id` = '.(int)$user->id
        );
        
        return true;
    }
    
    public static function updateProfile($form) {
        $user = self::checkUser($_SESSION['user']);
        
        if (!$user->id) {
            return t('not_logged_in');
        }
        
        if (!trim($form['name'])) {
            return '0;'.t('name_empty');
        }
        
        $row = Db::fetchRow('SELECT `name` FROM `users` WHERE `name` = "'.Db::escape($form['name']).'" LIMIT 1');
        if ($row && $row->name != $user->name) {
            return '0;'.t('name_taken');
        }
        else if (strlen(trim($form['name'])) > 20) {
            return '0;'.t('name_too_long');
        }
        
        $row = Db::fetchRow('SELECT `email` FROM `users` WHERE `email` = "'.Db::escape($form['email']).'" LIMIT 1');
        if ($row && $row->email != $user->email) {
            return '0;'.t('email_taken');
        }
        else if(!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            return '0;'.t('email_invalid');
        }
        
        Db::query(
            'UPDATE `users` SET '.
            '`name` = "'.Db::escape($form['name']).'", '.
            '`email` = "'.Db::escape($form['email']).'" '.
            'WHERE `id` = '.(int)$user->id
        );
        
        //Getting fresh updated data
        $row = Db::fetchRow('SELECT `u`.`id` AS `id`, `s`.`id` AS `sid`, `s`.`social_uid` AS `uid`, `u`.* '.
            'FROM `users` AS `u` '.
            'LEFT JOIN `users_social` AS `s` ON `s`.`user_id` = `u`.`id` '.
            'WHERE `u`.`id` = '.(int)$user->id.' '
        );
    	
    	$_SESSION['user'] = (array)$row;
        
        return '1;'.t('success_profile_update');
    }
}