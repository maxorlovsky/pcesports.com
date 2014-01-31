<?php
if ($_GET['invjob'] != 'asdok2910SJDAsld1029') {
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';

$text = '
Team 1: <b>%team1%</b><br />
Email: <b>%email1%</b><br />
Contact info: <b>%contact-info1%</b><br />
<br />
<br />
Team 2: <b>%team2%</b><br />
Email: <b>%email2%</b><br />
Contact info: <b>%contact-info2%</b>
<br />
Please remember that Round 1 games must be played in 3 days starting from now on!<br />
Use contact info of each other to agree on day and time of your match. Please don\'t forget to add pentaclickesports@gmail.com to CC or add organizers to skype chat.
<br />
PentaClick eSports.';

$answer = runChallongeAPI('tournaments/pentaclick-lol1/matches.json', array(), 'state=open');
$emails = array();
$i = 1;

foreach($answer as $f) {
    //Team ID #1 - $f->match->player1_id;
    //Team ID #2 - $f->match->player2_id;
    $msg = $text;
    $q = mysql_query('SELECT `id`, `name`, `email`, `cpt_player_id` FROM `teams` WHERE `challonge_id` = '.(int)$f->match->player1_id.' OR `challonge_id` = '.(int)$f->match->player2_id);
    while($r = mysql_fetch_object($q)) {
        $msg = str_replace(
            array('%team'.$i.'%', '%email'.$i.'%', '%contact-info'.$i.'%'),
            array($r->name, $r->email, nl2br($r->contact_info)),
            $msg
        );
        $emails[] = $r->email;
        ++$i;
    }
    $emails[] = 'pentaclickesports@gmail.com';
    dump($emails);
    sendMail('max.orlovsky@gmail.com', 'PentaClick tournament - Round 1', $msg);
    exit();
    $emails = array();
    $i = 1;
    //sleep(2);
}