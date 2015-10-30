<?php
class projectConfig
{
    public $system;
    public $project;
    public $strings;
    public $availableStrings = array();
    
	function __construct($params = array()) {
		$this->system = $params['system'];
        
        $this->project = Db::fetchRow(
    		'SELECT * FROM `projects` '.
    		'WHERE `name` = "'.Db::escape($this->system->user->login).'" AND '.
    		'`enabled` = 1 '
		);

        if (!$this->project->name) {
        	exit('Project disabled');
        }
        $this->project->smtp_config = json_decode($this->project->smtp_config);

		$rows = Db::fetchRows('SELECT * FROM `tm_strings` WHERE `key` LIKE "'.$this->project->name.'%"');
		if ($rows) {
			foreach($rows as $v) {
				$this->strings[$v->key] = $v->english;
			}
			$this->strings = (object)$this->strings;
		}

		$this->availableStrings = array(
			$this->project->name.'_payment_info',
			$this->project->name.'_join_tournament_complete',
			$this->project->name.'_agree_with_rules'
		);

		return $this;
	}

	public function edit($form) {
		$smtp_config = new stdClass();
		$smtp_config->host = Db::escape($form['smtp_host']);
		$smtp_config->port = Db::escape($form['smtp_port']);
		$smtp_config->login = Db::escape($form['smtp_login']);
		$smtp_config->password = Db::escape($form['smtp_password']);
		
		Db::query('UPDATE `projects` '.
			'SET `team_name` = "'.Db::escape($form['team_name']).'", '.
            '`challonge_link` = "'.Db::escape($form['challonge_link']).'", '.
            '`challonge_key` = "'.Db::escape($form['challonge_key']).'", '.
            '`widget_url` = "'.Db::escape($form['widget_url']).'", '.
            '`additional_mail_text` = "'.Db::escape($form['additional_mail_text']).'", '.
            '`smtp_config` = "'.Db::escape(json_encode($smtp_config)).'" '.
			'WHERE `name` = "'.Db::escape($this->system->user->login).'" AND '.
			'`enabled` = 1 '
		);

		foreach ($form as $k => $v) {
			if (in_array($k, $this->availableStrings)) {
				Db::query(
					'INSERT IGNORE INTO `tm_strings` '.
					'SET `key` = "'.Db::escape($k).'", '.
					'`english` = "'.Db::escape($v).'" '.
					'ON DUPLICATE KEY '.
					'UPDATE `english` = "'.Db::escape($v).'" '
				);
			}
		}
            
		$this->system->log('Editing project <b>Project updated</b> ('.$id.')', array('module'=>get_class(), 'type'=>'edit'));
        
		return '1;Project updated';
	}
}