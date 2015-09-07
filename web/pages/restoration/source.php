<?php

class restoration extends System
{
	public $expired = 0;

	public function __construct($params = array()) {
		parent::__construct();
	}
	
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'Password restoration';
		
		return $seo;
	}
	
	public function showTemplate() {
		if (!$_GET['val2']) {
            go(_cfg('site'));
            exit();
        }

		$row = Db::fetchRow(
			'SELECT * FROM `users_links`'.
			'WHERE `code` = "'.Db::escape($_GET['val2']).'" '.
			'LIMIT 1'
		);

		if (!$row) {
			$this->expired = 1;
		}

		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}

    public function restorePassword($data) {
    	$object = new stdClass();
        $object->status = '400';

        $queryUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $queryUrl .= '?secret='._cfg('recaptchaSecretKey');
        $queryUrl .= '&response='.urlencode($data['captcha']);
        $queryUrl .= '&remoteip='.urlencode(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']);
        $response = json_decode(file_get_contents($queryUrl));
        if ($response->success != 1) {
            $object->message = 'Captcha is not set, are you a robot?';
            return $object;
        }

        if ($data['password'] != $data['passwordRepeat']) {
        	$object->message = t('password_not_match');
        	return $object;
        }

        $row = Db::fetchRow(
        	'SELECT `u`.`id`, `ul`.`id` AS `ulid` FROM `users_links` AS `ul` '.
        	'LEFT JOIN `users` AS `u` ON `u`.`id` = `ul`.`user_id` '.
        	'WHERE `ul`.`type` = "pw" AND '.
        	'`ul`.`code` = "'.Db::escape($data['code']).'" AND '.
        	'`u`.`email` = "'.Db::escape($data['email']).'" '.
        	'LIMIT 1'
    	);

    	if (!$row) {
    		$object->message = t('combination_of_email_code_not_match');
    	}
    	else {
    		Db::query(
    			'UPDATE `users` SET '.
    			'`password` = "'.User::passwordConvert($data['password']).'" '.
    			'WHERE `id` = '.(int)$row->id
			);
			Db::query('DELETE FROM `users_links` WHERE `id` = '.(int)$row->ulid);

			$_SESSION['user'] = array('id' => $row->id);

        	User::token();

    		$object->status = '200';
    		$object->url = _cfg('href').'/profile';
    	}

    	return $object;
    }
}