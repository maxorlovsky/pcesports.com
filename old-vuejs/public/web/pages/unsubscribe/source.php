<?php

class unsubscribe
{
	public function __construct($params = array()) {
	}
    
    public function showTemplate() {
        if (isset($_GET['val2']) && $_GET['val2']) {
            if ($this->unsubscribe() === true) {
                include_once _cfg('pages').'/'.get_class().'/success.html';
                return;
            }
        }
        
		include_once _cfg('pages').'/'.get_class().'/error.html';
	}
    
    private function unsubscribe() {
        $row = Db::fetchRow('SELECT * FROM `subscribe` WHERE `unsublink` = "'.Db::escape($_GET['val2']).'" AND `removed` = 0 LIMIT 1');
        if ($row) {
            Db::query('UPDATE `subscribe` SET `removed` = 1 WHERE `id` = '.(int)$row->id);
            return true;
        }
        
        return false;
    }
    
    public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'Unsubscribe';
		
		return $seo;
	}
}