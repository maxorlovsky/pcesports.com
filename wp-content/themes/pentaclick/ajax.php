<?php
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	exit();
}

require_once $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';

$controller = $_POST['control'];
$post = array();
$err = array();
parse_str($_POST['post'], $post);
foreach($post as $k => $v) {
    $post[$k] = trim($v);
}

if ($controller == 'registerTeam') {
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
                ' `approved` = 1 AND '.
                ' `deleted` = 0'
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
            ' `ip` = "'.mysql_real_escape_string($_SERVER['REMOTE_ADDR']).'", '.
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
        
        $text = getMailTemplate('reg-lol-team');
        
        $text = str_replace(
            array('%name%', '%team%', '%teamId%', '%code%', '%url%'),
            array($players[1]['name'], $post['team'], $teamId, $code, get_site_url()),
            $text
        );
        
        sendMail($post['email'], 'PentaClick eSports tournament participation', $text);
    }
}
else {
    $answer['ok'] = 0;
    $answer['err'] = 'Control not found';
}

echo json_encode($answer);