<?php

class faq
{
    public $faq;
    
	public function __construct($params = array()) {
        $this->faq = Db::fetchRows('SELECT `question_'._cfg('fullLanguage').'` AS `question`, `answer_'._cfg('fullLanguage').'` AS `answer` '.
            'FROM `faq` '.
            'ORDER BY `weight` ASC'
        );
	}
	
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}