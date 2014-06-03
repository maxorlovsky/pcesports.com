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
		'registerInLOL',
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
            echo '0;'.t('controller_not_exist');
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
                return '0;'.t('error');
            }
            else {
                $name = $_FILES['upload']['name'];
				$breakdown = explode('.', $name);
                $end = end($breakdown);
                
                $fileName = $_SERVER['DOCUMENT_ROOT'].'/screenshots/'.$row->id1.'_vs_'.$row->id2.'_'.time().'.'.$end;
                $fileUrl = _cfg('site').'/screenshots/'.$row->id1.'_vs_'.$row->id2.'_'.time().'.'.$end;

                if ($end != 'png' && $end != 'jpg' && $end != 'jpeg') {
                    return '0;'.t('file_is_not_image').': '.$end;
                }            
                else if (!copy($_FILES['upload']['tmp_name'], $fileName)) {
                    return '0;'.t('move_file_error');
                }
                else if ($row->screenshots > 10) {
                    return '0;'.t('screenshot_limit_block');
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
            return '0;'.t('none').';'.t('waiting_for_opponent');
        }
        
        $playersRow = Db::fetchRow('SELECT `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2` '.
            'FROM `fights` AS `f` '.
            'LEFT JOIN `teams` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
            'LEFT JOIN `teams` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
            'WHERE (`f`.`player1_id` = '.(int)$_SESSION['participant']->challonge_id.' OR `f`.`player2_id` = '.(int)$_SESSION['participant']->challonge_id.') '.
            'AND`f`.`done` = 0'
        );
        
        if (!$playersRow) {
            return '0;'.t('none').';'.t('no_opponent');
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
                    $status = t('online');
                }
                else {
                    $status = t('offline');
                }

                return '1;'.$enemyRow->name.';'.$status;
            }
            
            return '0;'.t('none').';'.t('offline');
        }
        
        return '0;'.t('error');
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
            return '1;<p id="notice">'.t('chat_disabled_no_opp').'</p>';
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
                $chat = '<p id="notice">'.t('chat_active_can_start').'</p>';
            }
            
            return '1;'.$chat;
        }
        else {
            return '0;'.t('error');
        }
        
        return '0;'.t('error');
    }
    
    protected function registerInHS($data) {
    	$err = array();
    	$suc = array();
    	parse_str($data['form'], $post);
    	
    	$battleTagBreakdown = explode('#', $post['battletag']);
    	
    	$row = Db::fetchRow('SELECT * FROM `teams` WHERE '.
    		'`tournament_id` = '.(int)$this->data->settings['hs-current-number'].' AND '.
    		'`name` = "'.Db::escape($post['battletag']).'" AND '.
    		'`game` = "hs" AND '.
    		'`approved` = 1 AND '.
    		'`deleted` = 0'
    	);

    	if (!$post['battletag']) {
    		$err['battletag'] = '0;'.t('field_empty');
    	}
    	else if ($row) {
    		$err['battletag'] = '0;'.t('field_battletag_error');
    	}
    	else if (!isset($battleTagBreakdown[0]) || !$battleTagBreakdown[0] || !isset($battleTagBreakdown[1]) || !is_numeric($battleTagBreakdown[1])) {
    		$err['battletag'] = '0;'.t('field_battletag_incorrect');
    	}
    	else {
    		$suc['battletag'] = '1;'.t('approved');
    	}
    	
    	if (!$post['email']) {
    		$err['email'] = '0;'.t('field_empty');
    	}
    	else if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
    		$err['email'] = '0;'.t('email_invalid');
    	}
    	else {
    		$suc['email'] = '1;'.t('approved');
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
	    		' `tournament_id` = '.(int)$this->data->settings['hs-current-number'].', '.
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
    			' `tournament_id` = '.(int)$this->data->settings['hs-current-number'].', '.
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
	
	protected function registerInLOL($data) {
    	$err = array();
    	$suc = array();
    	parse_str($data['form'], $post);
    	
    	$row = Db::fetchRow('SELECT * FROM `teams` WHERE '.
    		'`tournament_id` = '.(int)$this->data->settings['lol-current-number'].' AND '.
    		'`name` = "'.Db::escape($post['team']).'" AND '.
    		'`game` = "lol" AND '.
    		'`approved` = 1 AND '.
    		'`deleted` = 0'
    	);

    	if (!$post['team']) {
    		$err['team'] = '0;'.t('field_empty');
    	}
		else if (strlen($post['team']) < 4) {
			$err['team'] = '0;'.t('team_name_small');
		}
		else if (strlen($post['team']) > 60) {
			$err['team'] = '0;'.t('team_name_big');
		}
        else if ($row) {
            $err['team'] = '0;'.t('team_name_taken');
        }
		else {
			$suc['team'] = '1;'.t('approved');
		}
    	
    	if (!$post['email']) {
    		$err['email'] = '0;'.t('field_empty');
    	}
    	else if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
    		$err['email'] = '0;'.t('email_invalid');
    	}
    	else {
    		$suc['email'] = '1;'.t('approved');
    	}
		
		$players = array();
		$checkForSame = array();
		for($i=1;$i<=7;++$i) {
			if (!$post['mem'.$i] && $i < 6) {
				$err['mem'.$i] = '0;'.t('field_empty');    
			}
			else if ($post['mem'.$i]) {
				$response = $this->runAPI('/euw/v1.4/summoner/by-name/'.rawurlencode(htmlspecialchars($post['mem'.$i])));
				$row = Db::fetchRow('SELECT `p`.* FROM `players` AS `p` '.
					'LEFT JOIN `teams` AS `t` ON `p`.`team_id` = `t`.`id` '.
					'WHERE '.
					'`p`.`tournament_id` = '.(int)$this->data->settings['lol-current-number'].' AND '.
					'`p`.`name` = "'.Db::escape($post['mem'.$i]).'" AND '.
					'`p`.`game` = "lol" AND '.
					'`t`.`approved` = 1 AND '.
					'`t`.`deleted` = 0'
				);
				if (!$response) {
					$err['mem'.$i] = '0;'.t('summoner_not_found_euw');
				}
				else if ($response && $response->summonerLevel != 30) {
					$err['mem'.$i] = '0;'.t('summoner_low_lvl');
				}
				else if (in_array($post['mem'.$i], $checkForSame)) {
					$err['mem'.$i] = '0;'.t('same_summoner');
				}
				else if ($row) {
					$err['mem'.$i] = '0;'.t('summoner_already_registered');
				}
				else {
					$players[$i]['id'] = $response->id;
					$players[$i]['name'] = $response->name;
					$suc['mem'.$i] = '1;'.t('approved');
				}
				
				$checkForSame[] = $post['mem'.$i];
			}
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
    	
    		$code = substr(sha1(time().rand(0,9999)).$post['team'], 0, 32);
    		Db::query('INSERT INTO `teams` SET '.
	    		'`game` = "lol", '.
	    		'`tournament_id` = '.(int)$this->data->settings['lol-current-number'].', '.
	    		'`timestamp` = NOW(), '.
	    		'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
	    		'`name` = "'.Db::escape($post['team']).'", '.
	    		'`email` = "'.Db::escape($post['email']).'", '.
	    		'`contact_info` = "'.Db::escape($team).'", '.
	    		'`link` = "'.$code.'"'
    		);
    	
    		$teamId = Db::lastId();
			
			foreach($players as $k => $v) {
				Db::query(
					'INSERT INTO `players` SET '.
					' `game` = "lol", '.
					' `tournament_id` = '.(int)$this->data->settings['lol-current-number'].', '.
					' `team_id` = '.(int)$teamId.', '.
					' `name` = "'.Db::escape($v['name']).'", '.
					' `player_num` = "'.(int)$k.'", '.
					' `player_id` = "'.(int)$v['id'].'"'
				);
			}
    		
    		$text = Template::getMailTemplate('reg-lol-team');
    	
    		$text = str_replace(
    			array('%name%', '%teamId%', '%code%', '%url%', '%href%'),
    			array($post['team'], $teamId, $code, _cfg('href').'/leagueoflegends', _cfg('url')),
    			$text
    		);
    	
    		$this->sendMail($post['email'], 'Pentaclick League of Legends tournament participation', $text);
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
    		IP: '.$_SERVER['REMOTE_ADDR'].'<br />
    		Message: '.nl2br($form['msg']).'
    	';
    	
    	if ($this->sendMail(_cfg('adminEmail'), 'Contact form submit: '.$form['subject'], $txt)) {
    		Db::query('INSERT INTO `contact_form_timeout` SET '.
    			'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
    			'`timestamp` = '.(time() + 300)
    		);
    		return '1;'.t('form_success_sent');
    	}
    	
    	return '0;'.t('error_sending_form');
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
