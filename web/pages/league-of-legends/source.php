<?php

class leagueOfLegends
{
	public $news;
	
	function __construct($params = array()) {
		include_once _cfg('pages').'/league-of-legends/index.tpl';
	}
}