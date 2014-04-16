<?php
class Pages
{
    public $languages = array();
    public $system;
    
	function __construct($params = array()) {
		$this->system = $params['system'];
		$this->languages = $this->fetchAvailableLanguages();
        
        if (isset($params['var1']) && $params['var1'] == 'edit' && isset($params['var2'])) {
            $this->editData = $this->fetchEditData($params['var2']);
        }
        
        if (isset($params['var1']) && $params['var1'] == 'delete' && isset($params['var2'])) {
            $this->deleteRow($params['var2']);
            //redirect
            go(_cfg('cmssite').'/#pages');
        }
        
        $this->pages = Db::fetchRows('SELECT `id`, `link`, `value`
		FROM `tm_pages`
		ORDER BY `id` ASC');

		return $this;
	}
    
	public function add($form) {
        if (!$form['title']) {
        	$this->system->log('Adding new page <b>'.at('title_err').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
			return '0;'.at('title_err');
		}
		else {
            $title = Db::escape($form['title']);
			$q = Db::query('SELECT * FROM `tm_pages` WHERE `link` = "'.$title.'"');
			if ($q->num_rows != 0) {
				$this->system->log('Adding new page <b>'.at('page_exist').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
                return '0;'.at('page_exist');
			}
			else {
				Db::query('INSERT INTO `tm_pages` '.
                'SET `link` = "'.$title.'", `value` = "'.Db::escape($form['strings']).'"');
				foreach ($form as $k => $v) {
					$string = explode('_', $k);
					if ($string[0] == 'string') {
						Db::query('UPDATE `tm_pages` '.
                        'SET `'.$string[1].'` = "'.Db::escape($v).'" '.
                        'WHERE `link` = "'.$title.'"');
					}
				}
				
				$this->system->log('Adding new page <b>'.at('page_added').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
                
                return '1;'.at('page_added');
			}
		}

		return '0;Error, contact admin!';
	}
    
    public function edit($form) {
        if (!$form['title']) {
        	$this->system->log('Editing page <b>'.at('title_err').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
			return '0;'.at('title_err');
		}
		else {
			$title = Db::escape($form['title']);
            
            $q = Db::query('SELECT * FROM `tm_pages` WHERE `link` = "'.$title.'" AND `id` != '.(int)$form['page_id']);
			if ($q->num_rows != 0) {
				$this->system->log('Editing page <b>'.at('page_exist').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
                return '0;'.at('page_exist');
			}
                
			Db::query('UPDATE `tm_pages` '.
            'SET `link` = "'.$title.'", `value` = "'.Db::escape($form['strings']).'"'.
            'WHERE `id` = '.(int)$form['page_id']);
			foreach ($form as $k => $v) {
				$string = explode('_', $k);
				if ($string[0] == 'string') {
					Db::query('UPDATE `tm_pages` '.
                    'SET `'.$string[1].'` = "'.Db::escape($v).'" '.
                    'WHERE `link` = "'.$title.'"');
				}
			}
			
			$this->system->log('Editing page <b>'.at('page_updated').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
            
            return '1;'.at('page_updated');
		}
        
        return '0;Error, contact admin!';
    }
    
    protected function fetchAvailableLanguages() {
        return Db::fetchRows('SELECT `title`, `flag` FROM `tm_languages`');
    }
    
    protected function fetchEditData($id) {
        return Db::fetchRow('SELECT * '.
        'FROM `tm_pages` '.
        'WHERE `id` = '.(int)$id.'
        LIMIT 1');
    }
    
    protected function deleteRow($id) {
    	$row = Db::fetchRow('SELECT `link` FROM `tm_pages` WHERE `id` = '.(int)$id);
        Db::fetchRow('DELETE FROM `tm_pages` WHERE `id` = "'.(int)$id.'"');
        $this->system->log('Deleted page <b>'.$row->link.'</b>', array('module'=>get_class(), 'type'=>'delete'));
    }
}