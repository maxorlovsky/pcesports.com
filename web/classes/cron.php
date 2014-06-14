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
        
        if ($this->data->settings['tournament-start-lol'] == 1) {
            if (_cfg('env') == 'prod') {
                $answer = $this->runChallongeAPI('tournaments/pentaclick-lol'.$this->data->settings['lol-current-number'].'/matches.json', array(), 'state=open');
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
        $rows = Db::fetchRows('SELECT `game`, `name`, `dates`, `time` '.
            'FROM `tournaments` '.
            'WHERE `status` = "Start" '.
            'AND ((`game` = "hs" AND `name` = '.$this->data->settings['hs-current-number'].') '.
            'OR (`game` = "lol" AND `name` = '.$this->data->settings['lol-current-number'].')) '
        );
        
        foreach($rows as $v) {
            $row = Db::fetchRow('SELECT * '.
                'FROM `notifications` '.
                'WHERE `game` = "'.Db::escape($v->game).'" '.
                'AND `tournament_name` = "'.Db::escape($v->name).'" '.
                'AND `delivered` = 0 '
            );
            $v->dates = '04.06.2014';
            $time = strtotime($v->dates.' '.$v->time) - 86400;
            if (!$row && $time <= time()) {
                $this->sendReminders($v);
            }
        }
    }
    
    protected function sendReminders($tournament) {
        $rows = Db::fetchRows('SELECT * '.
            'FROM `teams` '.
            'WHERE `game` = "'.Db::escape($tournament->game).'" '.
            'AND `tournament_id` = '.(int)$tournament->name.' '
        );
        
        if ($rows) {
            foreach($rows as $v) {
                //dump($v);
            }
        }
    }
}