<?php

class fifa extends System
{
	public $teamsCount;
	public $participants;
    public $winners;
    public $project;
	
	public function __construct($params = array()) {
        parent::__construct();

        $this->project = 'jelgava-fifa';
	}
    
    protected function approveRegisterPlayer($row) {
		Db::query('UPDATE `participants` '.
			'SET `approved` = 1 '.
			'WHERE `tournament_id` = '.(int)$this->currentTournament.' '.
			'AND `game` = "hs" '.
			'AND `id` = '.$row->id
		);
        
        $subscribeRow = Db::fetchRow(
            'SELECT * FROM `subscribe` WHERE '.
            '`email` = "'.Db::escape($row->email).'" '
        );
        
        if (!$subscribeRow) {
            Db::query('INSERT INTO `subscribe` SET '.
                '`email` = "'.Db::escape($row->email).'", '.
                '`unsublink` = "'.sha1(Db::escape($row->email).rand(0,9999).time()).'"'
            );
        }
        
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
	
	public function getTournamentData() {
        $rows = Db::fetchRows('SELECT * '.
            'FROM `participants_external` AS `p` '.
            'WHERE `project` = "'.$this->project.'" AND `deleted` = 0 '.
            'ORDER BY `id` ASC'
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
        
        include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public static function getSeo() {
        $seo = new stdClass();
        $u = new self;
		$seo->title = 'FIFA 2015';

		return $seo;
	}
	
	public function showTemplate() {
		$this->getTournamentData();
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
    		'`deleted` = 0 '
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
        
        $addStream = 0;
        if ($post['stream']) {
            $post['stream'] = str_replace(array('http://www.twitch.tv/', 'http://twitch.tv/'), array('',''), $post['stream']);
            
            $twitch = $this->runTwitchAPI($post['stream']);
            
            if (!$twitch) {
                $err['stream'] = '0;'.t('channel_not_found');
            }
            else {
                $addStream = 1;
                $suc['stream'] = '1;'.t('approved');
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
            
            if ($addStream == 1) {
                Db::query(
                    'INSERT INTO `streams_events` SET '.
                    '`user_id`  = '.(int)$this->data->user->id.', '.
                    '`participant_id` = '.(int)$teamId.', '.
                    ' `tournament_id` = '.(int)$this->data->settings['hs-current-number'].', '.
                    '`game` = "hs", '.
                    '`name` = "'.Db::escape($post['stream']).'" '
                );
            }
    		
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
}