<?php

if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])){
    $cfg['protocol'] = $_SERVER['HTTP_X_FORWARDED_PROTO'];
}
else{
    $cfg['protocol'] = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
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
    $cfg['adminEmail'] = 'info@pcesports.com';
    $cfg['site'] = $cfg['protocol'].'://www.pcesports.com';
    
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
	$cfg['site'] = $cfg['protocol'].'://test.pcesports.com';
	
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
    $cfg['site'] = $cfg['protocol'].'://dev.pcesports.com';
    
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 1);
    
	break;
}

$cfg['apiUrl'] = 'https://api.themages.net';
$cfg['apiUsername'] = 'pcesports';
$cfg['apiPassword'] = 'diaO2@(ujdp1';

$cfg['cronjob'] = 'askdjOLIKSJDoi2o12d09asLL';
$cfg['salt'] = 'eethaiASLDK21lae6AASDta9ChoDDCh';
$cfg['logs'] = 1;
$cfg['maxLevel'] = 4;
$cfg['allowedLanguages'] = array('en', 'ru');
$cfg['defaultLanguage'] = 'en';

$cfg['recaptchaSiteKey'] = '6LcwJ_8SAAAAAL2SgH-NYduvEp9DLUlndHrlMs7Z';
$cfg['recaptchaSecretKey'] = '6LcwJ_8SAAAAAFoAQ0onOpvPNMo8-Y-g-lwtY22P';

// SMTP config
$cfg['smtpMailName'] = 'pentaclickesports@gmail.com';
$cfg['smtpMailPort'] = '465';
$cfg['smtpMailHost'] = 'ssl://smtp.gmail.com';
$cfg['smtpMailPass'] = 'aveclickius777';
$cfg['smtpMailFrom'] = 'Pentaclick eSports';

$cfg['href'] = $cfg['site'].'/%lang%';
$cfg['hssite'] = '/hearthstone';
$cfg['lolsite'] = '/league';

$cfg['inc'] = $cfg['dir'].'/inc';
$cfg['classes'] = $cfg['dir'].'/classes';
$cfg['template'] = $cfg['dir'].'/template';
$cfg['template'] = $cfg['dir'].'/template';
$cfg['static'] = $cfg['site'].'/web/static';
$cfg['img'] = $cfg['static'].'/images';
$cfg['uploads'] = $cfg['dir'].'/uploads';
$cfg['imgu'] = $cfg['site'].'/web/uploads';
$cfg['avatars'] = $cfg['img'].'/avatar';

$cfg['timeDifference'] = 0; //in minutes (+3h)

/** social logins */
$cfg['social'] = array(
	//client_id, client_secure
    'fb'    => array('id' => '766575306708443', 'private' => '1cbf6970d0073b4490d97653afcb5ffc'), //facebook
    'tw'    => array('id' => 'eOgJnO2SkfjkBFwLZ9lrWSyrL', 'private' => 'dxgLUmgeoBb6AfBaaADLHykhQOb71sdyZcHoJLRZkuupWfzT2B'), //twitter
	'vk'    => array('id' => '4445595', 'private' => 'QctUnxnf6QqfcMbuTWas'), //vkontakte
	'gp'    => array('id' => '974420857967-p2jrt83osg4op0u3k9k22um06omqafpa.apps.googleusercontent.com', 'private'=>'bnBaBT6zB1CobY4MGXpwfOin'),
    'tc'    => array('id' => 'ew4ocriuxjr7b9c7najq3588f30gd63', 'private'=>'94nsyz930bomq4nf8f8a6ppvrpz8n2h'), //twitch
    'bn'    => array('id' => 'gv3s76c5mk8brmhwkg7q7qagt7w3ds48', 'private'=>'j5WkZNXDkSY6eVNyGtgzyfEVs2MTasJN'),
);

$cfg['streamGames'] = array(
    'lol'   => 'league_of_legends',
    'hs'    => 'hearthstone',
    'dota'  => 'dota',
    'smite' => 'smite',
    'cs'    => 'csgo',
    'other' => 'random',
);
$cfg['streamLanguages'] = array(
    'en'    => 'English',
    'ru'    => 'Русский',
    'both'  => 'both',
);
$cfg['lolRegions'] = array(
    'euw' => 'Europe West',
    'eune' => 'Europe Nordic & East',
    'tr' => 'Turkey',
    'ru' => 'Russia',
    'na' => 'North America',
    'lan' => 'Latin America North',
    'las' => 'Latin America South',
    'kr' => 'Korea',
    'br' => 'Brazil',
    'oce' => 'Oceania',
);

$cfg['boardGames'] = array('lol', 'hs', 'dota', 'smite', 'csgo');