<?php
$cfg['salt'] = 'eethaiASLDK21lae6AASDta9ChoDDCh';
$cfg['logs'] = 1;
$cfg['maxLevel'] = 4;
$cfg['allowedLanguages'] = array('en');//ru
$cfg['defaultLanguage'] = 'en';

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