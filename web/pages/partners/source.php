<?php

class partners extends system
{
	public $partners;

    public function __construct($params = array()) {
    	$this->partners = array(
    		'unicon' => array(
    			'logo' => _cfg('img').'/partners/unicon.png',
    			'link' => 'http://www.unicon.lv',
    			'text' => 'UniCon - one of the first multingenre conventions in Eastern Europe. The event for comic, anime, book, movie, cosplay, crafting and many other fans. We\'ve been working closely with UniCon since 2014, helping out with eSports events and holding our own events @ UniCon'
			),
			'skillz' => array(
    			'logo' => _cfg('img').'/partners/skillz.png',
    			'link' => 'http://skillz.lv',
    			'text' => 'Skillz - gaming organization, the main focus of which is to develop e-Sports in Latvia and in Baltics countries. Together with skillz we were able to made few co-op tournaments and exchanging informations almost every time'
			),
			'lesf' => array(
    			'logo' => _cfg('img').'/partners/lesf.png',
    			'link' => 'http://www.lesf.lv',
    			'text' => 'Latvian eSports Federation is an organization whose main objective is the development and promotion of Latvian eSports'
			),
		);
	}

	public function showTemplate() {
        include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}