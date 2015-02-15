<?
global $cfg;
global $astr;
//aa
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

$group = array(
    1 => 'A',
    2 => 'B',
    3 => 'C',
    4 => 'D',
    5 => 'E',
    6 => 'F',
    7 => 'G',
    8 => 'H',
);

$q = mysql_query('SELECT `name`, `contact_info`, `seed_number` '.
    'FROM `participants` '.
    'WHERE `game` = "hs" AND `tournament_id` = 6 AND `approved` = 1 AND `deleted` = 0 AND `seed_number` != 0 '.
    'ORDER BY `seed_number` ASC '.
    'LIMIT 32'
);
$previouSeed = 0;
while($r = mysql_fetch_object($q)) {
    $info = json_decode($r->contact_info);
    if ($r->seed_number != $previousSeed) {
        if ($r->seed_number != 1) {
            echo '<br />';
        }
        echo '<b>Group '.$group[$r->seed_number].'</b><br />';
    }
    echo $r->name.' - '.
        ucfirst($heroes[$info->hero1]).' / '.ucfirst($heroes[$info->hero2]).' '.' / '.ucfirst($heroes[$info->hero3]).' '.' / '.ucfirst($heroes[$info->hero4]).' '.
        ($info->phone?'('.$info->phone.')':null).'<br />';
        
    $previousSeed = $r->seed_number;
}

?>