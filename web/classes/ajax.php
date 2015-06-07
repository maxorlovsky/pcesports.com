<?php
class Ajax extends System
{
    public function __construct() {
        parent::__construct();
    }
	
    public function ajaxRun($data) {
    	$controller = $data['ajax'];
        
        if (method_exists($this, $controller)) {
            echo $this->$controller($data);
            return true;
        }
        else {
            echo '0;'.t('controller_not_exist');
            return false;
        }
    }

    protected function checkAchievements() {
        $achievements = new Achievements();
        $achievements->init();
    }
    
    protected function boardVote($data) {
        if (!$this->logged_in) {
            return '0;'.t('error');
        }
        
        if ($data['type'] == 'board') {
            $row = Db::fetchRow('SELECT * FROM `boards_votes` WHERE `board_id` = '.(int)$data['id'].' AND `user_id` = '.(int)$this->data->user->id.' LIMIT 1');
            if ($row && (($row->direction == 'plus' && $data['status'] == 'plus') || ($row->direction == 'minus' && $data['status'] == 'minus'))) {
                Db::query('DELETE FROM `boards_votes` WHERE `board_id` = '.(int)$data['id'].' AND `user_id` = '.(int)$this->data->user->id.' LIMIT 1');
                if ($data['status'] == 'plus') {
                    Db::query('UPDATE `boards` SET `votes` = `votes` - 1 WHERE `id` = '.(int)$data['id'].' LIMIT 1');
                }
                else {
                    Db::query('UPDATE `boards` SET `votes` = `votes` + 1 WHERE `id` = '.(int)$data['id'].' LIMIT 1');
                }
                return '2;1';
            }
            else if ($row) {
                return '3;1';
            }
            
            Db::query(
                'INSERT INTO `boards_votes` SET '.
                '`board_id` = '.(int)$data['id'].', '.
                '`user_id` = '.(int)$this->data->user->id.', '.
                '`direction` = "'.Db::escape_tags($data['status']).'" '
            );
            if ($data['status'] == 'plus') {
                Db::query('UPDATE `boards` SET `votes` = `votes` + 1 WHERE `id` = '.(int)$data['id'].' LIMIT 1');
            }
            else {
                Db::query('UPDATE `boards` SET `votes` = `votes` - 1 WHERE `id` = '.(int)$data['id'].' LIMIT 1');
            }
            
            return '1;1';
        }
        else {
            return '0;'.t('error');
        }
    }
    
    protected function submitBoard($data) {
        if (!$this->logged_in) {
            return '0;'.t('error');
        }
        
        $categoryList = _cfg('boardGames');
        $categoryList[] = 'general';
            
        if ($data['module'] == 'boards') {
            if (!trim($data['category']) || !in_array($data['category'], $categoryList)) {
                return '0;'.t('error');
            }
            
            if (!trim($data['title'])) {
                return '0;'.t('title_not_set');
            }
            else if (strlen(trim($data['title'])) > 50) {
                return '0;'.str_replace('%num%', 50, t('title_must_be_less'));
            }
            
            if (!trim($data['text'])) {
                return '0;'.t('text_not_set');
            }
            
            if ($this->logged_in != 1 || $this->data->user->id == 0) {
                return '0;'.t('not_logged_in');
            }
            
            $title = Db::escape_tags($data['title']);
            $text = Db::escape_tags($data['text']);
            Db::query(
                'INSERT INTO `boards` SET '.
                '`user_id` = '.(int)$this->data->user->id.', '.
                '`category` = "'.Db::escape_tags($data['category']).'", '.
                '`title` = "'.$title.'", '.
                '`text` = "'.$text.'", '.
                '`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
                '`activity` = '.time()
            );
            $id = Db::lastId();
            
            return '1;'._cfg('href').'/boards/'.$id;
        }
        else if ($data['module'] == 'editBoard') {
            if (!$data['id'] || !trim($data['category']) || !in_array($data['category'], $categoryList)) {
                return '0;'.t('error');
            }
            
            if (!trim($data['title'])) {
                return '0;'.t('title_not_set');
            }
            else if (strlen(trim($data['title'])) > 50) {
                return '0;'.str_replace('%num%', 50, t('title_must_be_less'));
            }
            
            if (!trim($data['text'])) {
                return '0;'.t('text_not_set');
            }
            
            if ($this->logged_in != 1 || $this->data->user->id == 0) {
                return '0;'.t('not_logged_in');
            }
            
            $title = Db::escape_tags($data['title']);
            $text = Db::escape_tags($data['text']);
            Db::query(
                'UPDATE `boards` SET '.
                '`category` = "'.Db::escape_tags($data['category']).'", '.
                '`title` = "'.$title.'", '.
                '`text` = "'.$text.'", '.
                '`edited` = 1 '.
                'WHERE '.
                '`user_id` = '.(int)$this->data->user->id.' AND '.
                '`id` = '.(int)$data['id'].' AND '.
                '`status` != 1 '
            );
            
            return '1;'._cfg('href').'/boards/'.$data['id'];
        }
        else if ($data['module'] == 'comment') {
            if (!trim($data['text'])) {
                return '0;'.t('text_not_set');
            }
            
            if (!$data['id']) {
                return '0;error';
            }
            
            $row = Db::fetchRow('SELECT `id` FROM `boards` WHERE `id` = '.(int)$data['id']);
            if (!$row) {
                return '0;Thread does not exist';
            }
            
            $text = Db::escape_tags($data['text']);
            Db::query(
                'INSERT INTO `boards_comments` SET '.
                '`board_id` = '.(int)$data['id'].', '.
                '`user_id` = '.(int)$this->data->user->id.', '.
                '`text` = "'.$text.'", '.
                '`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'" '
            );
            $id = Db::lastId();
            
            Db::query(
                'UPDATE `boards` SET '.
                '`comments` = `comments` + 1, '.
                '`activity` = '.time().' '.
                'WHERE `id` = '.(int)$data['id']
            );
            
            $text = $this->parseText($text);
            
            $text = str_replace('\n', '', $text);//don't know why it still there
            
            $html = '<div class="master" attr-id="'.$id.'">'.
                        '<div class="body">'.
                            '<div>'.$text.'</div>'.
                            '<span class="comment-user">'.
                                '<a href="'._cfg('href').'/member/'.$this->data->user->name.'">'.
                                    '<img class="avatar-block" src="'._cfg('avatars').'/'.$this->data->user->avatar.'.jpg" />'.
                                    $this->data->user->name.
                                '</a>'.
                            '</span>'.
                            '<span class="comment-time">- 0 '.t('seconds_ago').'</span> '.
                            '<span class="deleted edited hidden">('.t('edited').')</span>'.
                        '</div>'.
                        '<div class="clear"></div>'.
                        '<div class="actions">'.
                            '<a class="edit" href="javascript:void(0)">'.t('edit').'</a>'.
                                '<a class="delete" href="#" attr-msg="'.t('sure_to_delete_message').'">'.t('delete').'</a>'.
                                '<div class="edit-text">'.
                                    '<textarea>'.$text.'</textarea>'.
                                    '<div id="error"><p></p></div>'.
                                    '<a href="javascript:void(0)" class="button" id="editComment">'.t('edit').'</a>'.
                                    '<a href="javascript:void(0)" id="closeEditComment">'.t('cancel').'</a>'.
                                '</div>'.
                        '</div>'.
                    '</div>';
            
            return '1;'.$html;
        }
        else if ($data['module'] == 'delete') {
            if ($data['type'] == 'board') {
                Db::query(
                    'UPDATE `boards` SET '.
                    '`status` = 1 '.
                    'WHERE `id` = '.(int)$data['id'].' AND '.
                    '`user_id` = '.(int)$this->data->user->id.' '.
                    'LIMIT 1'
                );
                return '1;<span class="deleted">'.t('deleted').'</span>';
            }
            else if ($data['type'] == 'comment') {
                Db::query(
                    'UPDATE `boards_comments` SET '.
                    '`status` = 1 '.
                    'WHERE `id` = '.(int)$data['id'].' AND '.
                    '`user_id` = '.(int)$this->data->user->id.' '.
                    'LIMIT 1'
                );
                return '1;<span class="deleted">'.t('deleted').'</span>';
            }
            else if ($data['type'] == 'newsComment') {
                Db::query(
                    'UPDATE `blog_comments` SET '.
                    '`status` = 1 '.
                    'WHERE `id` = '.(int)$data['id'].' AND '.
                    '`user_id` = '.(int)$this->data->user->id.' '.
                    'LIMIT 1'
                );
                return '1;<span class="deleted">'.t('deleted').'</span>';
            }
            
            return '0;'.t('type_not_set');
        }
        
        return '0;'.t('module_not_exist');
    }
    
    protected function verifySummoner($data) {
        if (!$this->logged_in) {
            return '0;'.t('error');
        }
        
        $row = Db::fetchRow(
            'SELECT `id`, `region`, `summoner_id`, `masteries` FROM `summoners` WHERE '.
            '`id` = '.(int)$data['id'].' AND '.
            '`user_id`  = '.(int)$this->data->user->id.' '.
            'LIMIT 1 '
        );
        
        if ($row == false) {
            return '0;'.t('summoner_not_found');
        }
        
        $response = $this->runRiotAPI('/'.$row->region.'/v1.4/summoner/'.(int)$row->summoner_id.'/masteries', $row->region);
        foreach($response->pages as $v) {
            if (trim($v->name) == trim($row->masteries)) {
                Db::fetchRow(
                    'UPDATE `summoners` SET '.
                    '`approved` = 1, '.
                    '`masteries` = "" '.
                    'WHERE `id` = '.(int)$row->id.' AND '.
                    '`user_id`  = '.(int)$this->data->user->id.' '.
                    'LIMIT 1 '
                );
                return '1;'.$row->id;
            }
        }
        
        return '0;'.str_replace('%code%', $row->masteries, t('mastery_page_not_found'));
    }
    
    protected function removeSummoner($data) {
        if (!$this->logged_in) {
            return '0;'.t('not_logged_in');
        }
        
        $q = Db::query(
            'SELECT * FROM `summoners` WHERE '.
            '`id` = '.(int)$data['id'].' AND '.
            '`user_id`  = '.(int)$this->data->user->id.' '
        );
        
        if ($q->num_rows == 0) {
            return '0;Error';
        }
        
        Db::query(
            'DELETE FROM `summoners` WHERE '.
            '`id` = '.(int)$data['id'].' AND '.
            '`user_id`  = '.(int)$this->data->user->id.' '.
            'LIMIT 1 '
        );
        
        return '1;1';
    }
    
    protected function addSummoner($data) {
        if (!$this->logged_in) {
            return '0;'.t('not_logged_in');
        }
        
        $summoner = new stdClass();
        $name = $data['name'];
        $region = $data['region'];
        
        if (!$name) {
            return '0;'.t('input_name');
        }
        else if (!$region) {
            return '0;Set region';
        }
        $response = $this->runRiotAPI('/'.$region.'/v1.4/summoner/by-name/'.rawurlencode(htmlspecialchars($name)), $region);
        
        if ($response == 404 || !$response) {
            return '0;'.t('summoner_not_found').$response;
        }
        
        $summoner->summoner_id = (int)$response->id;
        $summoner->name = Db::escape($response->name);
        $summoner->region = Db::escape($region);
        
        $summoner->verificationCode = 'PC'.strtoupper(substr(md5(time().$response->name.$response->id), 1, 8));
        
        Db::query(
            'INSERT INTO `summoners` SET '.
            '`user_id`  = '.(int)$this->data->user->id.', '.
            '`region` = "'.$summoner->region.'", '.
            '`summoner_id` = '.$summoner->summoner_id.', '.
            '`name` = "'.$summoner->name.'", '.
            '`masteries` = "'.$summoner->verificationCode.'" '
        );
        
        $summoner->id = Db::lastId();
        
        foreach(_cfg('lolRegions') as $k => $v) {
            if ($k == $region) {
                $summoner->regionName = $v;
            }
        }
        
        return '1;'.json_encode($summoner);
    }
    
    protected function checkInHs() {
        if (!$_SESSION['participant']) {
            return '0;'.t('not_logged_in');
        }
        
        $server = $_SESSION['participant']->server;
        $currentTournament = (int)$this->data->settings['hs-current-number-'.$server];
        
        if ($this->data->settings['tournament-checkin-hs-'.$server] != 1) {
            return '0;Check in is not in progress';
        }
        
        //Generating other IDs for different environment
		if (_cfg('env') == 'prod') {
			$participant_id = $_SESSION['participant']->id + 100000;
		}
		else {
			$participant_id = $_SESSION['participant']->id;
		}
        
        $apiArray = array(
			'participant_id' => $participant_id,
			'participant[name]' => $_SESSION['participant']->name,
		);
		
		//Adding team to Challonge bracket
        if (_cfg('env') == 'prod') {
            $this->runChallongeAPI('tournaments/pentaclick-hs'.$server.$currentTournament.'/participants.post', $apiArray);
        }
        else {
            $this->runChallongeAPI('tournaments/pentaclick-test1/participants.post', $apiArray);
        }
		
		//Registering ID, because Challonge idiots not giving an answer with ID
        if (_cfg('env') == 'prod') {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-hs'.$server.$currentTournament.'/participants.json');
        }
        else {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-test1/participants.json');
        }
        
		array_reverse($answer, true);
		
		foreach($answer as $f) {
			if ($f->participant->name == $_SESSION['participant']->name) {
                $row = Db::fetchRow('SELECT * FROM `participants` '.
					'WHERE `tournament_id` = '.(int)$currentTournament.' '.
					'AND `game` = "hs" '.
					'AND `id` = '.(int)$_SESSION['participant']->id.' '.
                    'AND `approved` = 1 '.
                    'AND `verified` = 1 '.
                    'AND `checked_in` = 0 '
				);
                if ($row != 0) {
                    Db::query('UPDATE `participants` '.
                        'SET `challonge_id` = '.(int)$f->participant->id.', '.
                        '`checked_in` = 1 '.
                        'WHERE `tournament_id` = '.(int)$currentTournament.' '.
                        'AND `game` = "hs" '.
                        'AND `id` = '.(int)$_SESSION['participant']->id.' '.
                        'AND `approved` = 1 '.
                        'AND `verified` = 1 '
                    );
                    
                    $_SESSION['participant']->checked_in = 1;
                }
                
				break;
			}
		}
        
        return '1;1';
    }
    
    protected function checkInSmite() {
        if (!$_SESSION['participant']) {
            return '0;'.t('not_logged_in');
        }
        
        $server = $_SESSION['participant']->server;
        $currentTournament = (int)$this->data->settings['smite-current-number-'.$server];
        
        if ($this->data->settings['tournament-checkin-smite-'.$server] != 1) {
            return '0;Check in is not in progress';
        }
        
        //Generating other IDs for different environment
		if (_cfg('env') == 'prod') {
			$participant_id = $_SESSION['participant']->id + 100000;
		}
		else {
			$participant_id = $_SESSION['participant']->id;
		}
        
        $apiArray = array(
			'participant_id' => $participant_id,
			'participant[name]' => $_SESSION['participant']->name,
		);
		
		//Adding team to Challonge bracket
        if (_cfg('env') == 'prod') {
            $this->runChallongeAPI('tournaments/pentaclick-smite'.$server.$currentTournament.'/participants.post', $apiArray);
        }
        else {
            $this->runChallongeAPI('tournaments/pentaclick-test1/participants.post', $apiArray);
        }
		
		//Registering ID, because Challonge idiots not giving an answer with ID
        if (_cfg('env') == 'prod') {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-smite'.$server.$currentTournament.'/participants.json');
        }
        else {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-test1/participants.json');
        }
        
		array_reverse($answer, true);
		
		foreach($answer as $f) {
			if ($f->participant->name == $_SESSION['participant']->name) {
				Db::query('UPDATE `participants` '.
					'SET `challonge_id` = '.(int)$f->participant->id.', '.
                    '`checked_in` = 1 '.
					'WHERE `tournament_id` = '.(int)$currentTournament.' '.
					'AND `game` = "smite" '.
					'AND `id` = '.(int)$_SESSION['participant']->id.' '.
                    'AND `approved` = 1 '
				);
                
                $_SESSION['participant']->checked_in = 1;
                
				break;
			}
		}
        
        return '1;1';
    }
    
    protected function checkInLOL() {
        if (!$_SESSION['participant']) {
            return '0;'.t('not_logged_in');
        }
        
        $server = $_SESSION['participant']->server;
        $currentTournament = (int)$this->data->settings['lol-current-number-'.$server];
        
        if ($this->data->settings['tournament-checkin-lol-'.$server] != 1) {
            return '0;Check in is not in progress';
        }
        
        //Generating other IDs for different environment
		if (_cfg('env') == 'prod') {
			$participant_id = $_SESSION['participant']->id + 100000;
		}
		else {
			$participant_id = $_SESSION['participant']->id;
		}
        
        $apiArray = array(
			'participant_id' => $participant_id,
			'participant[name]' => $_SESSION['participant']->name,
		);
		
		//Adding team to Challonge bracket
        if (_cfg('env') == 'prod') {
            $this->runChallongeAPI('tournaments/pentaclick-lol'.$server.$currentTournament.'/participants.post', $apiArray);
        }
        else {
            $this->runChallongeAPI('tournaments/pentaclick-test1/participants.post', $apiArray);
        }
		
		//Registering ID, because Challonge idiots not giving an answer with ID
        if (_cfg('env') == 'prod') {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-lol'.$server.$currentTournament.'/participants.json');
        }
        else {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-test1/participants.json');
        }
        
		array_reverse($answer, true);
		
		foreach($answer as $f) {
			if ($f->participant->name == $_SESSION['participant']->name) {
				Db::query('UPDATE `participants` '.
					'SET `challonge_id` = '.(int)$f->participant->id.', '.
                    '`checked_in` = 1 '.
					'WHERE `tournament_id` = '.(int)$currentTournament.' '.
					'AND `game` = "lol" '.
					'AND `id` = '.(int)$_SESSION['participant']->id.' '.
                    'AND `approved` = 1 '
				);
                
                $_SESSION['participant']->checked_in = 1;
                
				break;
			}
		}
        
        return '1;1';
    }
    
    protected function editStreamer($data) {
        if (!$this->logged_in) {
            return '0;'.t('not_logged_in');
        }
        
        $id = (int)$data['id'];
        $game = Db::escape($data['game']);
        
        $row = Db::fetchRow(
            'SELECT * FROM `streams` '.
            'WHERE `id` = '.$id.' '.
            'AND `user_id` = '.(int)$this->data->user->id.' '.
            'LIMIT 1 '
        );
        if (!$row) {
            return '0;'.t('error');
        }
        
        Db::query(
            'UPDATE `streams` SET '.
            '`game` = "'.$game.'" '.
            'WHERE `id` = '.$id.' '.
            'LIMIT 1 '
        );
        
        return '1;1';
    }
    
    protected function removeStreamer($data) {
        if (!$this->logged_in) {
            return '0;'.t('not_logged_in');
        }
        
        $id = (int)$data['id'];
        
        $row = Db::fetchRow(
            'SELECT * FROM `streams` '.
            'WHERE `id` = '.$id.' '.
            'AND `user_id` = '.(int)$this->data->user->id.' '.
            'LIMIT 1 '
        );
        if (!$row) {
            return '0;'.t('error');
        }
        
        Db::query(
            'DELETE FROM `streams` '.
            'WHERE `id` = '.$id.' '.
            'LIMIT 1 '
        );
        
        return '1;1';
    }
    
    protected function submitStreamer($data) {
        if (!$this->logged_in) {
            return '0;'.t('not_logged_in');
        }
        
        parse_str($data['form'], $post);
        
        if (!isset($post['name']) || !$post['name']) {
            return '0;'.t('input_name');
        }
        else if (!$post['game']) {
            return '0;Error';
        }
        $post['name'] = str_replace(array('http://www.twitch.tv/', 'http://twitch.tv/'), array('',''), $post['name']);
        
        $twitch = $this->runTwitchAPI($post['name']);
        
        if (!$twitch) {
            return '0;'.t('channel_not_found');
        }
        
        $row = Db::fetchRow('SELECT * FROM `streams` WHERE `name` = "'.Db::escape($post['name']).'" LIMIT 1');
        if ($row) {
            return '0;'.t('stream_already_registered');
        }
        
        Db::query(
            'INSERT INTO `streams` SET '.
            '`user_id`  = '.(int)$this->data->user->id.', '.
            '`name` = "'.Db::escape($post['name']).'", '.
            '`game` = "'.Db::escape($post['game']).'", '.
            '`approved` = 1 '
        );
        
        return '1;'.t('stream_added');
    }
    
    protected function connectTeamToAccount() {
        if (!$this->logged_in || !$_SESSION['participant']) {
            return '0;'.t('not_logged_in');
        }
        
        Db::query(
            'UPDATE `participants` SET '.
            '`user_id` = '.(int)$this->data->user->id.' '.
            'WHERE `id` = '.(int)$_SESSION['participant']->id
        );
        
        return '1;1';
    }
    
    protected function getNewsComments($data) {
        $rows = Db::fetchRows(
            'SELECT `nc`.`id`, `nc`.`text`, `nc`.`added`, `nc`.`edited`, `nc`.`status`, `u`.`id` AS `userId`, `u`.`name`, `u`.`avatar` '.
            'FROM `blog_comments` AS `nc` '.
            'LEFT JOIN `users` AS `u` ON `nc`.`user_id` = `u`.`id` '.
            'WHERE `nc`.`blog_id` = '.(int)$data['id'].' '.
            'ORDER BY `nc`.`id` DESC '
        );
        
        $html = '';
        $currDate = new DateTime();
        if ($rows) {
            foreach($rows as $v) {
                $dbDate = new DateTime($v->added);
                $interval = $this->getAboutTime($currDate->diff($dbDate));
                
                $text = $this->parseText($v->text);
                
                $html .= '<div class="master" attr-id="'.$v->id.'" attr-module="newsComment">'.
                            '<div class="body">'.
                                '<div>'.$text.'</div>'.
                                '<span class="comment-user">'.
                                    '<a href="'._cfg('href').'/member/'.$v->name.'">'.
                                        '<img class="avatar-block" src="'._cfg('avatars').'/'.$v->avatar.'.jpg" />'.
                                        $v->name.
                                    '</a>'.
                                '</span> '.
                                '<span class="comment-time">- '.$interval.'</span> '.
                                '<span class="deleted edited '.($v->edited!=1?'hidden':null).'">('.t('edited').')</span>'.
                            '</div>'.
                            '<div class="clear"></div>';
                if ($v->userId == $this->data->user->id && $v->status != 1) {
                    $html .='<div class="actions">'.
                                '<a class="edit" href="javascript:void(0)">'.t('edit').'</a>'.
                                    '<a class="delete" href="#" attr-msg="'.t('sure_to_delete_message').'">'.t('delete').'</a>'.
                                    '<div class="edit-text">'.
                                        '<textarea>'.$v->text.'</textarea>'.
                                        '<div id="error"><p></p></div>'.
                                        '<a href="javascript:void(0)" class="button" id="editComment">'.t('edit').'</a>'.
                                        '<a href="javascript:void(0)" id="closeEditComment">'.t('cancel').'</a>'.
                                    '</div>'.
                            '</div>';
                }
                $html .= '</div>';
            }
        }
        
        return $html;
    }
    
    protected function comment($data) {
        if ($data['module'] == 'news') {
            if (!trim($data['text'])) {
                return '0;'.t('comment_is_empty');
            }
            if ($this->logged_in != 1 || $this->data->user->id == 0) {
                return '0;'.t('not_logged_in');
            }
            
            $text = Db::escape(strip_tags($data['text']));
            Db::query(
                'INSERT INTO `blog_comments` SET '.
                '`blog_id` = '.(int)$data['id'].', '.
                '`user_id` = '.(int)$this->data->user->id.', '.
                '`text` = "'.$text.'", '.
                '`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'" '
            );
            
            Db::query(
                'UPDATE `blog` SET `comments` = `comments` + 1 '.
                'WHERE `id` = '.(int)$data['id'].' '
            );
            
            return '1;1';
        }
        else if ($data['module'] == 'editBoardComment') {
            if (!trim($data['text'])) {
                return '0;'.t('text_not_set');
            }
            
            if (!$data['id']) {
                return '0;error';
            }
            
            $text = Db::escape_tags($data['text']);
            Db::query(
                'UPDATE `boards_comments` SET '.
                '`text` = "'.$text.'", '.
                '`edited` = 1 '.
                'WHERE '.
                '`id` = '.(int)$data['id'].' AND '.
                '`user_id` = '.(int)$this->data->user->id.' '.
                'LIMIT 1'
            );
            
            $text = $this->parseText($text);
            $text = str_replace('\n', '<br />', $text);//don't know why it still there
            return '1;'.$text;
        }
        else if ($data['module'] == 'editNewsComment') {
            if (!trim($data['text'])) {
                return '0;'.t('text_not_set');
            }
            
            if (!$data['id']) {
                return '0;error';
            }
            
            $text = Db::escape_tags($data['text']);
            Db::query(
                'UPDATE `blog_comments` SET '.
                '`text` = "'.$text.'", '.
                '`edited` = 1 '.
                'WHERE '.
                '`id` = '.(int)$data['id'].' AND '.
                '`user_id` = '.(int)$this->data->user->id.' '.
                'LIMIT 1'
            );
            
            $text = $this->parseText($text);
            $text = str_replace('\n', '<br />', $text);//don't know why it still there
            return '1;'.$text;
        }
        
        return '0;'.t('module_not_exist');
    }
    
    protected function updateProfile($data) {
        parse_str($data['form'], $post);
        return User::updateProfile($post);
    }
    
    protected function socialDisconnect($data) {
        $answer = User::socialDisconnect($data);
        
        if ($answer !== true) {
            $answer = '0;'.$answer;
        }
        else {
            $answer = '1;1';
        }
        
        return $answer;
    }
    
    protected function socialLogin($data) {
        $social = new Social();
        return $social->getToken($data['provider']);
    }
    
    protected function uploadScreenshot() {
        $mb = 5;
        
        if (!isset($_SESSION['participant']) && !$_SESSION['participant']->id) {
            return '0;'.t('not_logged_in');
        }
        
        $playersRow = Db::fetchRow('SELECT `challonge_id` FROM `participants` '.
            'WHERE `id` = '.(int)$_SESSION['participant']->id.' AND '.
            '`deleted` = 0 AND '.
            '`ended` = 0'
        );
        if (!$playersRow) {
            return '0;No fight registered';
        }

        if ($_FILES['upload']['size'] > ($mb * 1048576)) { //1024*1024
            return '0;File size is too big, allowed only: '.$mb.' MB';
        }
        else {
            $row = Db::fetchRow('SELECT `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2`, `f`.`screenshots` '.
                'FROM `fights` AS `f` '.
                'LEFT JOIN `participants` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
                'LEFT JOIN `participants` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
                'WHERE (`f`.`player1_id` = '.$playersRow->challonge_id.' OR `f`.`player2_id` = '.$playersRow->challonge_id.') AND '.
                '`f`.`done` = 0'
            );
            
            if (!$row) {
                return '0;'.t('error');
            }
            else {
                $name = $_FILES['upload']['name'];
				$breakdown = explode('.', $name);
                $end = end($breakdown);
                
                $fileName = $_SERVER['DOCUMENT_ROOT'].'/screenshots/'.$row->id1.'_vs_'.$row->id2.'_'.time().'.'.$end;
                $fileUrl = _cfg('site').'/screenshots/'.$row->id1.'_vs_'.$row->id2.'_'.time().'.'.$end;

                if ($end != 'png' && $end != 'jpg' && $end != 'jpeg') {
                    return '0;'.t('file_is_not_image').': '.$end;
                }            
                else if (!copy($_FILES['upload']['tmp_name'], $fileName)) {
                    return '0;'.t('move_file_error');
                }
                else if ($row->screenshots > 10) {
                    return '0;'.t('screenshot_limit_block');
                }
                else {
                    $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$row->id1.'_vs_'.$row->id2.'.txt';
                
                    $file = fopen($fileName, 'a');
                    $content = '<p><span id="notice">('.date('H:i:s', time()).')</span> '.($_SESSION['participant']->id==$row->id1?'<span class="player1">'.$row->name1.'</span>':'<span class="player2">'.$row->name2.'</span>').' <a href="'.$fileUrl.'" target="_blank">uploaded the file</a></p>';
                    fwrite($file, htmlspecialchars($content));
                    fclose($file);
                    
                    Db::query('UPDATE `fights` '.
                        'SET `screenshots` = `screenshots` + 1 '.
                        'WHERE `player1_id` = '.$playersRow->challonge_id.' OR `player2_id` = '.$playersRow->challonge_id
                    );
                    
                    return '1;Ok';
                }
            }
        }
    }

    protected function banHS($data) {
        if (!isset($_SESSION['participant']) && !$_SESSION['participant']->id) {
            return false;
        }

        $row = Db::fetchRow('SELECT `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2`, `f`.`match_id` '.
            'FROM `fights` AS `f` '.
            'LEFT JOIN `participants` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
            'LEFT JOIN `participants` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
            'WHERE (`f`.`player1_id` = '.(int)$_SESSION['participant']->challonge_id.' OR `f`.`player2_id` = '.(int)$_SESSION['participant']->challonge_id.') '.
            'AND`f`.`done` = 0'
        );

        $player = 0;
        if ($row->id1 == $_SESSION['participant']->id) {
            $player = 1;
        }
        else if ($row->id2 == $_SESSION['participant']->id) {
            $player = 2;
        }

        $gameRow = Db::fetchRow('SELECT * FROM `hs_games` WHERE `match_id` = '.(int)$row->match_id);
        if (!$gameRow) {
            $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$row->id1.'_vs_'.$row->id2.'.txt';
            
            $file = fopen($fileName, 'a');
            $content = '<p><span id="notice">('.date('H:i:s', time()).')</span> <b><span class="player'.($player==1?'1':'2').'">'.$_SESSION['participant']->name.'</span> picked his ban. Awaiting enemy ban.</b></p>';
            fwrite($file, htmlspecialchars($content));
            fclose($file);

            Db::query('INSERT INTO `hs_games` SET '.
                '`match_id` = '.(int)$row->match_id.', '.
                '`player'.$player.'_ban` = "'.Db::escape($data['hero']).'" '
            );
        }
        else {
            Db::query('UPDATE `hs_games` SET '.
                '`player'.$player.'_ban` = "'.Db::escape($data['hero']).'" '.
                'WHERE `match_id` = '.(int)$row->match_id
            );

            $gameRow = Db::fetchRow('SELECT * FROM `hs_games` WHERE `match_id` = '.(int)$row->match_id);

            $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$row->id1.'_vs_'.$row->id2.'.txt';
            
            $file = fopen($fileName, 'a');
            $content = '<p><span id="notice">('.date('H:i:s', time()).')</span> <b><span class="player1">'.$row->name1.'</span> banned "'.$gameRow->player1_ban.'"</b></p>';
            $content .= '<p><span id="notice">('.date('H:i:s', time()).')</span> <b><span class="player2">'.$row->name2.'</span> banned "'.$gameRow->player2_ban.'"</b></p>';
            fwrite($file, htmlspecialchars($content));
            fclose($file);
        }

        return '1;1';
    }
    
    protected function statusCheck($data) {
        if (isset($_SESSION['participant']) && $_SESSION['participant']->id) {
            $challonge_id = (int)$_SESSION['participant']->challonge_id;
            Db::query('UPDATE `participants` SET `online` = '.time().' '.
				'WHERE `id` = '.(int)$_SESSION['participant']->id
			);
        }
        else {
            $challonge_id = 0;
        }
        
        $row = Db::fetchRow('SELECT `game`, `server`, `checked_in` '.
            'FROM `participants` '.
            'WHERE '.
            '`id` = '.(int)$_SESSION['participant']->id.' AND '.
            '`deleted` = 0 AND '.
            '`ended` = 0 '
        );
        
        if ($row->checked_in == 0) {
            if (
               ($row->game == 'hs' && $this->data->settings['tournament-checkin-'.$row->game] != 1) ||
               $this->data->settings['tournament-checkin-'.$row->game.'-'.$row->server] != 1
               )
            {
                return '0;'.t('none').';'.t('tournament_not_started_yet').';'.t('none');
            }
            
            return '2;'.t('none').';'.t('check_in_required').';'.t('none');
        }
        
        $row = Db::fetchRow('SELECT * FROM `fights` '.
            'WHERE (`player1_id` = '.$challonge_id.' OR `player2_id` = '.$challonge_id.') AND '.
            '`done` = 0'
        );
        
        if (!$row) {
            return '0;'.t('none').';'.t('waiting_for_opponent').';'.t('none');
        }
        
        $playersRow = Db::fetchRow('SELECT `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2`, `f`.`match_id` '.
            'FROM `fights` AS `f` '.
            'LEFT JOIN `participants` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
            'LEFT JOIN `participants` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
            'WHERE (`f`.`player1_id` = '.(int)$_SESSION['participant']->challonge_id.' OR `f`.`player2_id` = '.(int)$_SESSION['participant']->challonge_id.') '.
            'AND`f`.`done` = 0'
        );
        
        if ($playersRow) {
            $enemyRow = Db::fetchRow('SELECT `id`, `name`, `online`, `server` '.
                'FROM `participants` '.
                'WHERE '.
                '`challonge_id` = '.(int)($_SESSION['participant']->challonge_id==$playersRow->player1_id?$playersRow->player2_id:$playersRow->player1_id).' AND '.
                '`deleted` = 0 AND '.
                '`ended` = 0 '
            );
			
            if ($enemyRow) {
                if ($enemyRow->online+30 >= time()) {
                    $status = t('online');
                }
                else {
                    $status = t('offline');
                }
                
                $code = '';
                $banStatus = '';
                if ($_SESSION['participant']->game == 'lol') {
                    if (_cfg('env') == 'prod') {
                        $reportTo = 'http://www.pcesports.com/run/riotcode/';
                    }
                    else {
                        $reportTo = 'http://test.pcesports.com/run/riotcode/';
                    }
                    
                    $array = array(
						'name'      => 'Pentaclick#'.(int)$this->data->settings['lol-current-number-'.$enemyRow->server].' - '.$playersRow->name1.' vs '.$playersRow->name2,
						'extra'     => $playersRow->match_id,
						'password'  => md5($playersRow->match_id),
						'report'    => $reportTo,
					);
					$code = 'pvpnet://lol/customgame/joinorcreate/map11/pick6/team5/specALL/';
					$code .= base64_encode(json_encode($array));
                }
                else if ($_SESSION['participant']->game == 'smite') {
                    $rows = Db::fetchRows(
                        'SELECT `name` FROM `players` '.
                        'WHERE `game` = "smite" AND '.
                        '`participant_id` = '.(int)$enemyRow->id.' '.
                        'ORDER BY `player_num` DESC '
                    );
                    
                    $code = '';
                    foreach($rows as $v) {
                        $code .= $v->name."\n";
                    }
                }
                else if ($_SESSION['participant']->game == 'hs') {
                    $row = Db::fetchRow(
                        'SELECT `contact_info` FROM `participants` '.
                        'WHERE `game` = "hs" AND '.
                        '`id` = '.(int)$enemyRow->id
                    );
                    
                    $row->contact_info = json_decode($row->contact_info);
                    $heroes = array(
                        1 => 'warrior',
                        2 => 'hunter',
                        3 => 'mage',
                        4 => 'warlock',
                        5 => 'shaman',
                        6 => 'rogue',
                        7 => 'druid',
                        8 => 'paladin',
                        9 => 'priest',
                    );
                    
                    $code = '';
                    foreach($row->contact_info as $k => $v) {
                        if (substr($k, 0, 4) == 'hero') {
                            $code[$k] = $heroes[$v];
                        }
                    }
                    $code = json_encode($code);

                    if ($_SESSION['participant']->challonge_id == $playersRow->player1_id) {
                        $player = 1;
                    }
                    else {
                        $player = 2;
                    }

                    $banStatus = 'none';
                    $hsGamesRow = Db::fetchRow('SELECT `player'.$player.'_ban` AS `ban` FROM `hs_games` WHERE `match_id` = '.(int)$playersRow->match_id.' LIMIT 1');
                    if ($hsGamesRow) {
                        $banStatus = strtolower($hsGamesRow->ban);
                    }
                }

                return '1;'.$enemyRow->name.';'.$status.';'.$code.';'.$banStatus;
            }
            
            return '0;'.t('none').';'.t('offline').';'.t('none');
        }
        
        return '0;'.t('none').';'.t('no_opponent').';'.t('none');
    }
    
    protected function chat($data) {
        if (isset($_SESSION['participant']) && $_SESSION['participant']->id) {
            $challonge_id = (int)$_SESSION['participant']->challonge_id;
        }
        else {
            $challonge_id = 0;
        }
        
        $row = Db::fetchRow('SELECT * FROM `fights` '.
            'WHERE (`player1_id` = '.$challonge_id.' OR `player2_id` = '.$challonge_id.') AND '.
            '`done` = 0'
        );
        
        if (!$row) {
            return '1;;<p id="notice">'.t('chat_disabled_no_opp').'</p>';
        }
        
        $playersRow = Db::fetchRow('SELECT `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2` '.
            'FROM `fights` AS `f` '.
            'LEFT JOIN `participants` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
            'LEFT JOIN `participants` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
            'WHERE (`f`.`player1_id` = '.(int)$_SESSION['participant']->challonge_id.' OR `f`.`player2_id` = '.(int)$_SESSION['participant']->challonge_id.') '.
            'AND`f`.`done` = 0'
        );
        
        if ($playersRow) {
            $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.(int)$playersRow->id1.'_vs_'.(int)$playersRow->id2.'.txt';
            
            $file = fopen($fileName, 'a');
            if ($data['action'] == 'send') {
                $content = '<p><span id="notice">('.date('H:i:s', time()).')</span> &#60;'.($_SESSION['participant']->id==$playersRow->id1?'<span class="player1">'.$playersRow->name1.'</span>':'<span class="player2">'.$playersRow->name2.'</span>').'&#62; - '.$data['text'].'</p>';
                fwrite($file, htmlspecialchars($content));
            }
            fclose($file);
            
            $chat = strip_tags(stripslashes(html_entity_decode(file_get_contents($fileName))), '<p><b><a><u><span>');
            
            if (!$chat) {
                $chat = '<p id="notice">'.t('chat_active_can_start').'</p>';
            }
            
            return '1;;'.$chat;
        }
        else {
            return '0;;'.t('error');
        }
        
        return '0;'.t('error');
    }
    
    protected function registerInHS($data) {
    	$err = array();
    	$suc = array();
    	parse_str($data['form'], $post);
        
        $server = 's1';

        if ($this->logged_in) {
            if ($this->data->user->battletag) {
                $post['battletag'] = $this->data->user->battletag;
            }

            if ($this->data->user->email) {
                $post['email'] = $this->data->user->email;
            }
        }
    	
    	$battleTagBreakdown = explode('#', $post['battletag']);
    	
    	$row = Db::fetchRow('SELECT * FROM `participants` WHERE '.
    		'`tournament_id` = '.(int)$this->data->settings['hs-current-number-s1'].' AND '.
    		'`name` = "'.Db::escape($post['battletag']).'" AND '.
    		'`game` = "hs" AND '.
    		//'`approved` = 1 AND '.
    		'`deleted` = 0 '
    	);


    	if (!$post['battletag']) {
    		$err['battletag'] = '0;'.t('field_empty');
    	}
    	else if ($row) {
    		$err['battletag'] = '0;'.t('field_battletag_error');
    	}
    	else if (!isset($battleTagBreakdown[0]) || !$battleTagBreakdown[0] || !isset($battleTagBreakdown[1]) || !is_numeric($battleTagBreakdown[1])) {
    		$err['battletag'] = '0;'.t('field_battletag_incorrect');
    	}
    	else {
    		$suc['battletag'] = '1;'.t('approved');
    	}
    	
    	if (!$post['email']) {
    		$err['email'] = '0;'.t('field_empty');
    	}
    	else if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
    		$err['email'] = '0;'.t('email_invalid');
    	}
    	else {
    		$suc['email'] = '1;'.t('approved');
    	}
        
        if (!$post['agree']) {
    		$err['agree'] = '0;'.t('must_agree_with_rules');
    	}
        else {
            $suc['agree'] = '1;'.t('approved');
        }
        
        $heroesPicked = array();
        for($i=1;$i<=4;++$i) {
            if (!$post['hero'.$i]) {
                $err['hero'.$i] = '0;'.t('pick_hero');
            }
            
            if (in_array($post['hero'.$i], $heroesPicked)) {
                $err['hero'.$i] = '0;'.t('same_hero_picked');
            }
            
            if ($post['hero'.$i]) {
                $heroesPicked[] = $post['hero'.$i];
            }
        }
        if ($post['hero1'] == $post['hero2'] && $post['hero1'] != 0) {
            $err['hero2'] = '0;'.t('same_hero_picked');
        }
        
        $addStream = 0;
        if ($post['stream']) {
            $post['stream'] = str_replace(array('http://www.twitch.tv/', 'http://twitch.tv/'), array('',''), $post['stream']);
            
            $twitch = $this->runTwitchAPI($post['stream']);
            
            if (!$twitch) {
                $err['stream'] = '0;'.t('channel_not_found');
            }
            else {
                $addStream = 1;
                $suc['stream'] = '1;'.t('approved');
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
            
            $contact_info = json_encode(array(
                'hero1' => $post['hero1'],
                'hero2' => $post['hero2'],
                'hero3' => $post['hero3'],
                'hero4' => $post['hero4'],
                'phone' => $post['phone'],
                'place' => 0,
            ));
    	
    		$code = substr(sha1(time().rand(0,9999)).$post['battletag'], 0, 32);
    		Db::query('INSERT INTO `participants` SET '.
	    		'`game` = "hs", '.
                '`server` = "'.Db::escape($server).'", '.
	    		'`tournament_id` = '.(int)$this->data->settings['hs-current-number-s1'].', '.
	    		'`timestamp` = NOW(), '.
	    		'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
	    		'`name` = "'.Db::escape($post['battletag']).'", '.
	    		'`email` = "'.Db::escape($post['email']).'", '.
	    		'`contact_info` = "'.Db::escape($contact_info).'", '.
                ($this->logged_in?'`approved` = "1", `user_id` = '.(int)$this->data->user->id.', ':null).
	    		'`link` = "'.$code.'"'
    		);
    	
    		$teamId = Db::lastId();
    	
    		Db::query(
    			'INSERT INTO `players` SET '.
    			' `game` = "hs", '.
    			' `tournament_id` = '.(int)$this->data->settings['hs-current-number-s1'].', '.
    			' `participant_id` = '.(int)$teamId.', '.
    			' `name` = "'.Db::escape($post['battletag']).'", '.
    			' `player_num` = 1'
    		);
            
            if ($addStream == 1) {
                Db::query(
                    'INSERT INTO `streams_events` SET '.
                    '`user_id`  = '.(int)$this->data->user->id.', '.
                    '`participant_id` = '.(int)$teamId.', '.
                    ' `tournament_id` = '.(int)$this->data->settings['hs-current-number-s1'].', '.
                    '`game` = "hs", '.
                    '`name` = "'.Db::escape($post['stream']).'" '
                );
            }
    		
            //Only sending email to not reggistered user
            if (!$this->logged_in) {
        		$text = Template::getMailTemplate('reg-hs-player');
        	
        		$text = str_replace(
        			array('%name%', '%teamId%', '%code%', '%url%', '%href%'),
        			array($post['battletag'], $teamId, $code, _cfg('href').'/hearthstone/'.$server, _cfg('site')),
        			$text
        		);
        	
        		$this->sendMail($post['email'], 'Pentaclick Hearthstone tournament participation', $text);
            }
            else {
                $answer['ok'] = 2;
            }
    	}
    	 
    	return json_encode($answer);
    }
    
    protected function editInHS($data) {
    	$err = array();
    	$suc = array();
    	parse_str($data['form'], $post);
        
        if ($this->logged_in) {
            $post['email'] = Db::escape($this->data->user->email);
        }

        if (!$post['email']) {
    		$err['email'] = '0;'.t('field_empty');
    	}
    	else if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
    		$err['email'] = '0;'.t('email_invalid');
    	}
    	else {
    		$suc['email'] = '1;'.t('approved');
    	}
        
        $heroesPicked = array();
        for($i=1;$i<=4;++$i) {
            if (!$post['hero'.$i]) {
                $err['hero'.$i] = '0;'.t('pick_hero');
            }
            
            if (in_array($post['hero'.$i], $heroesPicked)) {
                $err['hero'.$i] = '0;'.t('same_hero_picked');
            }
            
            if ($post['hero'.$i]) {
                $heroesPicked[] = $post['hero'.$i];
            }
        }
        if ($post['hero1'] == $post['hero2'] && $post['hero1'] != 0) {
            $err['hero2'] = '0;'.t('same_hero_picked');
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
            
            $contact_info = json_encode(array(
                'hero1' => $post['hero1'],
                'hero2' => $post['hero2'],
                'hero3' => $post['hero3'],
                'hero4' => $post['hero4'],
                'place' => 0,
            ));
            
    		Db::query('UPDATE `participants` SET '.
                '`email` = "'.Db::escape($post['email']).'", '.
	    		'`contact_info` = "'.Db::escape($contact_info).'" '.
	    		'WHERE `id` = '.(int)$_SESSION['participant']->id.' AND '.
                '`game` = "hs" '
    		);
    	}
        
    	return json_encode($answer);
    }
	
	protected function registerInLoL($data) {
    	$err = array();
    	$suc = array();
    	parse_str($data['form'], $post);
        
        if (in_array($post['server'], array('eune', 'euw'))) {
            $server = $post['server'];
        }
        else {
            $server = 'euw';
        }
        
        if ($this->data->settings['tournament-reg-lol-'.$server] != 1) {
            return '0;Server error!';
        }
        
        if (!$post['agree']) {
    		$err['agree'] = '0;'.t('must_agree_with_rules');
    	}
        else {
            $suc['agree'] = '1;'.t('approved');
        }
    	
    	$row = Db::fetchRow('SELECT * FROM `participants` WHERE '.
    		'`tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].' AND '.
    		'`name` = "'.Db::escape($post['team']).'" AND '.
            '`server` = "'.Db::escape($server).'" AND '.
    		'`game` = "lol" AND '.
    		'`approved` = 1 AND '.
    		'`deleted` = 0'
    	);

    	if (!$post['team']) {
    		$err['team'] = '0;'.t('field_empty');
    	}
		else if (strlen($post['team']) < 4) {
			$err['team'] = '0;'.t('team_name_small');
		}
		else if (strlen($post['team']) > 60) {
			$err['team'] = '0;'.t('team_name_big');
		}
        else if ($row) {
            $err['team'] = '0;'.t('team_name_taken');
        }
		else {
			$suc['team'] = '1;'.t('approved');
		}
    	
    	if (!$post['email']) {
    		$err['email'] = '0;'.t('field_empty');
    	}
    	else if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
    		$err['email'] = '0;'.t('email_invalid');
    	}
    	else {
    		$suc['email'] = '1;'.t('approved');
    	}
		
		$players = array();
		$checkForSame = array();
        $summonersNames = array();
		for($i=1;$i<=7;++$i) {
            $post['mem'.$i] = trim($post['mem'.$i]);
            
			if (!$post['mem'.$i] && $i < 6) {
				$err['mem'.$i] = '0;'.t('field_empty');    
			}
            else if (in_array($post['mem'.$i], $checkForSame)) {
                $err['mem'.$i] = '0;'.t('same_summoner');
            }
			else if ($post['mem'.$i]) {
                $summonersNames[] = rawurlencode(htmlspecialchars($post['mem'.$i]));
                $checkForSame[] = $post['mem'.$i];
			}
		}
        
        if (!$err) {
    		$summonersNames = implode(',', $summonersNames);
            $response = $this->runRiotAPI('/'.$server.'/v1.4/summoner/by-name/'.$summonersNames, $server, true);
            for($i=1;$i<=7;++$i) {
                $name = str_replace(' ', '', mb_strtolower($post['mem'.$i]));
                
                if (isset($response->$name) && $response->$name) {
                    if ($response->$name->summonerLevel != 30) {
                        $err['mem'.$i] = '0;'.t('summoner_low_lvl');
                    }
                    else {
                        $players[$i]['id'] = $response->$name->id;
                        $players[$i]['name'] = $response->$name->name;
                        $suc['mem'.$i] = '1;'.t('approved');
                    }
                }
                else if ($post['mem'.$i] && !isset($response->$name)) {
                    $err['mem'.$i] = '0;'.t('summoner_not_found_'.$server);
                }
            }
        }
        
        $addStream = 0;
        if ($post['stream']) {
            $post['stream'] = str_replace(array('http://www.twitch.tv/', 'http://twitch.tv/'), array('',''), $post['stream']);
            
            $twitch = $this->runTwitchAPI($post['stream']);
            
            if (!$twitch) {
                $err['stream'] = '0;'.t('channel_not_found');
            }
            else {
                $addStream = 1;
                $suc['stream'] = '1;'.t('approved');
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
    	
    		$code = substr(sha1(time().rand(0,9999)).$post['team'], 0, 32);
    		Db::query('INSERT INTO `participants` SET '.
	    		'`game` = "lol", '.
                '`user_id` = '.(int)$this->data->user->id.', '.
                '`server` = "'.$server.'", '.
	    		'`tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].', '.
	    		'`timestamp` = NOW(), '.
	    		'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
	    		'`name` = "'.Db::escape($post['team']).'", '.
	    		'`email` = "'.Db::escape($post['email']).'", '.
	    		'`contact_info` = "'.Db::escape($team).'", '.
                '`cpt_player_id` = '.(int)$players[1]['id'].', '.
	    		'`link` = "'.$code.'"'
    		);
    	
    		$teamId = Db::lastId();
			
			foreach($players as $k => $v) {
				Db::query(
					'INSERT INTO `players` SET '.
					' `game` = "lol", '.
					' `tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].', '.
					' `participant_id` = '.(int)$teamId.', '.
					' `name` = "'.Db::escape($v['name']).'", '.
					' `player_num` = "'.(int)$k.'", '.
					' `player_id` = "'.(int)$v['id'].'"'
				);
			}
            
            if ($addStream == 1) {
                Db::query(
                    'INSERT INTO `streams_events` SET '.
                    '`user_id`  = '.(int)$this->data->user->id.', '.
                    '`participant_id` = '.(int)$teamId.', '.
                    ' `tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].', '.
                    '`game` = "lol", '.
                    '`name` = "'.Db::escape($post['stream']).'" '
                );
            }
    		
    		$text = Template::getMailTemplate('reg-lol-team');
    	
    		$text = str_replace(
    			array('%name%', '%teamId%', '%code%', '%url%', '%href%'),
    			array($post['team'], $teamId, $code, _cfg('href').'/leagueoflegends/'.$server, _cfg('site')),
    			$text
    		);
    	
    		$this->sendMail($post['email'], 'Pentaclick League of Legends tournament participation', $text);
    	}
    	 
    	return json_encode($answer);
    }
    
    protected function editInSmite($data) {
    	$err = array();
    	$suc = array();
    	parse_str($data['form'], $post);
        
        if (in_array($post['server'], array('na', 'eu'))) {
            $server = $post['server'];
        }
        else {
            $server = 'eu';
        }
        
        if ($this->data->settings['tournament-start-smite-'.$server] == 1) {
            $err['mem1'] = '0;'.t('tournament_in_progress');
        }
        else {
            $players = array();
            $checkForSame = array();
            $summonersNames = array();
            for($i=1;$i<=7;++$i) {
                $post['mem'.$i] = trim($post['mem'.$i]);
                
                if (!$post['mem'.$i] && $i < 6) {
                    $err['mem'.$i] = '0;'.t('field_empty');    
                }
                else if (in_array($post['mem'.$i], $checkForSame)) {
                    $err['mem'.$i] = '0;'.t('same_player_nickname');
                }
                else if ($post['mem'.$i]) {
                    $summonersNames[$i] = rawurlencode(htmlspecialchars($post['mem'.$i]));
                    $checkForSame[] = $post['mem'.$i];
                }
            }
        }
        
        if (!$err) {
            //creating session
            $params['module'] = 'createsession';
            $smiteApiData = $this->runSmiteAPI($params);
            
            for($i=1;$i<=7;++$i) {
                //looping players
                $params = array(
                    'module'    => 'getplayer',
                    'command'   => $summonersNames[$i],
                    'session'   => $smiteApiData['session_id'],
                );
                $response = $this->runSmiteAPI($params);
                if (!$response && $summonersNames[$i]) {
                    $err['mem'.$i] = '0;'.t('player_not_found');
                }
                //else if ($response[0]['Level'] != 30 && $summonersNames[$i]) {
                //    $err['mem'.$i] = '0;'.t('player_low_lvl');
                //}
                else if ($summonersNames[$i]) {
                    $players[$i]['id'] = $response[0]['Id'];
                    $players[$i]['name'] = $response[0]['Name'];
                    $suc['mem'.$i] = '1;'.t('approved');
                }
                
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
            
            Db::query('UPDATE `participants` SET '.
                '`cpt_player_id` = "'.(int)$players[1]['id'].'" '.
                'WHERE `id` = '.(int)$_SESSION['participant']->id.' AND '.
                '`game` = "smite" AND '.
                '`tournament_id` = '.(int)$this->data->settings['smite-current-number-'.$server].' '
            );
            
            Db::query('DELETE FROM `players` '.
                'WHERE `participant_id` = '.(int)$_SESSION['participant']->id.' AND '.
                '`game` = "smite" AND '.
                '`tournament_id` = '.(int)$this->data->settings['smite-current-number-'.$server].' '
            );
            
            foreach($players as $k => $v) {
				Db::query(
					'INSERT INTO `players` SET '.
					' `game` = "smite", '.
					' `tournament_id` = '.(int)$this->data->settings['smite-current-number-'.$server].', '.
					' `participant_id` = '.(int)$_SESSION['participant']->id.', '.
					' `name` = "'.Db::escape($v['name']).'", '.
					' `player_num` = "'.(int)$k.'", '.
					' `player_id` = "'.(int)$v['id'].'"'
				);
			}
    	}
    	 
    	return json_encode($answer);
    }
    
    protected function registerInSmite($data) {
    	$err = array();
    	$suc = array();
    	parse_str($data['form'], $post);
        
        if (in_array($post['server'], array('na', 'eu'))) {
            $server = $post['server'];
        }
        else {
            $server = 'eu';
        }
        
        if ($this->data->settings['tournament-reg-smite-'.$server] != 1) {
            return '0;Server error!';
        }
        
        if (!$post['agree']) {
    		$err['agree'] = '0;'.t('must_agree_with_rules');
    	}
        else {
            $suc['agree'] = '1;'.t('approved');
        }
    	
    	$row = Db::fetchRow('SELECT * FROM `participants` WHERE '.
    		'`tournament_id` = '.(int)$this->data->settings['smite-current-number-'.$server].' AND '.
    		'`name` = "'.Db::escape($post['team']).'" AND '.
            '`server` = "'.Db::escape($server).'" AND '.
    		'`game` = "smite" AND '.
    		'`approved` = 1 AND '.
    		'`deleted` = 0'
    	);

    	if (!$post['team']) {
    		$err['team'] = '0;'.t('field_empty');
    	}
		else if (strlen($post['team']) < 4) {
			$err['team'] = '0;'.t('team_name_small');
		}
		else if (strlen($post['team']) > 60) {
			$err['team'] = '0;'.t('team_name_big');
		}
        else if ($row) {
            $err['team'] = '0;'.t('team_name_taken');
        }
		else {
			$suc['team'] = '1;'.t('approved');
		}
    	
    	if (!$post['email']) {
    		$err['email'] = '0;'.t('field_empty');
    	}
    	else if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
    		$err['email'] = '0;'.t('email_invalid');
    	}
    	else {
    		$suc['email'] = '1;'.t('approved');
    	}
		
		$players = array();
		$checkForSame = array();
        $summonersNames = array();
		for($i=1;$i<=7;++$i) {
            $post['mem'.$i] = trim($post['mem'.$i]);
            
			if (!$post['mem'.$i] && $i < 6) {
				$err['mem'.$i] = '0;'.t('field_empty');    
			}
            else if (in_array($post['mem'.$i], $checkForSame)) {
                $err['mem'.$i] = '0;'.t('same_player_nickname');
            }
			else if ($post['mem'.$i]) {
                $summonersNames[$i] = rawurlencode(htmlspecialchars($post['mem'.$i]));
                $checkForSame[] = $post['mem'.$i];
			}
		}
        
        if (!$err) {
            //creating session
            $params['module'] = 'createsession';
            $smiteApiData = $this->runSmiteAPI($params);
            
            for($i=1;$i<=7;++$i) {
                //looping players
                $params = array(
                    'module'    => 'getplayer',
                    'command'   => $summonersNames[$i],
                    'session'   => $smiteApiData['session_id'],
                );
                $response = $this->runSmiteAPI($params);
                if (!$response && $summonersNames[$i]) {
                    $err['mem'.$i] = '0;'.t('player_not_found');
                }
                //else if ($response[0]['Level'] != 30 && $summonersNames[$i]) {
                //    $err['mem'.$i] = '0;'.t('player_low_lvl');
                //}
                else if ($summonersNames[$i]) {
                    $players[$i]['id'] = $response[0]['Id'];
                    $players[$i]['name'] = $response[0]['Name'];
                    $suc['mem'.$i] = '1;'.t('approved');
                }
                
            }
        }
        
        $addStream = 0;
        if ($post['stream']) {
            $post['stream'] = str_replace(array('http://www.twitch.tv/', 'http://twitch.tv/'), array('',''), $post['stream']);
            
            $twitch = $this->runTwitchAPI($post['stream']);
            
            if (!$twitch) {
                $err['stream'] = '0;'.t('channel_not_found');
            }
            else {
                $addStream = 1;
                $suc['stream'] = '1;'.t('approved');
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
    	
    		$code = substr(sha1(time().rand(0,9999)).$post['team'], 0, 32);
    		Db::query('INSERT INTO `participants` SET '.
	    		'`game` = "smite", '.
                '`user_id` = '.(int)$this->data->user->id.', '.
                '`server` = "'.$server.'", '.
	    		'`tournament_id` = '.(int)$this->data->settings['smite-current-number-'.$server].', '.
	    		'`timestamp` = NOW(), '.
	    		'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
	    		'`name` = "'.Db::escape($post['team']).'", '.
	    		'`email` = "'.Db::escape($post['email']).'", '.
	    		'`contact_info` = "'.Db::escape($team).'", '.
                '`cpt_player_id` = '.(int)$players[1]['id'].', '.
	    		'`link` = "'.$code.'"'
    		);
    	
    		$teamId = Db::lastId();
			
			foreach($players as $k => $v) {
				Db::query(
					'INSERT INTO `players` SET '.
					' `game` = "smite", '.
					' `tournament_id` = '.(int)$this->data->settings['smite-current-number-'.$server].', '.
					' `participant_id` = '.(int)$teamId.', '.
					' `name` = "'.Db::escape($v['name']).'", '.
					' `player_num` = "'.(int)$k.'", '.
					' `player_id` = "'.(int)$v['id'].'"'
				);
			}
            
            if ($addStream == 1) {
                Db::query(
                    'INSERT INTO `streams_events` SET '.
                    '`user_id`  = '.(int)$this->data->user->id.', '.
                    '`participant_id` = '.(int)$teamId.', '.
                    ' `tournament_id` = '.(int)$this->data->settings['smite-current-number-'.$server].', '.
                    '`game` = "smite", '.
                    '`name` = "'.Db::escape($post['stream']).'" '
                );
            }
    		
    		$text = Template::getMailTemplate('reg-smite-team');
    	
    		$text = str_replace(
    			array('%name%', '%teamId%', '%code%', '%url%', '%href%'),
    			array($post['team'], $teamId, $code, _cfg('href').'/smite/'.$server, _cfg('site')),
    			$text
    		);
    	
    		$this->sendMail($post['email'], 'Pentaclick Smite tournament participation', $text);
    	}
    	 
    	return json_encode($answer);
    }
    
    protected function editInLOL($data) {
    	$err = array();
    	$suc = array();
    	parse_str($data['form'], $post);
        
        if (in_array($post['server'], array('eune', 'euw'))) {
            $server = $post['server'];
        }
        else {
            $server = 'euw';
        }
        
        if ($this->data->settings['tournament-start-lol-'.$server] == 1) {
            $err['mem1'] = '0;'.t('tournament_in_progress');
        }
        else {
            $players = array();
            $checkForSame = array();
            $summonersNames = array();
            for($i=1;$i<=7;++$i) {
                $post['mem'.$i] = trim($post['mem'.$i]);
                
                if (!$post['mem'.$i] && $i < 6) {
                    $err['mem'.$i] = '0;'.t('field_empty');    
                }
                else if (in_array($post['mem'.$i], $checkForSame)) {
                    $err['mem'.$i] = '0;'.t('same_summoner');
                }
                else if ($post['mem'.$i]) {
                    $summonersNames[] = rawurlencode(htmlspecialchars($post['mem'.$i]));
                    $checkForSame[] = $post['mem'.$i];
                }
            }
        }
        
        if (!$err) {
            $summonersNames = implode(',', $summonersNames);
            $response = $this->runRiotAPI('/'.$server.'/v1.4/summoner/by-name/'.$summonersNames, $server, true);
            for($i=1;$i<=7;++$i) {
                $name = str_replace(' ', '', mb_strtolower($post['mem'.$i]));
                if (isset($response->$name) && $response->$name) {
                    if ($response->$name->summonerLevel != 30) {
                        $err['mem'.$i] = '0;'.t('summoner_low_lvl');
                    }
                    else {
                        $players[$i]['id'] = $response->$name->id;
                        $players[$i]['name'] = $response->$name->name;
                        $suc['mem'.$i] = '1;'.t('approved');
                    }
                }
                else if ($post['mem'.$i] && !isset($response->$name)) {
                    $err['mem'.$i] = '0;'.t('summoner_not_found_'.$server);
                }
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
    	
    		Db::query('UPDATE `participants` SET '.
                '`cpt_player_id` = "'.(int)$players[1]['id'].'" '.
                'WHERE `id` = '.(int)$_SESSION['participant']->id.' AND '.
                '`game` = "lol" AND '.
                '`tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].' '
            );
            
            Db::query('DELETE FROM `players` '.
                'WHERE `participant_id` = '.(int)$_SESSION['participant']->id.' AND '.
                '`game` = "lol" AND '.
                '`tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].' '
            );
            
            foreach($players as $k => $v) {
				Db::query(
					'INSERT INTO `players` SET '.
					' `game` = "lol", '.
					' `tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].', '.
					' `participant_id` = '.(int)$_SESSION['participant']->id.', '.
					' `name` = "'.Db::escape($v['name']).'", '.
					' `player_num` = "'.(int)$k.'", '.
					' `player_id` = "'.(int)$v['id'].'"'
				);
			}
    	}
    	 
    	return json_encode($answer);
    }
    
    protected function submitContactForm($data) {
    	$form = array();
    	parse_str($data['form'], $form);
    	
    	$row = Db::fetchRow('SELECT `timestamp` FROM `contact_form_timeout`'.
    		'WHERE `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'" AND `timestamp` >= '.time().' '.
    		'LIMIT 1'
    	);
    	 
    	if ($row) {
            $str = str_replace('%timeleft%', $row->timestamp - time(), t('contact_form_ip_timeout'));
    		return '0;'.$str;
    	}
    	else if (!trim($form['name'])) {
    		return '0;'.t('input_name');
    	}
		else if (!trim($form['email']) || !filter_var(trim($form['email']), FILTER_VALIDATE_EMAIL)) {
			return '0;'.t('email_invalid');
    	}
    	
    	$txt = '
    		Name: '.$form['name'].'<br />
    		Email: '.$form['email'].'<br />
    		Subject: '.$form['subject'].'<br />
    		IP: '.$_SERVER['REMOTE_ADDR'].'<br />
    		Message: '.nl2br($form['msg']).'
    	';
    	
    	if ($this->sendMail(_cfg('adminEmail'), 'Contact form submit: '.$form['subject'], $txt)) {
    		Db::query('INSERT INTO `contact_form_timeout` SET '.
    			'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
    			'`timestamp` = '.(time() + 300)
    		);
            
            return '1;'.t('form_success_sent');
    	}
    	
    	return '0;'.t('error_sending_form');
    }
    
    protected function newsVote($data) {
    	$row = Db::fetchRow('SELECT * FROM `blog_likes`'.
    		'WHERE `blog_id` = '.(int)$data['id'].' AND `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
    		'LIMIT 1'
   		);
    	
    	if ($row) {
    		$num = '- 1';
    		Db::query('DELETE FROM `blog_likes`'.
    			'WHERE `blog_id` = '.(int)$data['id'].' AND `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
    			'LIMIT 1'
    		);
    	}
    	else {
    		$num = '+ 1';
    		Db::query('INSERT INTO `blog_likes` SET '.
    			'`blog_id` = '.(int)$data['id'].', '.
    			'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'
    		);
    	}
    	
    	Db::query('UPDATE `blog`'.
    		'SET `likes` = `likes` '.$num.' '.
    		'WHERE `id` = '.(int)$data['id'].' '.
    		'LIMIT 1'
    	);
    	
    	return '1;'.$num;
    }
    
    protected function registerInDota($data) {
    	$err = array();
    	$suc = array();
    	parse_str($data['form'], $post);
        
        if ($this->data->settings['tournament-reg-dota'] != 1) {
            return '0;Server error!';
        }
    	
    	$row = Db::fetchRow('SELECT * FROM `participants` WHERE '.
    		'`tournament_id` = '.(int)$this->data->settings['dota-current-number'].' AND '.
    		'`name` = "'.Db::escape($post['team']).'" AND '.
            '`server` = "'.Db::escape($server).'" AND '.
    		'`game` = "dota" AND '.
    		'`approved` = 1 AND '.
    		'`deleted` = 0'
    	);

    	if (!$post['team']) {
    		$err['team'] = '0;'.t('field_empty');
    	}
		else if (strlen($post['team']) < 4) {
			$err['team'] = '0;'.t('team_name_small');
		}
		else if (strlen($post['team']) > 60) {
			$err['team'] = '0;'.t('team_name_big');
		}
        else if ($row) {
            $err['team'] = '0;'.t('team_name_taken');
        }
		else {
			$suc['team'] = '1;'.t('approved');
		}
    	
    	if (!$post['email']) {
    		$err['email'] = '0;'.t('field_empty');
    	}
    	else if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
    		$err['email'] = '0;'.t('email_invalid');
    	}
    	else {
    		$suc['email'] = '1;'.t('approved');
    	}
		
		$players = array();
		$checkForSame = array();
		for($i=1;$i<=7;++$i) {
            $post['mem'.$i] = trim($post['mem'.$i]);
            
			if (!$post['mem'.$i] && $i < 6) {
				$err['mem'.$i] = '0;'.t('field_empty');    
			}
			else if ($post['mem'.$i]) {
                $accountId = $i;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://steamcommunity.com/id/'.rawurlencode(htmlspecialchars($post['mem'.$i])).'?xml=1'); // set url to post to
                curl_setopt($ch, CURLOPT_FAILONERROR, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
                curl_setopt($ch, CURLOPT_TIMEOUT, 5); // times out after 2s
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_POST, 0); // set POST method
                $response = curl_exec($ch); // run the whole process 
                curl_close($ch);
                $response = new SimpleXMLElement($response);
                
                if (!$response || $response->error) {
                    $err['mem'.$i] = '0;'.t('user_not_found');
                }
                else {
                    $accountId = (int)$response->steamID64 - 76561197960265728;

                    $params = array(
                        'module' => 'IDOTA2Match_570/GetMatchHistory/v001',
                        'get' => 'matches_requested=1&account_id='.$accountId,
                    );
                    $response = $this->runDotaAPI($params);
                    
                    if ($response['result']['status'] == 15 || !$response) {
                        $err['mem'.$i] = '0;'.t('dota_user_not_public');
                    }
                    else if (in_array($accountId, $checkForSame)) {
                        $err['mem'.$i] = '0;'.t('same_player');
                    }
                    else {
                        $players[$i]['id'] = $accountId;
                        $players[$i]['name'] = Db::escape($post['mem'.$i]);
                        $suc['mem'.$i] = '1;'.t('approved');
                        
                    }
                }
                
                $checkForSame[] = $accountId;
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
    	
    		$code = substr(sha1(time().rand(0,9999)).$post['team'], 0, 32);
    		Db::query('INSERT INTO `participants` SET '.
	    		'`game` = "dota", '.
                '`user_id` = '.(int)$this->data->user->id.', '.
                '`server` = "'.$server.'", '.
	    		'`tournament_id` = '.(int)$this->data->settings['dota-current-number'].', '.
	    		'`timestamp` = NOW(), '.
	    		'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
	    		'`name` = "'.Db::escape($post['team']).'", '.
	    		'`email` = "'.Db::escape($post['email']).'", '.
	    		'`contact_info` = "'.Db::escape($team).'", '.
                '`cpt_player_id` = '.(int)$players[1]['id'].', '.
	    		'`link` = "'.$code.'"'
    		);
    	
    		$teamId = Db::lastId();
			
			foreach($players as $k => $v) {
				Db::query(
					'INSERT INTO `players` SET '.
					' `game` = "dota", '.
					' `tournament_id` = '.(int)$this->data->settings['dota-current-number'].', '.
					' `participant_id` = '.(int)$teamId.', '.
					' `name` = "'.Db::escape($v['name']).'", '.
					' `player_num` = "'.(int)$k.'", '.
					' `player_id` = "'.(int)$v['id'].'"'
				);
			}
    		
    		$text = Template::getMailTemplate('reg-dota-team');
    	
    		$text = str_replace(
    			array('%name%', '%teamId%', '%code%', '%url%', '%href%'),
    			array($post['team'], $teamId, $code, _cfg('href').'/dota', _cfg('site')),
    			$text
    		);
    	
    		$this->sendMail($post['email'], 'Pentaclick DotA 2 tournament participation', $text);
    	}
    	 
    	return json_encode($answer);
    }
}
