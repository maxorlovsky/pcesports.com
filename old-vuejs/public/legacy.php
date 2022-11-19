<?php
/* 
 * Pentaclick eSports
 * http://www.pcesports.com
 * Credits (dev): Maxtream
 */

// Hardcoding parameters for language
if (isset($_GET['params']) && substr($_GET['params'], 0 , 2) != 'en') {
	$_GET['params'] = 'en/'.$_GET['params'];
}
if(isset($_GET['language'])) {
	$_GET['language'] = 'en';
}

require_once dirname(__FILE__).'/../vendor/autoload.php';
?>