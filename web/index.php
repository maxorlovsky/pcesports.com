<?php
require_once dirname(__FILE__).'/inc/config.php';

require_once _cfg('classes').'/system.php';

if(isset($_POST['ajax']) && $_POST['ajax']) {
	$system = new System(0);
	$system->ajax($_POST);
}
else {
	//Loading whole system
	$system = new System(0);
	$system->run();
}