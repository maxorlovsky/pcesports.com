<?php

class Cron extends System {
    public function __construct() {
        parent::__construct();
    }

    public function emailSender() {
        $limit = 5;

        $row = Db::fetchRow(
            'SELECT * FROM `subscribe_sender` '.
            'WHERE (`timestamp` < DATE_SUB( NOW(), INTERVAL 1 MINUTE ) OR `timestamp` IS NULL) AND `ended` = 0 '.
            'ORDER BY `id` ASC '.
            'LIMIT 1'
        );

        if (!$row) {
            return false;
        }

        $where = '';
        if ($row->type != '') {
            $where = $row->type.' AND ';
        }

        $query = 'SELECT `email`, `unsublink` FROM `subscribe` WHERE '.$where.' `removed` = 0 LIMIT '.$row->emails.','.$limit;

        $rows = Db::fetchRows($query);

        $i = 0;
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

            try {
                $mailer->send($message, $fails);
            }
            catch(Exception $e) {
                $eMessage = $e->getMessage();
                if (strpos($eMessage, '550 Requested action not taken') || strpos($eMessage, '550 Message was not accepted')) {
                    //Mail not accepting emails, ignoring and removing mail from subscribers list
                    Db::query(
                        'UPDATE `subscribe` SET '.
                        '`removed` = 1 '.
                        'WHERE `email` = "'.Db::escape($v->email).'" '
                    );
                }
                else if (strpos($eMessage, '550')) {
                    //Limit of SMTP reached, must stop spamming for next 24h
                    Db::query(
                        'UPDATE `subscribe_sender` SET '.
                        '`emails` = '.($row->emails + $i).', '.
                        '`timestamp` = DATE_ADD(NOW(), INTERVAL 1 DAY) '.
                        'WHERE `id` = '.$row->id
                    );
                }
                else if (strpos($eMessage, '535')) {
                    //Authentication failed
                    Db::query(
                        'UPDATE `subscribe_sender` SET '.
                        '`ended` = 2, '.
                        '`timestamp` = NOW()'.
                        'WHERE `id` = '.$row->id
                    );
                }

                exit();
                //dump($e);
            }

            sleep(3);

            ++$i;
        }

        if ($i < $limit) {
            //If number of sent mails are less then limit, then we probably sent all of them
            Db::query(
                'UPDATE `subscribe_sender` SET '.
                '`emails` = '.($row->emails + $i).', '.
                '`ended` = 1, '.
                '`timestamp` = NOW() '.
                'WHERE `id` = '.$row->id
            );
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
    
    public function tournamentsOpenReg() {
        $rows = Db::fetchRows('SELECT * FROM `tournaments` WHERE `status` = "upcoming"');
        
        if ($rows) {
            foreach($rows as $v) {
                if (strtotime($v->dates_registration.' '.$v->time) <= time()) {
                    //Opening up registration!

                    Db::query('UPDATE `tournaments` SET '.
                        '`status` = "registration" '. //new mark for setting tournament in registration process
                        'WHERE '.
                        '`id` = '.(int)$v->id
                    );

                    //Creating subscribe row for a tournament
                    $file = file_get_contents(_cfg('dir').'/template/mails/invite-to-tourn.html');

                    if ($v->game == 'hs') {
                        $gameName = 'Hearthstone League Season 2 Tournament '.$v->name;
                        $href = _cfg('href').'/hearthstone/'.$v->server.'/'.$v->name;
                    }
                    else if ($v->game == 'lol') {
                        $gameName = 'League of Legends '.strtoupper($v->server).' tournament #'.$v->name;
                        $href = _cfg('href').'/leagueoflegends/'.$v->server.'/'.$v->name;
                    }
                    else {
                        $gameName = '';
                        $href = _cfg('href');
                    }

                    $html = str_replace(
                        array('%tournamentName%', '%href%'),
                        array($gameName, $href),
                        $file
                    );

                    $type = '(`theme` = "'.$v->game.'" OR `theme` = "all")';

                    Db::query(
                        'INSERT INTO `subscribe_sender` SET '.
                        '`type` = "'.Db::escape($type).'", '.
                        '`subject` = "Pentaclick tournament invitation - '.Db::escape($gameName).'", '.
                        '`text` = "'.Db::escape($html).'" '
                    );
                }
            }
        }
        
        return true;
    }
    
    public function finalizeTournament() {
        $row = Db::fetchRow('SELECT `id`, `server`, `name`, `game` '.
            'FROM `tournaments` '.
            'WHERE `status` = "ended" AND '.
            '`finalized` = 0 '.
            'LIMIT 1 '
        );

        if (!$row) {
            return false;
        }
        
        //Closing tournament on challonge
        $this->runChallongeAPI('tournaments/pentaclick-'.$row->game.$row->server.$row->name.'/finalize.json', array('end'=>'1'));
        
        //Getting participants places
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

        //Getting list of users in latest matches
        /*$users = Db::fetchRow('SELECT `id`, `server`, `name`, `game` '.
            'FROM `participants` '.
            'WHERE `game` = "'.$row->game.'" AND '.
            '`server` = "'.$row->server.'" AND '.
            '`tournament_id` = "'.$row->name.'" AND '.
            '`place` <= 8 AND '.
            '`place` != 0 '.
            'ORDER BY `place` '
            'LIMIT 8 '
        );*/

        //Removing streams
        Db::query(
            'DELETE FROM `streams` '.
            'WHERE `tournament_id` = '.(int)$row->name
        );

        //Registering that tournament is finalized
        Db::query(
            'UPDATE `tournaments` SET '.
            '`finalized` = 1 '.
            'WHERE `id` = '.(int)$row->id
        );
        
        return true;
    }
    
    public function sendNotificationsSkillz() {
        $rows = Db::fetchRows('SELECT `id`, `name`, `email`, `link` '.
            'FROM `participants_external` '.
            'WHERE `project` = "skillz" AND '.
            '`deleted` = 0 AND '.
            '`ended` = 0'
        );
        
        $text = Template::getMailTemplate('widget-reminder-1');
        
        if ($rows) {
            $i = 0;
            foreach($rows as $v) {
                $tournamentName = 'MSI MCS Open Season 3 HearthStone Baltic Qualifier';
                $url = 'http://skillz.lv/ru/news/2046?&participant='.$v->id.'&link='.$v->link.'&';
                $additionalText = '';
                
                $mailText = str_replace(
                    array('%name%', '%tournamentName%', '%url%', '%additionalText%', '%teamName%'),
                    array($v->name, $tournamentName, $url, $additionalText, 'Skillz.lv and Pentaclick eSports'),
                    $text
                );

                $this->sendMail($v->email, $tournamentName.' - reminder', $mailText);
                
                ++$i;
                if ($i >= 3) {
                    sleep(1);
                    $i = 0;
                }
            }
        }
    }
    
    public function sendNotifications() {
        $rows = Db::fetchRows('SELECT `id`, `game`, `server`, `name`, `dates_start`, `time` '.
            'FROM `tournaments` '.
            'WHERE `status` = "Start" '. //old status
            'OR `status` = "registration" '. //new status
            'OR `status` = "check_in" ' //new status
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

        // Updating state of tournament before sending emails
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

        //Sending emails, even if emails too much and it fails, it will still go through
        if ($rows) {
            $i = 0;
            foreach($rows as $v) {
                if ($v->game == 'lol') {
                    $url = _cfg('site').'/en/leagueoflegends/'.$v->server;
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
    }
    
    private function checkInProcess($tournament) {
        //Enabling check in! (old)
        Db::query('UPDATE `tm_settings` SET '.
            '`value` = 1 '.
            'WHERE '.
            '`setting` = "tournament-checkin-'.$tournament->game.'-'.Db::escape($tournament->server).'" OR '.
            '`setting` = "tournament-checkin-'.$tournament->game.'" '
        );

        //Enabling check in! (new)
        Db::query('UPDATE `tournaments` SET '.
            '`status` = "check_in" '.
            'WHERE '.
            '`id` = '.(int)$tournament->id
        );
        
        return true;
    }
    
    private function startUpTournament($tournament) {
        //Closing up registration!
        Db::query('UPDATE `tm_settings` SET '.
            '`value` = 0 '.
            'WHERE '.
            '`setting` = "tournament-reg-'.$tournament->game.'-'.Db::escape($tournament->server).'" OR '.
            '`setting` = "tournament-reg-'.$tournament->game.'" '
        );
        
        //Disabling check in!
        Db::query('UPDATE `tm_settings` SET '.
            '`value` = 0 '.
            'WHERE '.
            '`setting` = "tournament-checkin-'.$tournament->game.'-'.Db::escape($tournament->server).'" OR '.
            '`setting` = "tournament-checkin-'.$tournament->game.'" '
        );
        
        //Starting up tournament
        Db::query('UPDATE `tm_settings` SET '.
            '`value` = 1 '.
            'WHERE '.
            '`setting` = "tournament-start-'.$tournament->game.'-'.Db::escape($tournament->server).'" OR '.
            '`setting` = "tournament-start-'.$tournament->game.'"'
        );

        //New status
        Db::query('UPDATE `tournaments` SET '.
            '`status` = "live" '.
            'WHERE '.
            '`id` = '.(int)$tournament->id
        );
        
        if ($tournament->game == 'hs') {
            $challongeTournament = $tournament->game.$tournament->server.$this->data->settings[$tournament->game.'-current-number'];
        }
        else {
            $challongeTournament = $tournament->game.$tournament->server.$this->data->settings[$tournament->game.'-current-number-'.$tournament->server];
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

        //Cleaning up not checked in participants. So they would not see "check in" button.
        if ($tournament->game == 'hs') {
            Db::query(
                'UPDATE `participants` SET '.
                '`ended` = 1 '.
                'WHERE `game` = "'.$tournament->game.'" AND '.
                '`server` = "'.$tournament->server.'" AND '.
                '`tournament_id` = "'.$this->data->settings[$tournament->game.'-current-number'].'" AND '.
                '`approved` = 1 AND '.
                '`deleted` = 0 AND '.
                '`checked_in` = 0 '
            );
        }
        else {
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
        }
        
        Db::query('UPDATE `notifications` SET '.
            '`delivered` = 0 '.
            'WHERE `id` = '.(int)$tournament->data->id
        );
        
        return true;
    }
}