<?php
class Ajax extends System
{
    public function __construct() {
        parent::__construct();
    }
    
    private $allowed_ajax_methods = array(
		'newsVote',
    	'submitContactForm',
	);
	
    public function ajaxRun($data) {
    	$controller = $data['ajax'];
        
        if ( in_array( $controller, $this->allowed_ajax_methods ) ) {
            echo $this->$controller($data);
            return true;
        }
        else {
            echo '0;'.at('controller_not_exists');
            return false;
        }
    }
    
    protected function submitContactForm($data) {
    	$form = array();
    	parse_str($data['form'], $form);
    	
    	$row = Db::fetchRow('SELECT `timestamp` FROM `contact_form_timeout`'.
    		'WHERE `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'" AND `timestamp` >= '.time().' '.
    		'LIMIT 1'
    	);
    	 
    	if ($row) {
    		$timeLeft = $row->timestamp - time();
    		return '0;One message was already sent from your IP, you need to wait before you can send another one. Timeout: '.$timeLeft.' seconds';
    	}
    	else if (!trim($form['name'])) {
    		return '0;Please input name';
    	}
		else if (!trim($form['email']) || !filter_var(trim($form['email']), FILTER_VALIDATE_EMAIL)) {
			return '0;Email is empty or incorrect';
    	}
    	
    	$txt = '
    		Name: '.$form['name'].'<br />
    		Email: '.$form['email'].'<br />
    		Subject: '.$form['subject'].'<br />
    		IP: '.$_SERVER['REMOTE_ADDR'].'<br />
    		Message: '.nl2br($form['msg']).'
    	';
    	
    	if ($this->sendMail(_cfg('adminEmail'), 'Contact form submit: '.$form['subject'], $txt)) {
    		Db::query('INSERT INTO `contact_form_timeout` SET '.
    			'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
    			'`timestamp` = '.(time() + 300)
    		);
    		return '1;Form successfully sent, we will contact you shortly';
    	}
    	
    	return '0;Error sending email';
    }
    
    protected function newsVote($data) {
    	$row = Db::fetchRow('SELECT * FROM `news_likes`'.
    		'WHERE `news_id` = '.(int)$data['id'].' AND `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
    		'LIMIT 1'
   		);
    	
    	if ($row) {
    		$num = '- 1';
    		Db::query('DELETE FROM `news_likes`'.
    			'WHERE `news_id` = '.(int)$data['id'].' AND `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
    			'LIMIT 1'
    		);
    	}
    	else {
    		$num = '+ 1';
    		Db::query('INSERT INTO `news_likes` SET '.
    			'`news_id` = '.(int)$data['id'].', '.
    			'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'
    		);
    	}
    	
    	Db::query('UPDATE `news`'.
    		'SET `likes` = `likes` '.$num.' '.
    		'WHERE `id` = '.(int)$data['id'].' '.
    		'LIMIT 1'
    	);
    	
    	return '1;'.$num;
    }
}
