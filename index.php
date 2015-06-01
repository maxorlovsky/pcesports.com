<?php
/* 
 * CMS TheMages v3.13
 * http://www.themages.net
 * Credits (dev): Maxtream
 * Credits (design): Maxtream, AnyaTheEagle
 * Github: https://github.com/Maxtream/themages-cms
 */

// Maintenance check
if (file_exists('../maint_mode')) {
	die('This site is on maintenance');
}

require_once dirname(__FILE__).'/cms/vendor/autoload.php';

session_start();
global $cfg;
global $astr;

if (isset($_GET['params']) && $_GET['params']) {
    $breakdown = explode('/', $_GET['params']);
    if ($breakdown) {
        $i = 0;
        foreach($breakdown as $f) {
            $_GET[($i==0?'language':'val'.$i)] = $f;
            ++$i;
        }
    }
}

$cfg['root'] = str_replace('\\', '/', __DIR__);

date_default_timezone_set('UTC');

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