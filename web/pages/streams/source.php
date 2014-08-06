<?php

class streams extends System
{
	public $streams;
    public $stream;
	
	public function __construct($params = array()) {
		parent::__construct();
	}
	
	public function getStreamList() {
        $this->streams = Db::fetchRows('SELECT `id`, `name`, `display_name`, `featured`, `game`, `viewers` FROM `streams` '.
            'WHERE `online` >= '.(time() - 360).' AND '.
            '(`languages` = "'.Db::escape(_cfg('language')).'" OR `languages` = "both") '.
            'ORDER BY `featured` DESC, `viewers` DESC '
		);
		$this->streams = (object)$rearangingNews;
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public function getStream() {
		$this->stream = Db::fetchRows('SELECT `id`, `name`, `display_name`, `featured`, `game`, `viewers` FROM `streams` '.
            'WHERE `id` = '.(int)$_GET['val2'].' '.
            'LIMIT 1'
		);
		
		include_once _cfg('pages').'/'.get_class().'/stream.tpl';
	}
	
	public function showTemplate() {
		if (isset($_GET['val1']) && $_GET['val1'] == 'stream') {
			$this->getStream();
		}
		else {
			$this->getStreamList();
		}
	}
}