<?php

class User extends System
{
    public function __construct() {
    }
    
    public function login($data) {
        if (!$data['login'] || !$data['password']) {
            unset($_SESSION['token']);
            return '0;'.at('login_pass_incorrect');
        }
        else {
            $row = Db::fetchRow('SELECT `id`, `login`, `email`, `level`, `language` '.
            'FROM `tm_admins` '.
            'WHERE `login` = "'.Db::escape($data['login']).'" AND `password` = "'.sha1($data['password']._cfg('salt')).'" '.
            'LIMIT 1');
            if ($row === false) {
                unset($_SESSION['token']);
                return '0;'.at('login_pass_incorrect');
            }
            else {
                $_SESSION['token'] = sha1(rand(0,9999).time());
                Db::query('DELETE FROM `tm_user_auth` WHERE `user_id` = "'.(int)$row->id.'" LIMIT 1');
                Db::query('INSERT INTO `tm_user_auth` '.
                	'SET '.
                	'`user_id` = '.(int)$row->id.', '.
                	'`token` = "'.$_SESSION['token'].'", '.
                	'`timestamp` = '.time()
				);
                Db::query('UPDATE `tm_admins` '.
	                'SET '.
	                '`last_login` = NOW(), '.
	                '`login_count` = `login_count` + 1 '.
	                '`login_ip` = "'.$_SERVER['REMOTE_ADDR'].'" '.
					'WHERE `id` = '.(int)$row->id
				);
                
                return $row;
            }
        }
        
        return false;
    }
    
    public function fetchUserByToken($token) {
        $row = Db::fetchRow('SELECT `a`.`id`, `a`.`login`, `a`.`email`, `a`.`level`, `a`.`language` '.
        'FROM `tm_user_auth` AS `ua` '.
        'LEFT JOIN `tm_admins` AS `a` ON `ua`.`user_id` = `a`.`id` '.
        'WHERE `ua`.`token` = "'.Db::escape($token).'" '.
        'LIMIT 1');
        if ($row !== false) {
            return $row;
        }
        else {
            unset($_SESSION['token']);
        }
        
        return false;
    }
}