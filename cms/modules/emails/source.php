<?php
class Emails extends System
{
    public $system;
    public $templates;
    public $directory;
    public $types;
    
	function __construct($params = array()) {
		$this->system = $params['system'];
        $this->directory = _cfg('dir').'/template/mails';
        $this->templates = $this->fetchEmailTemplates();
        $this->types = array('none', 'hs', 'lol', 'smite');
        
        return $this;
	}
    
    public function fetchTemplateText($form) {
        $file = $form;
        if (!file_exists($file)) {
            exit('0;File '.$file.' does not exists');
        }
        
    	return '1;'.file_get_contents($file);
    }

	public function send($form) {
        if (!$form['title']) {
            $this->system->log('Adding subscribing list <b>Title is empty</b>', array('module'=>get_class(), 'type'=>'send'));
            return '0;Title is empty';
        }
        if (!$form['text']) {
            $this->system->log('Adding subscribing list <b>Text is empty</b>', array('module'=>get_class(), 'type'=>'send'));
            return '0;Text is empty';
        }
        
        $type = '(';
        if ($form['query']) {
            $type .= Db::escape('`theme` = "'.$form['query'].'" OR ');
        }
        if ($form['all']) {
            $type .= Db::escape('`theme` = "all" OR ');
        }
        $type = substr($type, 0, -3);
        $type .= ')';

        Db::query(
            'INSERT INTO `subscribe_sender` SET '.
            '`type` = "'.$type.'", '.
            '`subject` = "'.Db::escape($form['title']).'", '.
            '`text` = "'.Db::escape($form['text']).'" '
        );

		$this->system->log('Adding subscribing list <b>success</b>', array('module'=>get_class(), 'type'=>'send'));
        
        return '1;Adding subscribing list';
	}

	protected function fetchEmailTemplates() {
        if (!file_exists($this->directory) && !is_dir($this->directory)) {
            exit('Directory '.$this->directory.' does not exists');
        }
    
        $fileList = array();
        $handler = opendir($this->directory);
        $ignoreFiles = array('.svn');
        while($file = readdir($handler)) {
            //Checking if not hidden files
            if ($file != "." && $file != "..") {
                //Checking if file ignoring is required
                if(!in_array($file, $ignoreFiles)) {
                    $fileList[$file] = $this->directory.'/'.$file;
                }
            }
        }
        closedir($handler);
        
        if ($fileList) {
    		return $fileList;
    	}
    	
    	return array();
    }
}