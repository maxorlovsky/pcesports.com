<?php

class Cron extends System {
    public function __construct() {
        parent::__construct();
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
    
    public function updateStreamers() {
        $rows = Db::fetchRows('SELECT * FROM `streams`');
        
        foreach($rows as $v) {
            $apiUrl = 'https://api.twitch.tv/kraken/streams/'.strtolower(htmlspecialchars($v->name, ENT_QUOTES)).'?client_id='._cfg('social')['tc']['id'];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl); // set url to post to
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
            curl_setopt($ch, CURLOPT_TIMEOUT, 3); // times out after 2s
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, 0); // set POST method
            $response = curl_exec($ch); // run the whole process 
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $json_array = json_decode($response, true);
            
            //$onlineStreams = array();
            if ($json_array['stream'] != NULL) {
                //$onlineStreams[] = (int)$v->id;
                
                Db::query(
                    'UPDATE `streams` '.
                    'SET `online` = '.time().', '.
                    '`viewers` = '.(int)$json_array['stream']['viewers'].', '.
                    '`display_name` = "'.Db::escape($json_array['stream']['channel']['display_name']).'" '.
                    'WHERE `id` = '.(int)$v->id
                );
                //$channelTitle = $json_array['stream']['channel']['display_name'];
                //$streamTitle = $json_array['stream']['channel']['status'];
                //$currentGame = $json_array['stream']['channel']['game'];
            }
        }
        
        /*if ($onlineStreams) {
            $ids = '`id` = ';
            $ids .= implode(' OR `id` = ', $onlineStreams);
            Db::query(
                'UPDATE `streams` '.
                'SET `online` = '.time().' '.
                'WHERE '.$ids
            );
        }*/
    }
    
    public function updateChallongeMatches() {
        if ($this->data->settings['tournament-start-hs'] == 1) {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-hs'.$this->data->settings['hs-current-number'].'/matches.json', array(), 'state=open');

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
    
    public function checkLolGames() {
        $text = '
        Team 1: %team1%<br />
        Players 1:<br />
        %players1%<br />
        
        Team 2: %team2%<br />
        Players 2:<br />
        %players2%<br />
        
        Team won: <b>%win%</b>';
    
        if ($this->data->settings['tournament-start-lol-euw'] == 1 || $this->data->settings['tournament-start-lol-eune'] == 1) {
            if ($this->data->settings['tournament-start-lol-euw'] == 1) {
                $server = 'euw';
            }
            else if ($this->data->settings['tournament-start-lol-eune'] == 1) {
                $server = 'eune';
            }
            else {
                //Can not go!
                return false;
            }
            
            //Getting all fights with status "done" = 0
            $rows = Db::fetchRows('SELECT `f`.`match_id`, `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `team1`, `t1`.`cpt_player_id` AS `captain1`, `t2`.`id` AS `team2`,  `t2`.`cpt_player_id` AS `captain2`, `t1`.`name` AS `teamName1`, `t2`.`name` AS `teamName2`, `t1`.`challonge_id` AS `challongeId1`, `t2`.`challonge_id` AS `challongeId2` '.
                'FROM `fights` AS `f` '.
                'LEFT JOIN `teams` AS `t1` ON `t1`.`challonge_id` = `f`.`player1_id` '.
                'LEFT JOIN `teams` AS `t2` ON `t2`.`challonge_id` = `f`.`player2_id` '.
                'WHERE `f`.`done` = 0 '
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
                    $insideRows = Db::fetchRows('SELECT `team_id`, `player_num`, `player_id`, `name` '.
                        'FROM `players` '.
                        'WHERE `team_id` = '.(int)$v->team1.' OR `team_id` = '.(int)$v->team2.' '.
                        'ORDER BY `player_num` ASC '
                    );
                    foreach($insideRows as $v2) {
                        $team[$v2->team_id]['players'][$v2->player_num] = array('id' => $v2->player_id, 'name' => $v2->name);
                    }
                    
                    //Getting team captain#1 recent games
                    $answer = $this->runAPI('/'.$server.'/v1.3/game/by-summoner/'.$team[$v->team1]['captain'].'/recent', $server, true);
                    $game = $answer->games[0]; //We're interested only in last game
                    
                    Db::query('INSERT INTO `lol_games` SET '.
                        '`match_id` = '.(int)$v->match_id.', '.
                        '`game_id` = '.(int)$game->gameId.', '.
                        '`teamId1` = '.(int)$v->team1.', '.
                        '`teamId2` = '.(int)$v->team2
                    );
                    $gameDbId = Db::lastId();
                    
                    //Do not check ranked and solo games
                    if ($game->gameType == 'CUSTOM_GAME' && $game->gameMode == 'CLASSIC' && $game->mapId == 1 && $game->fellowPlayers) {
                        $getPlayers = array();
                        $getPlayers[] = $team[$v->team1]['captain'];
                        foreach($game->fellowPlayers as $fellowPlayers) {
                            $getPlayers[] = $fellowPlayers->summonerId;
                        }
                        
                        //If enemy team captain not found, we don't interested in this match
                        if (in_array($team[$v->team2]['captain'], $getPlayers)) {
                            //Deciding who's won. If 1 then team 1 won of empty then team 2 won
                            if ($game->stats->win == 1) {
                                $whoWon = $v->team1; //Won team 1
                                $emailText = str_replace('%win%', $team[$v->team1]['name'], $text);
                                $winner = $team[$v->team1]['challonge_id'];
                                $scores = '1-0';
                                $loserId = $v->team2;
                            }
                            else {
                                $whoWon = $v->team2; //Won team 2
                                $emailText = str_replace('%win%', $team[$v->team2]['name'], $text);
                                $winner = $team[$v->team2]['challonge_id'];
                                $scores = '0-1';
                                $loserId = $v->team1;
                            }
                            
                            //Adding teams names to email text
                            $emailText = str_replace(array('%team1%', '%team2%'), array($team[$v->team1]['name'], $team[$v->team2]['name']), $emailText);
                            
                            $playersList = array(0=>'',1=>'');
                            //Looping team 1
                            foreach($team[$v->team1]['players'] as $players) {
                                if (in_array($players['id'], $getPlayers)) {
                                    $playersList[0] .= $players['name'].' - found ('.$players['id'].')<br />';
                                }
                                else {
                                    $playersList[0] .= '<u>'.$players['name'].'</u> - <span style="color:red">player not found</span> ('.$players['id'].')<br />';
                                }
                            }
                            
                            //Looping team 2
                            foreach($team[$v->team2]['players'] as $players) {
                                if (in_array($players['id'], $getPlayers)) {
                                    $playersList[1] .= $players['name'].' - found ('.$players['id'].')<br />';
                                }
                                else {
                                    $playersList[1] .= '<u>'.$players['name'].'</u> - <span style="color:red">player not found</span> ('.$players['id'].')<br />';
                                }
                            }
                            
                            //Sending email
                            $emailText = str_replace(array('%players1%', '%players2%'), array($playersList[0], $playersList[1]), $emailText);
                            $this->sendMail('max.orlovsky@gmail.com', 'Pentaclick LoL tournament - Result', $emailText);
                            
                            //Registering email
                            Db::query('UPDATE `lol_games` SET '.
                                '`message` = "'.Db::escape($emailText).'" '.
                                'WHERE `id` = '.(int)$gameDbId
                            );
                            
                            //Updating brackets
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
                            
                            Db::query('UPDATE `teams` SET `ended` = 1 '.
                                'WHERE `game` = "lol" AND '.
                                '`server` = "'.$server.'" AND '.
                                '`id` = '.(int)$loserId.' '
                            );
                            
                            Db::query('UPDATE `fights` SET `done` = 1 '.
                                'WHERE `match_id` = '.(int)$v->match_id.' '
                            );
                            
                            $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$whoWon.'_vs_'.$loserId.'.txt';
                                
                            $file = fopen($fileName, 'a');
                            $content = '<p><span id="notice">('.date('H:i:s', time()).')</span> <b>Automatic message. Team '.$team[$whoWon]['name'].' won</b></p>';
                            fwrite($file, htmlspecialchars($content));
                            fclose($file);
                        }
                        else {
                            Db::query('UPDATE `lol_games` SET '.
                                '`message` = "Enemy captain not found, ignoring match ['.(int)$v->match_id.'] ('.Db::escape($team[$v->team1]['name']).' VS '.Db::escape($team[$v->team2]['name']).')" '.
                                'WHERE `id` = '.(int)$gameDbId
                            );
                        }
                    }
                    else {
                        Db::query('UPDATE `lol_games` SET '.
                            '`message` = "Fight not found ['.(int)$v->match_id.'] ('.Db::escape($team[$v->team1]['name']).' VS '.Db::escape($team[$v->team2]['name']).')" '.
                            'WHERE `id` = '.(int)$gameDbId
                        );
                    }
                }
            }
        }
    }
    
    public function sendNotifications() {
        $rows = Db::fetchRows('SELECT `game`, `server`, `name`, `dates`, `time` '.
            'FROM `tournaments` '.
            'WHERE `status` = "Start" '
        );
        
        if ($rows) {
            foreach($rows as $v) {
                $row = Db::fetchRow('SELECT * '.
                    'FROM `notifications` '.
                    'WHERE `game` = "'.Db::escape($v->game).'" '.
                    'AND `tournament_name` = "'.Db::escape($v->name).'" '
                    //'AND `delivered` != 1 '
                );
                
                $time = array();
                $time['24'] = strtotime($v->dates.' '.$v->time) - 86400;
                $time['1'] = strtotime($v->dates.' '.$v->time) - 3600;
                if (!$row && $time['24'] <= time()) {
                    $v->template = 0;
                    $this->sendReminders($v);
                }
                else if ($row && $row->delivered == 24 && $time['1'] <= time()) {
                    $v->template = 1;
                    $v->data = $row;
                    $this->sendReminders($v);
                }
            }
        }
    }
    
    protected function sendReminders($tournament) {
        set_time_limit(600);
        
        $rows = Db::fetchRows('SELECT `name`, `server`, `email`, `id`, `link`, `server`, `game` '.
            'FROM `teams` '.
            'WHERE `game` = "'.Db::escape($tournament->game).'" AND '.
            ($tournament->server?'`server` = "'.Db::escape($tournament->server).'" AND ':null).
            '`tournament_id` = '.(int)$tournament->name.' AND '.
            '`approved` = 1 AND '.
            '`deleted` = 0 AND '.
            '`ended` = 0'
        );
        
        if ($tournament->template == 1) {
            $text = Template::getMailTemplate('reminder-1');
        }
        else {
            $text = Template::getMailTemplate('reminder-24');
        }
        
        if ($rows) {
            $i = 0;
            foreach($rows as $v) {
                if ($v->game == 'lol') {
                    $url = _cfg('site').'/en/leagueoflegends/'.$v->server;
                }
                else {
                    $url = _cfg('site').'/en/hearthstone';
                }
                
                $message = str_replace(
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
                
                $this->sendMail($v->email, 'Pentaclick tournament reminder', $message);
                
                ++$i;
                if ($i >= 3) {
                    sleep(1);
                    $i = 0;
                }
            }
            
            if ($tournament->template == 1 && $tournament->data) {
                Db::query('UPDATE `notifications` SET '.
                    '`delivered` = 1 '.
                    'WHERE `id` = '.(int)$tournament->data->id
                );
                $this->closeTournamentReg($tournament);
            }
            else {
                Db::query('INSERT INTO `notifications` SET '.
                    '`game` = "'.Db::escape($tournament->game).'", '.
                    '`tournament_name` = "'.Db::escape($tournament->name).'", '.
                    '`delivered` = 24'
                );
            }
        }
    }
    
    private function closeTournamentReg($tournament) {
        Db::query('UPDATE `tm_settings` SET '.
            '`value` = 0 '.
            'WHERE '.
            '`setting` = "tournament-reg-'.$tournament->game.($tournament->server?'-'.Db::escape($tournament->server):null).'"'
        );
            
        $challongeTournament = $tournament->game;
        if ($tournament->game == 'hs') {
            $challongeTournament = $this->data->settings['hs-current-number'];
        }
        else {
            $challongeTournament = $tournament->server.$this->data->settings['lol-current-number-'.$tournament->server];
        }
        
        $apiArray = array(
            'participant_id' => 0, //reshuffle all
        );
        
        if (_cfg('env') == 'prod') {
            $this->runChallongeAPI('tournaments/pentaclick-'.$challongeTournament.'/participants/randomize.post', $apiArray);
        }
        else {
            $this->runChallongeAPI('tournaments/pentaclick-test1/participants/randomize.post', $apiArray);
        }
        
        return true;
    }
}