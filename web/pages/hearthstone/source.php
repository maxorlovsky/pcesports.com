<?php

class hearthstone extends System
{
	public $teamsCount;
	public $currentTournament;
	public $teamsPlaces;
	public $participants;
    public $pickedTournament;
    public $groups;
    public $server;
    public $winners;
	
	public function __construct($params = array()) {
        parent::__construct();
        
        if (in_array($_GET['val2'], array('s1', 's2'))) {
            $this->server = $_GET['val2'];
        }
        else {
            $this->server = 's2';
        }
        
        $this->heroes = array(
            1 => 'warrior',
            2 => 'hunter',
            3 => 'mage',
            4 => 'warlock',
            5 => 'shaman',
            6 => 'rogue',
            7 => 'druid',
            8 => 'paladin',
            9 => 'priest',
        );
        
        $this->groups = array(
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'F',
            7 => 'G',
            8 => 'H'
        );
    
		$this->currentTournament = $this->data->settings['hs-current-number'];
	}
    
    public function editPage() {
        if (!isset($_SESSION['participant']) && !$_SESSION['participant']->id) {
			go(_cfg('href').'/hearthstone/'.$this->server);
		}
        $row = Db::fetchRow('SELECT `contact_info` '.
            'FROM `participants` '.
            'WHERE '.
            '`tournament_id` = '.(int)$this->currentTournament.' AND '.
            '`game` = "hs" AND '.
            '`id` = '.(int)$_SESSION['participant']->id.' '
        );
        $row->contact_info = json_decode($row->contact_info);
        
        $editData = $row;
        
        include_once _cfg('pages').'/'.get_class().'/edit.tpl';
    }
    
	public function participantPage() {
		$verified = 0;
		
		if (isset($_GET['val4']) && $_GET['val4'] == 'exit') {
			unset($_SESSION['participant']);
			go(_cfg('href').'/hearthstone/'.$this->server);
		}
		
		if (isset($_GET['val4']) && $_GET['val4'] == 'leave' && isset($_SESSION['participant']) && $_SESSION['participant']->id) {
            $this->leave();
		}
		
        if (isset($_GET['val4']) && $_GET['val4'] == 'surrender' && isset($_SESSION['participant']) && $_SESSION['participant']->id) {
            $this->surrender();
		}
		
		if (!isset($_GET['val5']) && !$_GET['val5'] && !$_SESSION['participant'] && !$_SESSION['participant']->id) {
			go(_cfg('href').'/hearthstone/'.$this->server);
		}
		
		if (isset($_SESSION['participant'])) {
			$id = $_SESSION['participant']->id;
			$code = $_SESSION['participant']->link;
		}
		else {
			$id = (int)$_GET['val4'];
			$code = $_GET['val5'];
		}
        
        $row = Db::fetchRow(
			'SELECT * '.
			'FROM `participants` AS `t` '.
			'WHERE '.
			'`t`.`tournament_id` = '.(int)$this->currentTournament.' AND '.
			'`t`.`game` = "hs" AND '.
            '`t`.`server` = "'.Db::escape($this->server).'" AND ' .
			'`t`.`id` = '.Db::escape($id).' AND '.
			'`t`.`link` = "'.Db::escape($code).'" AND '.
			'`t`.`deleted` = 0 AND '.
			'`t`.`ended` = 0'
		);

		if ($row && $row->approved == 0) {
			//Not approved, registration open, approving
			$this->approveRegisterPlayer($row);
            Achievements::give(array(21,22,23));//I am preparing my cards. (Register on Hearthstone tournament.)
			$verified = 1;
			$regged = 1;
		}
		else if ($row && $row->approved == 1) {
			$verified = 1;
		}
        //$paymentVerified = $row->verified;
        $paymentVerified = 1;
        
        $rows = Db::fetchRow('SELECT COUNT(`id`) AS `count` '.
            'FROM `participants` '.
            'WHERE `game` = "hs" AND '.
            '`server` = "'.Db::escape($this->server).'" AND' .
            '`approved` = 1 AND '.
            '`checked_in` = 1 AND '.
            '`tournament_id` = '.(int)$_SESSION['participant']->tournament_id.' AND '.
            '`deleted` = 0 '
        );
        
        $this->participantsCount = $rows->count;
		
		if ($verified == 1) {
			$_SESSION['participant'] = $row;

            if (isset($paymentVerified)) {
                $_SESSION['participant']->verified = $paymentVerified;
            }
			
			include_once _cfg('pages').'/'.get_class().'/participant-page.tpl';
		}
		else {
			include_once _cfg('pages').'/'.get_class().'/participant-error.tpl';
		}
	}
    
    protected function approveRegisterPlayer($row) {
		//Generating other IDs for different environment
		if (_cfg('env') == 'prod') {
			$participant_id = $row->id + 100000;
		}
		else {
			$participant_id = $row->id;
		}
		
		Db::query('UPDATE `participants` '.
			'SET `approved` = 1 '.
			'WHERE `tournament_id` = '.(int)$this->currentTournament.' '.
			'AND `game` = "hs" '.
			'AND `id` = '.$row->id
		);

        User::subscribe($data['email']);
        
        //Cleaning up duplicates
        Db::query('UPDATE `participants` '.
            'SET `deleted` = 1 '.
            'WHERE `tournament_id` = '.(int)$this->currentTournament.' AND '.
            '`game` = "hs" AND '.
            '`server` = "'.Db::escape($this->server).'" AND '.
            '`id` != '.$row->id.' AND '.
            '`name` = "'.Db::escape($row->name).'" '
        );
		
		return true;
	}
	
	public function getTournamentData($number) {
        $this->pickedTournament = (int)$number;
        
        $rows = Db::fetchRows('SELECT `p`.`name` AS `battletag`, `p`.`place`, `u`.`name`, `p`.`user_id`, `p`.`seed_number`, `p`.`place`, `p`.`contact_info`, `p`.`approved`, `p`.`checked_in`, `p`.`verified`, `u`.`avatar` '.
            'FROM `participants` AS `p` '.
            'LEFT JOIN `users` AS `u` ON `p`.`user_id` = `u`.`id` '.
            'WHERE `p`.`game` = "hs" AND `server` = "'.$this->server.'" AND `p`.`tournament_id` = '.(int)$this->pickedTournament.' AND `deleted` = 0 '.
            'ORDER BY `p`.`user_id` DESC, `p`.`id` ASC'
        );
        if ($rows) {
            foreach($rows as &$v) {
                $v->contact_info = json_decode($v->contact_info);

                if ($v->place >= 1 && $v->place <= 3) {
                    $this->winners[$v->place] = ($v->name?$v->name:$v->battletag);
                }
            }
        }
        $this->participants = $rows;
        unset($v);
        
        $tournamentRow = Db::fetchRow('SELECT `dates_start`, `dates_registration`, `time`, `status`, `prize` '.
            'FROM `tournaments` '.
            'WHERE `game` = "hs" AND '.
            '`server` = "'.$this->server.'" AND '.
            '`name` = '.(int)$this->pickedTournament.' '.
            'LIMIT 1'
        );

        if ($tournamentRow) {
            $tournamentTime['registration'] = $this->convertTime($tournamentRow->dates_registration.' '.$tournamentRow->time);
            $tournamentTime['checkin'] = $this->convertTime(strtotime($tournamentRow->dates_start.' '.$tournamentRow->time) - 3600);
            $tournamentTime['start'] = $this->convertTime($tournamentRow->dates_start.' '.$tournamentRow->time);

            include_once _cfg('pages').'/'.get_class().'/tournament.tpl';
        }
        else {
            include_once  _cfg('pages').'/404/error.tpl';
        }
	}
    
    public function getTournamentList() {
		$rows = Db::fetchRows('SELECT * '.
			'FROM `tournaments` '.
			'WHERE `game` = "hs" AND '.
            '`server` = "'.Db::escape($this->server).'" '.
			'ORDER BY `id` DESC '.
            'LIMIT 10'
		);
        if ($rows) {
            foreach($rows as $v) {
                if ($v->status != 'ended') {
                    $startTime = strtotime($v->dates_start.' '.$v->time);
                    $regTime = strtotime($v->dates_registration.' '.$v->time);
                    
                    if (time() > $startTime) {
                        $v->status = t('live');
                    }
                    else if (time() < $startTime && time() > $regTime) {
                        $v->status = t('registration');
                    }
                    else if ($v->status == 'ended') {
                        $v->status = t('ended');
                    }
                    else {
                        $v->status = t('upcoming');
                    }
                }
                $this->tournamentData[$v->name] = (array)$v;
            }
        }
		
        if ($this->tournamentData) {
            $rows = Db::fetchRows('SELECT `tournament_id`, COUNT(`tournament_id`) AS `value`'.
                'FROM `participants` '.
                'WHERE `game` = "hs" AND '.
                '`server` = "'.Db::escape($this->server).'" AND '.
                '`deleted` = 0 '.
                'GROUP BY `tournament_id` '.
                'ORDER BY `id` DESC'
            );
            if ($rows) {
                foreach($rows as $v) {
                    if ($this->tournamentData[$v->tournament_id]) {
                        $this->tournamentData[$v->tournament_id]['teamsCount'] = $v->value;
                    }
                }
            }
        
            $rows = Db::fetchRows('SELECT `tournament_id`, `name`, `place` '.
                'FROM `participants` '.
                'WHERE `game` = "hs" AND '.
                '`server` = "'.Db::escape($this->server).'" AND '.
                '`place` != 0 '.
                'ORDER BY `tournament_id`, `place`'
            );
            
            $previousTournamentId = 0;
            if ($rows) {
                foreach($rows as $v) {
                    if ($v->tournament_id != $previousTournamentId && $this->tournamentData[$v->tournament_id]) {
                        $this->tournamentData[$v->tournament_id]['places'][$v->place] = $v->name;
                    }
                }
            }
        }
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public static function getSeo() {
        $seo = new stdClass();
        $u = new self;
		$seo->title = 'Hearthstone League | '.strtoupper($u->server);
        
        if (is_numeric($_GET['val2'])) {
            $seo->title = 'Hearthstone League | '.strtoupper($u->server).'T'.$_GET['val2'];
            $seo->ogDesc = $seo->title;
        }
        
        $seo->ogImg = _cfg('img').'/hs-logo-big.png';
		
		return $seo;
	}
	
	public function showTemplate() {
        if (isset($_GET['val4']) && $_GET['val4'] == 'edit') {
			$this->editPage();
		}
        else if (isset($_GET['val3']) && $_GET['val3'] == 'participant') {
			$this->participantPage();
		}
        else if (isset($_GET['val3']) && is_numeric($_GET['val3'])) {
			$this->getTournamentData($_GET['val3']);
		}
		else {
			$this->getTournamentList();
		}
	}
    
    public function register($data) {
        $err = array();
    	$suc = array();
    	parse_str($data['form'], $post);
        
        if ($this->data->settings['tournament-reg-hs'] != 1) {
            return '0;Server error!';
        }
        
        $server = $this->data->settings['tournament-season-hs'];

        if ($this->logged_in) {
            if ($this->data->user->battletag) {
                $post['battletag'] = $this->data->user->battletag;
            }

            if ($this->data->user->email) {
                $post['email'] = $this->data->user->email;
            }
        }
    	
    	$row = Db::fetchRow('SELECT * FROM `participants` WHERE '.
    		'`tournament_id` = '.(int)$this->data->settings['hs-current-number'].' AND '.
            '`server` = "'.Db::escape($server).'" AND '.
    		'`name` = "'.Db::escape($post['battletag']).'" AND '.
    		'`game` = "hs" AND '.
    		'`deleted` = 0 AND '.
            '`approved` = 1 '
    	);

        $battleTagBreakdown = explode('#', $post['battletag']);
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
            $post['battletag'] = trim($battleTagBreakdown[0]).'#'.trim($battleTagBreakdown[1]);
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
        
        if (!$post['agree']) {
    		$err['agree'] = '0;'.t('must_agree_with_rules');
    	}
        else {
            $suc['agree'] = '1;'.t('approved');
        }
        
        $heroesPicked = array();
        for($i=1;$i<=3;++$i) {
            if (!$post['hero'.$i]) {
                $err['hero'.$i] = '0;'.t('pick_hero');
            }
            
            if (in_array($post['hero'.$i], $heroesPicked)) {
                $err['hero'.$i] = '0;'.t('same_hero_picked');
            }
            
            if ($post['hero'.$i]) {
                $heroesPicked[] = $post['hero'.$i];
            }
        }
        if ($post['hero1'] == $post['hero2'] && $post['hero1'] != 0) {
            $err['hero2'] = '0;'.t('same_hero_picked');
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
            
            $contact_info = json_encode(array(
                'hero1' => $post['hero1'],
                'hero2' => $post['hero2'],
                'hero3' => $post['hero3'],
                'place' => 0,
            ));
    	
    		$code = substr(sha1(time().rand(0,9999)).$post['battletag'], 0, 32);
    		Db::query('INSERT INTO `participants` SET '.
	    		'`game` = "hs", '.
                '`server` = "'.Db::escape($server).'", '.
	    		'`tournament_id` = '.(int)$this->data->settings['hs-current-number'].', '.
	    		'`timestamp` = NOW(), '.
	    		'`ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'", '.
	    		'`name` = "'.Db::escape($post['battletag']).'", '.
	    		'`email` = "'.Db::escape($post['email']).'", '.
	    		'`contact_info` = "'.Db::escape($contact_info).'", '.
                ($this->logged_in?'`approved` = "1", `user_id` = '.(int)$this->data->user->id.', ':null).
	    		'`link` = "'.$code.'"'
    		);
    	
    		$teamId = Db::lastId();
    	
    		Db::query(
    			'INSERT INTO `players` SET '.
    			' `game` = "hs", '.
    			' `tournament_id` = '.(int)$this->data->settings['hs-current-number'].', '.
    			' `participant_id` = '.(int)$teamId.', '.
    			' `name` = "'.Db::escape($post['battletag']).'", '.
    			' `player_num` = 1'
    		);
                		
            //Only sending email to not reggistered user
            if (!$this->logged_in) {
        		$text = Template::getMailTemplate('reg-hs-player');
        	
        		$text = str_replace(
        			array('%name%', '%teamId%', '%code%', '%url%', '%href%'),
        			array($post['battletag'], $teamId, $code, _cfg('href').'/hearthstone/'.$server, _cfg('site')),
        			$text
        		);
        	
        		$this->sendMail($post['email'], 'Pentaclick Hearthstone tournament participation', $text);
            }
            else {
                Achievements::give(array(21,22,23));//I am preparing my cards. (Register on Hearthstone tournament.)
                $answer['ok'] = 2;
            }
    	}

    	return json_encode($answer);
    }
    
    public function editParticipant($data) {
        $err = array();
    	$suc = array();
    	parse_str($data['form'], $post);
        
        if ($this->logged_in) {
            $post['email'] = Db::escape($this->data->user->email);
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
        
        $heroesPicked = array();
        for($i=1;$i<=3;++$i) {
            if (!$post['hero'.$i]) {
                $err['hero'.$i] = '0;'.t('pick_hero');
            }
            
            if (in_array($post['hero'.$i], $heroesPicked)) {
                $err['hero'.$i] = '0;'.t('same_hero_picked');
            }
            
            if ($post['hero'.$i]) {
                $heroesPicked[] = $post['hero'.$i];
            }
        }
        if ($post['hero1'] == $post['hero2'] && $post['hero1'] != 0) {
            $err['hero2'] = '0;'.t('same_hero_picked');
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
            
            $contact_info = json_encode(array(
                'hero1' => $post['hero1'],
                'hero2' => $post['hero2'],
                'hero3' => $post['hero3'],
                'place' => 0,
            ));
            
    		Db::query('UPDATE `participants` SET '.
                '`email` = "'.Db::escape($post['email']).'", '.
	    		'`contact_info` = "'.Db::escape($contact_info).'" '.
	    		'WHERE `id` = '.(int)$_SESSION['participant']->id.' AND '.
                '`game` = "hs" '
    		);
    	}
        
    	return json_encode($answer);
    }
    
    public function checkIn() {
        if (!$_SESSION['participant']) {
            return '0;'.t('not_logged_in');
        }
        
        $server = $_SESSION['participant']->server;
        $currentTournament = (int)$this->data->settings['hs-current-number'];

        $participantsCount = Db::fetchRow('SELECT COUNT(*) FROM `participants` '.
            'WHERE `tournament_id` = '.(int)$currentTournament.' '.
            'AND `game` = "hs" '.
            'AND `server` = "'.Db::escape($server).'" '.
            'AND `checked_in` = 1 '.
            'AND `approved` = 1 '.
            'AND `deleted` = 0 '
        );
        $tournamentRow = Db::fetchRow('SELECT * FROM `tournaments` '.
            'WHERE `name` = '.(int)$currentTournament.' '.
            'AND `game` = "hs" '.
            'AND `server` = "'.Db::escape($server).'" '
        );

        if ($participantsCount >= $tournamentRow->max_num) {
            //return '0;Maximum number of participants checked in, sorry, try next time';
        }
        else if ($this->data->settings['tournament-checkin-hs'] != 1) {
            return '0;Check in is not in progress';
        }
        
        //Generating other IDs for different environment
		if (_cfg('env') == 'prod') {
			$participant_id = $_SESSION['participant']->id + 100000;
		}
		else {
			$participant_id = $_SESSION['participant']->id;
		}
        
        $apiArray = array(
			'participant_id' => $participant_id,
			'participant[name]' => $_SESSION['participant']->name,
		);
		
		//Adding team to Challonge bracket
        if (_cfg('env') == 'prod') {
            $this->runChallongeAPI('tournaments/pentaclick-hs'.$server.$currentTournament.'/participants.post', $apiArray);
        }
        else {
            $this->runChallongeAPI('tournaments/pentaclick-test1/participants.post', $apiArray);
        }
		
		//Registering ID, because Challonge idiots not giving an answer with ID
        if (_cfg('env') == 'prod') {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-hs'.$server.$currentTournament.'/participants.json');
        }
        else {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-test1/participants.json');
        }
        
		array_reverse($answer, true);
		
		foreach($answer as $f) {
			if ($f->participant->name == $_SESSION['participant']->name) {
                $row = Db::fetchRow('SELECT * FROM `participants` '.
					'WHERE `tournament_id` = '.(int)$currentTournament.' '.
					'AND `game` = "hs" '.
                    'AND `server` = "'.Db::escape($server).'" '.
					'AND `id` = '.(int)$_SESSION['participant']->id.' '.
                    'AND `approved` = 1 '.
                    //'AND `verified` = 1 '.
                    'AND `checked_in` = 0 '
				);
                if ($row != 0) {
                    Db::query('UPDATE `participants` '.
                        'SET `challonge_id` = '.(int)$f->participant->id.', '.
                        '`checked_in` = 1 '.
                        'WHERE `tournament_id` = '.(int)$currentTournament.' '.
                        'AND `game` = "hs" '.
                        'AND `server` = "'.Db::escape($server).'" '.
                        'AND `id` = '.(int)$_SESSION['participant']->id.' '.
                        'AND `approved` = 1 '
                        //'AND `verified` = 1 '
                    );
                    
                    $_SESSION['participant']->checked_in = 1;
                }
                
				break;
			}
		}

        Achievements::give(array(24,25,26));//Cards means random! (Participate in Hearthstone tournament.)
        
        return '1;1';
    }
    
    protected function surrender() {
        $row = Db::fetchRow('SELECT `f`.`match_id`, `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2` '.
            'FROM `fights` AS `f` '.
            'LEFT JOIN `participants` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
            'LEFT JOIN `participants` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
            'WHERE (`f`.`player1_id` = '.(int)$_SESSION['participant']->challonge_id.' OR `f`.`player2_id` = '.(int)$_SESSION['participant']->challonge_id.') '.
            'AND `f`.`done` = 0 '
        );
        
        if (!$row) {
            go(_cfg('href').'/hearthstone/'.$this->server);
        }
        
        if ($row->player1_id == $_SESSION['participant']->challonge_id) {
            $winner = $row->player2_id;
            $scores = '0-1';
        }
        else {
            $winner = $row->player1_id;
            $scores = '1-0';
        }
        
        $apiArray = array(
            '_method' => 'put',
            'match_id' => $row->match_id,
            'match[scores_csv]' => $scores,
            'match[winner_id]' => $winner,
        );
        if (_cfg('env') == 'prod') {
            $this->runChallongeAPI('tournaments/pentaclick-hs'.$this->server.(int)$this->currentTournament.'/matches/'.$row->match_id.'.put', $apiArray);
        }
        else {
            $this->runChallongeAPI('tournaments/pentaclick-test1/matches/'.$row->match_id.'.put', $apiArray);
        }
        
        /*Db::query('UPDATE `participants` SET `ended` = 1 '.
            'WHERE `game` = "hs" AND '.
            '`id` = '.(int)$_SESSION['participant']->id.' AND '. 
            '`link` = "'.Db::escape($_SESSION['participant']->link).'" '
        );*/
        
        Db::query('UPDATE `fights` SET `done` = 1 '.
            'WHERE `match_id` = '.(int)$row->match_id.' '
        );
        
        $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$row->id1.'_vs_'.$row->id2.'.txt';
            
        $file = fopen($fileName, 'a');

        $content = '<div class="manager">';
        $content .= '<div class="message">';
        $content .= '<b>'.$_SESSION['participant']->name.'</b> surrendered';
        $content .= '</div>';
        $content .= '<span>System message</span>';
        $content .= '&nbsp;•&nbsp;<span id="notice">'.date('H:i', time()).'</span>';
        $content .= '</div>';

        fwrite($file, htmlspecialchars($content));
        fclose($file);
        
        unset($_SESSION['participant']);
        
        go(_cfg('href').'/hearthstone/'.$this->server);
    }
    
    protected function leave() {
        Db::query('UPDATE `participants` SET `deleted` = 1 '.
        'WHERE `game` = "hs" AND '.
        '`id` = '.(int)$_SESSION['participant']->id.' AND '. 
        '`link` = "'.Db::escape($_SESSION['participant']->link).'" ');
        
        $row = Db::fetchRow('SELECT `challonge_id` '.
            'FROM `participants` '.
            'WHERE `game` = "hs" AND '.
            '`id` = '.(int)$_SESSION['participant']->id.' AND '. 
            '`link` = "'.Db::escape($_SESSION['participant']->link).'" '
        );
        
        if ($row->challonge_id != 0) {
            $apiArray = array(
                '_method' => 'delete',
            );
            if (_cfg('env') == 'prod') {
                $this->runChallongeAPI('tournaments/pentaclick-hs'.$this->server.(int)$this->currentTournament.'/participants/'.(int)$row->challonge_id.'.post', $apiArray);
            }
            else {
                $this->runChallongeAPI('tournaments/pentaclick-test1/participants/'.(int)$row->challonge_id.'.post', $apiArray);
            }
        }
        
        unset($_SESSION['participant']);
        
        go(_cfg('href').'/hearthstone/'.$this->server);
    }
}