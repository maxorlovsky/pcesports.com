<?php

class team extends system
{
    public $team;
    public $data;

	public function __construct($params = array()) {
        $this->data = $params->data;
        $this->team = Db::fetchRow(
            'SELECT * FROM `teams` '.
            'WHERE LOWER(`name`) = "'.strtolower(urldecode($_GET['val2'])).'" '.
            'LIMIT 1 '
        );

        $this->team->members = Db::fetchRows(
            'SELECT `u`.*, `t2u`.`title` '.
            'FROM `teams2users` AS `t2u` '.
            'LEFT JOIN `users` AS `u` ON `t2u`.`user_id` = `u`.`id` '.
            'WHERE `t2u`.`team_id` = '.$this->team->id
        );
	}

	public function showTemplate() {
        if (!$this->team) {
            include_once _cfg('pages').'/404/error.tpl';
        }
        else {
            //Checks if this is the captain of a team and trying to enter manage page
            if (isset($_GET['val3']) && $_GET['val3'] == 'manage' && $this->team->user_id_captain == $this->data->user->id) {
                include_once _cfg('pages').'/'.get_class().'/manage.tpl';
            }
            else {
                include_once _cfg('pages').'/'.get_class().'/index.tpl';
            }
        }
	}
}