<?php
class TournamentList
{
    public $system;
	public $tournaments = array();
    public $availableGames = array();
    public $availableServers = array();
    public $project;
    
	function __construct($params = array()) {
		$this->system = $params['system'];

		//Checking if project specific access
		$row = Db::fetchRow('SELECT * FROM `projects` WHERE `name` = "'.Db::escape($this->system->user->login).'"');

		if ($row) {
			if ($row->enabled == 0) {
				exit('Project disabled');
			}
			$this->project = $row->name;

			$this->availableGames = array(
			    'lol' => 'League of Legends',
			    'hs' => 'Hearthstone',
			    /*'dota' => 'Dota 2',
	            'cs' => 'CS:Global Offensive',*/
			);

			$this->availableServers = array(
				'euw' 	=> 'EUW',
				'eune'	=> 'EUNE',
			);
		}
		else {
			$this->availableGames = array(
	            'lol' => 'League of Legends',
	            'hs' => 'Hearthstone',
	        );

	        $this->availableServers = array(
				'euw' 	=> 'EUW',
				'eune'	=> 'EUNE',
				's2'	=> 'Season 2',
				's1'	=> 'Season 1',
			);
		}
        
        if (isset($params['var1']) && $params['var1'] == 'edit' && isset($params['var2'])) {
			$this->editData = $this->fetchEditData($params['var2']);
		}
		
        if (isset($params['var1']) && $params['var1'] == 'delete' && isset($params['var2'])) {
			$this->deleteRow($params['var2']);
			//redirect
			go(_cfg('cmssite').'/#tournamentList');
		}
		
		if ($this->project) {
			$this->tournaments = Db::fetchRows(
				'SELECT * FROM `tournaments_external` '.
				'WHERE `project` = "'.$this->project.'" '.
				'ORDER BY `id` DESC '
			);
		}
		else {
			$this->tournaments = Db::fetchRows('SELECT * FROM `tournaments` ORDER BY `id` DESC');
		}

		return $this;
	}
    
    public function add($form) {
    	$row = Db::fetchRows(
			'SELECT * FROM `tournaments_external` '.
			'WHERE `project` = "'.$this->project.'" AND '.
			'`game` = "'.Db::escape($form['game']).'" AND '. 
			($form['game']=='lol'?'`server` = "'.Db::escape($form['server']).'" AND ':null).
            '`status` != "ended" '.
			'ORDER BY `id` DESC '
		);

    	if ($row) {
			$this->system->log('Adding tournament <b>There is already active tournament for this game/server</b>', array('module'=>get_class(), 'type'=>'add'));
			return '0;There is already active tournament for this game/server';
		}
        else if (!$form['name']) {
			$this->system->log('Adding tournament <b>Name not set</b>', array('module'=>get_class(), 'type'=>'add'));
			return '0;Name not set';
		}
        else if (!$form['game']) {
            $this->system->log('Adding tournament <b>Game not picked</b>', array('module'=>get_class(), 'type'=>'add'));
			return '0;Game not picked';
        }
        else if ($form['game'] == 'lol' && !$form['server']) {
        	$this->system->log('Adding tournament <b>Addition/server not picked</b>', array('module'=>get_class(), 'type'=>'add'));
			return '0;Addition/server not picked';	
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
		else {
			if ($this->project) {
				Db::query('INSERT INTO `tournaments_external` SET '.
					'`project` = "'.Db::escape($this->project).'", '.
					'`game` = "'.Db::escape($form['game']).'", '. 
	                '`server` = "'.Db::escape($form['server']).'", '. 
	                '`name` = "'.Db::escape($form['name']).'", '.
	                '`dates_registration` = "'.Db::escape($form['datesRegistration']).'", '.
	                '`dates_start` = "'.Db::escape($form['datesStart']).'", '.
	                '`time` = "'.Db::escape($form['time']).'", '.
	                '`event_id` = '.(int)$form['eventId'].', '.
	                '`prize` = "'.Db::escape($form['prize']).'", '.
	                '`max_num` = "'.Db::escape($form['maxNum']).'", '.
	                '`status` = "upcoming" '
				);
			}
			else {
				Db::query('INSERT INTO `tournaments` SET '.
					'`game` = "'.Db::escape($form['game']).'", '. 
	                '`server` = "'.Db::escape($form['server']).'", '. 
	                '`name` = "'.Db::escape($form['name']).'", '.
	                '`dates_registration` = "'.Db::escape($form['datesRegistration']).'", '.
	                '`dates_start` = "'.Db::escape($form['datesStart']).'", '.
	                '`time` = "'.Db::escape($form['time']).'", '.
	                '`event_id` = '.(int)$form['eventId'].', '.
	                '`prize` = "'.Db::escape($form['prize']).'", '.
	                '`max_num` = "'.Db::escape($form['maxNum']).'", '.
	                '`status` = "upcoming" '
				);
			}
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
			
			if ($this->project) {
				Db::query('UPDATE `tournaments_external` '.
					'SET `name` = "'.Db::escape($form['name']).'", '.
	                '`dates_registration` = "'.Db::escape($form['datesRegistration']).'", '.
	                '`dates_start` = "'.Db::escape($form['datesStart']).'", '.
	                '`time` = "'.Db::escape($form['time']).'", '.
	                '`event_id` = '.(int)$form['eventId'].', '.
	                '`prize` = "'.Db::escape($form['prize']).'", '.
	                '`max_num` = "'.Db::escape($form['maxNum']).'", '.
	                '`status` = "'.Db::escape($form['status']).'" '.
					'WHERE `id` = '.$id.' AND '.
					'`project` = "'.Db::escape($this->project).'" '
				);
			}
			else {
				Db::query('UPDATE `tournaments` '.
					'SET `name` = "'.Db::escape($form['name']).'", '.
	                '`dates_registration` = "'.Db::escape($form['datesRegistration']).'", '.
	                '`dates_start` = "'.Db::escape($form['datesStart']).'", '.
	                '`time` = "'.Db::escape($form['time']).'", '.
	                '`event_id` = '.(int)$form['eventId'].', '.
	                '`prize` = "'.Db::escape($form['prize']).'", '.
	                '`max_num` = "'.Db::escape($form['maxNum']).'", '.
	                '`status` = "'.Db::escape($form['status']).'" '.
					'WHERE `id` = '.$id
				);
			}
            
			$this->system->log('Editing tournament <b>Tournament updated</b> ('.$id.')', array('module'=>get_class(), 'type'=>'edit'));
            
			return '1;Tournament updated';
		}

		return '0;Error, contact admin!';
	}

	protected function fetchEditData($id) {
		if ($this->project) {
			$row = Db::fetchRow('SELECT * '.
				'FROM `tournaments_external` '.
				'WHERE `id` = '.(int)$id.' AND '.
				'`project` = "'.Db::escape($this->project).'" '.
				'LIMIT 1'
			);
		}
		else {
			$row = Db::fetchRow('SELECT * '.
				'FROM `tournaments` '.
				'WHERE `id` = '.(int)$id.' '.
				'LIMIT 1'
			);
		}
		return $row;
	}

	protected function deleteRow($id) {
		if ($this->project) {
			Db::query(
				'DELETE FROM `tournaments_external` '.
				'WHERE `id` = '.(int)$id.' AND '.
				'`project` = "'.Db::escape($this->project).'" '
			);
		}
		else {
			Db::query('DELETE FROM `tournaments` WHERE `id` = '.(int)$id);
		}
		$this->system->log('Deleting tournament <b>'.$id.'</b>', array('module'=>get_class(), 'type'=>'delete'));
	}
}