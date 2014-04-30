<?php

class errorPage
{
	function __construct($params = array()) {
		include_once _cfg('pages').'/404/error.tpl';
	}
}