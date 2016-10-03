<?php
require_once _cfg('classes').'/system.php';

if (isset($_POST['ajax']) && $_POST['ajax']) {
	$system = new System(0);
	$system->ajax($_POST);
}
//For angularjs
else if (file_get_contents("php://input")) {
	header('Content-Type: application/json');
	$input = (array)json_decode(file_get_contents("php://input"));
	$system = new System(0);
	$system->ajax($input, 'json');
}
//For upload
else if (isset($_GET['ajax']) && $_GET['ajax'] == 'uploadScreenshot') {
	$system = new System(0);
	$system->ajax($_GET);
}
else {
	//Loading whole system
	$system = new System(0);
	$system->run();
}