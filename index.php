<?php
//CMS TheMages v3.1
//Credits (dev): MaxOrlovsky
//Credits (design): MaxOrlovsky, AnyaOrlovsky

// Maintenance check
if (file_exists('../maint_mode')) {
	die('This site is on maintenance');
}

session_start();
global $cfg;
global $astr;

if (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && $_SERVER['HTTP_CF_CONNECTING_IP']) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

require_once dirname(__FILE__).'/cms/inc/config.php';
require_once $cfg['cmsinc'].'/functions.php';

//If catching admin variable, running admin system
if (isset($_GET['language']) && $_GET['language'] == 'admin') {
    //Loading main class
    require_once _cfg('cmsclasses').'/system.php';
    
    //Loading whole system
    $system = new System(0);
    $system->run();
}
//If catching AJAX request, sending to ajax directly
else if(isset($_POST['control']) && $_POST['control']) {
    //Loading main class
    require_once _cfg('cmsclasses').'/system.php';
    
    //Loading whole system
    $system = new System(0);
    $system->ajax($_POST);
}
//If not admin and not ajax, opening just website
else {
    require_once dirname(__FILE__).'/web/index.php';
}

//(c) MaxOrlovsky
?>