<?php
class Streamers
{
    public $system;
	public $stream = array();
    
	function __construct($params = array()) {
        $this->system = $params['system'];
        
        //Enable/disable
        if (isset($params['var1']) && $params['var1'] == 'able' && isset($params['var2'])) {
        	$this->able($params['var2']);
        	//redirect
        	go(_cfg('cmssite').'/#streamers');
        }
        
        //Enable/disable
        if (isset($params['var1']) && $params['var1'] == 'featured' && isset($params['var2'])) {
        	$this->featured($params['var2']);
        	//redirect
        	go(_cfg('cmssite').'/#streamers');
        }
        $this->streams = Db::fetchRows(
            'SELECT `s`.`id`, `s`.`name`, `s`.`display_name`, `s`.`featured`, `s`.`game`, `s`.`viewers`, `s`.`online`, IF(`s`.`online` >= '.(time()-360).', 1, 0) AS `onlineStatus`, `u`.`name` AS `added_by`, `s`.`languages`, `s`.`approved` '.
            'FROM `streams` AS `s` '.
            'LEFT JOIN `users` AS `u` ON `s`.`user_id` = `u`.`id` '.
            'WHERE `s`.`game` != "lolcup" AND '.
            '`s`.`game` != "smitecup" '.
            'ORDER BY `onlineStatus` DESC, `s`.`featured` DESC, `s`.`viewers` DESC '
		);

		return $this;
	}
    
    protected function able($id) {
    	$id = (int)$id;
    	$row = Db::fetchRow('SELECT `name`, `approved` FROM `streams` WHERE `id` = '.$id.' LIMIT 1');
    	if ($row->approved == 1) {
    		$enable = 0;
    	}
    	else {
    		$enable = 1;
    	}
    	Db::query('UPDATE `streams` SET `approved` = '.$enable.' WHERE `id` = '.$id);

    	if ($enable == 1) {
    		$this->system->log('Enabling stream <b>('.$row->name.')</b>', array('module'=>get_class(), 'type'=>'enabling'));
    	}
    	else {
    		$this->system->log('Disabling stream <b>('.$row->name.')</b>', array('module'=>get_class(), 'type'=>'disabling'));
    	}
    }
    
    protected function featured($id) {
    	$id = (int)$id;
    	$row = Db::fetchRow('SELECT `name`, `featured` FROM `streams` WHERE `id` = '.$id.' LIMIT 1');
    	if ($row->featured == 1) {
    		$enable = 0;
    	}
    	else {
    		$enable = 1;
    	}
    	Db::query('UPDATE `streams` SET `featured` = '.$enable.' WHERE `id` = '.$id);

    	if ($enable == 1) {
    		$this->system->log('Adding to featured streams <b>('.$row->name.')</b>', array('module'=>get_class(), 'type'=>'featuring'));
    	}
    	else {
    		$this->system->log('Removing from featured streams <b>('.$row->name.')</b>', array('module'=>get_class(), 'type'=>'unfeaturing'));
    	}
    }
}