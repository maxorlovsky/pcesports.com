<?php

class fifa extends System
{
	public $teamsCount;
	public $participants;
    public $winners;
    public $project;
    public $regOpen = 0;
	
	public function __construct($params = array()) {
        parent::__construct();

        $this->project = 'jelgava-fifa';
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
        
        if ($this->regOpen != 1) {
            return '0;Registration is closed';
        }
    	
    	$row = Db::fetchRow('SELECT * FROM `participants_external` WHERE '.
    		'`project` = "'.$this->project.'" AND '.
            '`name` = "'.Db::escape($post['nickname']).'" AND '.
    		'`deleted` = 0 '
    	);

    	if (!$post['nickname']) {
    		$err['nickname'] = '0;Nickname ir tukšs';
    	}
    	else {
    		$suc['nickname'] = '1;Apstiprināta';
    	}
    	
    	if (!$post['email']) {
    		$err['email'] = '0;E-pasts ir tukšs';
    	}
    	else if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
    		$err['email'] = '0;E-pasts nederīgs';
    	}
    	else {
    		$suc['email'] = '1;Apstiprināta';
    	}
        
        if (!$post['agree']) {
    		$err['agree'] = '0;Jāvienojas ar noteikumiem';
    	}
        else {
            $suc['agree'] = '1;Apstiprināta';
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
                'name' => $post['name'],
                'place' => 0,
            ));
    	
    		$code = substr(sha1(time().rand(0,9999)).$post['nickname'], 0, 32);
    		Db::query('INSERT INTO `participants_external` SET '.
	    		'`project` = "'.$this->project.'", '.
	    		'`timestamp` = NOW(), '.
	    		'`ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'", '.
	    		'`name` = "'.Db::escape($post['nickname']).'", '.
	    		'`email` = "'.Db::escape($post['email']).'", '.
	    		'`contact_info` = "'.Db::escape($contact_info).'", '.
	    		'`link` = "'.$code.'"'
    		);

            User::subscribe($data['email']);
    		
            $text = Template::getMailTemplate('reg-player-fifa');
        
            $text = str_replace(
                array('%name%'),
                array($post['name']?$post['name']:$post['nickname']),
                $text
            );
        
            $this->sendMail($post['email'], 'Jelgava FIFA 2015 tournament participation', $text);
    	}

    	return json_encode($answer);
    }
}