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
        
        $this->pages = Db::fetchRows('SELECT `id`, `link`, `value`, `logged_in` '.
            'FROM `tm_pages` '.
            'ORDER BY `id` ASC '
        );

		return $this;
	}
    
	public function add($form) {
        if (!$form['title']) {
        	$this->system->log('Adding new page <b>'.at('title_err').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
			return '0;'.at('title_err');
		}
        else if (strpos($form['title'], ' ')) {
    		$this->system->log('Adding new page <b>'.at('title_have_spaces').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
    		return '0;'.at('title_have_spaces');
    	}
		else {
            $title = Db::escape_tags($form['title']);
			$q = Db::query('SELECT * FROM `tm_pages` WHERE `link` = "'.$title.'"');
			if ($q->num_rows != 0) {
				$this->system->log('Adding new page <b>'.at('page_exist').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
                return '0;'.at('page_exist');
			}
			else {
				Db::query('INSERT INTO `tm_pages` SET '.
                    '`link` = "'.$title.'", '.
                    '`value` = "web-page-'.$title.'", '.
                    '`logged_in` = '.(int)$form['logged_in'].' '
                );
                
				foreach ($form as $k => $v) {
					$string = explode('_', $k);
					if ($string[0] == 'text') {
						Db::query('UPDATE `tm_pages` '.
                        'SET `text_'.Db::escape($string[1]).'` = "'.Db::escape($v).'" '.
                        'WHERE `link` = "'.$title.'"');
					}
				}
                
                Db::query('INSERT INTO `tm_strings` SET `key` = "web-page-'.$title.'", `status` = 1');
				
				foreach ($form as $k => $v) {
					$string = explode('_', $k);
					if ($string[0] == 'string') {
						Db::query('UPDATE `tm_strings` '.
                        'SET `'.Db::escape($string[1]).'` = "'.Db::escape_tags($v).'" '.
                        'WHERE `key` = "web-page-'.$title.'"');
					}
				}
				
				$this->system->log('Adding new page <b>'.at('page_added').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
                
                return '1;'.at('page_added');
			}
		}

		return '0;Error, contact admin!';
	}
    
    public function edit($form) {
        $title = Db::escape_tags($form['title']);
    	$pageId = (int)$form['page_id'];
    	$oldValue = Db::escape_tags($form['page_link']);
        
        if (!$title) {
        	$this->system->log('Editing page <b>'.at('title_err').'</b> ('.$title.')', array('module'=>get_class(), 'type'=>'edit'));
			return '0;'.at('title_err');
		}
        else if (strpos($title, ' ')) {
    		$this->system->log('Editing page <b>'.at('title_have_spaces').'</b> ('.$title.')', array('module'=>get_class(), 'type'=>'edit'));
    		return '0;'.at('title_have_spaces');
    	}
        else if ($oldValue != $title) {
    		$row = Db::fetchRow('SELECT `link` FROM `tm_pages` WHERE `link` = "'.$title.'" LIMIT 1');
            
    		if ($row && $row->link == $title) { //require this check for upper/lower case specifics
    			$this->system->log('Editing page <b>'.at('page_exist').'</b> ('.$title.')', array('module'=>get_class(), 'type'=>'edit'));
    			return '0;'.at('page_exist');
    		}
    		else {
                Db::query('UPDATE `tm_pages` SET '.
	    			'`link` = "'.$title.'", '.
                    '`value` = "web-page-'.$title.'", '.
                    '`logged_in` = '.(int)$form['logged_in'].' '.
	    			'WHERE `id` = '.$pageId
    			);
                
                foreach ($form as $k => $v) {
                    $string = explode('_', $k);
                    if ($string[0] == 'text') {
                        Db::query('UPDATE `tm_pages` SET '.
                            '`text_'.Db::escape($string[1]).'` = "'.Db::escape($v).'" '.
                            'WHERE `id` = '.$pageId
                        );
                    }
                }
                
    			Db::query('INSERT INTO `tm_strings` SET `key` = "web-page-'.$title.'", `status` = 1');
    			Db::query('DELETE FROM `tm_strings` WHERE `key` = "web-page-'.$oldValue.'"');
    			foreach ($form as $k => $v) {
					$string = explode('_', $k);
					if ($string[0] == 'string') {
						Db::query('UPDATE `tm_strings` '.
                        	'SET `'.Db::escape($string[1]).'` = "'.Db::escape_tags($v).'" '.
                        	'WHERE `key` = "web-page-'.$title.'"'
    					);
					}
				}
    			
    			$this->system->log('Editing page <b>'.at('page_updated').'</b> ('.$title.')', array('module'=>get_class(), 'type'=>'edit'));
    			
    			return '1;'.at('page_updated');
    		}
    	}
		else {
            Db::query('UPDATE `tm_pages` SET '.
                '`logged_in` = '.(int)$form['logged_in'].' '.
                'WHERE `id` = '.$pageId
            );
            
            foreach ($form as $k => $v) {
                $string = explode('_', $k);
                if ($string[0] == 'text') {
                    Db::query('UPDATE `tm_pages` SET '.
                        '`text_'.Db::escape($string[1]).'` = "'.Db::escape($v).'" '.
                        'WHERE `id` = '.$pageId
                    );
                }
            }
            foreach ($form as $k => $v) {
                $string = explode('_', $k);
                if ($string[0] == 'string') {
                    Db::query('UPDATE `tm_strings` '.
                        'SET `'.Db::escape($string[1]).'` = "'.Db::escape_tags($v).'" '.
                        'WHERE `key` = "web-page-'.$title.'"'
                    );
                }
            }
            
            $this->system->log('Editing page <b>'.at('page_updated').'</b> ('.$title.')', array('module'=>get_class(), 'type'=>'edit'));
            
            return '1;'.at('page_updated');
		}
        
        return '0;Error, contact admin!';
    }
    
    protected function fetchAvailableLanguages() {
        return Db::fetchRows('SELECT `title`, `flag` FROM `tm_languages`');
    }
    
    protected function fetchEditData($id) {
        $row = Db::fetchRow('SELECT * '.
        'FROM `tm_pages` AS `p` '.
        'LEFT JOIN `tm_strings`  AS `s` ON `p`.`value` = `s`.`key` '.
        'WHERE `id` = '.(int)$id.'
        LIMIT 1');
        $row->value = str_replace('web-page-', '', $row->value);
        
        return $row;
    }
    
    protected function deleteRow($id) {
    	$row = Db::fetchRow('SELECT `link` FROM `tm_pages` WHERE `id` = '.(int)$id);
        Db::fetchRow('DELETE FROM `tm_pages` WHERE `id` = "'.(int)$id.'"');
        $this->system->log('Deleted page <b>'.$row->link.'</b>', array('module'=>get_class(), 'type'=>'delete'));
    }
}