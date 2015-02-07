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
            '`id` = 213 OR '. //aven
            '`id` = 126 ' //angel-ada
            
        );
        
        $this->team = array(
            1 => array('avatar' => '', 'name' => '', 'role' => 'Pentaclick Creator', 'contact' => '&nbsp;'),
            44 => array('avatar' => '', 'name' => '', 'role' => 'Master Manager', 'contact' => '&nbsp;'),
            112 => array('avatar' => '', 'name' => '', 'role' => 'Creativity generator', 'contact' => '&nbsp;'),
            213 => array('avatar' => '', 'name' => '', 'role' => 'Co-creator of Pentaclick', 'contact' => '&nbsp;'),
            132 => array('avatar' => '', 'name' => '', 'role' => 'Graphic designer', 'contact' => '&nbsp;'),
            126 => array('avatar' => '', 'name' => '', 'role' => 'Community manager (VK)', 'contact' => '&nbsp;'),
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