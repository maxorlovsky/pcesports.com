<?php

class Logs
{
	public $system;
	public $logs = array();

	function __construct($params = array()) {
		$this->system = $params['system'];
		
		$this->logs = Db::fetchRows('
			SELECT `l`.*, `a`.`login` '.
			'FROM `tm_logs` AS `l` '.
			'LEFT JOIN `tm_admins` AS `a` ON `l`.`user_id` = `a`.`id` '.
			'ORDER BY `id` DESC LIMIT 20'
		);

		return $this;
	}
}

/*
$pages = ajaxPages(20, 3, $_POST['var1'], PREFIX.'admin_log', '');
$pages = explode('!',$pages);
$npp = $pages[0];
$strt = $pages[1];
$fpnd = $pages[2];

$admin_log = fquery('SELECT `login`, `time`, `ip`, `answer` FROM `'.PREFIX.'admin_log` ORDER BY `id` DESC LIMIT '.$strt.', '.$npp, 1, 1);
*/