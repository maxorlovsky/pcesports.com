<?php

class Logs
{
	public $system;
	public $logs = array();
    public $pages;
    public $pageNum = 1;
    public $modules;
    public $types;
    public $pickedModule;
    public $pickedType;

	function __construct($params = array()) {
		$this->system = $params['system'];
        if (isset($params['var2']) && $params['var2'] == 'page' && $params['var3']) {
            $this->pageNum = (int)$params['var3'];
        }

        if (isset($params['var5']) && $params['var5'] != '0' && $params['var5']) {
            $this->pickedModule = $params['var5'];
        }
        if (isset($params['var7']) && $params['var7'] != '0' && $params['var7']) {
            $this->pickedType = $params['var7'];
        }

        $this->types = array(
            'success',
            'fail',
            'edit',
            'add',
            'delete',
            'enabling',
            'disabling',
            'moveup',
            'movedown',
            'language',
        );
        sort($this->types);

        //Getting official CMS modules
        if ($this->system->data->settings) {
            foreach($this->system->data->settings as $k => $v) {
                if (substr($k, 0, 4) != 'site') {
                    $this->modules[] = $k;
                }
            }
        }
        //Adding additional "modules"
        $this->modules[] = 'login';
        $this->modules[] = 'logout';
        sort($this->modules);

        //Getting custom modules
        if ($this->system->data->modules) {
            foreach($this->system->data->modules as $f) {
                $this->modules[$f->name] = at('custom').': '.ucfirst($f->name);
            }
        }

        //Adding WHERE if some module or type picked
        $where = '';
        if ($this->pickedModule || $this->pickedType) {
            $where = 'WHERE ';
            $moduleAdded = 0;
            if ($this->pickedModule) {
                $where .= '`module` = "'.Db::escape_tags($this->pickedModule).'" ';
                $moduleAdded = 1;
            }
            if ($this->pickedType) {
                if ($moduleAdded) {
                    $where .= 'AND ';
                }
                $where .= '`type` = "'.Db::escape_tags($this->pickedType).'" ';
            }
        }
        
        $this->pages = pages(array(
            'countPerPage'  => 20,
            'pageNum'       => $this->pageNum,
            'tableName'     => 'tm_logs',
            'cms'           => 1,
            'additionalLink'=> '/index',
            'where'         => $where,
        ));
		
		$this->logs = Db::fetchRows('
			SELECT `l`.*, `a`.`login` '.
			'FROM `tm_logs` AS `l` '.
			'LEFT JOIN `tm_admins` AS `a` ON `l`.`user_id` = `a`.`id` '.
            $where.
			'ORDER BY `id` DESC '.
            'LIMIT '.$this->pages->start.', '.(int)$this->pages->countPerPage
		);
        
        if ($this->logs) {
            foreach($this->logs as &$v) {
                $v->info = Db::escape_tags($v->info, '<b>');
            }
            unset($v);
        }

		return $this;
	}
}