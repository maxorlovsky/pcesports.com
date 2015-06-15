<?php

class uniconhs extends System
{	
	public function __construct($params = array()) {
        parent::__construct();
	}
	
	public function showTemplate() {
        include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}