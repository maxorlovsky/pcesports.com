<?php
class Languages
{
	public $system;
	
	function __construct($params = array()) {
		$this->system = $params['system'];
        if (isset($params['var1']) && $params['var1'] == 'edit' && isset($params['var2'])) {
            $this->editData = $this->fetchEditData($params['var2']);
        }
        
        if (isset($params['var1']) && $params['var1'] == 'delete' && isset($params['var2'])) {
            $this->deleteRow($params['var2']);
            //redirect
            go(_cfg('cmssite').'/#languages');
        }
        
        $this->languages = Db::fetchRows('SELECT `id`, `title`, `flag`
		FROM `tm_languages`
		ORDER BY `title` ASC');

		return $this;
	}
    
	public function add($form) {
        if (!$form['title']) {
        	$this->system->log('Adding new language <b>'.at('title_err').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
			return '0;'.at('title_err');
		}
		else if(strlen($form['meta']) != 2) {
			$this->system->log('Adding new language <b>'.at('language_err_file').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
            return '0;'.at('language_err_file');
		}
		else {
			$title = Db::escape_tags(strtolower($form['title']));

			Db::query('INSERT INTO `tm_languages`'.
            'SET `title` = "'.$title.'", `flag` = "'.Db::escape_tags($form['meta']).'"');
            
			foreach(_cfg('ud_alter') as $v) {
				Db::query('ALTER TABLE `'.$v[0].'` ADD `'.$v[1].$title.'` TEXT NULL ');
			}
			
			$this->system->log('Adding new language <b>'.at('lang_suc_added').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
            
            return '1;'.at('lang_suc_added');
		}
        
		return '0;Error, contact admin!';
	}
    
    public function edit($form) {
        if (!$form['title']) {
        	$this->system->log('Editing language <b>'.at('title_err').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
			return '0;'.at('title_err');
		}
		else if(strlen($form['meta']) != 2) {
			$this->system->log('Editing language <b>'.at('language_err_file').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
            return '0;'.at('language_err_file');
		}
		else {
			$title = Db::escape_tags(strtolower($form['title']));

			Db::query('UPDATE `tm_languages` '.
            'SET `title` = "'.$title.'", `flag` = "'.Db::escape_tags($form['meta']).'" '.
            'WHERE `id` = '.(int)$form['lang_id']);
			foreach(_cfg('ud_alter') as $v) {
				Db::query('ALTER TABLE `'.$v[0].'` '.
                'CHANGE `'.Db::escape_tags($form['lang_old_title']).'` '.
                '`'.$v[1].$title.'` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL');
			}
			
			$this->system->log('Editing language <b>'.at('lang_suc_updated').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
			
            return '1;'.at('lang_suc_updated');
		}
        
        return '0;In progress';
    }
    
    protected function fetchEditData($id) {
        return Db::fetchRow('SELECT `id`, `title`, `flag` '.
        'FROM `tm_languages` '.
        'WHERE `id` = '.(int)$id.'
        LIMIT 1');
    }
    
    protected function deleteRow($id) {
		$row = Db::fetchRow('SELECT `title`, `flag` FROM `tm_languages` WHERE `id` = '.(int)$id);
		//unlink(_IMGP.'flags/'.$r->flag.'.gif');
		Db::query('DELETE FROM `tm_languages` WHERE `id` = "'.$id.'"');
		foreach(_cfg('ud_alter') as $v) {
			Db::query('ALTER TABLE `'.$v[0].'` DROP `'.$v[1].$row->title.'`');
		}
		$this->system->log('Deleted language <b>'.$row->title.'</b>', array('module'=>get_class(), 'type'=>'delete'));
    }
    
    private function fetchSetting() {
        $row = Db::fetchRow('SELECT `value` '.
        'FROM `tm_settings` '.
        'WHERE `setting` = "languages"
        LIMIT 1');
        
        return $row->value;
    }
}