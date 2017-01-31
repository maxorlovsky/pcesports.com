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
    $cfg['dbHost'] ='sql.maxorlovsky.net';
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

//This variables required for TheMages API
//It's used for external logs, but I don't know if people need it and if this should be improved, for now leaving as I'm using it
//Clients usually did stupid stuff in the past and told me that they "didn't do it". For this case you usually needed external logs.
$cfg['apiUsername'] = 'pcesports';
$cfg['apiPassword'] = 'diaO2@(ujdp1';

//Google ReCaptcha parameters, required for CMS in terms someone trying to brute-force login, usually bot's stupid and can't go through it
$cfg['recaptchaSiteKey'] = '6LcwJ_8SAAAAAL2SgH-NYduvEp9DLUlndHrlMs7Z';
$cfg['recaptchaSecretKey'] = '6LcwJ_8SAAAAAFoAQ0onOpvPNMo8-Y-g-lwtY22P';

$cfg['salt'] = 'eethaiASLDK21lae6AASDta9ChoDDCh';
$cfg['logs'] = 1;
$cfg['maxLevel'] = 4;
$cfg['allowedLanguages'] = array('en');//ru
$cfg['defaultLanguage'] = 'en';

///web/include directory
$cfg['inc'] = $cfg['dir'].'/inc';
//web/classes directory
$cfg['classes'] = $cfg['dir'].'/classes';
//web/template directory
$cfg['template'] = $cfg['dir'].'/template';
//web/static files directory
$cfg['static'] = $cfg['site'].'/web/assets';
$cfg['assets'] = $cfg['site'].'/web/assets';
//web/images directory
$cfg['img'] = $cfg['static'].'/images';
//web/uploads directory
$cfg['uploads'] = $cfg['dir'].'/uploads';
//web/uploads link for website
$cfg['imgu'] = $cfg['site'].'/web/uploads';

// SMTP config
$cfg['smtpMailName'] = 'info@pcesports.com';
$cfg['smtpMailPort'] = '465';
$cfg['smtpMailHost'] = 'ssl://smtp.gmail.com';
$cfg['smtpMailPass'] = '#KC^EmNth*bujeAjskEM';
$cfg['smtpMailFrom'] = 'Pentaclick eSports';

/*$cfg['smtpMailName'] = 'pentaclickesports@gmail.com';
$cfg['smtpMailPort'] = '465';
$cfg['smtpMailHost'] = 'ssl://smtp.gmail.com';
$cfg['smtpMailPass'] = 'zwAt!&JfA!MU!YE&gArw';
$cfg['smtpMailFrom'] = 'Pentaclick eSports';*/

$cfg['href'] = $cfg['site'].'/%lang%';
$cfg['widget'] = $cfg['site'].'/widget';
$cfg['hssite'] = '/hearthstone';
$cfg['lolsite'] = '/league';

$cfg['avatars'] = $cfg['img'].'/avatar';

$cfg['widgets'] = $cfg['dir'].'/pages/widgets';

$cfg['pages'] = $cfg['dir'].'/pages';

$cfg['timeDifference'] = 3600; //in seconds (-60m/-1h)

$cfg['cronjob'] = 'askdjOLIKSJDoi2o12d09asLL';

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

if ($cfg['env'] != 'prod') {
    $cfg['social']['bn'] = array('id' => 'tc4wkndnd8gm8d4k8ahmtgt4qk6z7n4q', 'private'=> 'EqXz2CgX3Am2BgNtjzb62NYF7qWMzuBD');
}

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

$cfg['boardGames'] = array('lol', 'hs', 'csgo');

$cfg['https'] = 0;
if ($cfg['protocol'] == 'https') {
    $cfg['https'] = 1;
}