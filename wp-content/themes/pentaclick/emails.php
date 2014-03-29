<?php
//exit('Not required!');
if ($_GET['mailjob'] != 'runrun') {
    exit('.');
}

require_once $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';

/*$text = 'Уважаемая команда <b>%team%</b>,<br />
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
PentaClick eSports.';*/

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

/*$text = 'Dear team <b>%team%</b>,<br />
Congratulations for making it to quarterfinals!<br />
<br />
We want to inform you that all games from quarterfinals to finals will be held this sunday, 9 feb.<br />
Games will be streamed online, with shoutcasters on 2 chanels and different languages.<br />
<br />
Without further ado, we will rush in schedule:<br />
Start at 12:00 CET/GMT+1<br />
12:00 - 2 "top" quarter finals games will be played.<br />
<i>DrakenDodders or Divine Desteny</i> VS LatvianSureno and Noximus Prime vs i2HARD.Gaming<br />
13:00 - 2 "bottom" quarter finals games will be played.<br />
Violent Gaming EU VS Bronzodia! and Æsy VS Splendid Gaming<br />
14:00 - first semifinal game<br />
15:00 - second semifinal game<br />
16:00 - match for bronze 3rd place<br />
17:00 - 1h break before finals
18:00 to 21:00 - finals<br />
<br />
Of course if games will be played faster, then whole tournament and stream will go much faster then planned.<br />
We kindly ask you to be ready to play at scheduled time because stream will be live online and we will not gonna be able to change the time. If team is unable to be on time it will be disqualified instantly.<br />
Time limit for absence - 15 minutes.<br />
In game time pause limit - 15 minutes.<br />
<br />
We wish you all good luck in your upcoming games and we hope to see great plays from you.<br />
<br />
PentaClick eSports.';*/

/*$text = 'Уважаемая команда <b>%team%</b>,<br />
Поздравляем вас. Вы достигли четвертьфинала!<br />
<br />
Мы хотим проинформировать вас, что все игры начиная с четвертифинала до финала будут сыграны в эту субботу, 9 фев.<br />
Игры будут проигрыватся в онлайн стриме, с комментаторами, 2-ух канала и разных языках.<br />
<br />
Без дальнейших церемоний, мы перейдём к расписанию:<br />
Старт будет в 12:00 CET/GMT+1. По Московскому времени это 15:00 (GMT+4).<br />
12:00 - играют 2 "верхних" четвертьфиналиста.<br />
<i>DrakenDodders или Divine Desteny</i> против LatvianSureno и Noximus Prime против i2HARD.Gaming<br />
13:00 - играют 2 "нижних" четвертьфиналиста.<br />
Violent Gaming EU против Bronzodia! и Æsy против Splendid Gaming<br />
14:00 - первая полуфинальная игра<br />
15:00 - вторая получинальная игра<br />
16:00 - матч за бронзовое 3-тье место<br />
17:00 - перерыв 1ч перед финалами
18:00 до 21:00 - финалы<br />
<br />
Если игры будут проходить гораздо быстрее чем 1ч. то конечно же и весь турнир будет проходить быстрее чем запланированно в расписании.<br />
Мы просим вас быть готовыми играть в назначенное время, поскольку стрим будет в лайв-трансляции и мы не сможем менять время. Если команда не сможем появится в назначенное время, она будет немедленно дисквалифицированна.<br />
Время разрешающее на задержку перед игрой - 15 минут.<br />
Внутри-игровая пауза - 15 минут.<br />
<br />
Мы желаем вам удачи в предстоящих боях и надеемся увидить от вас отличные игры.<br />
<br />
PentaClick eSports.';*/

/*$text = 'Dear <b>%team%</b>,<br />
the day almost come upon us!<br />
<br />
Here are some outlines about how our tournament will be held:<br />
<br />
Starting from 14:00 (GMT+1/CET) in your profiler (link bellow or in previous email) you will see your opponent status and name. You can add your opponent directly through battle.net invite by battle tag or chat about anything in battle chat. Remember, that Pentaclick eSports can read your Battle chat, so it is counts as official log/chat.<br />
After inviting each other one of you must challenge other person to a battle.<br />
Please do not forget that you can use only <b>1 class</b> in each best of three fight. For example if you started to play as Paladin, you must complete those best of three fights as Palading. But you <b>can change your cards</b> in the deck between the fights.<br />
After each fight, when you have a victory screen you <span style="color: red">MUST TAKE A SCREENSHOT of it, where we can clearly see your nickname, opponent nickname and was you victory or not!</span><br />
This screenshot must be afterwards uploaded into the battle chat, <u>upload button is located in the right bottom corner of battle chat</u>. Please keep in mind, that uploading unrelated images or spam uploading button will disqualify you from current tournament and maybe even the future ones. Also, there is a limit!<br />
<br />
After the games were played and images were uploaded organizators will going to check them and victorious player will be moved further in the bracket.<br />
Player who was victorious will receive info about new opponent as soon as he will be ready! Opponent name, status and battle chat will be updated automatically.<br />
<br /> 
If you will be streaming the games via twitch or youtube, let us know, we will tell everyone about it!<br /> 
<br />
Battles for our tournament will only count if played after 1 march. 14:00 CET , when the tournament officially starts.<br />
<br />
Your profiler: <a href="%link%" target="_blank">%link%</a>
<br />
<br />
Good luck.<br />
PentaClick eSports.';*/

/*$text = 'Dear <b>%team%</b>,<br />
Fights are about to start, please enter your profiler!<br />
<br />
Your profiler: <a href="%link%" target="_blank">%link%</a>
<br />
<br />
Good luck.<br />
PentaClick eSports.';*/

$text = 'Hello <b>%team%</b>.<br />
<br />
This is just a friendly reminder. Tournament will start today (in 1 hour) at 13:00 (CET/GMT+1). Don not forget to log in!<br />
<br />
Your profiler: <a href="%link%" target="_blank">%link%</a><br />
<br />
Best regards,<br />
PentaClick eSports<br />
<br />
<i>web: <span style="color: #3d85c6;">http://www.pceports.com</span><br />
email: <span style="color: #3d85c6;">info@pcesports.com</span><br />
phone: +37129788896<br />
business manager (skype): <span style="color: #3d85c6;">maxorlovsky</span><br />
community manager (skype): <span style="color: #3d85c6;">mr.aven</span><br />
community manager (skype): <span style="color: #3d85c6;">magnez-templarpenthouse</span><br />
<br />
Have ideas? Want to help? Mail us!</i>';

$q = mysql_query('SELECT `id`, `name`, `email`, `link` FROM teams WHERE approved = 1 AND deleted = 0 AND game = "lol" and tournament_id = 2');
while ($r = mysql_fetch_object($q)) {
    //if (substr($r->email, -2) == 'ru') {
        $msg = str_replace(
            array('%team%', '%link%'),
            array($r->name, LOLURL.'/profile/'.$r->id.'/'.$r->link.'/'),
            $text
        );
        sendMail($r->email, 'PentaClick LoL tournament #1, reminder', $msg);
        
        sleep(2);
    //}
}