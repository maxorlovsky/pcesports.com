<?php
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	exit();
}

require_once $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';

$dbcnx = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
if (!$dbcnx) {
	exit('Database error');
}
@mysql_select_db(DB_NAME, $dbcnx);
mysql_query("set names 'utf8'");

$post = array();
$err = array();
parse_str($_POST['post'], $post);

if (!$post['team']) {
    $err['team'] = '0;'._p('field_empty', 'pentaclick');
}    
else if (strlen($post['team']) < 4) {
    $err['team'] = '0;'._p('team_name_small', 'pentaclick');
}
else if (strlen($post['team']) > 60) {
    $err['team'] = '0;'._p('team_name_big', 'pentaclick');
}
else {
    $suc['team'] = '1;'._p('approved', 'pentaclick');
}

if (!$post['email']) {
    $err['email'] = '0;'._p('field_empty', 'pentaclick');
}    
else if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
    $err['email'] = '0;'._p('email_invalid', 'pentaclick');
}
else {
    $suc['email'] = '1;'._p('approved', 'pentaclick');
}

if (!$post['contact']) {
    $err['contact'] = '0;'._p('field_empty', 'pentaclick');
}
else if($post['contact']) {
    $suc['contact'] = '1;'._p('approved', 'pentaclick');
}

$players = array();
$checkForSame = array();
for($i=1;$i<=7;++$i) {
    $post['mem'.$i] = trim($post['mem'.$i]);
    
    if (!$post['mem'.$i] && $i < 6) {
        $err['mem'.$i] = '0;'._p('field_empty', 'pentaclick');    
    }
    else if ($post['mem'.$i]) {
        $response = runAPI('/euw/v1.3/summoner/by-name/'.rawurlencode(htmlspecialchars($post['mem'.$i])));
        $q = mysql_query(
    		'SELECT * FROM `players` WHERE '.
    		' `tournament_id` = 1 AND '.
    		' `name` = "'.mysql_real_escape_string($post['mem'.$i]).'" AND '.
    		' `game` = "lol" AND '.
            ' `approved` = 1'
        );
        if (!$response) {
            $err['mem'.$i] = '0;'._p('summoner_not_found_euw', 'pentaclick');
        }
        else if ($response && $response->summonerLevel != 30) {
            $err['mem'.$i] = '0;'._p('summoner_low_lvl', 'pentaclick');
        }
        else if (in_array($post['mem'.$i], $checkForSame)) {
            $err['mem'.$i] = '0;'._p('same_summoner', 'pentaclick');
        }
        else if (mysql_num_rows($q) != 0) {
            $err['mem'.$i] = '0;'._p('summoner_already_registered', 'pentaclick');
        }
        else {
            $players[$i]['id'] = $response->id;
            $players[$i]['name'] = $response->name;
            $suc['mem'.$i] = '1;'._p('approved', 'pentaclick');
        }
        
        $checkForSame[] = $post['mem'.$i];
    }
}

if ($err) {
    $answer['ok'] = 0;
    if ($suc) {
        $err = array_merge($err, $suc);
    }
    $answer['err'] = $err;
}
else {
    $answer['ok'] = 1;
    $answer['err'] = $suc;
    
    $code = substr(sha1(time().rand(0,9999)), 0, 32);
    mysql_query(
		'INSERT INTO `teams` SET '.
        ' `game` = "lol", '.
        ' `tournament_id` = 1, '.
        ' `timestamp` = NOW(), '.
		' `name` = "'.mysql_real_escape_string($post['team']).'", '.
        ' `email` = "'.mysql_real_escape_string($post['email']).'", '.
        ' `contact_info` = "'.mysql_real_escape_string($post['contact']).'", '.
        ' `link` = "'.$code.'", '.
		' `cpt_player_id` = "'.(int)$players[1]['id'].'"'
    );
    
    $teamId = sql_last_id();
    
    foreach($players as $k => $v) {
        mysql_query(
    		'INSERT INTO `players` SET '.
            ' `game` = "lol", '.
            ' `tournament_id` = 1, '.
            ' `team_id` = '.(int)$teamId.', '.
    		' `name` = "'.mysql_real_escape_string($v['name']).'", '.
            ' `player_num` = "'.(int)$k.'", '.
            ' `player_id` = "'.(int)$v['id'].'"'
        );
    }
    
    if ($_GET['lang'] == 'ru') {
        $text = 'Привет <b>'.$players[1]['name'].'</b><br />
        Твоя команда <b>"'.$post['team'].'"</b> была успешно зарегистрирована на турнире PentaClick eSports по LoL.<br />
        Всё что тебе осталось сделать это подтвердить ваше участие пройдя по ссылке указаную ниже.<br />
        <br />
        <b>Ссылка подтверждения: <a href="http://www.pcesports.com/ru/verify/'.$teamId.'/'.$code.'/" target="_blank">http://www.pcesports.com/ru/verify/'.$teamId.'/'.$code.'/</a></b><br />
        <br />
        Если вы решите не участвовать в турнире до 1 февраля, то вы можете использовать ссылку указаную ниже для того что бы удалить вашу команду из списка.<br />
        <b>Ссылка удаления: <a href="http://www.pcesports.com/ru/delete/'.$teamId.'/'.$code.'/" target="_blank">http://www.pcesports.com/ru/delete/'.$teamId.'/'.$code.'/</a></b>
        <p style="color: red;">Эта ссылка удалит команду даже после её подтверждения, так что будте осторожнее и никому её не давайте!</p>
        <br />
        Если вы хотите связатся с организаторами PentaClick eSports, просто ответьте на это письмо или посетите наш сайт <a href="http://www.pcesports.com" target="_blank">http://www.pcesports.com</a><br />
        <br />
        С уважением.<br />
        PentaClick eSports
        ';
    }
    else {
        $text = 'Hello <b>'.$players[1]['name'].'</b><br />
        Your team <b>"'.$post['team'].'"</b> were successfully registered in the PentaClick eSports LoL tournament.<br />
        All what is left for you to do is to verify your participation by click a link below.<br />
        <br />
        <b>Verification link: <a href="http://www.pcesports.com/verify/'.$teamId.'/'.$code.'/" target="_blank">http://www.pcesports.com/verify/'.$teamId.'/'.$code.'/</a></b><br />
        <br />
        If you will decide not to participate in the tournament before 1st of february, you can use the link below to delete your team from the link.<br />
        <b>Deletion link: <a href="http://www.pcesports.com/delete/'.$teamId.'/'.$code.'/" target="_blank">http://www.pcesports.com/delete/'.$teamId.'/'.$code.'/</a></b>
        <p style="color: red;">This link will delete the team from the list even if it was verified, so use carefully and don\'t give it to anyone else!</p>
        <br />
        If you want to contact PentaClick eSports managers, just reply to this email or visit our website <a href="http://www.pcesports.com" target="_blank">http://www.pcesports.com</a><br />
        <br />
        Best regard.<br />
        PentaClick eSports
        ';
    }
    
    sendMail($post['email'], 'PentaClick eSports tournament participation', $text);
}

echo json_encode($answer);



function runAPI($apiAdditionalData) {
    $startTime = microtime(true);
    
    $apiUrl = 'http://prod.api.pvp.net/api/lol';
    $apiUrl .= $apiAdditionalData;
    $apiUrl .= '?api_key=84bd1101-4ea8-4814-be9f-ae26467c8275';
    
    
    mysql_query(
		'INSERT INTO `riot_requests` SET '.
		' `timestamp` = NOW(), '.
		' `ip` = "'.mysql_real_escape_string($_SERVER['REMOTE_ADDR']).'", '.
		' `data` = "'.$apiUrl.'"'
    );
    
    $lastId = sql_last_id();
    
	$ch = curl_init();
    
    //---
    curl_setopt($ch, CURLOPT_URL, $apiUrl); // set url to post to
    curl_setopt($ch, CURLOPT_FAILONERROR, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // times out after 119s
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_POST, 0); // set POST method
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $apiArray); // add POST fields
    
    $response = curl_exec($ch); // run the whole process 
    
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    if ($http_status == 400) {
		//$error = curl_error($ch);
        $error = 'Bad request';
	}
    else if ($http_status == 503) {
        $error = 'Service unavailable';
    }
    else if ($http_status == 500) {
        $error = 'Internal server error';
    }
    else if ($http_status == 401) {
        $error = 'Unauthorized';
    }
    else if ($http_status == 404) {
        $error = 'Not found';
    }
    
    $endTime = microtime(true);
    $duration = $endTime - $startTime; //calculates total time taken
    
    mysql_query(
		'UPDATE `riot_requests` SET '.
			' `response` = "'.($error?$error:mysql_real_escape_string( $response )).'", '.
            ' `time` = "'.(float)$duration.'" '.
		' WHERE id='.$lastId
	);
	
	if ( $error )
	{
		return false;
	}
    
    $response = (array)json_decode($response);
    $response = array_values($response);
    $response = $response[0];
    
    return (object)$response;
}

//Getting last insert ID from mysql query
function sql_last_id() {
	$q = mysql_query('SELECT LAST_INSERT_ID()');
	$id = mysql_result($q, 0, 0);
	return $id;
}

function sendMail($email, $subject, $msg) {
    // SMTP config
    $cfg['smtpMailName'] = 'pentaclickesports@gmail.com';
    $cfg['smtpMailPort'] = '465';
    $cfg['smtpMailHost'] = 'ssl://smtp.gmail.com';
    $cfg['smtpMailPass'] = 'knyaveclickius888';
    $cfg['smtpMailFrom'] = 'PentaClick eSports';

    $mailData = 'Date: '.date('D, d M Y H:i:s')." UT\r\n";
    $mailData .= 'Subject: =?UTF-8?B?'.base64_encode($subject). "=?=\r\n";
    $mailData .= 'Reply-To: '.$cfg['smtpMailName']. "\r\n";
    $mailData .= 'MIME-Version: 1.0'."\r\n";
    $mailData .= 'Content-Type: text/html; charset="UTF-8"'."\r\n";
    $mailData .= 'Content-Transfer-Encoding: 8bit'."\r\n";
    $mailData .= 'From: "'.$cfg['smtpMailFrom'].'" <'.$cfg['smtpMailName'].'>'."\r\n";
    $mailData .= 'To: '.$email.' <'.$email.'>'."\r\n";
    $mailData .= 'X-Priority: 3'."\r\n\r\n";
    
    $mailData .= $msg."\r\n";
    
    if(!$socket = fsockopen($cfg['smtpMailHost'], $cfg['smtpMailPort'], $errno, $errstr, 30)) {
        return $errno."&lt;br&gt;".$errstr;
    }
    if (!serverParse($socket, '220', __LINE__)) return false;
    
    fputs($socket, 'HELO '.$cfg['smtpMailHost']. "\r\n");
    if (!serverParse($socket, '250', __LINE__)) return false;
    
    fputs($socket, 'AUTH LOGIN'."\r\n");
    if (!serverParse($socket, '334', __LINE__)) return false;
    
    fputs($socket, base64_encode($cfg['smtpMailName']) . "\r\n");
    if (!serverParse($socket, '334', __LINE__)) return false;
    
    fputs($socket, base64_encode($cfg['smtpMailPass']) . "\r\n");
    if (!serverParse($socket, '235', __LINE__)) return false;
    
    fputs($socket, 'MAIL FROM: <'.$cfg['smtpMailName'].'>'."\r\n");
    if (!serverParse($socket, '250', __LINE__)) return false;
    
    fputs($socket, 'RCPT TO: <'.$email.'>'."\r\n");
    if (!serverParse($socket, '250', __LINE__)) return false;
    
    fputs($socket, 'DATA'."\r\n");
    if (!serverParse($socket, '354', __LINE__)) return false;
    
    fputs($socket, $mailData."\r\n.\r\n");
    if (!serverParse($socket, '250', __LINE__)) return false;
    
    fputs($socket, 'QUIT'."\r\n");
    
    fclose($socket);
    
    return true;
}

function serverParse($socket, $response, $line = __LINE__) {
    while (substr($server_response, 3, 1) != ' ') {
        if (!($server_response = fgets($socket, 256))) {
            echo 'Error: '.$server_response.', '. $line;
            return false;
        }
    }
    if (!(substr($server_response, 0, 3) == $response)) {
        echo 'Error: '.$server_response.', '. $line;
        return false;
    }
    return true;
}

function _p($text, $domain) {
    return __($text, $domain);
}