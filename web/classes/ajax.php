<?php
class Ajax extends System
{
    public function __construct() {
        parent::__construct();
    }
    
    private $allowed_ajax_methods = array(
		'newsVote',
    	'submitContactForm',
    	'registerInHS',
        'chat',
        'statusCheck',
        'uploadScreenshot',
	);
	
    public function ajaxRun($data) {
    	$controller = $data['ajax'];
        
        if ( in_array( $controller, $this->allowed_ajax_methods ) ) {
            echo $this->$controller($data);
            return true;
        }
        else {
            echo '0;Controller does not exist';
            return false;
        }
    }
    
    protected function uploadScreenshot() {
        $mb = 5;
        
        if (!isset($_SESSION['participant']) && !$_SESSION['participant']->id) {
            return '0;Not logged in';
        }
        
        $playersRow = Db::fetchRow('SELECT `challonge_id` FROM `teams` '.
            'WHERE `id` = '.(int)$_SESSION['participant']->id.' AND '.
            '`deleted` = 0 AND '.
            '`ended` = 0'
        );
        if (!$playersRow) {
            return '0;No fight registered';
        }

        if ($_FILES['upload']['size'] > 1024*$mb*1024) {
            return '0;File size is too big, allowed only: '.$mb.' MB';
        }
        else {
            $row = Db::fetchRow('SELECT `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2`, `f`.`screenshots` '.
                'FROM `fights` AS `f` '.
                'LEFT JOIN `teams` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
                'LEFT JOIN `teams` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
                'WHERE (`f`.`player1_id` = '.$playersRow->challonge_id.' OR `f`.`player2_id` = '.$playersRow->challonge_id.') AND '.
                '`f`.`done` = 0'
            );
            
            if (!$row) {
                return '0;Error';
            }
            else {
                $name = $_FILES['upload']['name'];
				$breakdown = explode('.', $name);
                $end = end($breakdown);
                
                $fileName = $_SERVER['DOCUMENT_ROOT'].'/screenshots/'.$row->id1.'_vs_'.$row->id2.'_'.time().'.'.$end;
                $fileUrl = _cfg('site').'/screenshots/'.$row->id1.'_vs_'.$row->id2.'_'.time().'.'.$end;

                if ($end != 'png' && $end != 'jpg' && $end != 'jpeg') {
                    return '0;File is not an image, it is: '.$end;
                }            
                else if (!copy($_FILES['upload']['tmp_name'], $fileName)) {
                    return '0;File can not be loaded, file expired or something goes terribly wrong';
                }
                else if ($row->screenshots > 10) {
                    return '0;Screenshot limit achieved, please wait for admin response or write an email to info@pcesports.com';
                }
                else {
                    $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$row->id1.'_vs_'.$row->id2.'.txt';
                
                    $file = fopen($fileName, 'a');
                    $content = '<p><span id="notice">('.date('H:i:s', time()).')</span> '.($_SESSION['participant']->id==$row->id1?$row->name1:$row->name2).' <a href="'.$fileUrl.'" target="_blank">uploaded the file</a></p>';
                    fwrite($file, htmlspecialchars($content));
                    fclose($file);
                    
                    Db::query('UPDATE `fights` '.
                        'SET `screenshots` = `screenshots` + 1 '.
                        'WHERE `player1_id` = '.$playersRow->challonge_id.' OR `player2_id` = '.$playersRow->challonge_id
                    );
                    
                    return '1;Ok';
                }
            }
        }
    }
    
    protected function statusCheck($data) {
        if (isset($_SESSION['participant']) && $_SESSION['participant']->id) {
            $challonge_id = (int)$_SESSION['participant']->challonge_id;
            Db::query('UPDATE `teams` SET `online` = '.time().' '.
				'WHERE `id` = '.(int)$_SESSION['participant']->id
			);
        }
        else {
            $challonge_id = 0;
        }
        
        $row = Db::fetchRow('SELECT * FROM `fights` '.
            'WHERE (`player1_id` = '.$challonge_id.' OR `player2_id` = '.$challonge_id.') AND '.
            '`done` = 0'
        );
        
        if (!$row) {
            return '0;none;waiting for opponent';
        }
        
        $playersRow = Db::fetchRow('SELECT `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2` '.
            'FROM `fights` AS `f` '.
            'LEFT JOIN `teams` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
            'LEFT JOIN `teams` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
            'WHERE (`f`.`player1_id` = '.(int)$_SESSION['participant']->challonge_id.' OR `f`.`player2_id` = '.(int)$_SESSION['participant']->challonge_id.') '.
            'AND`f`.`done` = 0'
        );
        
        if (!$playersRow) {
            return '0;none;no opponent';
        }
        else {
            $enemyRow = Db::fetchRow('SELECT `name`, `online` '.
                'FROM `teams` '.
                'WHERE '.
                '`challonge_id` = '.(int)($_SESSION['participant']->challonge_id==$playersRow->player1_id?$playersRow->player2_id:$playersRow->player1_id).' AND '.
                '`deleted` = 0 AND '.
                '`ended` = 0 '
            );
			
            if ($enemyRow) {
                if ($enemyRow->online+30 >= time()) {
                    $status = 'online';
                }
                else {
                    $status = 'offline';
                }

                return '1;'.$enemyRow->name.';'.$status;
            }
            
            return '0;none;offline';
        }
        
        return '0;Error';
    }
    
    protected function chat($data) {
        if (isset($_SESSION['participant']) && $_SESSION['participant']->id) {
            $challonge_id = (int)$_SESSION['participant']->challonge_id;
        }
        else {
            $challonge_id = 0;
        }
        
        $row = Db::fetchRow('SELECT * FROM `fights` '.
            'WHERE (`player1_id` = '.$challonge_id.' OR `player2_id` = '.$challonge_id.') AND '.
            '`done` = 0'
        );
        
        if (!$row) {
            return '1;<p id="notice">Chat is disabled at the moment, waiting for your opponent to appear</p>';
        }
        
        $playersRow = Db::fetchRow('SELECT `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2` '.
            'FROM `fights` AS `f` '.
            'LEFT JOIN `teams` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
            'LEFT JOIN `teams` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
            'WHERE (`f`.`player1_id` = '.(int)$_SESSION['participant']->challonge_id.' OR `f`.`player2_id` = '.(int)$_SESSION['participant']->challonge_id.') '.
            'AND`f`.`done` = 0'
        );
        
        if ($playersRow) {
            $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.(int)$playersRow->id1.'_vs_'.(int)$playersRow->id2.'.txt';
            
            $file = fopen($fileName, 'a');
            if ($data['action'] == 'send') {
                $content = '<p><span id="notice">('.date('H:i:s', time()).')</span> &#60;'.($_SESSION['participant']->id==$playersRow->id1?$playersRow->name1:$playersRow->name2).'&#62; - '.$data['text'].'</p>';
                fwrite($file, htmlspecialchars($content));
            }
            fclose($file);
            
            $chat = str_replace(';', '', strip_tags(stripslashes(html_entity_decode(file_get_contents($fileName))), '<p><b><a><u><span>'));
            
            if (!$chat) {
                $chat = '<p id="notice">Battle and admins are online, you can start using chat<br />To start a chat, input text and press "Enter"</p>';
            }
            
            return '1;'.$chat;
        }
        else {
            return '0;Error';
        }
        
        return '0;Error';
    }
    
    protected function registerInHS($data) {
    	$err = array();
    	$suc = array();
    	parse_str($data['form'], $post);
    	
    	$battleTagBreakdown = explode('#', $post['battletag']);
    	
    	$row = Db::fetchRow('SELECT * FROM `players` WHERE '.
    		' `tournament_id` = 4 AND '.
    		' `name` = "'.Db::escape($post['battletag']).'" AND '.
    		' `game` = "hs" AND '.
    		' `approved` = 1 AND '.
    		' `deleted` = 0'
    	);

    	if (!$post['battletag']) {
    		$err['battletag'] = '0;Field is empty';
    	}
    	else if ($row) {
    		$err['battletag'] = '0;Player with this battle tag is already registered';
    	}
    	else if (!isset($battleTagBreakdown[0]) || !$battleTagBreakdown[0] || !isset($battleTagBreakdown[1]) || !is_numeric($battleTagBreakdown[1])) {
    		$err['battletag'] = '0;BattleTag is incorrect it must look like YourName#1234';
    	}
    	else {
    		$suc['battletag'] = '1;Approved';
    	}
    	
    	if (!$post['email']) {
    		$err['email'] = '0;Field is empty';
    	}
    	else if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
    		$err['email'] = '0;Email is invalid';
    	}
    	else {
    		$suc['email'] = '1;Approved';
    	}
    	
    	if ($err) {
    		$answer['ok'] = 0;
    		if ($suc) {
    			$err = array_merge($err, $suc);
    		}
    		$answer['err'] = $err;
    	}
    	else {
    		$answer['ok'] = 1;
    		$answer['err'] = $suc;
    	
    		$code = substr(sha1(time().rand(0,9999)).$post['battletag'], 0, 32);
    		Db::query('INSERT INTO `teams` SET '.
	    		' `game` = "hs", '.
	    		' `tournament_id` = 4, '.
	    		' `timestamp` = NOW(), '.
	    		' `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
	    		' `name` = "'.Db::escape($post['battletag']).'", '.
	    		' `email` = "'.Db::escape($post['email']).'", '.
	    		' `contact_info` = "'.Db::escape($battleTagBreakdown[0]).'", '.
	    		' `link` = "'.$code.'"'
    		);
    	
    		$teamId = Db::lastId();
    	
    		Db::query(
    			'INSERT INTO `players` SET '.
    			' `game` = "hs", '.
    			' `tournament_id` = 4, '.
    			' `team_id` = '.(int)$teamId.', '.
    			' `name` = "'.Db::escape($post['battletag']).'", '.
    			' `player_num` = 1'
    		);
    		
    		$text = Template::getMailTemplate('reg-hs-player');
    	
    		$text = str_replace(
    			array('%name%', '%teamId%', '%code%', '%url%', '%href%'),
    			array($post['battletag'], $teamId, $code, _cfg('href').'/hearthstone', _cfg('url')),
    			$text
    		);
    	
    		$this->sendMail($post['email'], 'Pentaclick Hearthstone tournament participation', $text);
    	}
    	 
    	return json_encode($answer);
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
