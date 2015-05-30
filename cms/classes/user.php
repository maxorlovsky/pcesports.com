<?php

class User
{
    public static function login($data) {
        if (!isset($_SESSION['recaptcha_login'])) {
            $_SESSION['recaptcha_login'] = 0;
        }
        
        Db::query('DELETE FROM `tm_user_auth_attempts` WHERE `ip` = "'.Db::escape_tags($_SERVER['REMOTE_ADDR']).'" AND `timestamp` < NOW() - INTERVAL 5 MINUTE LIMIT 2');
        $row = Db::fetchRow('SELECT `attempts` FROM `tm_user_auth_attempts` WHERE `ip` = "'.Db::escape_tags($_SERVER['REMOTE_ADDR']).'"');
        
        if (isset($row->attempts) && $row->attempts >= 20) {
            return '0;Brute force detected, your IP is blocked for 5 minutes';
        }
        
        if ($row) {
            Db::query('UPDATE `tm_user_auth_attempts` SET `attempts` = `attempts` + 1, `timestamp` = NOW() WHERE `ip` = "'.Db::escape_tags($_SERVER['REMOTE_ADDR']).'"');
        }
        else {
            Db::query('INSERT INTO `tm_user_auth_attempts` SET `ip` = "'.Db::escape_tags($_SERVER['REMOTE_ADDR']).'", `attempts` = 1');
        }
        
        if (isset($_SESSION['recaptcha_login']) && $_SESSION['recaptcha_login'] >= _cfg('availableLoginAttempts')) {
            $_SESSION['recaptcha_login'] += 1;
            if (isset($data['g-recaptcha-response']) && $data['g-recaptcha-response']) {
                $queryUrl = 'https://www.google.com/recaptcha/api/siteverify';
                $queryUrl .= '?secret='._cfg('recaptchaSecretKey');
                $queryUrl .= '&response='.urlencode($data['g-recaptcha-response']);
                $queryUrl .= '&remoteip='.urlencode($_SERVER['REMOTE_ADDR']);
                $response = json_decode(file_get_contents($queryUrl));
                
                if ($response->success != 1) {
                    return '0;'.at('are_you_robot');
                }
            }
            else {
                return '0;'.at('prove_not_robot');
            }
        }
        
        if (!$data['login'] || !$data['password']) {
            $_SESSION['recaptcha_login'] += 1;
            unset($_SESSION['token']);
            return '0;'.at('login_pass_incorrect');
        }
        else {
            $row = Db::fetchRow(
                'SELECT `id`, `login`, `email`, `level`, `language`, `editRedirect` '.
                'FROM `tm_admins` '.
                'WHERE `login` = "'.Db::escape_tags($data['login']).'" AND `password` = "'.sha1($data['password']._cfg('salt')).'" '.
                'LIMIT 1'
            );
            
            if (!isset($row) || $row === false) {
                Db::query('UPDATE `tm_admins` '.
                    'SET '.
                    '`login_attempts` = `login_attempts` + 1 '.
                    'WHERE `login` = "'.Db::escape_tags($data['login']).'" '.
                    'LIMIT 1'
                );
                
                $_SESSION['recaptcha_login'] += 1;
                
                unset($_SESSION['token']);
                return '0;'.at('login_pass_incorrect');
            }
            else {
                $_SESSION['token'] = sha1(rand(0,9999).time());
                Db::query('DELETE FROM `tm_user_auth` WHERE `user_id` = "'.(int)$row->id.'" LIMIT 1');
                Db::query('INSERT IGNORE INTO `tm_user_auth` '.
                    'SET '.
                    '`user_id` = '.(int)$row->id.', '.
                    '`token` = "'.$_SESSION['token'].'", '.
                    '`timestamp` = '.time()
                );
                
                Db::query('UPDATE `tm_admins` '.
                    'SET '.
                    '`last_login` = NOW(), '.
                    '`login_count` = `login_count` + 1, '.
                    '`last_ip` = "'.$_SERVER['REMOTE_ADDR'].'" '.
                    'WHERE `id` = '.(int)$row->id
                );
                
                unset($_SESSION['recaptcha_login']);
                
                
                return $row;
            }
        }
        
        return false;
    }
    
    public static function fetchUserByToken($token) {
        $row = Db::fetchRow('SELECT `a`.`id`, `a`.`login`, `a`.`email`, `a`.`level`, `a`.`language`, `a`.`custom_access`, `a`.`editRedirect` '.
        'FROM `tm_user_auth` AS `ua` '.
        'LEFT JOIN `tm_admins` AS `a` ON `ua`.`user_id` = `a`.`id` '.
        'WHERE `ua`.`token` = "'.Db::escape_tags($token).'" '.
        'LIMIT 1');
        if ($row !== false) {
            $row->custom_access = json_decode($row->custom_access);
            return $row;
        }
        else {
            unset($_SESSION['token']);
        }
        
        return false;
    }
    
    public static function logout() {
        unset($_SESSION['token'], $_SESSION['recaptcha_login']);
        
        go(_cfg('site').'/admin');
        
        die;
    }
}