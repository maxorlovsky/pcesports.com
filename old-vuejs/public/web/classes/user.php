<?php
class User extends System
{
    function __construct() {
		
	}
    
    public static function passwordConvert($password, $old = 0) {
        //Deprecated method of hash, not safe
        if ($old === 1) {
            $salt = 'adoj29!@#!1dj019@*&#!2j';
        
            $returnPassword = sha1(base64_encode($password.$salt));
        }
        //By default using modern approach of password encryption
        else {
            $returnPassword = password_hash($password, PASSWORD_BCRYPT);
        }
        
        return $returnPassword;
    }
    
    public static function passwordVerify($password, $hash) {
        if (password_verify($password, $hash)) {
            return true;
        }
        
        return false;
    }
    
    public static function login($email, $password) {
        $error = array();
        $object = new stdClass();
        
        //Check for old password first
        $row = Db::fetchRow('SELECT * '.
            'FROM `users` '.
            'WHERE `email` = "'.Db::escape($email).'" '.
            'LIMIT 1'
        );
        
        //Email found, need to do the password checking
        if ($row) {
            //BCrypt password hash checking
            //and then old sha1 password check
            if (User::passwordVerify($password, $row->password) === false && User::passwordConvert($password, 1) != $row->password) {
                $s = new System();
                return $s->errorLogin();
            }
            
            //One of authentications worked, logging in user
            $_SESSION['user'] = (array)$row;
            self::token();
            if ($_SESSION['user']['id']) {
                Achievements::giveLogin($_SESSION['user']['id']);//I see you for *th time!
            }
        }
        else {
            //Add additional layer of bcrypt password check
            $s = new System();
            return $s->errorLogin();
        }
        
        $object->status = 200;
        
        return $object;
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

        User::subscribe($data['email']);
        
        return $this->getUser($uid);
    }

    public static function registerSimple($data) {
        $object = new stdClass();
        $object->status = '400';
        if (!$data['email'] || !$data['password'] || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $object->message = 'Field is empty';
            return $object;
        }

        $queryUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $queryUrl .= '?secret='._cfg('recaptchaSecretKey');
        $queryUrl .= '&response='.urlencode($data['captcha']);
        $queryUrl .= '&remoteip='.urlencode(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']);
        $response = json_decode(file_get_contents($queryUrl));
        if ($response->success != 1) {
            $object->message = 'Captcha is not set, are you a robot?';
            return $object;
        }

        $row = Db::fetchRow('SELECT `id`, `password` FROM `users` WHERE `email` = "'.Db::escape($data['email']).'" LIMIT 1');
        if ($row) {
            if ($row->password == 'social') {
                $object->message = 'Sorry, email is already taken and used with social network, if it is your account, login with social network and add password for future logins';
            }
            else {
                $object->message = 'Sorry, email is already taken';
            }
            return $object;
        }

        $row = Db::fetchRow('SELECT `id` FROM `users_temp` WHERE `email` = "'.Db::escape($data['email']).'" LIMIT 1');
        if ($row) {
            $object->message = 'Sorry, this email is already in validation process, check your spam folder if you did not get our email in your inbox folder';
            return $object;
        }

        $s = new System();

        $row = Db::fetchRow('SELECT COUNT(`id`) AS `count` FROM `users_temp`');
        $count = $row->count;
        if ($count >= 300) {
            //Registration overload, probably spam
            $object->message = 'Registration overload, please contact admins.';
            $s->sendMail(_cfg('adminEmail'), 'Registration overload', 'Registration count limit exceted CHECK IMPORTANTLY!');
            return $object;
        }
        
        $code = substr(sha1($data['email'].$data['password']), 0, 50);
        Db::query('INSERT INTO `users_temp` SET '.
            '`ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'", '.
            '`email` = "'.Db::escape($data['email']).'", '.
            '`password` = "'.User::passwordConvert($data['password']).'", '.
            '`code` = "'.$code.'" '
        );

        $text = Template::getMailTemplate('user-registration');
        $text = str_replace(
            array('%url%'),
            array(_cfg('site').'/run/registration/'.$code),
            $text
        );
        $s->sendMail($data['email'], 'Pentaclick eSports - registration!', $text);

        $object->status = '200';
        $object->message = t('success_registration');
        
        return $object;
    }

    public function completeRegistration($code) {
        $_SESSION['errors'] = array();
        
        $row = Db::fetchRow('SELECT * FROM `users_temp` WHERE `code` = "'.Db::escape($code).'" LIMIT 1');
        if (!$row) {
            $_SESSION['errors'][] = 'Sorry, code expired, please register again.';
            return false;
        }

        $name = $this->checkRegName(array('name' => 'Anonymous', 'originalName' => 'Anonymous'));

        Db::query(
            'INSERT INTO `users` SET '.
            '`email` = "'.$row->email.'", '.
            '`password` = "'.$row->password.'", '.
            '`name` = "'.$name.'" '
        );
        $lastId = Db::lastId();

        $_SESSION['user'] = array('id' => $lastId);

        User::token();

        User::subscribe($row->email);

        Db::query('DELETE FROM `users_temp` WHERE `code` = "'.Db::escape($code).'" LIMIT 1');
        
        return true;
    }
    
    private function checkRegName($regName, $i = 2) {
        $row = Db::fetchRow('SELECT * FROM `users` WHERE `name` = "'.Db::escape($regName['name']).'"');
        if ($row) {
            $regName['name'] = $regName['originalName'].$i;
            $returnName = $this->checkRegName($regName, rand(0, 99999));
        }
        else {
            $returnName = $regName['name'];
        }
        
        return $returnName;
    }

    public static function restorePassword($data) {
        $object = new stdClass();
        $object->status = '400';

        if (!$data['email'] || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $object->message = 'Field is empty';
            return $object;
        }

        $row = Db::fetchRow(
            'SELECT `id`, `name`, `email`, `registration_date` '.
            'FROM `users` '.
            'WHERE `email` = "'.Db::escape($data['email']).'" '.
            'LIMIT 1'
        );
        if (!$row) {
            $object->message = 'Sorry, there are no existing user with registered email. It is free to register with it.';
        }
        else {
            $code = sha1(substr($row->name.$row->email.$row->registration_date.time(), 0, 50));
            Db::query(
                'INSERT INTO `users_links` SET '.
                '`user_id` = '.(int)$row->id.', '.
                '`type` = "pw", '.
                '`code` = "'.$code.'" '
            );

            $s = new System();
            $text = Template::getMailTemplate('user-password-restoration');
            $text = str_replace(
                array('%name%', '%url%'),
                array($row->name, _cfg('href').'/restoration/'.$code),
                $text
            );
            $s->sendMail($row->email, 'Pentaclick eSports - password restoration!', $text);

            $object->status = '200';
            $object->message = 'We sent you an email, with link to restore your password, do not forget to check your spam folder if it taking too long to receive email from us!';
        }

        return $object;
    }
    
    public function getUser($id) {
    	$row = Db::fetchRow('SELECT * FROM `users` WHERE `id` = '.(int)$id);
        if ($row) {
            return $row;
        }
        
        $_SESSION['errors'][] = t('user_not_exist').' ('.__LINE__.')';
        
        return '0;'.t('user_not_exist');
    }
    
    public static function checkUser() {
        $row = Db::fetchRow(
            'SELECT `user_id` FROM `users_auth` '.
            'WHERE `user_id` = '.(int)$_COOKIE['uid'].' AND '.
            '`token` = "'.Db::escape($_COOKIE['token']).'" '
        );

        if (!$row) {
            return false;
        }
        
        $userRow = Db::fetchRow(
            'SELECT * FROM `users` '.
            'WHERE `id` = '.(int)$row->user_id.' '.
            'LIMIT 1 '
        );
        
        if (!$userRow) {
            return false;
        }
        
        $userRow->socials = Db::fetchRows(
            'SELECT * FROM `users_social` '.
            'WHERE `user_id` = '.$userRow->id
        );
        if ($userRow->socials) {
            $userRow->socials->connected = array();
            foreach($userRow->socials as $v) {
                if ($v->social) {
                    $userRow->socials->connected[] = $v->social;
                }
            }
        }
        else {
            $userRow->socials = new stdClass();
            $userRow->socials->connected = array();
        }
        
        $userRow->summoners = Db::fetchRows(
            'SELECT * FROM `summoners` '.
            'WHERE `user_id` = '.$userRow->id.' AND '.
            '`approved` = 1 '
        );
        
        //$userRow->timezone = $userRow->timezone * 60;

        /*if (!isset($_SESSION['participant']) && !$_SESSION['participant']) {
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
        }*/
        
        /*if (!isset($_SESSION['user']) || !$_SESSION['user']) {
            //Getting fresh updated data (stupid way, but must do it)
            $row = Db::fetchRow('SELECT `u`.`id` AS `id`, `us`.`id` AS `sid`, `us`.`social_uid` AS `uid`, `u`.* '.
                'FROM `users` AS `u` '.
                'LEFT JOIN `users_social` AS `us` ON `us`.`user_id` = `u`.`id` '.
                'WHERE `u`.`id` = '.(int)$userRow->id.' '
            );
            
            $_SESSION['user'] = (array)$row;
        }*/

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
        $user = self::checkUser();
        
        if (!$user->id) {
            return t('not_logged_in');
        }
        
        $row = Db::fetchRow('SELECT COUNT(`id`) AS `count` FROM `users_social` WHERE `user_id` = '.(int)$user->id);
        if ($row->count <= 1 && $user->password == 'social') {
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
        $user = self::checkUser();
        
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
        
        /*if (isset($form['https']) && $form['https'] == 1) {
            $form['https'] = 1;
            Achievements::give(10);//007 is my way!
        }
        else {
            $form['https'] = 0;
        }*/

        if (isset($form['timestyle']) && $form['timestyle'] == 1) {
            $form['timestyle'] = 1;
        }
        else {
            $form['timestyle'] = 0;
        }
        
        Db::query(
            'UPDATE `users` SET '.
            '`name` = "'.Db::escape($form['name']).'", '.
            //'`timezone` = "'.Db::escape($timezone).'", '.
            '`timestyle` = '.(int)$form['timestyle'].', '.
            '`avatar` = '.(int)$form['avatar'].', '.
            //'`https` = '.(int)$form['https'].', '.
            '`battletag` = "'.Db::escape($form['battletag']).'" '.
            'WHERE `id` = '.(int)$user->id
        );
        
        //Getting fresh updated data
        $row = Db::fetchRow('SELECT `u`.`id` AS `id`, `us`.`id` AS `sid`, `us`.`social_uid` AS `uid`, `u`.* '.
            'FROM `users` AS `u` '.
            'LEFT JOIN `users_social` AS `us` ON `us`.`user_id` = `u`.`id` '.
            'WHERE `u`.`id` = '.(int)$user->id.' '
        );
    	
    	$_SESSION['user'] = (array)$row;

        $subscribeRow = Db::fetchRow(
            'SELECT * FROM `subscribe` WHERE '.
            '`email` = "'.Db::escape($_SESSION['user']['email']).'" '
        );
        
        if ($_SESSION['user']['email'] && !isset($form['subscribe']) || $form['subscribe'] == 'none' || !$form['subscribe']) {
            Db::query(
                'UPDATE `subscribe` SET '.
                '`removed` = 1 '.
                'WHERE `email` = "'.Db::escape($_SESSION['user']['email']).'" '.
                'LIMIT 1'
            );
        }
        else if ($_SESSION['user']['email']) {
            Db::query(
                'UPDATE `subscribe` SET '.
                '`removed` = 0, '.
                '`theme` = "'.Db::escape($form['subscribe']).'" '.
                'WHERE `email` = "'.Db::escape($_SESSION['user']['email']).'" '.
                'LIMIT 1'
            );
        }
        
        return '1;'.t('success_profile_update');
    }

    public static function updateEmail($form) {
        $user = self::checkUser();
        
        if (!$user->id) {
            return t('not_logged_in');
        }
        
        if ($user->email && !trim($form['email'])) {
            return '0;'.t('email_is_empty');
        }
        else if ($user->email == trim($form['email'])) {
            return '0;'.t('why_change_same_mail');
        }

        if ($user->password != 'social' && ($user->password != User::passwordConvert($form['password'], 1) && User::passwordVerify($form['password'], $user->password) === false) ) {
            return '0;'.t('current_password_is_incorrect');
        }

        $code = substr(sha1($form['email'].$user->password.time()), 0, 50);        
        Db::query(
            'INSERT INTO `users_links` SET '.
            '`user_id` = '.(int)$user->id.', '.
            '`type` = "email", '.
            '`code` = "'.$code.'", '.
            '`additional` = "'.Db::escape($form['email']).'" '
        );

        $text = Template::getMailTemplate('change-email');
        $text = str_replace(
            array('%name%', '%url%'),
            array($user->name, _cfg('site').'/run/email-change/'.$code),
            $text
        );
        $s = new System();
        $s->sendMail($form['email'], 'Pentaclick eSports - email change', $text);
        
        return '1;'.t('success_email_update');
    }

    public function completeMailChange($code) {
        $user = self::checkUser();

        $row = Db::fetchRow(
            'SELECT * FROM `users_links` '.
            'WHERE `type` = "email" AND '.
            '`code` = "'.Db::escape($code).'" AND '.
            '`user_id` = '.(int)$user->id
        );

        if (!$row) {
            $_SESSION['error'][] = t('code_incorrect_or_user_logged_out');
            return false;
        }

        $_SESSION['mailChange'] = 1;

        Db::query(
            'UPDATE `users` '.
            'SET `email` = "'.Db::escape($row->additional).'" '.
            'WHERE `id` = '.(int)$user->id.' '.
            'LIMIT 1 '
        );
        Db::query('DELETE FROM `users_links` WHERE `id` = '.(int)$row->id.' LIMIT 1');

        $subscribeRow = Db::fetchRow(
            'SELECT * FROM `subscribe` WHERE '.
            '`email` = "'.Db::escape($user->email).'" '.
            'LIMIT 1 '
        );

        if ($subscribeRow) {
            Db::query('DELETE FROM `subscribe` WHERE `id` = '.(int)$subscribeRow->id.' LIMIT 1');
        }
        
        Db::query('INSERT INTO `subscribe` SET '.
            '`email` = "'.Db::escape($row->additional).'", '.
            '`unsublink` = "'.sha1(Db::escape($row->additional).rand(0,9999).time()).'", '.
            '`theme` = "'.Db::escape($subscribeRow->theme).'" '
        );

        return true;
    }

    public static function updatePassword($form) {
        $user = self::checkUser();
        
        if (!$user->id) {
            return t('not_logged_in');
        }
        
        if ($user->password != 'social' && !trim($form['password'])) {
            return '0;'.t('current_password_is_empty');
        }

        if (strlen(trim($form['new_password'])) < 6) {
            return '0;'.t('password_too_small');
        }
        else if (trim($form['new_password']) != trim($form['new_repeat_password'])) {
            return '0;'.t('password_not_match');
        }

        if ($user->password != 'social' && ( $user->password != User::passwordConvert($form['password'], 1) && User::passwordVerify($form['password'], $user->password) === false ) ) {
            return '0;'.t('current_password_is_incorrect');
        }
        
        Db::query(
            'UPDATE `users` SET '.
            '`password` = "'.User::passwordConvert(trim($form['new_password'])).'" '.
            'WHERE `id` = '.(int)$user->id
        );
        
        //Getting fresh updated data
        $row = Db::fetchRow('SELECT `u`.`id` AS `id`, `us`.`id` AS `sid`, `us`.`social_uid` AS `uid`, `u`.* '.
            'FROM `users` AS `u` '.
            'LEFT JOIN `users_social` AS `us` ON `us`.`user_id` = `u`.`id` '.
            'WHERE `u`.`id` = '.(int)$user->id.' '
        );
        
        $_SESSION['user'] = (array)$row;
        
        return '1;'.t('success_password_update');
    }

    public static function subscribe($email) {
        $subscribeRow = Db::fetchRow(
            'SELECT * FROM `subscribe` WHERE '.
            '`email` = "'.Db::escape($email).'" '
        );
        
        if (!$subscribeRow && $email) {
            Db::query('INSERT INTO `subscribe` SET '.
                '`email` = "'.Db::escape($email).'", '.
                '`unsublink` = "'.sha1(Db::escape($email).rand(0,9999).time()).'"'
            );

            return true;
        }

        return false;
    }
}