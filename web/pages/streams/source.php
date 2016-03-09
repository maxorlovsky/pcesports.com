<?php

class streams extends System
{
	public $streams;
    public $stream;
    public $pickedStream = 0;
	
	public function __construct($params = array()) {
		parent::__construct();
	}
	
	public function getStreamList() {
        $this->streams = Db::fetchRows('SELECT `id`, `name`, `display_name`, `featured`, `game`, `viewers`, IF(`online` >= '.(time()-360).', 1, 0) AS `onlineStatus`, `name` AS `link` FROM `streams` '.
            'WHERE `online` != 0 '.
            'AND `online` > '.(time()-1209600).' '.
            'AND `approved` = 1 '.
            'ORDER BY `onlineStatus` DESC, `featured` DESC, `viewers` DESC '
        );
        
        if (isset($_GET['val2']) && $_GET['val2']) {
            $this->pickedStream = (int)$_GET['val2'];
        }
        
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public function showTemplate() {
		$this->getStreamList();
	}
}