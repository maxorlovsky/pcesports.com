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
        $where = '';
        if ($this->data->settings['tournament-start-hs-s1'] == 1) {
            $where .= '`game` = "hs" AND `tournament_id` = '.(int)$this->data->settings['hs-current-number-s1'].' ';
        }
        if ($this->data->settings['tournament-start-lol-euw'] == 1 || $this->data->settings['tournament-start-lol-eune'] == 1) {
            $where .= '`game` = "lol" AND (`tournament_id` = '.(int)$this->data->settings['lol-current-number-euw'].' OR `tournament_id` = '.(int)$this->data->settings['lol-current-number-eune'].') ';
        }
        if ($this->data->settings['tournament-start-smite-na'] == 1 || $this->data->settings['tournament-start-smite-eu'] == 1) {
            $where .= '`game` = "smite" AND (`tournament_id` = '.(int)$this->data->settings['smite-current-number-na'].' OR `tournament_id` = '.(int)$this->data->settings['smite-current-number-eu'].') ';
        }

        if ($where) {
            $eventStreams = Db::fetchRows(
                'SELECT `id`, `name`, `display_name`, `game`, `viewers`, IF(`online` >= '.(time()-360).', 1, 0) AS `onlineStatus`, 1 AS `event`, `name` AS `link` '.
                'FROM `streams_events` '.
                'WHERE '.$where.
                'ORDER BY `viewers` DESC '
            );
        }
        
        $this->streams = Db::fetchRows('SELECT `id`, `name`, `display_name`, `featured`, `game`, `viewers`, IF(`online` >= '.(time()-360).', 1, 0) AS `onlineStatus`, `name` AS `link` FROM `streams` '.
            'WHERE `online` != 0 AND '.
            '`approved` = 1 '.
            'ORDER BY `onlineStatus` DESC, `featured` DESC, `viewers` DESC '
        );

        if ($eventStreams && $this->streams) {
            $this->streams = (object)array_merge((array)$eventStreams, (array)$this->streams);
        }
        else if ($eventStreams) {
            $this->streams = (object)$eventStreams;
        }

        
        if (isset($_GET['val2']) && $_GET['val2']) {
            $this->pickedStream = (int)$_GET['val2'];
        }
        
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
		/*if (isset($_GET['val1']) && $_GET['val1'] == 'stream') {
			$this->getStream();
		}
		else {*/
			$this->getStreamList();
		//}
	}
}