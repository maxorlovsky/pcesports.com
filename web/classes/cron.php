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
    
    public function sendNotifications() {
        $rows = Db::fetchRows('SELECT `game`, `server`, `name`, `dates`, `time` '.
            'FROM `tournaments` '.
            'WHERE `status` = "Start" '
            //'AND ((`game` = "hs" AND `name` = '.$this->data->settings['hs-current-number'].') '.
            //'OR (`game` = "lol" AND `name` = '.$this->data->settings['lol-current-number'].')) '
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