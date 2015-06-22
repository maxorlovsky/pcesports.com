<?php
class ParticipantsHs
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
        	go(_cfg('cmssite').'/#participantsHs');
        }
        
        $row = Db::fetchRow('SELECT `value` FROM `tm_settings` WHERE `setting` = "hs-current-number-s1"');
        $this->currentTournament = $row->value;
        
		$this->participants = Db::fetchRows(
            'SELECT `id`, `name`, `email`, `approved` '.
            'FROM `participants` '.
            'WHERE `game` = "hs" AND '.
            '`server` = "s1" AND '.
            '`tournament_id` = '.(int)$this->currentTournament.' AND '.
            '`deleted` = 0 AND '.
            '`ended` = 0 '.
            'ORDER BY `verified` ASC'
        );
        
        if ($this->participants) {
            foreach($this->participants as &$v) {
                $v->contact_info = json_decode($v->contact_info);
            }
            unset($v);
        }

		return $this;
	}
    
    protected function able($id) {
    	$id = (int)$id;
    	$row = Db::fetchRow('SELECT `name`, `verified` FROM `participants` WHERE `id` = '.$id.' AND `tournament_id` = '.(int)$this->currentTournament.' AND `game` = "hs" LIMIT 1');
    	if ($row->verified == 1) {
    		$enable = 0;
    	}
    	else {
    		$enable = 1;
    	}
    	Db::query('UPDATE `participants` SET `verified` = '.$enable.' WHERE `id` = '.$id.' AND `tournament_id` = '.(int)$this->currentTournament.' AND `game` = "hs" LIMIT 1');

    	if ($enable == 1) {
    		$this->system->log('Verify HS participant <b>('.$row->name.')</b>', array('module'=>get_class(), 'type'=>'enabling'));
    	}
    	else {
    		$this->system->log('Removing HS participant verification <b>('.$row->name.')</b>', array('module'=>get_class(), 'type'=>'disabling'));
    	}
    }
}