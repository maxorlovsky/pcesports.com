<?php

class blog
{
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
    
    public static function getSeo() {        
        $seo = array(
            'title' => 'Blog'
        );
        
        return (object)$seo;
    }
}