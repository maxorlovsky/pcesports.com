<?php
if ($_GET['cronjob'] != 'u9a8sdu1209102129dSAD2u1239') {
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';

$answer = runChallongeAPI('tournaments/pentaclick-lol1/matches.json', array(), 'state=open');
foreach($answer as $f) {
    //Team ID #1 - $f->match->player1_id;
    //Team ID #2 - $f->match->player2_id;
    $q = mysql_query('SELECT `id`, `name`, `email`, `cpt_player_id` FROM `teams` WHERE `challonge_id` = '.(int)$f->match->player1_id.' OR `challonge_id` = '.(int)$f->match->player2_id);
    while($r = mysql_fetch_object($q)) {
        dump($r);
    }
    echo '<br>---<br>';
    //sleep(2);
}