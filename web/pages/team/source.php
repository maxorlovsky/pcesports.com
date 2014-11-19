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
            '`id` = 132 OR '. //arturs
            '`id` = 209 OR '. //zanko
            '`id` = 213 OR '. //aven
            '`id` = 126 OR '. //angel-ada
            '`id` = 220 ' //darkwing229
            
        );
        
        $this->team = array(
            1 => array('avatar' => '', 'name' => '', 'role' => 'Founder', 'contact' => 'info (at) pcesports (dot) com'),
            213 => array('avatar' => '', 'name' => '', 'role' => 'Co-founder', 'contact' => 'aven (at) pcesports (dot) com'),
            112 => array('avatar' => '', 'name' => '', 'role' => 'Graphic designer', 'contact' => '&nbsp;'),
            44 => array('avatar' => '', 'name' => '', 'role' => 'Communications manager', 'contact' => 'connect (at) pcesports (dot) com'),
            126 => array('avatar' => '', 'name' => '', 'role' => 'Community manager (VK)', 'contact' => 'bugerman21 (at) pisem (dot) net'),
            4 => array('avatar' => '', 'name' => 'Acolent', 'role' => 'Shoutcaster', 'contact' => '&nbsp;'),
            132 => array('avatar' => '', 'name' => '', 'role' => 'Graphic designer', 'contact' => '&nbsp;'),
            5 => array('avatar' => '', 'name' => 'Soldecroix', 'role' => 'Hearthstone judge', 'contact' => '&nbsp;'),
            209 => array('avatar' => '', 'name' => '', 'role' => 'LoL EUNE manager', 'contact' => 'izaanko (at) pcesports (dot) com'),
            220 => array('avatar' => '', 'name' => '', 'role' => 'Video Editor', 'contact' => '&nbsp;'),
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