<?php

class team
{
    public $team = array();
    
	public function __construct($params = array()) {
        $rows = Db::fetchRows(
            'SELECT `name`, `avatar` FROM `users` '.
            'WHERE `name` = "Maxtream" OR '.
            '`name` = "Serge" OR '.
            '`name` = "AnyaTheEagle" '
        );
        if ($rows) {
            foreach($rows as $v) {
                $this->team[strtolower($v->name)] = $v->avatar;
            }
        }
	}
	
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}