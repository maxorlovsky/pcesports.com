<?php

class contacts
{
	public function __construct($params = array()) {
	}
	
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}

	public function submit($data) {
		$form = array();
		parse_str($data['form'], $form);
		
		$row = Db::fetchRow('SELECT `timestamp` FROM `contact_form_timeout`'.
		    'WHERE `ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'" AND `timestamp` >= '.time().' '.
		    'LIMIT 1'
		);
		 
		if ($row) {
		    $str = str_replace('%timeleft%', $row->timestamp - time(), t('contact_form_ip_timeout'));
		    return '0;'.$str;
		}
		else if (!trim($form['name'])) {
		    return '0;'.t('input_name');
		}
		else if (!trim($form['email']) || !filter_var(trim($form['email']), FILTER_VALIDATE_EMAIL)) {
		    return '0;'.t('email_invalid');
		}
		
		$txt = '
		    Name: '.$form['name'].'<br />
		    Email: '.$form['email'].'<br />
		    Subject: '.$form['subject'].'<br />
		    IP: '.(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'<br />
		    Message: '.nl2br($form['msg']).'
		';
		
		if ($this->sendMail(_cfg('adminEmail'), 'Contact form submit: '.$form['subject'], $txt)) {
		    Db::query('INSERT INTO `contact_form_timeout` SET '.
		        '`ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'", '.
		        '`timestamp` = '.(time() + 300)
		    );
		    
		    return '1;'.t('form_success_sent');
		}
		
		return '0;'.t('error_sending_form');
	}
}