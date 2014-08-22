<?
global $cfg;
global $astr;

require_once dirname(__FILE__).'/cms/inc/config.php';
require_once $cfg['cmsinc'].'/functions.php';

$dbcnx = mysql_connect($cfg['dbHost'].':'.$cfg['dbPort'],$cfg['dbUser'],$cfg['dbPass']);
@mysql_select_db($cfg['dbBase'], $dbcnx);
mysql_query ("set names 'utf8'");

$heroes = array(
    1 => 'warrior',
    2 => 'hunter',
    3 => 'mage',
    4 => 'warlock',
    5 => 'shaman',
    6 => 'rogue',
    7 => 'druid',
    8 => 'paladin',
    9 => 'priest',
);
        
$q = mysql_query('SELECT `name`, `contact_info` '.
    'FROM `teams` '.
    'WHERE `game` = "hslan" AND `approved` = 1 AND `deleted` = 0 '.
    'ORDER BY `id` ASC'
);

while($r = mysql_fetch_object($q)) {
    $info = json_decode($r->contact_info);
    echo '<b>'.$r->name.'</b> - '.ucfirst($heroes[$info->hero1]).' / '.ucfirst($heroes[$info->hero2]).'<br />';
}

?>