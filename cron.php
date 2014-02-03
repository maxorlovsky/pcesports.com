<?php
if ($_GET['cronjob'] != 'u9a8sdu1209102129dSAD2u1239') {
    exit();
}
set_time_limit(300);

require_once $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';

$text = '
Team 1: %team1%<br />
Players 1:<br />
%players1%<br />

Team 2: %team2%<br />
Players 2:<br />
%players2%<br />

Team won: <b>%win%</b><br />
<br />
PentaClick eSports.';

$answer = runChallongeAPI('tournaments/pentaclick-lol1/matches.json', array(), 'state=open');

foreach($answer as $f) {
    $i = 1;
    $players = array();
    $team = array();
    $checkPlayers = array();
    $checkInGamePlayers = array();
    $captains = array();
    
    $msg = $text;
    //Team ID #1 - $f->match->player1_id;
    //Team ID #2 - $f->match->player2_id;   
    $q = mysql_query('SELECT `id`, `name`, `cpt_player_id` FROM `teams` WHERE (`challonge_id` = '.(int)$f->match->player1_id.' OR `challonge_id` = '.(int)$f->match->player2_id.') AND `approved` = 1 AND `deleted` = 0');
    if (mysql_num_rows($q) != 0) {
        while($r = mysql_fetch_object($q)) {
            $team[$i] = $r->name;
            $q2 = mysql_query('SELECT `id`, `name`, `player_id`, `player_num` FROM `players` WHERE `team_id` = '.$r->id.' AND `approved` = 1');
            $j = 0;
            while($r2 = mysql_fetch_object($q2)) {
                if ($r2->player_num == '1') {
                    $captains[$i] = $r2->player_id;
                }
                $players[$r2->player_id] = $r2->name;
                $checkPlayers[] = $r2->player_id;
                
                ++$j;
            }
            ++$i;
        }
        
        $answer = runAPI('/euw/v1.3/game/by-summoner/'.$captains[1].'/recent', true);
        $won = '';
        
        foreach($answer->games as $f2) {
            if ($f2->gameType == 'CUSTOM_GAME' && $f2->fellowPlayers) {
                $q3 = mysql_query('SELECT * FROM `fights` WHERE `game_id` = '.$f2->gameId);
                //If fight not registered
                //If fellowPlayers array even exists
                //If enemy team captain is in the fight
                foreach($f2->fellowPlayers as $f3) {
                    $checkInGamePlayers[] = $f3->summonerId;
                }
                
                if (mysql_num_rows($q3) == 0 && in_array($captains[2], $checkInGamePlayers)) {
                    $playersList = array(0=>'',1=>'');
                    
                    //Deciding who's won. If 1 then team 1 won of empty then team 2 won
                    if ($f2->stats->win == 1) {
                        $won = $team[1];
                    }
                    else {
                        $won = $team[2];
                    }
                    
                    if ($f2->teamId == 100) {
                        $playersList[0] .= $players[$captains[1]].' ('.$captains[1].')<br />';
                    }
                    else {
                        $playersList[1] .= $players[$captains[2]].' ('.$captains[2].')<br />';
                    }
                    
                    foreach($f2->fellowPlayers as $f3) {
                        if (in_array($f3->summonerId, $checkPlayers) && $f3->teamId == 100) {
                            $playersList[0] .= $players[$f3->summonerId].' ('.$f3->summonerId.')<br />';
                        }
                        else if (in_array($f3->summonerId, $checkPlayers) && $f3->teamId == 200) {
                            $playersList[1] .= $players[$f3->summonerId].' ('.$f3->summonerId.')<br />';
                        }
                        else if ($f3->teamId == 100) {
                            $playersList[0] .= '<u>'.$f3->summonerId.'</u> - <span style="color:red">player not found</span><br />';
                        }
                        else {
                            $playersList[1] .= '<u>'.$f3->summonerId.'</u> - <span style="color:red">player not found</span><br />';
                        }
                    }
                    
                    $msg = str_replace(
                        array('%team1%', '%team2%', '%players1%', '%players2%', '%win%'),
                        array($team[1], $team[2], $playersList[0], $playersList[1], $won),
                        $msg
                    );
                    
                    echo $msg;
                    echo '<br><br><br>';
                    exit();
                    
                    //mysql_query('INSERT INTO fights SET game_id = '.$f2->gameId);
                    //sendMail('max.orlovsky@gmail.com', 'PentaClick tournament - Result', $msg);
                    break(1);
                }
            }
        }

        sleep(3);
    }
}