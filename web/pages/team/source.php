<?php

class team
{
    public $team = array();
    
	public function __construct($params = array()) {
        $rows = Db::fetchRows(
            'SELECT `id`, `name`, `avatar` FROM `users` '.
            'WHERE `id` = 1 OR '. //max
            '`id` = 44 OR '. //serge
            '`id` = 112 OR '. //anya
            '`id` = 132 ' //arturs
        );
        
        $this->team = array(
            1 => array('avatar' => '', 'name' => '', 'role' => 'Founder', 'email' => 'maxtream (at) pcesports (dot) com'),
            2 => array('avatar' => '', 'name' => 'Aven', 'role' => 'Co-founder', 'email' => 'aven (at) pcesports (dot) com'),
            112 => array('avatar' => '', 'name' => '', 'role' => 'Graphic designer', 'email' => '&nbsp;'),
            44 => array('avatar' => '', 'name' => '', 'role' => 'Communications manager', 'email' => 'connect (at) pcesports (dot) com'),
            3 => array('avatar' => '', 'name' => 'Angel-ada', 'role' => 'Community manager (VK)', 'email' => '&nbsp;'),
            4 => array('avatar' => '', 'name' => 'Acolent', 'role' => 'Shoutcaster', 'email' => '&nbsp;'),
            132 => array('avatar' => '', 'name' => '', 'role' => 'Graphic designer', 'email' => '&nbsp;'),
            5 => array('avatar' => '', 'name' => 'Soldecroix', 'role' => 'Hearthstone judge', 'email' => '&nbsp;'),
        );
        
        if ($rows) {
            foreach($rows as $v) {
                $this->team[$v->id]['name'] = $v->name;
                $this->team[$v->id]['avatar'] = $v->avatar;
            }
        }
	}
	
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}