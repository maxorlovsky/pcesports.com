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

    /*
    Function from AJAX class
    */
    public function editStreamer($data) {
        if (!$this->logged_in) {
            return '0;'.t('not_logged_in');
        }
        
        $id = (int)$data['id'];
        
        $row = Db::fetchRow(
            'SELECT * FROM `streams` '.
            'WHERE `id` = '.$id.' '.
            'AND `user_id` = '.(int)$this->data->user->id.' '.
            'LIMIT 1 '
        );
        if (!$row) {
            return '0;'.t('error');
        }
        
        Db::query(
            'UPDATE `streams` SET '.
            'WHERE `id` = '.$id.' '.
            'LIMIT 1 '
        );
        
        return '1;1';
    }
    
    public function removeStreamer($data) {
        if (!$this->logged_in) {
            return '0;'.t('not_logged_in');
        }
        
        $id = (int)$data['id'];
        
        $row = Db::fetchRow(
            'SELECT * FROM `streams` '.
            'WHERE `id` = '.$id.' '.
            'AND `user_id` = '.(int)$this->data->user->id.' '.
            'LIMIT 1 '
        );
        if (!$row) {
            return '0;'.t('error');
        }
        
        Db::query(
            'DELETE FROM `streams` '.
            'WHERE `id` = '.$id.' '.
            'LIMIT 1 '
        );
        
        return '1;1';
    }
    
    public function submitStreamer($data) {
        if (!$this->logged_in) {
            return '0;'.t('not_logged_in');
        }
        
        parse_str($data['form'], $post);
        
        if (!isset($post['name']) || !$post['name']) {
            return '0;'.t('input_name');
        }
        $post['name'] = str_replace(array('http://www.twitch.tv/', 'http://twitch.tv/'), array('',''), $post['name']);
        
        $twitch = $this->runTwitchAPI($post['name']);
        
        if (!$twitch) {
            return '0;'.t('channel_not_found');
        }
        
        $row = Db::fetchRow('SELECT * FROM `streams` WHERE `name` = "'.Db::escape($post['name']).'" LIMIT 1');
        if ($row) {
            return '0;'.t('stream_already_registered');
        }
        
        Db::query(
            'INSERT INTO `streams` SET '.
            '`user_id`  = '.(int)$this->data->user->id.', '.
            '`name` = "'.Db::escape($post['name']).'", '.
            '`game` = "other", '.
            '`approved` = 1 '
        );
        
        return '1;'.t('stream_added');
    }
}