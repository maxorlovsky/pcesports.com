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
			go(_cfg('cmssite').'/#news');
		}
		
		//Enable/disable
		if (isset($params['var1']) && $params['var1'] == 'able' && isset($params['var2'])) {
			$this->able($params['var2']);
			//redirect
			go(_cfg('cmssite').'/#news');
		}
		
		$this->news = Db::fetchRows('SELECT *, `english` AS `value` FROM `news` '.
			'ORDER BY `id` DESC'
		);
		/*foreach($this->news as $v) {
			if ($v->extension && !file_exists(_cfg('uploads').'/news/original-'.$v->id.'.'.$v->extension)) {
				$this->news->$v->extension = null; 
			}
		}*/

		return $this;
	}
	
	protected function able($id) {
		$id = (int)$id;
		$row = Db::fetchRow('SELECT `id`, `title`, `able` FROM `news` WHERE `id` = '.$id.' LIMIT 1');
		if ($row->able == 1) {
			$enable = 0;
		}
		else {
			$enable = 1;
		}
		Db::query('UPDATE `news` SET `able` = '.$enable.' WHERE `id` = '.$id);
	
		if ($enable == 1) {
			$this->system->log('Enabling article <b>('.$row->title.' ['.$row->id.'])</b>', array('module'=>get_class(), 'type'=>'enabling'));
		}
		else {
			$this->system->log('Disabling article <b>('.$row->title.' ['.$row->id.'])</b>', array('module'=>get_class(), 'type'=>'disabling'));
		}
		 
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
			$thumb->adaptiveResize(791, 280);
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
			$getExt = null;
			if (isset($form['uploadedFiles']) && $form['uploadedFiles']) {
				$getExt = substr(str_replace('.tmp', '', $form['uploadedFiles']), -3);
			}
			
			Db::query('INSERT INTO `news` SET '.
				'`title` = "'.Db::escape($form['title']).'", '.
				'`extension` = "'.Db::escape($getExt).'", '.
				'`admin_id` = '.(int)$this->system->user->id
			);
			$lastId = Db::lastId();
			
			if ($getExt != null) {
				rename(_cfg('uploads').'/news/original-'.$form['uploadedFiles'], _cfg('uploads').'/news/original-'.$lastId.'.'.$getExt);
				rename(_cfg('uploads').'/news/big-'.$form['uploadedFiles'], _cfg('uploads').'/news/big-'.$lastId.'.'.$getExt);
				rename(_cfg('uploads').'/news/small-'.$form['uploadedFiles'], _cfg('uploads').'/news/small-'.$lastId.'.'.$getExt);
			}
			
			foreach ($form as $k => $v) {
				$string = explode('_', $k);
				if ($string[0] == 'string') {
					Db::query('UPDATE `news` '.
						'SET `'.$string[1].'` = "'.Db::escape($v).'" '.
						'WHERE `id` = '.$lastId
					);
				}
				else if ($string[0] == 'short') {
					Db::query('UPDATE `news` '.
							'SET `short_'.$string[1].'` = "'.Db::escape($v).'" '.
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
		if (!$form['title']) {
			$this->system->log('Editing news <b>'.at('title_err').'</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
			return '0;'.at('title_err');
		}
		else {
			$id = (int)$form['id'];
			
			$getExt = null;
			$extQuery = '';
			if (isset($form['uploadedFiles']) && $form['uploadedFiles']) {
				if ($form['uploadedFiles'] == 'remove') {
					$extQuery = ', `extension` = NULL';
					$row = Db::fetchRow('SELECT `extension` FROM `news` WHERE `id` = '.(int)$id.' LIMIT 1');
					if (file_exists(_cfg('uploads').'/news/original-'.$id.'.'.$row->extension)) {
						unlink(_cfg('uploads').'/news/original-'.$id.'.'.$row->extension);
						unlink(_cfg('uploads').'/news/big-'.$id.'.'.$row->extension);
						unlink(_cfg('uploads').'/news/small-'.$id.'.'.$row->extension);
					}
				}
				else if ($form['uploadedFiles'] != 'leave') {
					$getExt = substr(str_replace('.tmp', '', $form['uploadedFiles']), -3);
					$extQuery = ', `extension` = "'.Db::escape($getExt).'"';
					rename(_cfg('uploads').'/news/original-'.$form['uploadedFiles'], _cfg('uploads').'/news/original-'.$id.'.'.$getExt);
					rename(_cfg('uploads').'/news/big-'.$form['uploadedFiles'], _cfg('uploads').'/news/big-'.$id.'.'.$getExt);
					rename(_cfg('uploads').'/news/small-'.$form['uploadedFiles'], _cfg('uploads').'/news/small-'.$id.'.'.$getExt);
				}
			}
			
			Db::query('UPDATE `news` '.
				'SET `title` = "'.Db::escape($form['title']).'" '. 
				$extQuery.' '.
				'WHERE `id` = '.$id
			);
			
			foreach ($form as $k => $v) {
				$string = explode('_', $k);
				if ($string[0] == 'string') {
					Db::query('UPDATE `news` '.
							'SET `'.$string[1].'` = "'.Db::escape($v).'" '.
							'WHERE `id` = '.$id
					);
				}
				else if ($string[0] == 'short') {
					Db::query('UPDATE `news` '.
							'SET `short_'.$string[1].'` = "'.Db::escape($v).'" '.
							'WHERE `id` = '.$id
					);
				}
			}
			
							 
			$this->system->log('Editing news <b>Article updated</b> ('.$form['title'].')', array('module'=>get_class(), 'type'=>'edit'));
							 
			return '1;Article updated';
		}

		return '0;Error, contact admin!';
	}

	protected function fetchAvailableLanguages() {
		return Db::fetchRows('SELECT `title`, `flag` FROM `tm_languages`');
	}

	protected function fetchEditData($id) {
		return Db::fetchRow('SELECT * '.
			'FROM `news` '.
			'WHERE `id` = '.(int)$id.' '.
			'LIMIT 1'
		);
	}

	protected function deleteRow($id) {
		$row = Db::fetchRow('SELECT `title`, `extension` FROM `news` WHERE `id` = '.(int)$id.' LIMIT 1');
		if (!$row) {
			return false;
		}
		
		if (file_exists(_cfg('uploads').'/news/original-'.$id.'.'.$row->extension)) {
			unlink(_cfg('uploads').'/news/original-'.$id.'.'.$row->extension);
			unlink(_cfg('uploads').'/news/big-'.$id.'.'.$row->extension);
			unlink(_cfg('uploads').'/news/small-'.$id.'.'.$row->extension);
		}
		Db::query('DELETE FROM `news` WHERE `id` = '.(int)$id);
		$this->system->log('Deleting news <b>'.$row->title.'</b>', array('module'=>get_class(), 'type'=>'delete'));
	}
}