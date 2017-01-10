<?php

class tournaments
{
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'Tournaments list';
        
		return $seo;
	}
	
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}