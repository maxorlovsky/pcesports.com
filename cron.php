<?php
if ($_GET['cronjob'] != 'u9a8sdu1209102129dSAD2u1239') {
    exit();
}
set_time_limit(300);

require_once $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';

$text = '
Team 1: <b>%team1%</b><br />
Email: <b>%email1%</b><br />
Contact info: <b>%contact-info1%</b><br />
<br />
<br />
Team 1: <b>%team2%</b><br />
Email: <b>%email2%</b><br />
Contact info: <b>%contact-info2%</b>
<br />
Please remember that Round 1 games must be played in 3 days starting from now on!<br />
Use contact info of each other to agree on day and time of your match. Please don\'t forget to add pentaclickesports@gmail.com to CC or add organizers to skype chat.
<br />
PentaClick eSports.';

$answer = runChallongeAPI('tournaments/pentaclick-lol1/matches.json', array(), 'state=open');
$i = 1;
$players = array();
$checkPlayers = array();
foreach($answer as $f) {
    //Team ID #1 - $f->match->player1_id;
    //Team ID #2 - $f->match->player2_id;
    $q = mysql_query('SELECT `id`, `name`, `cpt_player_id` FROM `teams` WHERE `challonge_id` = '.(int)$f->match->player1_id.' OR `challonge_id` = '.(int)$f->match->player2_id);
    while($r = mysql_fetch_object($q)) {
        $q2 = mysql_query('SELECT `id`, `name`, `player_id` FROM `players` WHERE `team_id` = '.$r->id.' AND `approved` = 1');
        $j = 0;
        while($r2 = mysql_fetch_object($q2)) {
            $players[$i][$j]['id'] = $r2->id;
            $players[$i][$j]['name'] = $r2->name;
            $players[$i][$j]['player_id'] = $r2->player_id;
            
            $checkPlayers[$i][] = $r->player_id;
            ++$j;
        }
        ++$i;
    }
    
    $answer = runAPI('/euw/v1.3/game/by-summoner/'.$players[1][0]['player_id'].'/recent', true);
    
    foreach($answer->games as $f2) {
        if ($f2->gameType == 'CUSTOM_GAME') {
            dump($f2);
            foreach($f2->fellowPlayers as $f3) {
                if (!in_array($f3->summonerId, $checkPlayers)) {
                    
                }
            }
        }
    }
    
    //sendMail('max.orlovsky.net, pentaclickesports@gmail.com', 'PentaClick tournament - Round 1', $msg);
    
    $i = 1;
    $players = array();
    $checkPlayers = array();
    
    //exit();
    sleep(5);
}