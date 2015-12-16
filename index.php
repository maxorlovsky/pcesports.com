<?php
/* 
 * Pentaclick eSports
 * http://www.pcesports.com
 * Credits (dev): Maxtream
 */

// Maintenance check
if (file_exists('maint_mode')) {
	die('This site is on maintenance');
}

require_once dirname(__FILE__).'/vendor/autoload.php';
?>