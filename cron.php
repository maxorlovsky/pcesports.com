<?php
if ($_GET['cronjob'] != 'u9a8sdu1209102129dSAD2u1239') {
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';

$answer = runChallongeAPI('tournaments/3ygi2xm0/matches.json', array(), 'state=open');
foreach($answer as $f) {
    //Team ID #1 - $f->match->player1_id;
    //Team ID #2 - $f->match->player2_id;
    $f->match->player1_id = 12007002; //420gaming
    $f->match->player2_id = 12007004; //Splendid gaming
    $q = mysql_query('SELECT `id`, `name`, `cpt_player_id` FROM `teams` WHERE `challonge_id` = '.(int)$f->match->player1_id.' OR `challonge_id` = '.(int)$f->match->player2_id);
    //dump($r);
    
    
    //sleep(2);
}