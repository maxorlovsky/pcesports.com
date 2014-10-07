<?php
class TournamentList
{
    public $system;
	public $tournaments = array();
    
	function __construct($params = array()) {
		
		$this->system = $params['system'];
        
        if (isset($params['var1']) && $params['var1'] == 'edit' && isset($params['var2'])) {
			$this->editData = $this->fetchEditData($params['var2']);
		}
		
        if (isset($params['var1']) && $params['var1'] == 'delete' && isset($params['var2'])) {
			$this->deleteRow($params['var2']);
			//redirect
			go(_cfg('cmssite').'/#tournamentList');
		}
		
		$this->tournaments = Db::fetchRows('SELECT * FROM `tournaments` ORDER BY `id` DESC');

		return $this;
	}
    
    public function add($form) {
        if (!$form['name']) {
			$this->system->log('Adding tournament <b>Name not set</b>', array('module'=>get_class(), 'type'=>'add'));
			return '0;Name not set';
		}
        else if (!$form['game']) {
            $this->system->log('Adding tournament <b>Game not picked</b>', array('module'=>get_class(), 'type'=>'add'));
			return '0;Game not picked';
        }
        else if (!$form['datesRegistration']) {
            $this->system->log('Adding tournament <b>Dates (registration) not set</b>', array('module'=>get_class(), 'type'=>'add'));
			return '0;Dates (registration) not set';
        }
        else if (!$form['datesStart']) {
            $this->system->log('Adding tournament <b>Dates (start) not set</b>', array('module'=>get_class(), 'type'=>'add'));
			return '0;Dates (start) not set';
        }
        else if (!$form['prize']) {
            $this->system->log('Adding tournament <b>Prize not set</b>', array('module'=>get_class(), 'type'=>'add'));
			return '0;Prize not set';
        }
        else if (!$form['status']) {
            $this->system->log('Adding tournament <b>Status not set</b>', array('module'=>get_class(), 'type'=>'add'));
			return '0;Status not set';
        }
		else {
			Db::query('INSERT INTO `tournaments` SET '.
				'`game` = "'.Db::escape($form['game']).'", '. 
                '`server` = "'.Db::escape($form['server']).'", '. 
                '`name` = "'.Db::escape($form['name']).'", '.
                '`dates_registration` = "'.Db::escape($form['datesRegistration']).'", '.
                '`dates_start` = "'.Db::escape($form['datesStart']).'", '.
                '`time` = "'.Db::escape($form['time']).'", '.
                '`prize` = "'.Db::escape($form['prize']).'", '.
                '`max_num` = "'.Db::escape($form['maxNum']).'", '.
                '`status` = "'.Db::escape($form['status']).'" '
			);
            $lastId = Db::lastId();
			
			$this->system->log('Adding tournament <b>Tournament added</b> ('.$lastId.')', array('module'=>get_class(), 'type'=>'add'));

			return '1;Tournament added';
			
		}

		return '0;Error, contact admin!';
	}

	public function edit($form) {
		if (!$form['name']) {
			$this->system->log('Editing tournament <b>Name not set</b> ('.$id.')', array('module'=>get_class(), 'type'=>'edit'));
			return '0;Name not set';
		}
        else if (!$form['game']) {
            $this->system->log('Editing tournament <b>Game not picked</b> ('.$id.')', array('module'=>get_class(), 'type'=>'edit'));
			return '0;Game not picked';
        }
        else if (!$form['datesRegistration']) {
            $this->system->log('Editing tournament <b>Dates (registration) not set</b> ('.$id.')', array('module'=>get_class(), 'type'=>'edit'));
			return '0;Dates (registration) not set';
        }
        else if (!$form['datesStart']) {
            $this->system->log('Editing tournament <b>Dates (start) not set</b> ('.$id.')', array('module'=>get_class(), 'type'=>'edit'));
			return '0;Dates (start) not set';
        }
        else if (!$form['prize']) {
            $this->system->log('Editing tournament <b>Prize not set</b> ('.$id.')', array('module'=>get_class(), 'type'=>'edit'));
			return '0;Prize not set';
        }
        else if (!$form['status']) {
            $this->system->log('Editing tournament <b>Status not set</b> ('.$id.')', array('module'=>get_class(), 'type'=>'edit'));
			return '0;Status not set';
        }
		else {
			$id = (int)$form['id'];
			
			Db::query('UPDATE `tournaments` '.
				'SET `game` = "'.Db::escape($form['game']).'", '. 
                '`server` = "'.Db::escape($form['server']).'", '. 
                '`name` = "'.Db::escape($form['name']).'", '.
                '`dates_registration` = "'.Db::escape($form['datesRegistration']).'", '.
                '`dates_start` = "'.Db::escape($form['datesStart']).'", '.
                '`time` = "'.Db::escape($form['time']).'", '.
                '`prize` = "'.Db::escape($form['prize']).'", '.
                '`max_num` = "'.Db::escape($form['maxNum']).'", '.
                '`status` = "'.Db::escape($form['status']).'" '.
				'WHERE `id` = '.$id
			);
            
			$this->system->log('Editing tournament <b>Tournament updated</b> ('.$id.')', array('module'=>get_class(), 'type'=>'edit'));
            
			return '1;Tournament updated';
		}

		return '0;Error, contact admin!';
	}

	protected function fetchEditData($id) {
		return Db::fetchRow('SELECT * '.
			'FROM `tournaments` '.
			'WHERE `id` = '.(int)$id.' '.
			'LIMIT 1'
		);
	}

	protected function deleteRow($id) {
		Db::query('DELETE FROM `tournaments` WHERE `id` = '.(int)$id);
		$this->system->log('Deleting tournament <b>'.$id.'</b>', array('module'=>get_class(), 'type'=>'delete'));
	}
}