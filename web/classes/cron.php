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
            $twitch = $this->runTwitchAPI($v->name);
            
            if ($twitch['stream'] != NULL) {
                Db::query(
                    'UPDATE `streams` '.
                    'SET `online` = '.time().', '.
                    '`viewers` = '.(int)$twitch['stream']['viewers'].', '.
                    '`display_name` = "'.Db::escape($twitch['stream']['channel']['display_name']).'" '.
                    'WHERE `id` = '.(int)$v->id
                );
            }
        }
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
        if ($this->data->settings['tournament-start-lol-euw'] == 1 || $this->data->settings['tournament-start-lol-eune'] == 1) {
            if ($this->data->settings['tournament-start-lol-euw'] == 1) {
                $this->checkLolGamesByServer('euw');
            }
            if ($this->data->settings['tournament-start-lol-eune'] == 1) {
                $this->checkLolGamesByServer('eune');
            }
            
            return false;
        }
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
                $insideRows = Db::fetchRows('SELECT `participant_id`, `player_num`, `player_id`, `name` '.
                    'FROM `players` '.
                    'WHERE `participant_id` = '.(int)$v->team1.' OR `participant_id` = '.(int)$v->team2.' '.
                    'ORDER BY `player_num` ASC '
                );
                foreach($insideRows as $v2) {
                    $team[$v2->participant_id]['players'][$v2->player_num] = array('id' => $v2->player_id, 'name' => $v2->name);
                }
                
                Db::query('INSERT INTO `lol_games` SET '.
                    '`match_id` = '.(int)$v->match_id.', '.
                    '`message` = "Fight not found, ignoring match ('.Db::escape($team[$v->team1]['name']).' VS '.Db::escape($team[$v->team2]['name']).')", '.
                    '`participant_id1` = '.(int)$v->team1.', '.
                    '`participant_id2` = '.(int)$v->team2
                );
                $gameDbId = Db::lastId();

                $i = 0;
                foreach($insideRows as $vPlayer) {
                    //Getting player recent games
                    $answer = $this->runAPI('/'.$server.'/v1.3/game/by-summoner/'.$vPlayer->player_id.'/recent', $server, true);
                    $game = $answer->games[0]; //We're interested only in last game
                    
                    //Do not check ranked and solo games
                    if ($game->gameType == 'CUSTOM_GAME' && $game->gameMode == 'CLASSIC' && $game->mapId == 1 && $game->fellowPlayers) {
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
                                $this->sendMail('max.orlovsky@gmail.com', 'Pentaclick LoL tournament - Result', $emailText);
                                
                                //Registering email
                                Db::query('UPDATE `lol_games` SET '.
                                    '`message` = "'.Db::escape($emailText).'", '.
                                    '`game_id` = '.(int)$game->gameId.' '.
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
                                
                                Db::query('UPDATE `participants` SET `ended` = 1 '.
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
    
    public function sendNotifications() {
        $rows = Db::fetchRows('SELECT `game`, `server`, `name`, `dates_start`, `time` '.
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
                    $this->sendReminders($v);
                    $this->checkInProcess($v);
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
        
        $challongeTournament = $tournament->game;
        if ($tournament->game == 'hs') {
            $challongeTournament = $this->data->settings['hs-current-number'];
        }
        else {
            $challongeTournament = $tournament->server.$this->data->settings['lol-current-number-'.$tournament->server];
        }
        
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
        
        Db::query('UPDATE `notifications` SET '.
            '`delivered` = 0 '.
            'WHERE `id` = '.(int)$tournament->data->id
        );
        
        return true;
    }
}