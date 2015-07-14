<?php

class skillzhs extends System
{	
	public function __construct($params = array()) {
        parent::__construct();

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
	}
	
	public function showTemplate() {
        $rows = Db::fetchRows('SELECT `name` AS `battletag`, `contact_info` '.
            'FROM `participants_external` '.
            //'WHERE `project` = "skillz" '.
            'WHERE `tournament_id` = 10 '.
            'ORDER BY `id` ASC'
        );
        if ($rows) {
            foreach($rows as &$v) {
                $v->contact_info = json_decode($v->contact_info);
            }
        }
        $this->participants = $rows;
        unset($v);
        
        include_once _cfg('widgets').'/'.get_class().'/index.tpl';
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
        
        if (!$post['country'] || $post['country'] == 0) {
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
        
            Db::query('INSERT INTO `participants_external` SET '.
                '`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
                '`name` = "'.Db::escape($post['battletag']).'", '.
                '`email` = "'.Db::escape($post['email']).'", '.
                '`contact_info` = "'.Db::escape($contact_info).'" '.
                '`project` = "skillz" '
            );

            $answer['ok'] = 1;
        }
         
        return json_encode($answer);
    }
}