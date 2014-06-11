<?php
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	exit();
}

require_once $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';

$controller = $_POST['control'];
$action = $_POST['action'];
$post = array();
$err = array();
parse_str($_POST['post'], $post);
foreach($post as $k => $v) {
    $post[$k] = trim($v);
}

if ($controller == 'updateTeam' && $_POST['u']) {
    $players = array();
    $checkForSame = array();
    $err = array();
    $suc = array();
    parse_str($_POST['u'], $u);
    
    $q = mysql_query('SELECT `t`.`challonge_id`, `hsf`.`player1_id`, `hsf`.`player2_id`, `hsf`.`done`
    FROM `teams` AS `t`
    LEFT JOIN `hs_fights` AS `hsf` ON (`t`.`challonge_id` = `hsf`.`player1_id` OR `t`.`challonge_id` = `hsf`.`player2_id`)
    WHERE
    `t`.`id` = '.(int)$u['tId'].' AND
    `t`.`link` = "'.mysql_real_escape_string($u['code']).'" AND
    `t`.`deleted` = 0
    ');
    if (mysql_num_rows($q) == 0) {
        $answer['ok'] = 0;
    }
    else {
        for($i=1;$i<=7;++$i) {
            if (!$post['mem'.$i] && $i < 6) {
                $err['mem'.$i] = '0;'._p('field_empty', 'pentaclick');    
            }
            else if ($post['mem'.$i]) {
                $response = runAPI('/euw/v1.3/summoner/by-name/'.rawurlencode(htmlspecialchars($post['mem'.$i])));
                /*$q = mysql_query(
            		'SELECT * FROM `players` WHERE '.
            		'`tournament_id` = '.(int)cOptions('tournament-lol-number').' AND '.
            		'`game` = "lol" AND '.
                    '`approved` = 1 AND '.
                    '`deleted` = 0 AND '.
                    '`id` != '.(int)$post['mem'.$i.'-id'].' AND '.'`name` = "'.mysql_real_escape_string($post['mem'.$i]).'"'
                );*/
                if (!$response) {
                    $err['mem'.$i] = '0;'._p('summoner_not_found_euw', 'pentaclick');
                }
                else if ($response && $response->summonerLevel != 30) {
                    $err['mem'.$i] = '0;'._p('summoner_low_lvl', 'pentaclick');
                }
                else if (in_array($post['mem'.$i], $checkForSame)) {
                    $err['mem'.$i] = '0;'._p('same_summoner', 'pentaclick');
                }
                /*else if (mysql_num_rows($q) != 0) {
                    $err['mem'.$i] = '0;'._p('summoner_already_registered', 'pentaclick');
                }*/
                else {
                    $players[$i]['id'] = $response->id;
                    $players[$i]['name'] = $response->name;
                    $suc['mem'.$i] = '1;'._p('approved', 'pentaclick');
                }
                
                $checkForSame[] = $post['mem'.$i];
            }
        }
        
        $answer['ok'] = 1;
        if ($suc) {
            $err = array_merge($err, $suc);
        }
        $answer['message'] = $err;
    
        mysql_query(
    		'UPDATE `teams` SET '.
    		'`cpt_player_id` = "'.(int)$players[1]['id'].'" '.
            'WHERE `id` = '.(int)$u['tId'].' AND '.
            '`game` = "lol" AND '.
            '`tournament_id` = '.(int)cOptions('tournament-lol-number')
        );
        
        mysql_query(
            'DELETE FROM `players` '.
            'WHERE '.
            '`team_id` = '.(int)$u['tId'].' AND '.
            '`game` = "lol" AND '.
            '`tournament_id` = '.(int)cOptions('tournament-lol-number')
        );
        
        foreach($players as $k => $v) {
            mysql_query(
        		'INSERT INTO `players` SET '.
                '`game` = "lol", '.
                '`tournament_id` = '.(int)cOptions('tournament-lol-number').', '.
                '`team_id` = '.(int)$u['tId'].', '.
        		'`name` = "'.mysql_real_escape_string($v['name']).'", '.
                '`player_num` = "'.(int)$k.'", '.
                '`player_id` = "'.(int)$v['id'].'", '.
                '`approved` = 1 '
            );
        }
    }
}
else {
    $answer['ok'] = 0;
    $answer['err'] = 'Control not found';
}

echo json_encode($answer);