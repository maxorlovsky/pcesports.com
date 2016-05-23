<?php
class Ajax extends System
{
    public function __construct() {
        parent::__construct();
    }
	
    public function ajaxRun($data, $type) {
    	$controller = $data['ajax'];
        
        if (method_exists($this, $controller)) {
            if ($type == 'json') {
                $answer = $this->$controller($data);
                if (!$answer->status) {
                    header('HTTP/1.1 400 Bad request', true, 400);
                }
                else {
                    header('HTTP/1.1 '.$answer->status.' OK', true, $answer->status);//$answer->message
                }
                echo json_encode($answer);
            }
            else {
                echo $this->$controller($data);
            }
            return true;
        }
        else {
            if ($type == 'json') {
                header('HTTP/1.1 404 '.t('controller_not_exist'), true, 404);
                $array = array('message' => t('controller_not_exist'));
                echo json_encode($array);
            }
            else {
                echo '0;'.t('controller_not_exist');
            }
            return false;
        }
    }

    protected function checkAchievements() {
        $achievements = new Achievements();
        $achievements->init();
    }

    /*
     * Boards functions
     * Vote/Submit(edit/delete/add)
    */
    protected function boardVote($data) {
        require_once _cfg('pages').'/boards/source.php';
        $board = new boards();
        return $board->vote($data);
    }
    
    protected function boardSubmit($data) {
        require_once _cfg('pages').'/boards/source.php';
        $board = new boards();
        return $board->submit($data);
    }
    
    /*
     * Profile functions
     * Add/Remove/Verify
    */
    protected function summonerVerify($data) {
        require_once _cfg('pages').'/profile/source.php';
        $profile = new profile();
        return $profile->verifySummoner($data);
    }
    
    protected function summonerRemove($data) {
        require_once _cfg('pages').'/profile/source.php';
        $profile = new profile();
        return $profile->removeSummoner($data);
    }
    
    protected function summonerAdd($data) {
        require_once _cfg('pages').'/profile/source.php';
        $profile = new profile();
        return $profile->addSummoner($data);
    }

    /*
     * Teams functions
     * Add/Edit/Remove
    */
    protected function addTeam($data) {
        require_once _cfg('pages').'/profile/source.php';
        $profile = new profile();
        parse_str($data['form'], $post);
        return $profile->addTeam($post);
    }
    protected function editTeam($data) {
        require_once _cfg('pages').'/team/source.php';
        $team = new team();
        parse_str($data['form'], $post);
        return $team->editTeam($post);
    }
    protected function requestJoinTeam($data) {
        require_once _cfg('pages').'/team/source.php';
        $team = new team();
        return $team->requestJoin($data);
    }
    protected function acceptToTeam($data) {
        require_once _cfg('pages').'/team/source.php';
        $team = new team();
        return $team->accept($data);
    }
    protected function rejectFromTeam($data) {
        require_once _cfg('pages').'/team/source.php';
        $team = new team();
        return $team->reject($data);
    }
    protected function leaveTeam($data) {
        require_once _cfg('pages').'/team/source.php';
        $team = new team();
        return $team->leave($data);
    }
    protected function removeTeamMember($data) {
        require_once _cfg('pages').'/team/source.php';
        $team = new team();
        return $team->remove($data);
    }
    protected function changeTeamCaptain($data) {
        require_once _cfg('pages').'/team/source.php';
        $team = new team();
        return $team->changeCaptain($data);
    }
    
    /*
     * Widgets functions
    */
    protected function registerInExternalHs($data) {
        require_once _cfg('pages').'/widgets/hearthstone/source.php';
        $hearthstoneExternal = new hearthstone($data['project']);
        return $hearthstoneExternal->registerInTournament($data);
    }
    protected function editInExternalHs($data) {
        require_once _cfg('pages').'/widgets/hearthstone/source.php';
        $hearthstoneExternal = new hearthstone($data['project']);
        return $hearthstoneExternal->editInTournament($data);
    }
    
    //Function for NodeJS, to get active players
    protected function getHsList() {
        require_once _cfg('pages').'/hearthstone/source.php';
        $hearthstone = new hearthstone();
        return $hearthstone->getNodeList();
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

                if ($v->status != 1) {
                    $text = $this->parseText($v->text);
                }
                else {
                    $text = '<span class="deleted">'.t('deleted').'</span>';
                }
                
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
                '`ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'" '
            );
            
            Db::query(
                'UPDATE `blog` SET `comments` = `comments` + 1 '.
                'WHERE `id` = '.(int)$data['id'].' '
            );

            Achievements::give(array(27,28,29));//I'm afraid of people (Post * comments on boards or articles).
            
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
    protected function updateEmail($data) {
        parse_str($data['form'], $post);
        return User::updateEmail($post);
    }
    protected function updatePassword($data) {
        parse_str($data['form'], $post);
        return User::updatePassword($post);
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
    
    protected function login($data) {
        return User::login($data['email'], $data['password']);
    }
    protected function register($data) {
        return User::registerSimple($data);
    }
    protected function restorePassword($data) {
        return User::restorePassword($data);
    }
    protected function restorePasswordCode($data) {
        require_once _cfg('pages').'/restoration/source.php';
        $restoration = new restoration();
        return $restoration->restorePassword($data);
    }
    protected function socialLogin($data) {
        $social = new Social();
        return $social->getToken($data['provider']);
    }

    protected function submitContactForm($data) {
        require_once _cfg('pages').'/contacts/source.php';
        $contacts = new contacts();
        return $contacts->submit($data);
    }
    
    /* blogVote actually */
    protected function newsVote($data) {
        require_once _cfg('pages').'/blog/source.php';
        $blog = new blog();
        return $blog->vote($data);
    }

    protected function chatExternal($data) {
        if (!$data['id']) {
            return false;
        }

        $participant = Db::fetchRow('SELECT * '.
            'FROM `participants_external` '.
            'WHERE '.
            '`project` = "skillz" AND '.
            '`id` = '.(int)$data['id'].' AND '.
            '`link` = "'.Db::escape($data['link']).'" AND '.
            '`deleted` = 0 AND '.
            '`ended` = 0 '
        );
        if ($participant) {
            $challonge_id = $participant->challonge_id;
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
            'LEFT JOIN `participants_external` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
            'LEFT JOIN `participants_external` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
            'WHERE (`f`.`player1_id` = '.(int)$participant->challonge_id.' OR `f`.`player2_id` = '.(int)$participant->challonge_id.') '.
            'AND`f`.`done` = 0'
        );
        
        if ($playersRow) {
            $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/ext_'.(int)$playersRow->id1.'_vs_'.(int)$playersRow->id2.'.txt';
            
            $file = fopen($fileName, 'a');
            if ($data['action'] == 'send') {
                $content = '<div class="'.($participant->id==$playersRow->id1?'player1':'player2').'">';
                $content .= '<div class="message">'.$data['text'].'</div>';
                $content .= '<span>'.($participant->id==$playersRow->id1?$playersRow->name1:$playersRow->name2).'</span>';
                $content .= '&nbsp;•&nbsp;<span id="notice">'.date('H:i', time()).'</span>';
                $content .= '</div>';

                fwrite($file, htmlspecialchars($content));
            }
            fclose($file);
            
            $chat = strip_tags(stripslashes(html_entity_decode(file_get_contents($fileName))), '<div><p><b><a><u><span>');
            
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
                //Escape of breaking characters
                $data['text'] = str_replace(
                    array('<', '>'),
                    '',
                    $data['text']
                );

                $content = '<div class="'.($_SESSION['participant']->id==$playersRow->id1?'player1':'player2').'">';
                $content .= '<div class="message">'.$data['text'].'</div>';
                $content .= '<span>'.($_SESSION['participant']->id==$playersRow->id1?$playersRow->name1:$playersRow->name2).'</span>';
                $content .= '&nbsp;•&nbsp;<span id="notice">'.date('H:i', time()).'</span>';
                $content .= '</div>';

                fwrite($file, htmlspecialchars($content));
            }
            fclose($file);
            
            $chat = strip_tags(stripslashes(html_entity_decode(file_get_contents($fileName))), '<div><p><b><a><u><span>');
            
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
}
