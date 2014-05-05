<?php

class team
{
	public $news;
	
	function __construct($params = array()) {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}