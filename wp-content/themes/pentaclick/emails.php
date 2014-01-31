<?php
if ($_GET['mailjob'] != 'runrun') {
    exit('.');
}

require_once $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';

$text = 'Уважаемая команда <b>%team%</b>,<br />
первый день практически на носу, скоро начнутся первые бои в нашем турнире!<br />
<br />
В первую очередь хотелось бы рассказать как будет проходить турнир:<br />
<br />
Когда сетка будет доступна, через какое то время вы получите письмо с контактной информацией капитана противников.<br />
Будет это е-почта или скайп, мы советуем добавлять всегда нашу е-почту (pentaclickesports@gmail.com) в CC (копию), или добавлять нас в общий скайп-чат, где вы будете общатся с капитаном-оппонентом. Скайп имена организаторов - <u>maxorlovsky</u> и <u>facebook:judgement.hasnoname</u><br />
<br />
Когда вы будете переписыватся с капитаном-оппонентом, от вас требуется, чтоб вы договорили о дне и времени вашего боя. Всегда учитывайте времяную зону соперников и пожалуйста используйте английский язык для общения, если команда соперник не русско-языная. Так же вам надо будет вместе решить, хотите ли вы, чтоб ваша игра попала на стрим ( перед четверть финалом стрим игр будет доступен только 1 февраля, без комментариев ). Если обе команды соглашаются на стрим, то вы должны нам об этом сообщить и назвать время игры. ( Мы можем задержать время проведения игры по причине, что в тот момент проходит стрим другой игры. Так же, мы скорее всего не сможем стримить полностью все игры ).<br /> 
<br />
Битвы в турнире будут засчитаны, если они будут сыграны после 1 фев. 16:00 МСК, когда турнир официально стартует.<br />
Битвы в каждом раунде должны быть сыграны в течении 3-ёх дней. (исключая раунд 1 и раунд 2, которые должны быть сыграны в течении первых 3 дней).<br />
<br />
После того как вы сыграли, вам ничего не нужно делать. Мы получим автоматическую нотификацию и примерно через 60 минут вы увидите ваше продвежение по сетке и получите информацию о вашем следующем оппоненте.<br />
<br />
После того как команды достигли четверть финала, турнир возьмёт не большую паузу и будет анонсирован день финальных игр.<br />
<br />
PentaClick eSports.';

/*$text = 'Dear team <b>%team%</b>,<br />
the day almost come upon us for the first days of battles in our tournament!<br />
<br />
Here are some outlines about how our tournament will proceed:<br />
<br />
When the brackets will open in some time after you will receive an email with other team captains contact information.<br />
Whether you will be contacting through e-mail or skype, it is advised to add our e-mail (pentaclickesports@gmail.com) in CC, or add us in skype chat between captains. Organizers skype names are - <u>maxorlovsky</u> and <u>facebook:judgement.hasnoname</u><br />
<br />
When contacting other teams captain, you will have to agree on day and time of your battle ( keep in mind that a team might have a different time zone from yours), and decide whether or not you want your game to be streamed ( before quarterfinals streaming will be available only on 1st February and without commentary). If both agree on streaming, you will have to contact us about it, and tell approximate time of the game. ( We might delay the a game a bit due to streaming another game, and also we will not be able to stream all games ).<br /> 
<br />
Battles for our tournament will only count if played after 1 feb. 14:00 CET , when the tournament officially starts.<br />
The battles for each round should be played within 3 days. (excluding this months tournament when round 1 and 2 will be played within first 3 days).<br />
<br />
After winning a game you won\'t have to do anything. We will receive results automatically and within approx. 60 minutes you will see your promotion in the bracket, and get the e-mail about your next opponents.<br />
<br />
After all teams have reached quarterfinals, the tournament will briefly stop, and we will announce the day of quarterfinal to final games.<br />
<br />
PentaClick eSports.';*/

$q = mysql_query('SELECT name, email FROM teams WHERE approved = 1 AND deleted = 0 AND game = "lol" and tournament_id = 1');
while ($r = mysql_fetch_object($q)) {
    if (substr($r->email, -2) == 'ru') {
        $msg = str_replace(
            array('%team%'),
            array($r->name),
            $text
        );
        
        sendMail($r->email, 'PentaClick LoL tournament #1 - детали', $msg);
        
        sleep(2);
    }
}