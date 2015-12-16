<?php
class Faq
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
			go(_cfg('cmssite').'/#faq');
		}
		
		$this->faq = Db::fetchRows(
            'SELECT * FROM `faq` '.
            'ORDER BY `weight` ASC'
        );
        
        $this->moduleName = ucfirst(get_class());

		return $this;
	}
    
    public function add($form) {
        if (!trim($form['question_english'])) {
            $this->system->log('Adding faq <b>Question not set</b>', array('module'=>get_class(), 'type'=>'add'));
			return '0;Question not set';
        }
        else {
            Db::query(
                'INSERT INTO `faq` SET '.
                '`weight` = '.(int)$form['order'].' '
            );
            $lastId = Db::lastId();
            foreach ($form as $k => $v) {
				$string = explode('_', $k);
				if ($string[0] == 'question') {
					Db::query('UPDATE `faq` '.
                        'SET `question_'.$string[1].'` = "'.Db::escape($v).'" '.
                        'WHERE `id` = '.$lastId
					);
				}
                else if ($string[0] == 'answer') {
					Db::query('UPDATE `faq` '.
                        'SET `answer_'.$string[1].'` = "'.Db::escape($v).'" '.
                        'WHERE `id` = '.$lastId
					);
				}
			}
			
			$this->system->log('Adding faq <b>Faq added</b> ('.$lastId.')', array('module'=>get_class(), 'type'=>'add'));

			return '1;FAQ added';
			
		}

		return '0;Error, contact admin!';
	}

	public function edit($form) {
		if (!trim($form['question_english'])) {
            $this->system->log('Editing faq <b>Question not set</b>', array('module'=>get_class(), 'type'=>'edit'));
			return '0;Name not set';
        }
		else {
			$id = (int)$form['id'];
            Db::query('UPDATE `faq` SET '.
                '`weight` = '.(int)$form['order'].' '.
				'WHERE `id` = '.$id
			);
            foreach ($form as $k => $v) {
				$string = explode('_', $k);
				if ($string[0] == 'question') {
					Db::query('UPDATE `faq` '.
                        'SET `question_'.$string[1].'` = "'.Db::escape($v).'" '.
                        'WHERE `id` = '.$id
					);
				}
                else if ($string[0] == 'answer') {
					Db::query('UPDATE `faq` '.
                        'SET `answer_'.$string[1].'` = "'.Db::escape($v).'" '.
                        'WHERE `id` = '.$id
					);
				}
			}

			$this->system->log('Editing faq <b>FAQ updated</b> ('.$id.')', array('module'=>get_class(), 'type'=>'edit'));

			return '1;FAQ updated';
		}

		return '0;Error, contact admin!';
	}

	protected function fetchEditData($id) {
		return Db::fetchRow('SELECT * '.
			'FROM `faq` '.
			'WHERE `id` = '.(int)$id.' '.
			'LIMIT 1'
		);
	}

	protected function deleteRow($id) {
		Db::query('DELETE FROM `faq` WHERE `id` = '.(int)$id);
		$this->system->log('Deleting faq <b>'.$id.'</b>', array('module'=>get_class(), 'type'=>'delete'));
	}
    
    protected function fetchAvailableLanguages() {
		return Db::fetchRows('SELECT `title`, `flag` FROM `tm_languages`');
	}
}