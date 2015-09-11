<?php

class bnb extends System
{
    public $tournamentData;
	public $teamsPlaces;
    public $allowedGames;
    public $project = 'bnb';
    
	public function __construct($params = array()) {
        parent::__construct();
        
        $this->allowedGames = array('hearthstone');//leagueoflegends
	}
    
    public function getTournamentList() {
		$rows = Db::fetchRows('SELECT * FROM `tournaments` WHERE '.
            //'`project` = "'.$this->project.'" '.
            '(`game` = "lol" AND `name` = 1 AND `server` = "euw") OR '.
            //'(`game` = "lol" AND `name` = '.(int)$this->data->settings['lol-current-number-eune'].' AND `server` = "eune") OR '.
            //'(`game` = "hs" AND `name` = '.(int)$this->data->settings['hs-current-number'].' AND `server` = "'.Db::escape($this->data->settings['tournament-season-hs']).'") '.
            '(`game` = "hs" AND `name` = 1 AND `server` = "s1") '.
            'ORDER BY `id` DESC '
        );
        
        if ($rows) {
            foreach($rows as $v) {
                $startTime = strtotime($v->dates_start.' '.$v->time);
                $regTime = strtotime($v->dates_registration.' '.$v->time);
                $time = $regTime;
                
                if ($v->server) {
                    $checkInStatus = $this->data->settings['tournament-checkin-'.$v->game.'-'.$v->server];
                    $checkLive = $this->data->settings['tournament-start-'.$v->game.'-'.$v->server];
                    $checkReg = $this->data->settings['tournament-reg-'.$v->game.'-'.$v->server];
                }
                else {
                    $checkInStatus = $this->data->settings['tournament-checkin-'.$v->game];
                    $checkLive = $this->data->settings['tournament-start-'.$v->game];
                    $checkReg = $this->data->settings['tournament-reg-'.$v->game];
                }
                
                if ($checkInStatus == 1) {
                    $v->status = t('check_in');
                    $time = $startTime;
                }
                else if ($checkLive == 1) {
                    $v->status = t('live');
                    $time = $startTime;
                }
                else if ($checkReg == 1) {
                    $v->status = t('registration');
                }
                else if ($v->status == 'Ended') {
                    $v->status = t('ended');
                }
                else {
                    $v->status = t('upcoming');
                }
                
                if ($v->game == 'lol') {
                    $link = 'leagueoflegends/'.$v->server;
                }
                else if ($v->game == 'hs') {
                    $link = 'hearthstone';
                }
                
                $this->tournamentData[] = array(
                    'id'	=> $v->name,
                    'server'=> $v->server,
                    'game'  => $v->game,
                    'name' 	=> $v->name,
                    'status'=> $v->status,
                    'max_num'=> $v->max_num,
                    'prize' => $v->prize,
                    'dates_start'=> $v->dates_start,
                    'link'  => $link,
                );
            }
        }
	}
	
	public function showTemplate() {
        if (isset($_GET['val2']) && in_array($_GET['val2'], $this->allowedGames)) {
            include_once _cfg('widgets').'/'.$_GET['val2'].'/source.php';
            
            $game = new $_GET['val2'];
            $game->showTemplate();
        }
        else {
            include_once _cfg('widgets').'/'.$this->project.'/index.tpl';
        }
	}
    
    public function editInTournament($data) {
        $err = array();
        $suc = array();
        parse_str($data['form'], $post);
        
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
        
        if (!$post['country']) {
            $err['country'] = '0;'.t('please_pick_country');
        }
        else {
            $suc['country'] = '1;'.t('approved');
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
                'phone' => Db::escape($post['phone']),
                'country' => Db::escape($post['country']),
            ));
            
            Db::query(
                'UPDATE `participants_external` SET '.
                '`contact_info` = "'.Db::escape($contact_info).'", '.
                '`update_timestamp` = NOW() '.
                'WHERE `id` = '.(int)$post['participant'].' AND '.
                '`link` = "'.Db::escape($post['link']).'" AND '.
                '`deleted` = 0 AND '.
                '`project` = "'.$this->project.'" '
            );
        }
        
        return json_encode($answer);
    }
    
    public function registerInTournament($data) {
        $err = array();
        $suc = array();
        parse_str($data['form'], $post);
        
        $battleTagBreakdown = explode('#', $post['battletag']);

        if (!$post['battletag']) {
            $err['battletag'] = '0;'.t('field_empty');
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
        
        if (!$post['country']) {
            $err['country'] = '0;'.t('please_pick_country');
        }
        else {
            $suc['country'] = '1;'.t('approved');
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
                'phone' => Db::escape($post['phone']),
                'country' => Db::escape($post['country']),
            ));
            
            $code = substr(sha1(time().rand(0,9999)).$post['battletag'], 0, 32);
            Db::query(
                'INSERT INTO `participants_external` SET '.
                '`ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'", '.
                '`name` = "'.Db::escape($post['battletag']).'", '.
                '`email` = "'.Db::escape($post['email']).'", '.
                '`contact_info` = "'.Db::escape($contact_info).'", '.
                '`link` = "'.$code.'", '.
                '`project` = "'.$this->project.'" '
            );
            
            $lastId = Db::lastId();
            $tournamentName = 'MSI MCS Open Season 3 HearthStone Baltic Qualifier';
            $url = 'http://skillz.lv/ru/news/2046?&participant='.$lastId.'&link='.$code.'&';
            $additionalText = 'Tournament is going to happen only if 8 participants going to register (with payment) in the tournament.<br />Do not forget that tournament starts this Saturday at 12:00. To participate in the tournament, you must log in from 11:00 till 12:00 and "check in" to approve, that you are online. Then you will see a chat with your opponent and brackets.';

            $text = Template::getMailTemplate('reg-player-widget');

            $text = str_replace(
                array('%name%', '%tournamentName%', '%url%', '%additionalText%', '%teamName%'),
                array($post['battletag'], $tournamentName.' tournament', $url, $additionalText, 'Skillz.lv and Pentaclick eSports'),
                $text
            );
        
            $this->sendMail($post['email'], $tournamentName.' participation', $text);

            $answer['ok'] = 1;
        }
         
        return json_encode($answer);
    }
}