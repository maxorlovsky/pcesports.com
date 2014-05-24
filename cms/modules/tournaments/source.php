<?php

class Tournaments
{
	function __construct($params = array()) {
		
		$this->system = $params['system'];
		
		$this->chats = Db::fetchRows('SELECT `f`.`match_id`, `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2` '.
			'FROM `fights` AS `f` '.
			'LEFT JOIN `teams` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
			'LEFT JOIN `teams` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
			'WHERE `f`.`done` = 0 '
		);

		return $this;
	}
	
	public function fetchChat($form) {
		foreach($form as $v) {
			$fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$v[1].'.txt';
			$return[$v[0]] = '<p id="notice">Chat is empty</p>';
			if (file_exists($fileName)) {
				$return[$v[0]] = str_replace(';', '', strip_tags(stripslashes(html_entity_decode(file_get_contents($fileName))), '<p><b><a><span>'));
			}
		}
		
		return json_encode($return);
	}
	
	public function sendChat($form) {
		$fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$form[1].'.txt';
		if (substr($form[0], 0, 1) == '/') {
			$breakdown = explode('/', $form[0]);
			$command = $breakdown[1];
			if ($command == 'end') {
				Db::query('UPDATE `fights` SET `done` = 1 WHERE `match_id` = '.(int)$form[2]);
				return '0;ENDED';
			}
			else {
				return '0;Command not found';
			}
		}
		else {
			$file = fopen($fileName, 'a');
			$content = '<p><span id="notice">('.date('H:i:s', time()).')</span> &#60;Pentaclick Admin&#62; - '.$form[0].'</p>';
			fwrite($file, htmlspecialchars($content));
			fclose($file);
		}
		
		return '1;1';
	}
	
	public function statusCheck($form) {
		$playersStatus = array();
		foreach($form as $v) {
			$breakdown = explode('_vs_', $v);
			$rows = Db::fetchRows('SELECT `online` FROM `teams` '.
				'WHERE `id` = '.(int)$breakdown[0].' OR `id` = '.(int)$breakdown[1].' '.
				'LIMIT 2'
			);
			
			foreach($rows as $k => $v2) {
				if ($v2->online+30 >= time()) {
                    $playersStatus[$breakdown[$k]] = 'online';
                }
                else {
                    $playersStatus[$breakdown[$k]] = 'offline';
                }
			}
		}
		
		return json_encode($playersStatus);
	}
}