<?php
class Winners extends System
{
    public $system;
    public $emails;
    public $templates;
    public $directory;
    public $query;
    
	function __construct($params = array()) {
		$this->system = $params['system'];
        $this->directory = _cfg('dir').'/template/mails';
        $this->emails = array();
        $this->templates = $this->fetchEmailTemplates();
        
        $this->query = 'SELECT * FROM `subscribe` WHERE (`theme` = "all" OR `theme` = "lol") AND `removed` = 0;';
        
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
        set_time_limit(600);
        
        $rows = Db::fetchRows($form['query']);
        
        if (!$rows) {
			$this->system->log('Sending email <b>No emails to send</b>', array('module'=>get_class(), 'type'=>'send'));
			return '0;No emails to send';
		}
        
        $i = 0;
        foreach($rows as $v) {
            $text = str_replace(
    			array(
                    '%unsublink%',
                    '%url%',
                    '%hsurl%',
                    '%lolurl%',
                    '%code%',
                    '%teamId%',
                    '%name%',
                ),
    			array(
                    _cfg('href').'unsubscribe/'.$v->unsublink,
                    _cfg('site'),
                    _cfg('site').'/en/hearthstone',
                    _cfg('site').'/en/leagueoflegends',
                    $v->code,
                    $v->teamId,
                    $v->name,
                ),
    			$form['text']
    		);
            
            $this->sendMail($v->email, $form['title'], $text);
            
            ++$i;
            if ($i >= 3) {
                sleep(1);
                $i = 0;
            }
        }

		$this->system->log('Sending email <b>Emails sent</b>', array('module'=>get_class(), 'type'=>'send'));
							 
        return '1;Emails sent';
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