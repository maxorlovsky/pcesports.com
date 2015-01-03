<?php

class Logs
{
	public $system;
	public $logs = array();
    public $pages;
    public $pageNum = 1;

	function __construct($params = array()) {
		$this->system = $params['system'];
        if (isset($params['var2']) && $params['var2'] == 'page' && $params['var3']) {
            $this->pageNum = (int)$params['var3'];
        }
        
        $this->pages = pages(array(
            'countPerPage'  => 20,
            'pageNum'       => $this->pageNum,
            'tableName'     => 'tm_logs',
            'cms'           => 1,
            'additionalLink'=> '/index',
        ));
		
		$this->logs = Db::fetchRows('
			SELECT `l`.*, `a`.`login` '.
			'FROM `tm_logs` AS `l` '.
			'LEFT JOIN `tm_admins` AS `a` ON `l`.`user_id` = `a`.`id` '.
			'ORDER BY `id` DESC '.
            'LIMIT '.$this->pages->start.', '.(int)$this->pages->countPerPage
		);
        
        foreach($this->logs as &$v) {
            $v->info = Db::escape_tags($v->info, '<b>');
        }
        unset($v);

		return $this;
	}
}