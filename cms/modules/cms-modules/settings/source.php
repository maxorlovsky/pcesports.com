<?php

class Settings
{
	public $languages = array();
	public $system;
	public $settings = array();
	public $siteSettings;
	public $mainPages;
	
	function __construct($params = array()) {
		$this->system = $params['system'];

		$this->settings = Db::fetchRows('SELECT `value`, `setting` '.
			'FROM `tm_settings` '.
			'WHERE setting != "site_prefix" AND setting != "load_type" '.
			'ORDER BY `setting` = "site_name" DESC, `value` DESC'
		);
		
		$i = 0;
		foreach($this->settings as $v) {
			if (substr($v->setting,0,4) == 'site' && $v->setting != 'site_prefix') {
				$this->siteSettings[$i]['value'] = at($v->setting);
				$this->siteSettings[$i]['html'] = '<div id="setting-'.$v->setting.'" class="pointer settings_div hint" name="'.at('hint_'.$v->setting).'" onclick="do_input(\'setting-'.$v->setting.'\', 0);">'.$v->value.'</div>';
			}
			else {
				$this->mainPages[$i]['value'] = at($v->setting);
				$this->mainPages[$i]['html'] = '<div id="setting-'.$v->setting.'" class="pointer settings_div" onclick="do_input(\'setting-'.$v->setting.'\', 1);">'.$v->value.'</div>';
			}
			++$i;
		}

		return $this;
	}

}