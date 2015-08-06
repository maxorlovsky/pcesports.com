<?php

class Cron extends System {
    public function __construct() {
        parent::__construct();
    }

    public function createLinks() {
        $rows = Db::fetchRows('SELECT * FROM `participants_external` WHERE `project` = "skillz" AND `link` IS NULL LIMIT 10');

        $text = Template::getMailTemplate('reg-player-widget');

        foreach($rows as $v) {
            $code = substr(sha1(time().rand(0,9999)).$v->name, 0, 32);
            $tournamentName = 'MSI MCS Open Season 3 HearthStone Baltic Qualifier';
            $url = 'http://skillz.lv/ru/news/2046?&participant='.$lastId.'&link='.$code.'&';
            $additionalText = 'Tournament is going to happen only if 8 participants going to register (with payment) in the tournament.<br />Do not forget that tournament starts this Saturday at 12:00. To participate in the tournament, you must log in from 11:00 till 12:00 and "check in" to approve, that you are online. Then you will see a chat with your opponent and brackets.';

            $mailText = str_replace(
                array('%name%', '%tournamentName%', '%url%', '%additionalText%', '%teamName%'),
                array($v->name, $tournamentName.' tournament', $url, $additionalText, 'Skillz.lv and Pentaclick eSports'),
                $text
            );

            Db::query(
                'UPDATE `participants_external` '.
                'SET `link` = "'.$code.'" '.
                'WHERE `project` = "skillz" AND `id` = '.$v->id
            );
            
            $this->sendMail($v->email, $tournamentName.' participation', $mailText);
        }
    }

    public function emailSender() {
        $limit = 500;

        $row = Db::fetchRow(
            'SELECT * FROM `subscribe_sender` '.
            'WHERE (`timestamp` < DATE_SUB( NOW(), INTERVAL 2 HOUR ) OR `timestamp` IS NULL) '.
            'ORDER BY `id` ASC '.
            'LIMIT 1'
        );

        if (!$row) {
            return false;
        }

        $query = 'SELECT * FROM `subscribe` WHERE '.$row->type.' AND `removed` = 0 LIMIT '.$row->emails.','.$limit;

        $rows = Db::fetchRows($query);

        $i = 0;
        $j = 0;
        foreach($rows as $v) {
            $text = str_replace(
                array(
                    '%url%',
                    '%unsublink%',
                ),
                array(
                    _cfg('site'),
                    _cfg('href').'/unsubscribe/'.$v->unsublink,
                ),
                $row->text
            );

            $transport = Swift_SmtpTransport::newInstance('ssl://smtp.gmail.com', 465);
            $transport->setUsername('pentaclickesports@gmail.com');
            $transport->setPassword('zwAt!&JfA!MU!YE&gArw');
            
            $message = Swift_Message::newInstance()
            ->setSubject($row->subject)
            ->setFrom(array('pentaclickesports@gmail.com' => 'Pentaclick eSports'))
            ->setTo(array($v->email))
            ->setBody($text, 'text/html');

            //Sending message
            $mailer = Swift_Mailer::newInstance($transport);
            $mailer->send($message, $fails);
            
            ++$i;
            ++$j;
            if ($i >= 5) {
                sleep(3);
                $i = 0;
            }
        }

        if ($j < $limit) {
            //If number of sent mails are less then limit, then we probably sent all of them
            Db::query('DELETE FROM `subscribe_sender` WHERE `id` = '.$row->id);
        }
        else {
            //Not all sent, adding timestamp and email limit
            Db::query(
                'UPDATE `subscribe_sender` SET '.
                '`emails` = '.($row->emails + $limit).', '.
                '`timestamp` = NOW() '.
                'WHERE `id` = '.$row->id
            );
        }
    }
    
    public function sqlCleanUp() {
        Db::multi_query('CALL cleanupOldData()');
        
        do 
        {
            $r = Db::store_result();
        }
        while ( Db::more_results() && Db::next_result() );
        
        return true;
    }
    
    public function cleanImagesTmp() {
        $file = _cfg('uploads').'/cronCleanImages';

        if (file_exists($file) && filemtime($file)+24*60*60 > time()) { //24h hours timer, no need to update the file
            return false;
        }
        else if (!file_exists($file)) {
            $fopen = fopen($file, 'w');
            fclose($fopen);
        }
        
        $folder = _cfg('uploads').'/news';
        
        $fileList = array();
        $handler = opendir($folder);
        $ignoreFiles = array('.svn');
        while($file = readdir($handler)) {
            //Checking if not hidden files
            if ($file != "." && $file != "..") {
                //Checking if file ignoring is required
                if(!in_array($file, $ignoreFiles) && substr($file, -3) == 'tmp') {
                    unlink($folder.'/'.$file);
                }
            }
        }
        closedir($handler);
    }

    public function updateSummoners() {
        $limit = 10; //Maximum 10, limit by Riot

        $row = Db::fetchRow('SELECT COUNT(`id`) AS `count` FROM `summoners`');
        $currentCount = $row->count;

        foreach(_cfg('lolRegions') as $server => $v) {
            $rows = Db::fetchRows(
                'SELECT * FROM `summoners` '.
                'WHERE `region` = "'.$server.'" AND '.
                '`approved` = 1 '.
                'LIMIT '.(int)$this->data->settings['latest-summoner'].', '.$limit
            );
            
            if ($rows) {
                $summonersIds = array();
                $summonersIdsString = '';
                foreach($rows as $v) {
                    $summonersIds[$v->summoner_id] = $v->id;
                    $summonersIdsString .= $v->summoner_id.',';
                }
                $summonersIdsString = substr($summonersIdsString, 0, -1);

                $response = $this->runRiotAPI('/'.$server.'/v2.5/league/by-summoner/'.$summonersIdsString.'/entry', $server, true);
                
                foreach($response as $k => $v) {
                    $dbId = $summonersIds[$k];
                    $v = (array)$v;

                    $arrayNum = null;
                    if ($v[0]->queue == 'RANKED_SOLO_5x5') {
                        $arrayNum = 0;
                    }
                    else if ($v[1]->queue == 'RANKED_SOLO_5x5') {
                        $arrayNum = 1;
                    }
                    else if ($v[2]->queue == 'RANKED_SOLO_5x5') {
                        $arrayNum = 2;
                    }
                    else {
                        break;
                    }

                    //$v[$arrayNum]->entries[0] //probably there are more entries, check required
                    Db::query(
                        'UPDATE `summoners` '.
                        'SET `league` = "'.Db::escape($v[$arrayNum]->tier).'", '.
                        '`division` = "'.Db::escape($v[$arrayNum]->entries[0]->division).'", '.
                        '`name` = "'.Db::escape($v[$arrayNum]->entries[0]->playerOrTeamName).'", '.
                        '`last_update` = NOW() '.
                        'WHERE `id` = '.(int)$dbId
                    );
                }
            }
        }

        if ($this->data->settings['latest-summoner'] > $currentCount) {
            $this->data->settings['latest-summoner'] = 0;
        }
        else {
            $this->data->settings['latest-summoner'] = $this->data->settings['latest-summoner'] + $limit;
        }

        Db::query(
            'UPDATE `tm_settings` '.
            'SET `value` = '.$this->data->settings['latest-summoner'].' '.
            'WHERE `setting` = "latest-summoner" '
        );
    }
    
    public function updateStreamers() {
        $rows = Db::fetchRows('SELECT * FROM `streams`');
        
        foreach($rows as $v) {
            $twitch = $this->runTwitchAPI($v->name);
            
            if ($twitch['stream'] != NULL) {
                Db::query(
                    'UPDATE `streams` '.
                    'SET `online` = '.time().', '.
                    '`game` = "'.$this->convertGame($twitch['stream']['game']).'", '.
                    '`viewers` = '.(int)$twitch['stream']['viewers'].', '.
                    '`display_name` = "'.Db::escape($twitch['stream']['channel']['display_name']).'" '.
                    'WHERE `id` = '.(int)$v->id
                );
            }
        }

        $rows = Db::fetchRows('SELECT * FROM `streams_events`');
        
        foreach($rows as $v) {
            $twitch = $this->runTwitchAPI($v->name);
            
            if ($twitch['stream'] != NULL) {
                Db::query(
                    'UPDATE `streams_events` '.
                    'SET `online` = '.time().', '.
                    '`viewers` = '.(int)$twitch['stream']['viewers'].', '.
                    '`display_name` = "'.Db::escape($twitch['stream']['channel']['display_name']).'" '.
                    'WHERE `id` = '.(int)$v->id
                );
            }
        }
    }

    private function convertGame($game) {
        $array = array(
            'Hearthstone: Heroes of Warcraft'   => 'hs',
            'League of Legends'                 => 'lol',
            'Counter-Strike: Global Offensive'  => 'cs',
            'Dota 2'                            => 'dota',
            'Smite'                             => 'smite',
        );

        if (isset($array[$game]) && $array[$game]) {
            $answer = $array[$game];
        }
        else {
            $answer = 'other';
        }

        return $answer;
    }
    
    public function updateChallongeMatches() {
        if ($this->data->settings['tournament-start-hs-s1'] == 1) {
            if (_cfg('env') == 'prod') {
                $answer = $this->runChallongeAPI('tournaments/pentaclick-hss1'.$this->data->settings['hs-current-number-s1'].'/matches.json', array(), 'state=open');
            }
            else {
                $answer = $this->runChallongeAPI('tournaments/pentaclick-test1/matches.json', array(), 'state=open');
            }

            foreach($answer as $v) {
                //Checking if match is already registered
                $row = Db::fetchRow('SELECT `match_id` '.
                    'FROM `fights` '.
                    'WHERE `match_id` = '.(int)$v->match->id
                );
                if (!$row) {
                    //Registering match, if still not yet registered
                    Db::query('INSERT INTO `fights` SET '.
                        '`match_id` = '.(int)$v->match->id.', '.
                        '`player1_id` = '.(int)$v->match->player1_id.', '.
                        '`player2_id` = '.(int)$v->match->player2_id
                    );
                }
            }
        }
        
        if ($this->data->settings['tournament-start-lol-euw'] == 1) {
            if (_cfg('env') == 'prod') {
                $answer = $this->runChallongeAPI('tournaments/pentaclick-loleuw'.$this->data->settings['lol-current-number-euw'].'/matches.json', array(), 'state=open');
            }
            else {
                $answer = $this->runChallongeAPI('tournaments/pentaclick-test1/matches.json', array(), 'state=open');
            }

            foreach($answer as $v) {
                //Checking if match is already registered
                $row = Db::fetchRow('SELECT `match_id` '.
                    'FROM `fights` '.
                    'WHERE `match_id` = '.(int)$v->match->id
                );
                if (!$row) {
                    //Registering match, if still not yet registered
                    Db::query('INSERT INTO `fights` SET '.
                        '`match_id` = '.(int)$v->match->id.', '.
                        '`player1_id` = '.(int)$v->match->player1_id.', '.
                        '`player2_id` = '.(int)$v->match->player2_id
                    );
                }
            }
        }
        
        if ($this->data->settings['tournament-start-lol-eune'] == 1) {
            if (_cfg('env') == 'prod') {
                $answer = $this->runChallongeAPI('tournaments/pentaclick-loleune'.$this->data->settings['lol-current-number-eune'].'/matches.json', array(), 'state=open');
            }
            else {
                $answer = $this->runChallongeAPI('tournaments/pentaclick-test1/matches.json', array(), 'state=open');
            }
            
            if ($answer) {
                foreach($answer as $v) {
                    //Checking if match is already registered
                    $row = Db::fetchRow('SELECT `match_id` '.
                        'FROM `fights` '.
                        'WHERE `match_id` = '.(int)$v->match->id
                    );
                    if (!$row) {
                        //Registering match, if still not yet registered
                        Db::query('INSERT INTO `fights` SET '.
                            '`match_id` = '.(int)$v->match->id.', '.
                            '`player1_id` = '.(int)$v->match->player1_id.', '.
                            '`player2_id` = '.(int)$v->match->player2_id
                        );
                    }
                }
            }
        }
        
        if ($this->data->settings['tournament-start-smite-na'] == 1) {
            if (_cfg('env') == 'prod') {
                $answer = $this->runChallongeAPI('tournaments/pentaclick-smitena'.$this->data->settings['smite-current-number-na'].'/matches.json', array(), 'state=open');
            }
            else {
                $answer = $this->runChallongeAPI('tournaments/pentaclick-test1/matches.json', array(), 'state=open');
            }
            
            if ($answer) {
                foreach($answer as $v) {
                    //Checking if match is already registered
                    $row = Db::fetchRow('SELECT `match_id` '.
                        'FROM `fights` '.
                        'WHERE `match_id` = '.(int)$v->match->id
                    );
                    if (!$row) {
                        //Registering match, if still not yet registered
                        Db::query('INSERT INTO `fights` SET '.
                            '`match_id` = '.(int)$v->match->id.', '.
                            '`player1_id` = '.(int)$v->match->player1_id.', '.
                            '`player2_id` = '.(int)$v->match->player2_id
                        );
                    }
                }
            }
        }
        
        if ($this->data->settings['tournament-start-smite-eu'] == 1) {
            if (_cfg('env') == 'prod') {
                $answer = $this->runChallongeAPI('tournaments/pentaclick-smiteeu'.$this->data->settings['smite-current-number-eu'].'/matches.json', array(), 'state=open');
            }
            else {
                $answer = $this->runChallongeAPI('tournaments/pentaclick-test1/matches.json', array(), 'state=open');
            }
            
            if ($answer) {
                foreach($answer as $v) {
                    //Checking if match is already registered
                    $row = Db::fetchRow('SELECT `match_id` '.
                        'FROM `fights` '.
                        'WHERE `match_id` = '.(int)$v->match->id
                    );
                    if (!$row) {
                        //Registering match, if still not yet registered
                        Db::query('INSERT INTO `fights` SET '.
                            '`match_id` = '.(int)$v->match->id.', '.
                            '`player1_id` = '.(int)$v->match->player1_id.', '.
                            '`player2_id` = '.(int)$v->match->player2_id
                        );
                    }
                }
            }
        }
    }
    
    public function checkDotaGames() {
    
        /*$params = array(
            'module' => 'IDOTA2Match_570/GetMatchHistory/v001',
            'get' => 'matches_requested=1&account_id=84762925',
        );
        $response = $this->runDotaAPI($params);
        foreach($response['result']['matches'] as $v) {
            $matchId = $v['match_id'];
            
            $params = array(
                'module' => 'IDOTA2Match_570/GetMatchDetails/v001',
                'get' => 'match_id='.$matchId,
            );
            $response = $this->runDotaAPI($params);
            if ($response['result']['radiant_win'] == 1) {
                dump('Match ID '.$matchId.' won by radiant');
            }
            else {
                dump('Match ID '.$matchId.' won by dire');
            }
        }
        exit();*/
        
        if ($this->data->settings['tournament-start-dota'] == 1) {
            $text = '
            Team 1: %team1%<br />
            Players 1:<br />
            %players1%<br />
            
            Team 2: %team2%<br />
            Players 2:<br />
            %players2%<br />
            
            Team won: <b>%win%</b>';
            
            //Getting all fights with status "done" = 0
            $rows = Db::fetchRows(
                'SELECT `f`.`match_id`, `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `team1`, `t1`.`cpt_player_id` AS `captain1`, `t2`.`id` AS `team2`,  `t2`.`cpt_player_id` AS `captain2`, `t1`.`name` AS `teamName1`, `t2`.`name` AS `teamName2`, `t1`.`challonge_id` AS `challongeId1`, `t2`.`challonge_id` AS `challongeId2` '.
                'FROM `fights` AS `f` '.
                'LEFT JOIN `participants` AS `t1` ON `t1`.`challonge_id` = `f`.`player1_id` '.
                'LEFT JOIN `participants` AS `t2` ON `t2`.`challonge_id` = `f`.`player2_id` '.
                'LEFT JOIN `dota_games` AS `lg` ON `lg`.`match_id` = `f`.`match_id` '.
                'WHERE `f`.`done` = 0 OR '.
                '`lg`.`ended` = 0 '
            );
            
            /*if ($rows)
            {
                foreach($rows as $v) {
                    //Gathering team data
                    $team = array(
                        $v->team1 => array('captain' => $v->captain1, 'name' => $v->teamName1, 'challonge_id' => $v->challongeId1),
                        $v->team2 => array('captain' => $v->captain2, 'name' => $v->teamName2, 'challonge_id' => $v->challongeId2),
                    );
                    
                    //Gathering players
                    $insideRows = Db::fetchRows('SELECT `participant_id`, `player_num`, `player_id`, `name` '.
                        'FROM `players` '.
                        'WHERE `participant_id` = '.(int)$v->team1.' OR `participant_id` = '.(int)$v->team2.' '.
                        'ORDER BY `player_num` ASC '
                    );
                    foreach($insideRows as $v2) {
                        $team[$v2->participant_id]['players'][$v2->player_num] = array('id' => $v2->player_id, 'name' => $v2->name);
                    }
                    
                    //Checking if match already registered
                    $row = Db::fetchRow('SELECT `id` FROM `dota_games` WHERE `match_id` = '.(int)$v->match_id.' ORDER BY `id` DESC LIMIT 1');
                    if (!$row) {
                        Db::query('INSERT INTO `dota_games` SET '.
                            '`match_id` = '.(int)$v->match_id.', '.
                            '`message` = "Fight not found, ignoring match ('.Db::escape($team[$v->team1]['name']).' VS '.Db::escape($team[$v->team2]['name']).')", '.
                            '`participant_id1` = '.(int)$v->team1.', '.
                            '`participant_id2` = '.(int)$v->team2
                        );
                        $gameDbId = Db::lastId();
                    }
                    else {
                        Db::query('UPDATE `dota_games` SET `date` = NOW() WHERE `id` = '.(int)$row->id.' LIMIT 1');
                        $gameDbId = $row->id;
                    }

                    $i = 0;
                    foreach($insideRows as $vPlayer) {
                        //Getting player recent games
                        $answer = $this->runRiotAPI('/'.$server.'/v1.3/game/by-summoner/'.$vPlayer->player_id.'/recent', $server, true);
                        $game = $answer->games[0]; //We're interested only in last game
                        
                        //Do not check ranked and solo games
                        if ($game->gameType == 'CUSTOM_GAME' && $game->gameMode == 'CLASSIC' && $game->mapId == 11 && $game->fellowPlayers) {
                            $getPlayers = array();
                            $getPlayers[] = $team[$v->team1]['captain'];
                            foreach($game->fellowPlayers as $fellowPlayers) {
                                $getPlayers[] = $fellowPlayers->summonerId;
                            }
                            
                            //If player not found in the list, we aren't interested in this match
                            if (in_array($vPlayer->player_id, $getPlayers)) {
                            
                                $playersList = array(0=>'',1=>'');
                                //Looping teams
                                for($j=0;$j<=1;++$j) {
                                    $found = 0;
                                    foreach($team[($j==0?$v->team1:$v->team2)]['players'] as $players) {
                                        if (in_array($players['id'], $getPlayers)) {
                                            $playersList[$j]['list'] .= $players['name'].' - found ('.$players['id'].')<br />';
                                            ++$found;
                                            if ($players['id'] == $vPlayer->player_id) {
                                                $playerTeam['id'] = ($j==0?$v->team1:$v->team2);
                                                $playerTeam['num'] = $j;
                                                $playerTeam['riotNum'] = $game->stats->team;
                                                $playerTeam['vsTeamId'] = ($j==1?$v->team1:$v->team2);
                                                $playerTeam['vsTeamNum'] = ($j==1?$v->team1:$v->team2);
                                            }
                                        }
                                        else {
                                            $playersList[$j]['list'] .= '<u>'.$players['name'].'</u> - <span style="color:red">player not found</span> ('.$players['id'].')<br />';
                                        }
                                    }
                                    $playersList[$j]['list'] .= '<b>Found:</b> '.$found.'<br />';
                                    $playersList[$j]['count'] = $found;
                                }
                                
                                if ($playersList[0]['count'] >= 1 && $playersList[1]['count'] >= 1) {
                                    //Deciding who's won. If 1 then team 1 won of empty then team 2 won
                                    if ($game->stats->win == 1 && $game->stats->team == $playerTeam['riotNum']) {
                                        $whoWon = $playerTeam['id'];
                                        $emailText = str_replace('%win%', $team[$playerTeam['id']]['name'], $text);
                                        $winner = $team[$playerTeam['id']]['challonge_id'];
                                        $loserId = $playerTeam['vsTeamId'];
                                        if ($playerTeam['num'] == 0) {
                                            $scores = '1-0';
                                        }
                                        else {
                                            $scores = '0-1';
                                        }
                                    }
                                    else {
                                        $whoWon = $playerTeam['vsTeamId'];
                                        $emailText = str_replace('%win%', $team[$playerTeam['vsTeamId']]['name'], $text);
                                        $winner = $team[$playerTeam['vsTeamId']]['challonge_id'];
                                        $loserId = $playerTeam['id'];
                                        if ($playerTeam['num'] == 0) {
                                            $scores = '0-1';
                                        }
                                        else {
                                            $scores = '1-0';
                                        }
                                    }
                            
                                    //Adding teams names to email text
                                    $emailText = str_replace(array('%team1%', '%team2%'), array($team[$v->team1]['name'], $team[$v->team2]['name']), $emailText);
                                    
                                    //Sending email
                                    $emailText = str_replace(array('%players1%', '%players2%'), array($playersList[0]['list'], $playersList[1]['list']), $emailText);
                                    $this->sendMail('max.orlovsky@gmail.com', 'Pentaclick Dota tournament - Result', $emailText);
                                    
                                    //Registering email, ending the game
                                    Db::query('UPDATE `dota_games` SET '.
                                        '`message` = "'.Db::escape($emailText).'", '.
                                        '`game_id` = '.(int)$game->gameId.', '.
                                        '`ended` = 1 '.
                                        'WHERE `id` = '.(int)$gameDbId
                                    );
                                    echo 1;
                                    //Updating brackets only if automatic function is enabled
                                    if (_cfg('tournament-auto-dota') == 1) {
                                        echo 2;
                                        $apiArray = array(
                                            '_method' => 'put',
                                            'match_id' => $v->match_id,
                                            'match[scores_csv]' => $scores,
                                            'match[winner_id]' => $winner,
                                        );
                                        if (_cfg('env') == 'prod') {
                                            $this->runChallongeAPI('tournaments/pentaclick-dota'.$this->data->settings['dota-current-number'.'/matches/'.$v->match_id.'.put', $apiArray);
                                        }
                                        else {
                                            $this->runChallongeAPI('tournaments/pentaclick-test1/matches/'.$v->match_id.'.put', $apiArray);
                                        }
                                        
                                        Db::query('UPDATE `participants` SET `ended` = 1 '.
                                            'WHERE `game` = "lol" AND '.
                                            '`server` = "'.$server.'" AND '.
                                            '`id` = '.(int)$loserId.' '
                                        );
                                        echo 3;
                                        Db::query('UPDATE `fights` SET `done` = 1 '.
                                            'WHERE `match_id` = '.(int)$v->match_id.' '
                                        );
                                    }
                                    
                                    $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$whoWon.'_vs_'.$loserId.'.txt';
                                    echo 4;
                                    $file = fopen($fileName, 'a');
                                    $content = '<p><span id="notice">('.date('H:i:s', time()).')</span> <b>Team '.$team[$whoWon]['name'].' won</b>';
                                    if (_cfg('tournament-auto-lol-'.$server) == 0) {
                                        $content .= ' (automatic advancement disabled, manual check required) ';
                                    }
                                    $content .= '</p>';
                                    echo 5;
                                    fwrite($file, htmlspecialchars($content));
                                    fclose($file);
                                }
                            }
                        }
                        
                        if ($i >= 5) {
                            break(1);
                        }
                        ++$i;
                    }
                }
            }*/
        }
    }
    
    public function checkSmiteGames($server) {
        if ($this->data->settings['tournament-start-smite-'.$server] == 1) {
            $this->checkSmiteGamesByServer($server);
        }
        
        return false;
    }
    
    protected function checkSmiteGamesByServer($server) {
        $text = '
        Team 1: %team1%<br />
        Players 1:<br />
        %players1%<br />
        
        Team 2: %team2%<br />
        Players 2:<br />
        %players2%<br />
        
        Team won: <b>%win%</b>';
        
        //Getting all fights with status "done" = 0
        $rows = Db::fetchRows(
            'SELECT `f`.`match_id`, `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `team1`, `t1`.`cpt_player_id` AS `captain1`, `t2`.`id` AS `team2`,  `t2`.`cpt_player_id` AS `captain2`, `t1`.`name` AS `teamName1`, `t2`.`name` AS `teamName2`, `t1`.`challonge_id` AS `challongeId1`, `t2`.`challonge_id` AS `challongeId2` '.
            'FROM `fights` AS `f` '.
            'LEFT JOIN `participants` AS `t1` ON `t1`.`challonge_id` = `f`.`player1_id` '.
            'LEFT JOIN `participants` AS `t2` ON `t2`.`challonge_id` = `f`.`player2_id` '.
            'LEFT JOIN `smite_games` AS `sg` ON `sg`.`match_id` = `f`.`match_id` '.
            'WHERE `f`.`done` = 0 OR '.
            '`sg`.`ended` = 0 '
        );
        
        if ($rows)
        {
            $params['module'] = 'createsession';
            $smiteApiData = $this->runSmiteAPI($params);
            
            foreach($rows as $v) {
                //Gathering team data
                $team = array(
                    $v->team1 => array('captain' => $v->captain1, 'name' => $v->teamName1, 'challonge_id' => $v->challongeId1),
                    $v->team2 => array('captain' => $v->captain2, 'name' => $v->teamName2, 'challonge_id' => $v->challongeId2),
                );
                
                //Gathering players
                $insideRows = Db::fetchRows('SELECT `participant_id`, `player_num`, `player_id`, `name` '.
                    'FROM `players` '.
                    'WHERE `participant_id` = '.(int)$v->team1.' OR `participant_id` = '.(int)$v->team2.' '.
                    'ORDER BY `player_num` ASC '
                );
                
                foreach($insideRows as $v2) {
                    $team[$v2->participant_id]['players'][$v2->player_num] = array('id' => $v2->player_id, 'name' => $v2->name);
                }
                
                //Checking if match already registered
                $row = Db::fetchRow('SELECT `id` FROM `smite_games` WHERE `match_id` = '.(int)$v->match_id.' ORDER BY `id` DESC LIMIT 1');
                if (!$row) {
                    Db::query('INSERT INTO `smite_games` SET '.
                        '`match_id` = '.(int)$v->match_id.', '.
                        '`message` = "Fight not found, ignoring match ('.Db::escape($team[$v->team1]['name']).' VS '.Db::escape($team[$v->team2]['name']).')", '.
                        '`participant_id1` = '.(int)$v->team1.', '.
                        '`participant_id2` = '.(int)$v->team2
                    );
                    $gameDbId = Db::lastId();
                }
                else {
                    Db::query('UPDATE `smite_games` SET `date` = NOW() WHERE `id` = '.(int)$row->id.' LIMIT 1');
                    $gameDbId = $row->id;
                }

                $i = 0;
                foreach($insideRows as $vPlayer) {
                    $vPlayer->name = preg_replace('/\[.*?\]/','',$vPlayer->name);
                    //Getting player recent games
                    $params = array(
                        'module'    => 'getmatchhistory',
                        'command'   => $vPlayer->name,
                        'session'   => $smiteApiData['session_id'],
                    );
                    $answer = $this->runSmiteAPI($params);
                    $game = $answer[0];
                    
                    //Do not other games, only custom conquest
                    if ($game['Queue'] == 'Custom: Conquest') {
                        $params = array(
                            'module'    => 'getmatchdetails',
                            'command'   => $game['Match'],
                            'session'   => $smiteApiData['session_id'],
                        );
                        $match = $this->runSmiteAPI($params);

                        $getPlayers = array();
                        $matchStatus = array();
                        $getPlayers[] = $team[$v->team1]['captain'];
                        foreach($match as $matchPlayers) {
                            if ($team[$v->team1]['captain'] != $matchPlayers['playerId']) {
                                $getPlayers[] = $matchPlayers['playerId'];
                            }
                            
                            $matchStatus[$matchPlayers['playerId']] = $matchPlayers['Win_Status'];
                        }
                        
                        //If player not found in the list, we aren't interested in this match
                        if (in_array($vPlayer->player_id, $getPlayers)) {
                            
                            $playersList = array(0=>'',1=>'');
                            //Looping teams
                            for($j=0;$j<=1;++$j) {
                                $found = 0;
                                foreach($team[($j==0?$v->team1:$v->team2)]['players'] as $players) {
                                    if (in_array($players['id'], $getPlayers)) {
                                        $playersList[$j]['list'] .= $players['name'].' - found ('.$players['id'].')<br />';
                                        ++$found;
                                        if ($players['id'] == $vPlayer->player_id) {
                                            $playerTeam['id'] = ($j==0?$v->team1:$v->team2);
                                            $playerTeam['num'] = $j;
                                            $playerTeam['vsTeamId'] = ($j==1?$v->team1:$v->team2);
                                        }
                                    }
                                    else {
                                        $playersList[$j]['list'] .= '<u>'.$players['name'].'</u> - <span style="color:red">player not found</span> ('.$players['id'].')<br />';
                                    }
                                }
                                $playersList[$j]['list'] .= '<b>Found:</b> '.$found.'<br />';
                                $playersList[$j]['count'] = $found;
                            }
                            
                            $playersList[1]['count'] = 1;
                            
                            if ($playersList[0]['count'] >= 1 && $playersList[1]['count'] >= 1) {
                                //Deciding who's won. If 1 then team 1 won of empty then team 2 won
                                if ($matchStatus[$vPlayer->player_id] == 'Winner') {
                                    $whoWon = $playerTeam['id'];
                                    $emailText = str_replace('%win%', $team[$playerTeam['id']]['name'], $text);
                                    $winner = $team[$playerTeam['id']]['challonge_id'];
                                    $loserId = $playerTeam['vsTeamId'];
                                    if ($playerTeam['num'] == 0) {
                                        $scores = '1-0';
                                    }
                                    else {
                                        $scores = '0-1';
                                    }
                                }
                                else {
                                    $whoWon = $playerTeam['vsTeamId'];
                                    $emailText = str_replace('%win%', $team[$playerTeam['vsTeamId']]['name'], $text);
                                    $winner = $team[$playerTeam['vsTeamId']]['challonge_id'];
                                    $loserId = $playerTeam['id'];
                                    if ($playerTeam['num'] == 0) {
                                        $scores = '0-1';
                                    }
                                    else {
                                        $scores = '1-0';
                                    }
                                }
                                
                                //Adding teams names and players names to email text
                                $emailText = str_replace(
                                    array('%team1%', '%team2%', '%players1%', '%players2%'),
                                    array($team[$v->team1]['name'], $team[$v->team2]['name'], $playersList[0]['list'], $playersList[1]['list']),
                                    $emailText
                                );
                                
                                //Sending email only if automatic function is disabled
                                if ($this->data->settings['tournament-auto-smite-'.$server] != 1) {
                                    $this->sendMail('max.orlovsky@gmail.com', 'Pentaclick Smite tournament - Result', $emailText);
                                }
                                
                                //Registering email, ending the game
                                Db::query('UPDATE `smite_games` SET '.
                                    '`message` = "'.Db::escape($emailText).'", '.
                                    '`game_id` = '.(int)$game->gameId.', '.
                                    '`ended` = 1 '.
                                    'WHERE `id` = '.(int)$gameDbId
                                );
                                
                                //Updating brackets only if automatic function is enabled
                                if ($this->data->settings['tournament-auto-smite-'.$server] == 1) {
                                    $apiArray = array(
                                        '_method' => 'put',
                                        'match_id' => $v->match_id,
                                        'match[scores_csv]' => $scores,
                                        'match[winner_id]' => $winner,
                                    );
                                    if (_cfg('env') == 'prod') {
                                        $this->runChallongeAPI('tournaments/pentaclick-smite'.$server.$this->data->settings['smite-current-number-'.$server].'/matches/'.$v->match_id.'.put', $apiArray);
                                    }
                                    else {
                                        $this->runChallongeAPI('tournaments/pentaclick-test1/matches/'.$v->match_id.'.put', $apiArray);
                                    }
                                    
                                    /*Db::query('UPDATE `participants` SET `ended` = 1 '.
                                        'WHERE `game` = "smite" AND '.
                                        '`server` = "'.$server.'" AND '.
                                        '`id` = '.(int)$loserId.' '
                                    );*/
                                    
                                    Db::query('UPDATE `fights` SET `done` = 1 '.
                                        'WHERE `match_id` = '.(int)$v->match_id.' '
                                    );
                                }
                                
                                $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$whoWon.'_vs_'.$loserId.'.txt';
                                
                                $file = fopen($fileName, 'a');
                                $content = '<p><span id="notice">('.date('H:i:s', time()).')</span> <b>Team '.$team[$whoWon]['name'].' won</b>';
                                if ($this->data->settings['tournament-auto-smite-'.$server] == 0) {
                                    $content .= ' (automatic advancement disabled, manual check required) ';
                                }
                                $content .= '</p>';
                                
                                fwrite($file, htmlspecialchars($content));
                                fclose($file);
                            }
                        }
                    }
                    
                    if ($i >= 5) {
                        break(1);
                    }
                    ++$i;
                }
            }
        }
    }
    
    public function checkLolGames($server) {
        if ($this->data->settings['tournament-start-lol-'.$server] == 1) {
            $this->checkLolGamesByServer($server);
        }
        
        return false;
    }
    
    protected function checkLolGamesByServer($server) {
        $text = '
        Team 1: %team1%<br />
        Players 1:<br />
        %players1%<br />
        
        Team 2: %team2%<br />
        Players 2:<br />
        %players2%<br />
        
        Team won: <b>%win%</b>';
        
        //Getting all fights with status "done" = 0
        $rows = Db::fetchRows(
            'SELECT `f`.`match_id`, `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `team1`, `t1`.`cpt_player_id` AS `captain1`, `t2`.`id` AS `team2`,  `t2`.`cpt_player_id` AS `captain2`, `t1`.`name` AS `teamName1`, `t2`.`name` AS `teamName2`, `t1`.`challonge_id` AS `challongeId1`, `t2`.`challonge_id` AS `challongeId2` '.
            'FROM `fights` AS `f` '.
            'LEFT JOIN `participants` AS `t1` ON `t1`.`challonge_id` = `f`.`player1_id` '.
            'LEFT JOIN `participants` AS `t2` ON `t2`.`challonge_id` = `f`.`player2_id` '.
            'LEFT JOIN `lol_games` AS `lg` ON `lg`.`match_id` = `f`.`match_id` '.
            'WHERE `f`.`done` = 0 OR '.
            '`lg`.`ended` = 0 '
        );
        
        if ($rows)
        {
            foreach($rows as $v) {
                //Gathering team data
                $team = array(
                    $v->team1 => array('captain' => $v->captain1, 'name' => $v->teamName1, 'challonge_id' => $v->challongeId1),
                    $v->team2 => array('captain' => $v->captain2, 'name' => $v->teamName2, 'challonge_id' => $v->challongeId2),
                );
                
                //Gathering players
                $insideRows = Db::fetchRows('SELECT `participant_id`, `player_num`, `player_id`, `name` '.
                    'FROM `players` '.
                    'WHERE `participant_id` = '.(int)$v->team1.' OR `participant_id` = '.(int)$v->team2.' '.
                    'ORDER BY `player_num` ASC '
                );
                foreach($insideRows as $v2) {
                    $team[$v2->participant_id]['players'][$v2->player_num] = array('id' => $v2->player_id, 'name' => $v2->name);
                }
                
                //Checking if match already registered
                $row = Db::fetchRow('SELECT `id` FROM `lol_games` WHERE `match_id` = '.(int)$v->match_id.' ORDER BY `id` DESC LIMIT 1');
                if (!$row) {
                    Db::query('INSERT INTO `lol_games` SET '.
                        '`match_id` = '.(int)$v->match_id.', '.
                        '`message` = "Fight not found, ignoring match ('.Db::escape($team[$v->team1]['name']).' VS '.Db::escape($team[$v->team2]['name']).')", '.
                        '`participant_id1` = '.(int)$v->team1.', '.
                        '`participant_id2` = '.(int)$v->team2
                    );
                    $gameDbId = Db::lastId();
                }
                else {
                    Db::query('UPDATE `lol_games` SET `date` = NOW() WHERE `id` = '.(int)$row->id.' LIMIT 1');
                    $gameDbId = $row->id;
                }

                $i = 0;
                foreach($insideRows as $vPlayer) {
                    //Getting player recent games
                    $answer = $this->runRiotAPI('/'.$server.'/v1.3/game/by-summoner/'.$vPlayer->player_id.'/recent', $server, true);
                    $game = $answer->games[0]; //We're interested only in last game
                    
                    //Do not check ranked and solo games
                    if ($game->gameType == 'CUSTOM_GAME' && $game->gameMode == 'CLASSIC' && $game->mapId == 11 && $game->fellowPlayers) {
                        $getPlayers = array();
                        $getPlayers[] = $team[$v->team1]['captain'];
                        foreach($game->fellowPlayers as $fellowPlayers) {
                            $getPlayers[] = $fellowPlayers->summonerId;
                        }
                        
                        //If player not found in the list, we aren't interested in this match
                        if (in_array($vPlayer->player_id, $getPlayers)) {
                        
                            $playersList = array(0=>'',1=>'');
                            //Looping teams
                            for($j=0;$j<=1;++$j) {
                                $found = 0;
                                foreach($team[($j==0?$v->team1:$v->team2)]['players'] as $players) {
                                    if (in_array($players['id'], $getPlayers)) {
                                        $playersList[$j]['list'] .= $players['name'].' - found ('.$players['id'].')<br />';
                                        ++$found;
                                        if ($players['id'] == $vPlayer->player_id) {
                                            $playerTeam['id'] = ($j==0?$v->team1:$v->team2);
                                            $playerTeam['num'] = $j;
                                            $playerTeam['riotNum'] = $game->stats->team;
                                            $playerTeam['vsTeamId'] = ($j==1?$v->team1:$v->team2);
                                            $playerTeam['vsTeamNum'] = ($j==1?$v->team1:$v->team2);
                                        }
                                    }
                                    else {
                                        $playersList[$j]['list'] .= '<u>'.$players['name'].'</u> - <span style="color:red">player not found</span> ('.$players['id'].')<br />';
                                    }
                                }
                                $playersList[$j]['list'] .= '<b>Found:</b> '.$found.'<br />';
                                $playersList[$j]['count'] = $found;
                            }
                            
                            if ($playersList[0]['count'] >= 1 && $playersList[1]['count'] >= 1) {
                                //Deciding who's won. If 1 then team 1 won of empty then team 2 won
                                if ($game->stats->win == 1 && $game->stats->team == $playerTeam['riotNum']) {
                                    $whoWon = $playerTeam['id'];
                                    $emailText = str_replace('%win%', $team[$playerTeam['id']]['name'], $text);
                                    $winner = $team[$playerTeam['id']]['challonge_id'];
                                    $loserId = $playerTeam['vsTeamId'];
                                    if ($playerTeam['num'] == 0) {
                                        $scores = '1-0';
                                    }
                                    else {
                                        $scores = '0-1';
                                    }
                                }
                                else {
                                    $whoWon = $playerTeam['vsTeamId'];
                                    $emailText = str_replace('%win%', $team[$playerTeam['vsTeamId']]['name'], $text);
                                    $winner = $team[$playerTeam['vsTeamId']]['challonge_id'];
                                    $loserId = $playerTeam['id'];
                                    if ($playerTeam['num'] == 0) {
                                        $scores = '0-1';
                                    }
                                    else {
                                        $scores = '1-0';
                                    }
                                }
                        
                                //Adding teams names to email text
                                $emailText = str_replace(array('%team1%', '%team2%'), array($team[$v->team1]['name'], $team[$v->team2]['name']), $emailText);
                                //Adding player lists to email text
                                $emailText = str_replace(array('%players1%', '%players2%'), array($playersList[0]['list'], $playersList[1]['list']), $emailText);
                                
                                //Sending email only if automatic function is disabled
                                if ($this->data->settings['tournament-auto-lol-'.$server] != 1) {
                                    $this->sendMail('max.orlovsky@gmail.com', 'Pentaclick LoL tournament - Result', $emailText);
                                }
                                
                                //Registering email, ending the game
                                Db::query('UPDATE `lol_games` SET '.
                                    '`message` = "'.Db::escape($emailText).'", '.
                                    '`game_id` = '.(int)$game->gameId.', '.
                                    '`ended` = 1 '.
                                    'WHERE `id` = '.(int)$gameDbId
                                );
                                
                                //Updating brackets only if automatic function is enabled
                                if ($this->data->settings['tournament-auto-lol-'.$server] == 1) {
                                    $apiArray = array(
                                        '_method' => 'put',
                                        'match_id' => $v->match_id,
                                        'match[scores_csv]' => $scores,
                                        'match[winner_id]' => $winner,
                                    );
                                    if (_cfg('env') == 'prod') {
                                        $this->runChallongeAPI('tournaments/pentaclick-lol'.$server.$this->data->settings['lol-current-number-'.$server].'/matches/'.$v->match_id.'.put', $apiArray);
                                    }
                                    else {
                                        $this->runChallongeAPI('tournaments/pentaclick-test1/matches/'.$v->match_id.'.put', $apiArray);
                                    }
                                    
                                    /*Db::query('UPDATE `participants` SET `ended` = 1 '.
                                        'WHERE `game` = "lol" AND '.
                                        '`server` = "'.$server.'" AND '.
                                        '`id` = '.(int)$loserId.' '
                                    );*/
                                    
                                    Db::query('UPDATE `fights` SET `done` = 1 '.
                                        'WHERE `match_id` = '.(int)$v->match_id.' '
                                    );
                                }
                                
                                $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$whoWon.'_vs_'.$loserId.'.txt';
                                
                                $file = fopen($fileName, 'a');

                                $content = '<div class="manager">';
                                $content .= '<div class="message">';
                                $content .= 'Team <b>'.$team[$whoWon]['name'].'</b> won';
                                if ($this->data->settings['tournament-auto-lol-'.$server] == 0) {
                                    $content .= ' (automatic advancement disabled, manual check required) ';
                                }
                                $content .= '</div>';
                                $content .= '<span>System message</span>';
                                $content .= '&nbsp;•&nbsp;<span id="notice">'.date('H:i', time()).'</span>';
                                $content .= '</div>';
                                
                                fwrite($file, htmlspecialchars($content));
                                fclose($file);
                            }
                        }
                    }
                    
                    if ($i >= 7) {
                        break(1);
                    }
                    ++$i;
                }
            }
        }
    }
    
    public function tournamentsOpenReg() {
        $rows = Db::fetchRows('SELECT * '.
            'FROM `tournaments` '.
            'WHERE `status` = "Start" AND '.
            '`reg_activated` = 0 '
        );
        
        if ($rows) {
            foreach($rows as $v) {
                if (strtotime($v->dates_registration.' '.$v->time) <= time()) {
                    //Opening up registration!
                    Db::query('UPDATE `tm_settings` SET '.
                        '`value` = 1 '.
                        'WHERE '.
                        '`setting` = "tournament-reg-'.$v->game.($v->server?'-'.Db::escape($v->server):null).'"'
                    );
                    Db::query('UPDATE `tournaments` SET '.
                        '`reg_activated` = 1 '.
                        'WHERE '.
                        '`id` = '.(int)$v->id
                    );
                }
            }
        }
        
        return true;
    }
    
    public function finalizeTournament() {
        $row = Db::fetchRow('SELECT `id`, `server`, `name`, `game` '.
            'FROM `tournaments` '.
            'WHERE `status` = "Ended" AND '.
            '`finalized` = 0 '.
            'LIMIT 1 '
        );
        
        if ($row) {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-'.$row->game.$row->server.$row->name.'/participants.json');
            if ($answer) {
                foreach($answer as $v) {
                    //Settings places for participants
                    Db::query('UPDATE `participants` SET '.
                        '`place` = '.(int)$v->participant->final_rank.', '.
                        '`seed_number` = '.(int)$v->participant->seed.' '.
                        'WHERE `challonge_id` = '.(int)$v->participant->id.' AND '.
                        '`game` = "'.$row->game.'" AND '.
                        '`approved` = 1 AND '.
                        '`checked_in` = 1 AND '.
                        '`server` = "'.$row->server.'" AND '.
                        '`deleted` = 0 AND '.
                        '`tournament_id` = "'.$row->name.'" '
                    );
                }
            }

            //Removing event stream
            Db::query(
                'DELETE FROM `streams_events` '.
                'WHERE `tournament_id` = "'.Db::escape($row->name).'" AND '.
                '`game` = "'.Db::escape($row->game).'" '
            );
            
            //Removing tournament start
            Db::query(
                'UPDATE `tm_settings` SET '.
                '`value` = 0 '.
                'WHERE `setting` = "tournament-start-'.$row->game.'-'.$row->server.'" '
            );

            //Ending participants
            Db::query(
                'UPDATE `participants` SET '.
                '`ended` = 1 '.
                'WHERE `game` = "'.$row->game.'" AND '.
                '`server` = "'.$row->server.'" AND '.
                '`tournament_id` = "'.$row->name.'" '
            );

            //Registering that tournament is finalized
            Db::query(
                'UPDATE `tournaments` SET '.
                '`finalized` = 1 '.
                'WHERE `id` = '.(int)$row->id
            );
        }
        
        return true;
    }
    
    public function sendNotifications() {
        $rows = Db::fetchRows('SELECT `game`, `server`, `name`, `dates_start`, `time` '.
            'FROM `tournaments` '.
            'WHERE `status` = "Start" '
        );
        
        if ($rows) {
            foreach($rows as $v) {
                $row = Db::fetchRow('SELECT * '.
                    'FROM `notifications` '.
                    'WHERE `game` = "'.Db::escape($v->game.$v->server).'" '.
                    'AND `tournament_name` = "'.Db::escape($v->name).'" '
                    //'AND `delivered` != 1 '
                );
                
                $time = array();
                $time['0'] = strtotime($v->dates_start.' '.$v->time);
                $time['24'] = $time['0'] - 86400;
                $time['1'] = $time['0'] - 3600;
                
                if (!$row && $time['24'] <= time()) {
                    $v->template = 0;
                    $this->sendReminders($v);
                }
                else if ($row && $row->delivered == 24 && $time['1'] <= time()) {
                    $v->template = 1;
                    $v->data = $row;
                    $this->checkInProcess($v);
                    $this->sendReminders($v);
                }
                else if ($row && $row->delivered == 1 && $time['0'] <= time()) {
                    $v->data = $row;
                    $this->startUpTournament($v);
                }
            }
        }
    }
    
    protected function sendReminders($tournament) {
        set_time_limit(600);
        
        $rows = Db::fetchRows('SELECT `name`, `server`, `email`, `id`, `link`, `server`, `game` '.
            'FROM `participants` '.
            'WHERE `game` = "'.Db::escape($tournament->game).'" AND '.
            ($tournament->server?'`server` = "'.Db::escape($tournament->server).'" AND ':null).
            '`tournament_id` = '.(int)$tournament->name.' AND '.
            '`approved` = 1 AND '.
            '`deleted` = 0 AND '.
            '`ended` = 0'
        );
        
        if ($tournament->template == 1) {
            $text = Template::getMailTemplate($tournament->game.'-reminder-1');
        }
        else {
            $text = Template::getMailTemplate($tournament->game.'-reminder-24');
        }

        if ($rows) {
            $i = 0;
            foreach($rows as $v) {
                if ($v->game == 'lol') {
                    $url = _cfg('site').'/en/leagueoflegends/'.$v->server;
                }
                else if ($v->game == 'smite') {
                    $url = _cfg('site').'/en/smite/'.$v->server;
                }
                else if ($v->game == 'hs') {
                    $url = _cfg('site').'/en/hearthstone/'.$v->server;
                }
                else {
                    return 'Game not found';
                }
                
                $body = str_replace(
                    array(
                        '%url%',
                        '%code%',
                        '%teamId%',
                        '%name%',
                    ),
                    array(
                        $url,
                        $v->link,
                        $v->id,
                        $v->name,
                    ),
                    $text
                );

                $this->sendMail($v->email, 'Pentaclick tournament reminder', $body);
                
                ++$i;
                if ($i >= 3) {
                    sleep(1);
                    $i = 0;
                }
            }
        }
        
        if ($tournament->template == 1 && $tournament->data) {
            Db::query('UPDATE `notifications` SET '.
                '`delivered` = 1 '.
                'WHERE `id` = '.(int)$tournament->data->id
            );
        }
        else {
            Db::query('INSERT INTO `notifications` SET '.
                '`game` = "'.Db::escape($tournament->game.$tournament->server).'", '.
                '`tournament_name` = "'.Db::escape($tournament->name).'", '.
                '`delivered` = 24'
            );
        }
    }
    
    private function checkInProcess($tournament) {
        //Enabling check in!
        Db::query('UPDATE `tm_settings` SET '.
            '`value` = 1 '.
            'WHERE '.
            '`setting` = "tournament-checkin-'.$tournament->game.($tournament->server?'-'.Db::escape($tournament->server):null).'"'
        );
        
        return true;
    }
    
    private function startUpTournament($tournament) {
        //Closing up registration!
        Db::query('UPDATE `tm_settings` SET '.
            '`value` = 0 '.
            'WHERE '.
            '`setting` = "tournament-reg-'.$tournament->game.($tournament->server?'-'.Db::escape($tournament->server):null).'"'
        );
        
        //Disabling check in!
        Db::query('UPDATE `tm_settings` SET '.
            '`value` = 0 '.
            'WHERE '.
            '`setting` = "tournament-checkin-'.$tournament->game.($tournament->server?'-'.Db::escape($tournament->server):null).'"'
        );
        
        //Starting up tournament
        Db::query('UPDATE `tm_settings` SET '.
            '`value` = 1 '.
            'WHERE '.
            '`setting` = "tournament-start-'.$tournament->game.($tournament->server?'-'.Db::escape($tournament->server):null).'"'
        );
        
        $challongeTournament = $tournament->game.$tournament->server.$this->data->settings[$tournament->game.'-current-number-'.$tournament->server];
        
        
        //Reshuffle tournament
        if (_cfg('env') == 'prod') {
            $apiArray = array(
                'participant_id' => 0, //reshuffle all
                'tournament'     => 'pentaclick-'.$challongeTournament,
            );
        
            $this->runChallongeAPI('tournaments/pentaclick-'.$challongeTournament.'/participants/randomize.post', $apiArray);
        }
        else {
            $apiArray = array(
                'participant_id' => 0, //reshuffle all
                'tournament'     => 'pentaclick-test1',
            );
            
            $this->runChallongeAPI('tournaments/pentaclick-test1/participants/randomize.post', $apiArray);
        }
        
        //Starting up tournament on challonge.com
        if (_cfg('env') == 'prod') {
            $apiArray = array(
                'tournament'     => 'pentaclick-'.$challongeTournament,
            );
            
            $this->runChallongeAPI('tournaments/pentaclick-'.$challongeTournament.'/start.post', $apiArray);
        }
        else {
            $apiArray = array(
                'tournament'     => 'pentaclick-test1',
            );
            
            $this->runChallongeAPI('tournaments/pentaclick-test1/start.post', $apiArray);
        }

        //Cleaning up not checked in participants. So they would see "check in" button.
        Db::query(
            'UPDATE `participants` SET '.
            '`ended` = 1 '.
            'WHERE `game` = "'.$tournament->game.'" AND '.
            '`server` = "'.$tournament->server.'" AND '.
            '`tournament_id` = "'.$this->data->settings[$tournament->game.'-current-number-'.$tournament->server].'" AND '.
            '`approved` = 1 AND '.
            '`deleted` = 0 AND '.
            '`checked_in` = 0 '
        );
        
        Db::query('UPDATE `notifications` SET '.
            '`delivered` = 0 '.
            'WHERE `id` = '.(int)$tournament->data->id
        );
        
        return true;
    }
}