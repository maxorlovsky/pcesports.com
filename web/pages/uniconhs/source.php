<?php

class uniconhs extends System
{	
	public function __construct($params = array()) {
        parent::__construct();

        $this->heroes = array(
            1 => 'warrior',
            2 => 'hunter',
            3 => 'mage',
            4 => 'warlock',
            5 => 'shaman',
            6 => 'rogue',
            7 => 'druid',
            8 => 'paladin',
            9 => 'priest',
        );
	}
	
	public function showTemplate() {
        include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}