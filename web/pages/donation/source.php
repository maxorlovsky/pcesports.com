<?php

class donation extends system
{
    public function __construct($params = array()) {
        
	}

	public function showTemplate() {
        include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}