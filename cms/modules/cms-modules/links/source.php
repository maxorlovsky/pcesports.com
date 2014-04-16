<?php
class Links
{
    public $languages = array();
    public $positions = array(0=>'Top menu', 1=>'Bottom menu'); //If there are more places where links suppose to be use array(0=>'menu1',1=>'menu2')
    public $system;
    public $links = array();
    public $sublinks = array();
    
	function __construct($params = array()) {
		$this->system = $params['system'];
		$this->languages = $this->fetchAvailableLanguages();
        
        if (isset($params['var1']) && $params['var1'] == 'edit' && isset($params['var2'])) {
            $this->editData = $this->fetchEditData($params['var2']);
        }
        
        if (isset($params['var1']) && $params['var1'] == 'delete' && isset($params['var2'])) {
            $this->deleteRow($params['var2']);
            //redirect
            go(_cfg('cmssite').'/#links');
        }
        
        //Enable/disable
        if (isset($params['var1']) && $params['var1'] == 'able' && isset($params['var2'])) {
        	$this->able($params['var2']);
        	//redirect
        	go(_cfg('cmssite').'/#links');
        }
        
        //MoveUp
        if (isset($params['var1']) && $params['var1'] == 'moveup' && isset($params['var2'])) {
        	$this->moveUp($params['var2']);
        	//redirect
        	go(_cfg('cmssite').'/#links');
        }
        
        //MoveDown
        if (isset($params['var1']) && $params['var1'] == 'movedown' && isset($params['var2'])) {
        	$this->moveDown($params['var2']);
        	//redirect
        	go(_cfg('cmssite').'/#links');
        }
        
        $this->links = Db::fetchRows('SELECT `l`.`id`, `l`.`link`, `l`.`position`, `l`.`able`, `l`.`value`, `l`.`block` '.
		'FROM `tm_links` AS `l` '.
        'LEFT JOIN `tm_strings` AS `s` ON `l`.`value` = `s`.`key` '.
        'WHERE `l`.`main_link` = 0 '.
		'ORDER BY `l`.`position`, `l`.`block`, `l`.`id`');
        
        $this->sublinks = Db::fetchRows('SELECT `l`.`id`, `l`.`link`, `l`.`main_link`, `l`.`position`, `l`.`able`, `l`.`value`, `l`.`block` '.
        'FROM `tm_links` AS `l` '.
        'LEFT JOIN `tm_strings`  AS `s` ON `l`.`value` = `s`.`key` '.
        'WHERE `l`.`main_link` != "0" '.
        'ORDER BY `l`.`position`, `l`.`id`');
        
		return $this;
	}
    
	public function add($form) {
        if (!$form['title']) {
        	$this->system->log('Adding new link <b>'.at('title_err').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
			return '0;'.at('title_err');
		}
		else if (strpos($form['title'], ' ')) {
			$this->system->log('Adding new link <b>'.at('title_have_spaces').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
			return '0;'.at('title_have_spaces');
		}
		else {
            $title = Db::escape($form['title']);
			$q = Db::query('SELECT * FROM `tm_links` WHERE `value` = "web-link-'.$title.'" LIMIT 1');
			if ($q->num_rows != 0) {
				$this->system->log('Adding new link <b>'.at('link_exist').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
                return '0;'.at('link_exist');
			}
			else {
				$row = Db::fetchRow('SELECT `position` FROM `tm_links` ORDER BY `position` DESC LIMIT 1');
				$position = intval($row->position) + 1;
				
				Db::query('INSERT INTO `tm_links` '.
                	'SET `value` = "web-link-'.$title.'", '.
					'`main_link` = "'.intval($form['main_link']).'", '.
					'`position` = '.$position.', '.
					'`block` = "'.Db::escape($form['link_block']).'", '.
					'`link` = "'.Db::escape($form['href']).'" '
				);
				Db::query('INSERT INTO `tm_strings` SET `key` = "web-link-'.$title.'", `status` = 1');
				
				foreach ($form as $k => $v) {
					$string = explode('_', $k);
					if ($string[0] == 'string') {
						Db::query('UPDATE `tm_strings` '.
                        'SET `'.$string[1].'` = "'.Db::escape($v).'" '.
                        'WHERE `key` = "web-link-'.$title.'"');
					}
				}
				
				$this->system->log('Adding new link <b>'.at('link_added').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
                
                return '1;'.at('link_added');
			}
		}

		return '0;Error, contact admin!';
	}
    
    public function edit($form) {
    	$title = Db::escape($form['title']);
    	$linkId = (int)$form['link_id'];
    	$oldValue = Db::escape($form['link_value']);
    	
    	if (!$form['title']) {
    		$this->system->log('Editing link <b>'.at('title_err').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
    		return '0;'.at('title_err');
    	}
    	else if (strpos($form['title'], ' ')) {
    		$this->system->log('Editing link <b>'.at('title_have_spaces').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
    		return '0;'.at('title_have_spaces');
    	}
    	else if ($oldValue != $title) {
    		$q = Db::query('SELECT * FROM `tm_links` WHERE `value` = "web-link-'.$title.'" LIMIT 1');
    		if ($q->num_rows != 0) {
    			$this->system->log('Editing link <b>'.at('link_exist').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
    			return '0;'.at('link_exist');
    		}
    		else {
    			Db::query('UPDATE `tm_links` '.
	    			'SET `value` = "web-link-'.$title.'", '.
	    			'`main_link` = "'.(int)$form['main_link'].'", '.
	    			'`block` = "'.$form['link_block'].'", '.
	    			'`link` = "'.Db::escape($form['href']).'" '.
	    			'WHERE `id` = "'.$linkId.'"'
    			);
    			Db::query('INSERT INTO `tm_strings` SET `key` = "web-link-'.$title.'", `status` = 1');
    			Db::query('DELETE FROM `tm_strings` WHERE `key` = "web-link-'.$oldValue.'"');
    			foreach ($form as $k => $v) {
					$string = explode('_', $k);
					if ($string[0] == 'string') {
						Db::query('UPDATE `tm_strings` '.
                        	'SET `'.$string[1].'` = "'.Db::escape($v).'" '.
                        	'WHERE `key` = "web-link-'.$title.'"'
    					);
					}
				}
    			
    			$this->system->log('Editing link <b>'.at('link_updated').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
    			
    			return '1;'.at('link_updated');
    		}
    	}
		else {
			Db::query('UPDATE `tm_links` '.
    			'SET `value` = "web-link-'.$title.'", '.
    			'`main_link` = "'.(int)$form['main_link'].'", '.
    			'`block` = "'.$form['link_block'].'", '.
    			'`link` = "'.Db::escape($form['href']).'" '.
    			'WHERE `id` = "'.$linkId.'"'
    		);
    		foreach ($form as $k => $v) {
				$string = explode('_', $k);
				if ($string[0] == 'string') {
					Db::query('UPDATE `tm_strings` '.
                        'SET `'.$string[1].'` = "'.Db::escape($v).'" '.
                    	'WHERE `key` = "web-link-'.$title.'"'
    				);
				}
			}
				
			$this->system->log('Editing link <b>'.at('link_updated').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
			
            return '1;'.at('link_updated');
		}
        
        return '0;Error, contact admin!';
    }
    
    protected function fetchAvailableLanguages() {
        return Db::fetchRows('SELECT `title`, `flag` FROM `tm_languages`');
    }
    
    protected function fetchEditData($id) {
        $row = Db::fetchRow('SELECT * '.
        'FROM `tm_links` AS `l` '.
        'LEFT JOIN `tm_strings`  AS `s` ON `l`.`value` = `s`.`key` '.
        'WHERE `l`.`id` = '.(int)$id.' '.
        'LIMIT 1');
        $row->value = str_replace('web-link-', '', $row->value);
        
		return $row;
    }
    
    protected function deleteRow($id) {
    	$id = (int)$id;
        $row = Db::fetchRow('SELECT `value` FROM `tm_links` WHERE `id` = '.$id.' LIMIT 1');
        
        Db::query('DELETE FROM `tm_links` WHERE `id` = '.$id);
        Db::query('DELETE FROM `tm_strings` WHERE `key` = "'.$row->value.'"');
        
        $rows = Db::fetchRows('SELECT `id` FROM `tm_links` WHERE `main_link` = '.$id.' AND `main_link` != 0');
        if ($rows) {
	        foreach($rows as $v) {
	        	Db::query('UPDATE `tm_links` SET `main_link` = 0 WHERE `id` = "'.$v->id.'"');
	        }
        }
        
        $this->recalculateLocations();
        
        $this->system->log('Deleted link <b>'.$row->value.'</b>', array('module'=>get_class(), 'type'=>'delete'));
    }
    
    protected function able($id) {
    	$id = (int)$id;
    	$row = Db::fetchRow('SELECT `id`, `value`, `able` FROM `tm_links` WHERE `id` = '.$id.' LIMIT 1');
    	if ($row->able == 1) {
    		$enable = 0;
    	}
    	else {
    		$enable = 1;
    	}
    	Db::query('UPDATE `tm_links` SET `able` = '.$enable.' WHERE `id` = '.$id);

    	if ($enable == 1) {
    		$this->system->log('Enabling link <b>('.str_replace('web-link-','',$row->value).' ['.$row->id.'])</b>', array('module'=>get_class(), 'type'=>'enabling'));
    	}
    	else {
    		$this->system->log('Disabling link <b>('.str_replace('web-link-','',$row->value).' ['.$row->id.'])</b>', array('module'=>get_class(), 'type'=>'disabling'));
    	}
    	
    }
    
    protected function moveUp($id) {
    	$id = (int)$id;
    	$row = Db::fetchRow('SELECT `position` FROM `tm_links` WHERE `id` = '.$id.' LIMIT 1');
    	$position = $row->position - 1;
    	
		Db::query('UPDATE `tm_links` SET `position` = '.($position+1).' WHERE `position` <= '.$position.' ORDER BY  `position` DESC  LIMIT 1');
		Db::query('UPDATE `tm_links` SET `position` = '.$position.' WHERE `id` = '.$id);
    	
    	$this->recalculateLocations();
    
    	$this->system->log('Moving link UP <b>('.$id.')</b>', array('module'=>get_class(), 'type'=>'moveup'));
    }
    
    protected function moveDown($id) {
    	$id = (int)$id;
    	$row = Db::fetchRow('SELECT `position` FROM `tm_links` WHERE `id` = '.$id.' LIMIT 1');
    	$position = $row->position + 1;
    	Db::query('UPDATE `tm_links` SET `position` = '.($position-1).' WHERE `position` >= '.$position.' LIMIT 1');
    	Db::query('UPDATE `tm_links` SET `position` = '.$position.' WHERE `id` = '.$id);
    	 
    	$this->recalculateLocations();
    
    	$this->system->log('Moving link DOWN <b>('.$id.')</b>', array('module'=>get_class(), 'type'=>'movedown'));
    }
    
    protected function recalculateLocations() {
    	$rows = Db::fetchRows('SELECT `id` FROM `tm_links` ORDER BY `position`');
    	$i = 1;
    	if ($rows) {
	    	foreach($rows as $v) {
	    		Db::query('UPDATE `tm_links` SET position = '.$i.' WHERE id = '.$v->id);
	    		++$i;
	    	}
    	}
    }
}