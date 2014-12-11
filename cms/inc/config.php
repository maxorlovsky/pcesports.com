<?php
//*===================================================*//
//*CMS TheMages Configuration file					  *//
//*===================================================*//

//=====================================================
// Making some defines for easyer coding (main)
$cfg['dir'] = $_SERVER['DOCUMENT_ROOT'].'/web';
$cfg['cmsdir'] = dirname(__DIR__);
date_default_timezone_set('UTC');
//=====================================================

//=====================================================
// Defining environment
$breakDown = explode('.', $_SERVER['HTTP_HOST']);
if ($breakDown[0] == 'dev') { //Development environment
    $cfg['env'] = 'dev';
}
else if ($breakDown[0] == 'test' || $breakDown[1] == 'test') { //Test environment
    $cfg['env'] = 'test';
}
else {
    //This is where CMS go live
    $cfg['env'] = 'prod';
}
if (!$cfg['env'])
{
	die('Configuration Error 0');
}

// Defining main default variables
// DB config
$cfg['dbHost'] ='';
$cfg['dbBase'] ='';
$cfg['dbUser'] ='';
$cfg['dbPass'] ='';
$cfg['dbPort'] =3306;

//Admin email (in case of errors)
$cfg['adminEmail'] = '';
$cfg['site'] = '';

// SMTP config
$cfg['smtpMailName'] = '';
$cfg['smtpMailPort'] = '';
$cfg['smtpMailHost'] = '';
$cfg['smtpMailPass'] = '';
$cfg['smtpMailFrom'] = '';

//Additional variables
$cfg['maxLevel'] = 4;
$cfg['logs'] = 1;
$cfg['allowedLanguages'] = array('en', 'ru');
$cfg['defaultLanguage'] = 'en';
$cfg['availableLoginAttempts'] = 5;
$cfg['recaptchaSecretKey'] = '6LcwJ_8SAAAAAFoAQ0onOpvPNMo8-Y-g-lwtY22P';

// Adding site config
require_once $cfg['dir'].'/inc/config.php';

//=====================================================
// Making some defines for easyer coding (directories)
$cfg['cmssite'] = $cfg['site'].'/admin';
$cfg['cmsinc'] = $cfg['cmsdir'].'/inc';
$cfg['cmsclasses'] = $cfg['cmsdir'].'/classes';
$cfg['cmstemplate'] = $cfg['cmsdir'].'/template';
$cfg['cmslib'] = $cfg['cmsdir'].'/lib';
$cfg['cmsstatic'] = $cfg['site'].'/cms/static';
$cfg['cmsimg'] = $cfg['site'].'/cms/static/images';
$cfg['cmslocale'] = $cfg['cmsdir'].'/locale';
$cfg['cmsmodules'] = $cfg['cmsdir'].'/modules';
$cfg['cmslib'] = $cfg['cmsdir'].'/lib';
$cfg['uploads'] = $cfg['dir'].'/uploads';
$cfg['imgu'] = $cfg['site'].'/web/uploads';
$cfg['pages'] = $cfg['dir'].'/pages';

// Needed for Language functionality (to add/delete)
// Add new language table fields here
$cfg['ud_alter'] = array(
	array('tm_strings', ''),
	array('tm_pages', ''),
);
//=====================================================

//==================================================
// Needed for dev to change password when changing
// _SALT
//==================================================
// $newPass = '';
// exit('UPDATE `tm_admins` SET `password` = "'.sha1($newPass.$cfg['salt']).'" WHERE `login` = "dev"');
//==================================================