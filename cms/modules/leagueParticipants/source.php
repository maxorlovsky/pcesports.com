<?php
class LeagueParticipants
{
    public $system;
	public $participants = array();
    public $currentTournament;
    public $groups;
    
	function __construct($params = array()) {
		$this->system = $params['system'];
        
        //Enable/disable
        if (isset($params['var1']) && $params['var1'] == 'able' && isset($params['var2'])) {
        	$this->able($params['var2']);
        	//redirect
        	go(_cfg('cmssite').'/#leagueParticipants');
        }
        
        $row = Db::fetchRow('SELECT `value` FROM `tm_settings` WHERE `setting` = "hslan-current-number"');
        $this->currentTournament = $row->value;
        
		$this->participants = Db::fetchRows(
            'SELECT `id`, `name`, `email`, `contact_info`, `approved`, `place`, `seed_number` '.
            'FROM `participants` '.
            'WHERE `game` = "hslan" AND '.
            '`tournament_id` = '.(int)$this->currentTournament.' AND '.
            '`deleted` = 0 AND '.
            '`ended` = 0 '.
            'ORDER BY `seed_number` ASC'
        );
        
        foreach($this->participants as &$v) {
            $v->contact_info = json_decode($v->contact_info);
        }
        unset($v);
        
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

		return $this;
	}
    
    public function groups() {
        Db::query(
            'UPDATE `participants` SET '.
            '`seed_number` = '.(int)$_POST['value'].' '.
            'WHERE `id` = '.(int)$_POST['id']
        );
    }
    
    public function place() {
        $row = Db::fetchRow('SELECT `contact_info` FROM `participants` WHERE `id` = '.(int)$_POST['id'].' LIMIT 1');
        
        $row->contact_info = json_decode($row->contact_info);
        $row->contact_info->place = (int)$_POST['value'];
        $row->contact_info = json_encode($row->contact_info);
        
        Db::query(
            'UPDATE `participants` SET '.
            '`contact_info` = "'.Db::escape($row->contact_info).'" '.
            'WHERE `id` = '.(int)$_POST['id']
        );
    }
    
    protected function able($id) {
    	$id = (int)$id;
    	$row = Db::fetchRow('SELECT `name`, `approved` FROM `participants` WHERE `id` = '.$id.' AND `game` = "hslan" LIMIT 1');
    	if ($row->approved == 1) {
    		$enable = 0;
    	}
    	else {
    		$enable = 1;
    	}
    	Db::query('UPDATE `participants` SET `approved` = '.$enable.' WHERE `id` = '.$id.' AND `game` = "hslan" LIMIT 1');

    	if ($enable == 1) {
    		$this->system->log('Verified participant <b>('.$row->name.')</b>', array('module'=>get_class(), 'type'=>'enabling'));
    	}
    	else {
    		$this->system->log('Removing participant verification <b>('.$row->name.')</b>', array('module'=>get_class(), 'type'=>'disabling'));
    	}
    }
}