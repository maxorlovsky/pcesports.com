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
        $answer = $this->runChallongeAPI('tournaments/pentaclick-test1/matches.json', array(), 'state=open');

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