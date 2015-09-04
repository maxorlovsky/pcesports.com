<?php
class User extends System
{
    function __construct() {
		
	}
    
    public static function passwordConvert($password) {
        $salt = 'adoj29!@#!1dj019@*&#!2j';
        
        return sha1(base64_encode($password.$salt));
    }
    
    public static function login($email, $password) {
        $error = array();
        echo 'SELECT * '.
            'FROM `users` '.
            'WHERE `email` = "'.Db::escape($email).'" AND '.
            '`password` = "'.User::passwordConvert($password).'" '.
            'LIMIT 1';
        
        $row = Db::fetchRow('SELECT * '.
            'FROM `users` '.
            'WHERE `email` = "'.Db::escape($email).'" AND '.
            '`password` = "'.User::passwordConvert($password).'" '.
            'LIMIT 1'
        );
        
        if ($row) {
            $_SESSION['user'] = (array)$row;
            self::token();
            if ($_SESSION['user']['id']) {
                Achievements::giveLogin($_SESSION['user']['id']);//I see you for *th time!
            }
        }
        else {
            $s = new System();
            return $s->errorLogin();
        }
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
                self::token();
                if ($_SESSION['user']['id']) {
                    Achievements::giveLogin($_SESSION['user']['id']);//I see you for *th time!
                }
            }
        }
        
        return true;
    }
    
    public static function token() {
        if (!$_SESSION['user'] || !$_SESSION['user']['id']) {
            return false;
        }

        $_SESSION['user']['token'] = sha1(rand(0,9999).time());
        Db::query('DELETE FROM `users_auth` WHERE `user_id` = '.(int)$_SESSION['user']['id'].' LIMIT 1');
        Db::query('INSERT IGNORE INTO `users_auth` '.
            'SET '.
            '`user_id` = '.(int)$_SESSION['user']['id'].', '.
            '`token` = "'.Db::escape($_SESSION['user']['token']).'" '
        );
        
        $days = time()+7776000; //90 days
        setcookie('uid', $_SESSION['user']['id'], $days, '/', 'pcesports.com', false, true);
        setcookie('token', $_SESSION['user']['token'], $days, '/', 'pcesports.com', false, true);
        
        return true;
    }
    
    public static function tokenDestroy() {
        Db::query('DELETE FROM `users_auth` WHERE `user_id` = '.(int)$_COOKIE['uid'].' LIMIT 1');
        setcookie('uid', 0, time(), '/', 'pcesports.com', false, true);
        setcookie('token', 0, time(), '/', 'pcesports.com', false, true);
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
        
        User::token();
        
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
        
        $subscribeRow = Db::fetchRow(
            'SELECT * FROM `subscribe` WHERE '.
            '`email` = "'.Db::escape($data['email']).'" '
        );
        
        if (!$subscribeRow && $data['email']) {
            Db::query('INSERT INTO `subscribe` SET '.
                '`email` = "'.Db::escape($data['email']).'", '.
                '`unsublink` = "'.sha1(Db::escape($data['email']).rand(0,9999).time()).'"'
            );
        }
        
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
        $q = Db::query(
            'SELECT * FROM `users_auth` '.
            'WHERE `user_id` = '.(int)$_COOKIE['uid'].' AND '.
            '`token` = "'.Db::escape($_COOKIE['token']).'" '
        );

        if ($q->num_rows == 0) {
            return false;
        }
        
        $userRow = Db::fetchRow(
            'SELECT * FROM `users` '.
            'WHERE `id` = '.(int)$_COOKIE['uid'].' '
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
        
        $userRow->summoners = Db::fetchRows(
            'SELECT * FROM `summoners` '.
            'WHERE `user_id` = '.$userRow->id.' AND '.
            '`approved` = 1 '
        );
        
        //$userRow->timezone = $userRow->timezone * 60;

        if (!isset($_SESSION['participant']) && !$_SESSION['participant']) {
            //Check if user is participant
            $row = Db::fetchRow(
                'SELECT * '.
                'FROM `participants` '.
                'WHERE '.
                '`user_id` = '.$userRow->id.' AND '.
                '`ended` = 0 AND ' .
                '`deleted` = 0 AND '.
                '`approved` = 1 '.
                'LIMIT 1 '
            );
                
            if ($row) {
                $_SESSION['participant'] = $row;
                $userRow->participant = $row;
            }
        }
        
        if (!isset($_SESSION['user']) || !$_SESSION['user']) {
            //Getting fresh updated data (stupid way, but must do it)
            $row = Db::fetchRow('SELECT `u`.`id` AS `id`, `us`.`id` AS `sid`, `us`.`social_uid` AS `uid`, `u`.* '.
                'FROM `users` AS `u` '.
                'LEFT JOIN `users_social` AS `us` ON `us`.`user_id` = `u`.`id` '.
                'WHERE `u`.`id` = '.(int)$userRow->id.' '
            );
            
            $_SESSION['user'] = (array)$row;
        }

        //Fetching all available achievements
        $userRow->achievements = Db::fetchRows('SELECT * FROM `users_achievements` WHERE `user_id` = '.$userRow->id);
        
        return $userRow;
    }
    
    public static function logOut() {
        self::tokenDestroy();
        unset($_SESSION['user'], $_SESSION['participant'], $_COOKIE['uid'], $_COOKIE['token']);
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

        $socialAchievements = array(
            'fb' => 2, //Facebook connection achievement
            'tw' => 3, //Twitter achievement
            'vk' => 4, //VK achievement
            'gp' => 5, //Google+ achievement
            'tc' => 6, //Twitch achievement
            'bn' => 7, //Battle.Net achievement
        );

        Achievements::give($socialAchievements[$data['social']]); //Connect social network

        $count = Db::fetchRow('SELECT COUNT(*) AS `count` FROM `users_social` WHERE `user_id` = '.(int)$_SESSION['user']['id']);
        if ($count->count == count($socialAchievements)) {
            Achievements::give(8);//I am very social now!
        }
        
        exit('<script>window.opener.location = "'.str_replace('https', 'http', _cfg('href')).'/profile"; window.close()</script>');
        
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

        Achievements::give(11); //Don't spy me!
        
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
        else if (!preg_match('/^[a-zA-Z0-9-_]+$/', $form['name'])) {
            return '0;'.t('name_have_forbidden_chars');
        }
        
        $row = Db::fetchRow('SELECT `email` FROM `users` WHERE `email` = "'.Db::escape($form['email']).'" LIMIT 1');
        if ($row && $row->email != $user->email) {
            return '0;'.t('email_taken');
        }
        else if(!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            return '0;'.t('email_invalid');
        }
        
        /*if ($form['timezone'] <= 720 && $form['timezone'] >= -720) {
            $timezone = $form['timezone'];
        }
        else {
            $timezone = 0;
        }*/
        
        if ($form['battletag']) {
            $battleTagBreakdown = explode('#', $form['battletag']);
            if (!isset($battleTagBreakdown[0]) || !$battleTagBreakdown[0] || !isset($battleTagBreakdown[1]) || !is_numeric($battleTagBreakdown[1])) {
                return '0;'.t('field_battletag_incorrect');
            }
            else {
                $form['battletag'] = trim($battleTagBreakdown[0]).'#'.trim($battleTagBreakdown[1]);
            }
            Achievements::give(31);//The winter is comming.. I mean Blizzard!
        }
        
        if (!is_numeric($form['avatar']) || $form['avatar'] > 30 || $form['avatar'] < 1) {
            $form['avatar'] = 1;
        }
        else {
            Achievements::give(9);//I have a face now!
        }
        
        if (isset($form['https']) && $form['https'] == 1) {
            $form['https'] = 1;
            Achievements::give(10);//007 is my way!
        }
        else {
            $form['https'] = 0;
        }

        if (isset($form['timestyle']) && $form['timestyle'] == 1) {
            $form['timestyle'] = 1;
        }
        else {
            $form['timestyle'] = 0;
        }
        
        Db::query(
            'UPDATE `users` SET '.
            '`name` = "'.Db::escape($form['name']).'", '.
            '`email` = "'.Db::escape($form['email']).'", '.
            //'`timezone` = "'.Db::escape($timezone).'", '.
            '`timestyle` = '.(int)$form['timestyle'].', '.
            '`avatar` = '.(int)$form['avatar'].', '.
            '`battletag` = "'.Db::escape($form['battletag']).'", '.
            '`https` = '.(int)$form['https'].' '.
            'WHERE `id` = '.(int)$user->id
        );
        
        $subscribeRow = Db::fetchRow(
            'SELECT * FROM `subscribe` WHERE '.
            '`email` = "'.Db::escape($form['email']).'" '
        );
        
        if (!$subscribeRow && $form['email']) {
            Db::query('INSERT INTO `subscribe` SET '.
                '`email` = "'.Db::escape($form['email']).'", '.
                '`unsublink` = "'.sha1(Db::escape($form['email']).rand(0,9999).time()).'"'
            );
        }
        else if ($form['email'] && !isset($form['subscribe']) || $form['subscribe'] == 'none' || !$form['subscribe']) {
            Db::query(
                'UPDATE `subscribe` SET '.
                '`removed` = 1 '.
                'WHERE `email` = "'.Db::escape($form['email']).'" '.
                'LIMIT 1'
            );
        }
        else if ($form['email']) {
            Db::query(
                'UPDATE `subscribe` SET '.
                '`removed` = 0, '.
                '`theme` = "'.Db::escape($form['subscribe']).'" '.
                'WHERE `email` = "'.Db::escape($form['email']).'" '.
                'LIMIT 1'
            );
        }
        
        //Getting fresh updated data
        $row = Db::fetchRow('SELECT `u`.`id` AS `id`, `us`.`id` AS `sid`, `us`.`social_uid` AS `uid`, `u`.* '.
            'FROM `users` AS `u` '.
            'LEFT JOIN `users_social` AS `us` ON `us`.`user_id` = `u`.`id` '.
            'WHERE `u`.`id` = '.(int)$user->id.' '
        );
    	
    	$_SESSION['user'] = (array)$row;
        
        return '1;'.t('success_profile_update');
    }
}