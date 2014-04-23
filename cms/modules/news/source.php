<?php

class News
{
	public $languages = array();
	public $system;
	public $news = array();
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
		
		$this->news = Db::fetchRows('SELECT `id`, `title`, `able`, `extension`, `english` AS `value` FROM `news` '.
			'ORDER BY `id` DESC'
		);
		foreach($this->news as $k => $v) {
			if ($v->extension && !file_exists(_cfg('uploads').'/news/original-'.$v->id.'.'.$v->extension)) {
				$this->news->$k->extension = null; 
			}
		}

		return $this;
	}
	
	public function uploadImage($data) {
		require_once _cfg('cmslib').'/phpthumb/start.php';
		
		if (!file_exists(_cfg('uploads').'/news')) {
			mkdir(_cfg('uploads').'/news');
		}
		
		if (substr(sprintf('%o', fileperms(_cfg('uploads').'/news')), -4) != '0777') {
			chmod(_cfg('uploads').'/news', 0777);
		}
		
		$ext = pathinfo($_FILES['upload']['name']);
		$file = 'news-'.time().'.'.$ext['extension'].'.tmp';
		
		if (move_uploaded_file($_FILES['upload']['tmp_name'], _cfg('uploads').'/news/original-'.$file)) {
			list($width, $height) = getimagesize(_cfg('uploads').'/news/original-'.$file);
			
			//Line
			$thumb = PhpThumbFactory::create(_cfg('uploads').'/news/original-'.$file);
			$thumb->adaptiveResize(791, 140);
			$thumb->save(_cfg('uploads').'/news/big-'.$file);
			
			//Square
			$thumb = PhpThumbFactory::create(_cfg('uploads').'/news/original-'.$file);
			$thumb->adaptiveResize(225, 170);
			$thumb->save(_cfg('uploads').'/news/small-'.$file);
			
			return '1;'._cfg('imgu').'/news/original-'.$file.';'.$file;
		}
		
		return '0;Error';
	}

	public function add($form) {
		if (!$form['title']) {
			$this->system->log('Adding news <b>'.at('title_err').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));
			return '0;'.at('title_err');
		}
		else {
			$title = Db::escape($form['title']);
			
			$getExt = null;
			if (isset($form['uploadedFiles']) && $form['uploadedFiles']) {
				$getExt = substr(str_replace('.tmp', '', $form['uploadedFiles']), -3);
				rename(_cfg('uploads').'/news/original-'.$form['uploadedFiles'], _cfg('uploads').'/news/original-'.$lastId.'.'.$getExt);
				rename(_cfg('uploads').'/news/big-'.$form['uploadedFiles'], _cfg('uploads').'/news/big-'.$lastId.'.'.$getExt);
				rename(_cfg('uploads').'/news/small-'.$form['uploadedFiles'], _cfg('uploads').'/news/small-'.$lastId.'.'.$getExt);
			}
			
			Db::query('INSERT INTO `news` SET `title` = "'.$title.'", `extension` = "'.Db::escape($getExt).'"');
			$lastId = Db::lastId();
			foreach ($form as $k => $v) {
				$string = explode('_', $k);
				if ($string[0] == 'string') {
					Db::query('UPDATE `news` '.
						'SET `'.$string[1].'` = "'.Db::escape($v).'" '.
						'WHERE `id` = '.$lastId
					);
				}
			}
			
			$this->system->log('Adding news <b>Article added</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'add'));

			return '1;Article added';
			
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
								 
				$this->system->log('Editing string <b>'.at('link_updated').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
								 
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

	protected function fetchEditData($id) {
		return Db::fetchRow('SELECT * '.
			'FROM `news` '.
			'WHERE `id` = "'.(int)$id.'" '.
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