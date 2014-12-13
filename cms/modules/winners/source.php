<?php
class Winners extends System
{
    public $system;
    public $config;
    public $file;
    
	function __construct($params = array()) {
		$this->system = $params['system'];
        $this->file = _cfg('dir').'/template/mails/lol-winners.html';
        
        $rows = Db::fetchRows('SELECT * '.
			'FROM `tm_settings` '.
			'WHERE `setting` = "lol-current-number-euw" OR '.
            '`setting` = "lol-current-number-eune" '
		);
        
        foreach($rows as $v) {
            $this->config[$v->setting] = $v->value;
        }
        
        return $this;
	}

	public function send($form) {
        $rows = Db::fetchRows(
            'SELECT `name`, `place`, `server` '.
            'FROM `participants` '.
            'WHERE `game` = "lol" AND '.
            '`server` = "'.Db::escape('eune').'" AND '.
            '`tournament_id` = "'.Db::escape($this->config['lol-current-number-eune']).'" AND '.
            '`place` >= 1 AND `place` <= 4 '.
            'LIMIT 4'
        );
        
        if (!$rows) {
			$this->system->log('Sending email to winners <b>No emails to send</b>', array('module'=>get_class(), 'type'=>'send'));
			return '0;No emails to send';
		}
        
        if (!file_exists($this->file)) {
            $this->system->log('Sending email to winners <b>File does not exist</b>', array('module'=>get_class(), 'type'=>'send'));
			return '0;File does not exist';
        }
        
        $emailText = file_get_contents($this->file);
        
        $places = array(1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth');
        $placesNum = array(1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th');
        $prizes = array(1 => '3200 RP + Triumphant Ryze skin', 2 => '2400 RP', 3 => '1600 RP', 4 => '800 RP');
        $i = 1;
        foreach($rows as &$v) {
            if ($v->place == $i) {
            dump($v);
                $v->prizes = $prizes[$i];
                $v->playersList = $form[$places[$i].'_place'];
                $v->placesNum = $placesNum[$i];
            }
            ++$i;
        }
        unset($v);
        
        $i = 0;
        foreach($rows as $v) {
            $text = str_replace(
    			array(
                    '%name%',
                    '%place%',
                    '%prize%',
                    '%server%',
                    '%playerList%',
                    '%subscriptionNumber%',
                ),
    			array(
                    $v->name,
                    $v->placesNum,
                    $v->prizes,
                    strtoupper($v->server),
                    $v->playersList,
                    $form['subscription_number'],
                ),
    			$emailText
    		);
            
            $title = 'Pentaclick LoL tournament '.strtoupper($v->server).' #'.$this->config['lol-current-number-eune'].' - '.$v->placesNum.' place';
            dump($v->email);
            dump($title);
            dump($text);
            //$this->sendMail($v->email, $title, $text);
        }

		$this->system->log('Sending email to winners <b>Emails sent</b>', array('module'=>get_class(), 'type'=>'send'));
							 
        return '1;Emails sent to winners';
	}
}