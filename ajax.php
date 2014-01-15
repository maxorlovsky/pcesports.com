<?php
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	exit();
}
$post = array();
$err = array();
parse_str($_POST['post'], $post);

foreach($post as $k => $v) {
    if (!$v) {
        $err[$k] = 'Field can not be empty';
    }
}
if ($err) {
    $answer['ok'] = 0;
    $answer['err'] = $err;
}

echo json_encode($answer);

/*
//Loading main files
require_once dirname(__FILE__).'/inc/config.php';
require_once $cfg['inc'].'/functions.php';

//Loading main class
require_once _cfg('classes').'/system.php';
require_once _cfg('classes').'/ajax.php';

//Fetching language in ajax query, we can't get it directly from link
if (isset($_POST['language'])) {
    $cfg['language'] = $_POST['language'];
}

//Checking only if lang is given, if it is empty it will be checked in system class
if (isset($_POST['language']) && $_POST['language']) {
    $cfg['language'] = $_POST['language'];
}
else if (isset($_GET['language']) && $_GET['language']) {
    $cfg['language'] = $_GET['language'];
}

$system = new Ajax();
if (defined('secureAjax')) {
    $system->ajaxRun($_GET);
}
else {
    $system->ajaxRun($_POST);
}
*/