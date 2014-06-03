<?php
//*===================================================*//
//*CMS TheMages Configuration file					  *//
//*===================================================*//

//=====================================================
// Making some defines for easyer coding (main)
$cfg['dir'] = $_SERVER['DOCUMENT_ROOT'].'/web';
$cfg['cmsdir'] = dirname(__DIR__);
date_default_timezone_set('Europe/Riga');
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

//=====================================================
// Defining main variables
switch ( $cfg['env'] )
{
//=====================================================
// Prod Env
//=====================================================
case 'prod':
    // DB config
    $cfg['dbHost'] ='127.0.0.1';
    $cfg['dbBase'] ='pentaclick_prod';
    $cfg['dbUser'] ='pcuserprod';
    $cfg['dbPass'] ='ASDIJ201*S*D912kka';
    $cfg['dbPort'] =3306;
    
    //Admin email (in case of errors)
    $cfg['adminEmail'] = 'max.orlovsky@gmail.com';
    $cfg['site'] = 'http://www.pcesports.com';
    
    // SMTP config
    $cfg['smtpMailName'] = 'pentaclickesports@gmail.com';
    $cfg['smtpMailPort'] = '465';
    $cfg['smtpMailHost'] = 'ssl://smtp.gmail.com';
    $cfg['smtpMailPass'] = 'knyaveclickius888';
    $cfg['smtpMailFrom'] = 'info@pcesports.com';
    
    ini_set('display_errors', 0);
	
	break;
//=====================================================
// Test Env
//=====================================================
case 'test':
	// DB config
	$cfg['dbHost'] ='127.0.0.1';
	$cfg['dbBase'] ='pentaclick_dev';
	$cfg['dbUser'] ='pcusertest';
	$cfg['dbPass'] ='s12WD@#$asdaAD2';
	$cfg['dbPort'] =3306;

	//Admin email (in case of errors)
	$cfg['adminEmail'] = 'max.orlovsky@gmail.com';
	$cfg['site'] = 'http://test.pcesports.com';

	// SMTP config
	$cfg['smtpMailName'] = 'pentaclickesports@gmail.com';
	$cfg['smtpMailPort'] = '465';
	$cfg['smtpMailHost'] = 'ssl://smtp.gmail.com';
	$cfg['smtpMailPass'] = 'knyaveclickius888';
	$cfg['smtpMailFrom'] = 'info@pcesports.com';
	
	ini_set('display_errors', 1);

	break;
		
//=====================================================
// Dev Env
//=====================================================
case 'dev':
	// DB config
    $cfg['dbHost'] ='77.93.30.172';
    $cfg['dbBase'] ='pentaclick_dev';
    $cfg['dbUser'] ='pcusertest';
    $cfg['dbPass'] ='s12WD@#$asdaAD2';
    $cfg['dbPort'] =3306;
    
    //Admin email (in case of errors)
    $cfg['adminEmail'] = 'max.orlovsky@gmail.com';
    $cfg['site'] = 'http://dev.pcesports.com';
    
    // SMTP config
    $cfg['smtpMailName'] = 'pentaclickesports@gmail.com';
    $cfg['smtpMailPort'] = '465';
    $cfg['smtpMailHost'] = 'ssl://smtp.gmail.com';
    $cfg['smtpMailPass'] = 'knyaveclickius888';
    $cfg['smtpMailFrom'] = 'info@pcesports.com';
    
    ini_set('display_errors', 1);
    
	break;
}
//=====================================================

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

$cfg['cronjob'] = 'askdjOLIKSJDoi2o12d09asLL';
$cfg['salt'] = 'eethaiASLDK21lae6AASDta9ChoDDCh';
$cfg['logs'] = 1;
$cfg['maxLevel'] = 4;
$cfg['allowedLanguages'] = array('en', 'ru');
$cfg['defaultLanguage'] = 'en';

$cfg['apiUrl'] = 'https://api.themages.net';
$cfg['apiUsername'] = 'pcesports';
$cfg['apiPassword'] = 'diaO2@(ujdp1';

// Needed for Language functionality (to add/delete)
// Add new language table fields here
$cfg['ud_alter'] = array(
	array('tm_strings', ''),
	array('tm_pages', ''),
    
    /*
	array('news', 'title_'),
	array('news', 'short_text_'),
	array('news', 'text_'),
	*/
);
//=====================================================

//==================================================
// Needed for dev to change password when changing
// _SALT
//==================================================
// $newPass = '';
// mysql_query('UPDATE `tm_admin` SET `pass` = "'.sha1($newPass.$cfg['salt']).'" WHERE `login` = "dev"');
//==================================================