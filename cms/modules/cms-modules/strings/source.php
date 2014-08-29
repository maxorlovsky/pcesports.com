<?php

class Strings
{
	public $languages = array();
	public $system;
	public $strings = array();
	public $searchString = '';

	function __construct($params = array()) {
		$this->system = $params['system'];
		$this->languages = $this->fetchAvailableLanguages();

		if (isset($params['var1']) && $params['var1'] == 'edit' && isset($params['var2'])) {
			$this->editData = $this->fetchEditData($params['var2']);
		}
		
		if (isset($params['var1']) && $params['var1'] == 'delete' && isset($params['var2'])) {
			$this->deleteRow($params['var2']);
			//redirect
			go(_cfg('cmssite').'/#strings');
		}
		
		if (isset($params['var1']) && $params['var1'] == 'index' && isset($params['var2'])) {
			$this->searchString = urldecode($params['var2']);
            $_SESSION['searchString'] = $this->searchString;
			$this->strings = Db::fetchRows('SELECT `key`, `status`, `english` AS `value` '.
				'FROM `tm_strings` '.
				'WHERE `key` LIKE "%'.Db::escape($this->searchString).'%" OR '.
				'`english` LIKE "%'.Db::escape($this->searchString).'%"'
			);
		}
		else {
			$this->strings = Db::fetchRows('SELECT `key`, `status`, `english` AS `value` FROM `tm_strings`');
		}
        
        if (isset($_SESSION['searchString']) && $_SESSION['searchString'] && !$this->searchString) {
            $this->searchString = $_SESSION['searchString'];
        }

		return $this;
	}

	public function add($form) {
		if (!$form['title']) {
			$this->system->log('Adding new string <b>'.at('title_err').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
			return '0;'.at('title_err');
		}
		else if (strpos($form['title'], ' ')) {
			$this->system->log('Adding new string <b>'.at('title_have_spaces').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
			return '0;'.at('title_have_spaces');
		}
		else {
			$title = Db::escape($form['title']);
			$q = Db::query('SELECT * FROM `tm_strings` WHERE `key` = "'.$title.'" LIMIT 1');
			if ($q->num_rows != 0) {
				$this->system->log('Adding new string <b>'.at('string_exist').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
				return '0;'.at('string_exist');
			}
			else {
				Db::query('INSERT INTO `tm_strings` SET `key` = "'.$title.'"');
				foreach ($form as $k => $v) {
					$string = explode('_', $k);
					if ($string[0] == 'string') {
						Db::query('UPDATE `tm_strings` '.
							'SET `'.$string[1].'` = "'.Db::escape($v).'" '.
							'WHERE `key` = "'.$title.'"'
						);
					}
				}

				$this->system->log('Adding new string <b>'.at('string_added').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));

				return '1;'.at('string_added');
			}
		}

		return '0;Error, contact admin!';
	}

	public function edit($form) {
		$title = Db::escape($form['title']);
		$oldTitle = Db::escape($form['string_old_key']);
		 
		if (!$form['title']) {
			$this->system->log('Editing string <b>'.at('title_err').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
			return '0;'.at('title_err');
		}
		else if (strpos($form['title'], ' ')) {
			$this->system->log('Editing string <b>'.at('title_have_spaces').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
			return '0;'.at('title_have_spaces');
		}
		else if ($oldTitle != $title) {
			$q = Db::query('SELECT * FROM `tm_strings` WHERE `key` = "'.$title.'" LIMIT 1');
			if ($q->num_rows != 0) {
				$this->system->log('Editing string <b>'.at('string_exist').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
				return '0;'.at('string_exist');
			}
			else {
				Db::query('UPDATE `tm_strings` '.
					'SET `key` = "'.$title.'" '.
					'WHERE `key` = "'.$oldTitle.'"'
				);
				
				foreach ($form as $k => $v) {
					$string = explode('_', $k);
					if ($string[0] == 'string') {
						Db::query('UPDATE `tm_strings` '.
							'SET `'.$string[1].'` = "'.Db::escape($v).'" '.
							'WHERE `key` = "'.$title.'"'
						);
					}
				}
								 
				$this->system->log('Editing string <b>'.at('string_updated').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
								 
				return '1;'.at('string_updated');
			}
		}
		else {
			foreach ($form as $k => $v) {
				$string = explode('_', $k);
				if ($string[0] == 'string') {
					Db::query('UPDATE `tm_strings` '.
						'SET `'.$string[1].'` = "'.Db::escape($v).'" '.
						'WHERE `key` = "'.$title.'"'
					);
				}
			}
							 
			$this->system->log('Editing string <b>'.at('link_updated').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
							 
			return '1;'.at('string_updated');
		}

		return '0;Error, contact admin!';
	}

	protected function fetchAvailableLanguages() {
		return Db::fetchRows('SELECT `title`, `flag` FROM `tm_languages`');
	}

	protected function fetchEditData($key) {
		return Db::fetchRow('SELECT * '.
			'FROM `tm_strings` '.
			'WHERE `key` = "'.Db::escape($key).'" '.
			'LIMIT 1'
		);
	}

	protected function deleteRow($key) {
		$row = Db::fetchRow('SELECT `status` FROM `tm_strings` WHERE `key` = "'.Db::escape($key).'" LIMIT 1');
		if ($row->status == 1) {
			$this->system->log('Deleting string error, it doesnt exist or unavailable by status <b>('.$key.')</b>', array('module'=>get_class(), 'type'=>'delete'));
		}
		else {
			Db::query('DELETE FROM `tm_strings` WHERE `key` = "'.Db::escape($key).'" AND `status` = 0');
			$this->system->log('Deleted string <b>'.$row->value.'</b>', array('module'=>get_class(), 'type'=>'delete'));
		}
	}
}

/*
if ($_POST['var1'] == 'page' && $_POST['var2']) {
	$pageNum = (int)$_POST['var2'];
}
else {
	$pageNum = 1;
}

$pages = pages(20, 3, $pageNum, PREFIX.'strings', '', 'key');
$pages = explode('!',$pages);
$npp = $pages[0];
$strt = $pages[1];
$fpnd = $pages[2];

$q = mysql_query('SELECT `key`, `status`, `'.$slang.'` FROM `'.PREFIX.'strings` LIMIT '.$strt.', '.$npp.'');
*/