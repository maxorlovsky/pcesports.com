<?php
if ($_GET['cronjob'] != 'u9a8sdu1209102129dSAD2u1239') {
    exit();
}
set_time_limit(300);

require_once $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';

$text = '
Team 1: %team1%<br />
<br />
Team 2: %team2%<br />
<br />
Team won: <b>%win%</b>
<br />
PentaClick eSports.';

$answer = runChallongeAPI('tournaments/pentaclick-lol1/matches.json', array(), 'state=open');

$i = 1;
$players = array();
$team = array();
$checkPlayers = array();
foreach($answer as $f) {
    $msg = $text;
    //Team ID #1 - $f->match->player1_id;
    //Team ID #2 - $f->match->player2_id;   
    $q = mysql_query('SELECT `id`, `name`, `cpt_player_id` FROM `teams` WHERE (`challonge_id` = '.(int)$f->match->player1_id.' OR `challonge_id` = '.(int)$f->match->player2_id.') AND `approved` = 1 AND `deleted` = 0');
    while($r = mysql_fetch_object($q)) {
        $team[$i] = $r->name;
        $q2 = mysql_query('SELECT `id`, `name`, `player_id` FROM `players` WHERE `team_id` = '.$r->id.' AND `approved` = 1');
        $j = 0;
        while($r2 = mysql_fetch_object($q2)) {
            $players[$i][$j]['id'] = $r2->id;
            $players[$i][$j]['name'] = $r2->name;
            $players[$i][$j]['player_id'] = $r2->player_id;
            
            $checkPlayers[$i][] = $r2->player_id;
            ++$j;
        }
        ++$i;
    }
    
    $answer = runAPI('/euw/v1.3/game/by-summoner/'.$players[1][0]['player_id'].'/recent', true);
    $won = '';
    foreach($answer->games as $f2) {
        if ($f2->gameType == 'CUSTOM_GAME') {
            $q3 = mysql_query('SELECT * FROM fights WHERE game_id = '.$f2->gameId);
            if (mysql_num_rows($q3) == 0) {
                if ($f2->fellowPlayers) {
                    foreach($f2->fellowPlayers as $f3) {
                        if (!in_array($f3->summonerId, $checkPlayers)) {
                            if ($f2->stats->win == 1) {
                                $won = $team[1];
                            }
                            else {
                                $won = $team[2];
                            }
                            
                            $msg = str_replace(
                                array('%team1%', '%team2%', '%win%'),
                                array($team[1], $team[2], $won),
                                $msg
                            );
                            mysql_query('INSERT INTO fights SET game_id = '.$f2->gameId);
                            sendMail('max.orlovsky@gmail.com', 'PentaClick tournament - Result', $msg);
                            break(2);
                        }
                    }
                }
            }
        }
    }
    
    
    
    $i = 1;
    $players = array();
    $checkPlayers = array();
    
    //exit();
    sleep(3);
}