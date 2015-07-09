<?php

class boards extends System
{
	public $boards;
    public $page;
    public $pages;
	
	public function __construct($params = array()) {
		parent::__construct();
	}
    
    public function getBoardsList() {
        $pagesData = array(
            'countPerPage'  => 10,
            'maxNumShow'    => 5,
            'tableName'     => 'boards',
            'pageNum'       => $_GET['val3'],
        );
        
        $this->pages = pages($pagesData);
        
        $additionalSelect = '';
        $additionalSql = '';
        if ($this->logged_in) {
            $additionalSelect .= ', `bv`.`direction`';
            $additionalSql .= 'LEFT JOIN `boards_votes` AS `bv` ON `b`.`id` = `bv`.`board_id` AND `bv`.`user_id` = '.(int)$this->data->user->id.' ';
        }
        
		$this->boards = Db::fetchRows('SELECT `b`.`id`, `b`.`title`, `b`.`category`, `b`.`added`, `b`.`votes`, `b`.`comments`, `b`.`user_id`, `b`.`edited`, `b`.`status`, `u`.`name`, `u`.`avatar` '.$additionalSelect.
			'FROM `boards` AS `b` '.
            $additionalSql.
            'LEFT JOIN `users` AS `u` ON `b`.`user_id` = `u`.`id` '.
			'ORDER BY `activity` DESC '.
			'LIMIT '.(int)$this->pages->start.', '.(int)$this->pages->countPerPage
		);
        
        $currDate = new DateTime();
        
        foreach($this->boards as &$v) {
            $dbDate = new DateTime($v->added);
            $v->interval = $this->getAboutTime($currDate->diff($dbDate));
        }
        unset($v);
        
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public function getBoard() {
        $additionalSelect = '';
        $additionalSql = '';
        if ($this->logged_in) {
            $additionalSelect .= ', `bv`.`direction`';
            $additionalSql .= 'LEFT JOIN `boards_votes` AS `bv` ON `b`.`id` = `bv`.`board_id` AND `bv`.`user_id` = '.(int)$this->data->user->id.' ';
        }
        
        $row = Db::fetchRow('SELECT `b`.`id`, `b`.`title`, `b`.`text`, `b`.`category`, `b`.`added`, `b`.`votes`, `b`.`comments`, `b`.`user_id`, `b`.`edited`, `b`.`status`, `u`.`name`, `u`.`avatar` '.$additionalSelect.
			'FROM `boards` AS `b` '.
			$additionalSql.
            'LEFT JOIN `users` AS `u` ON `b`.`user_id` = `u`.`id` '.
            'WHERE `b`.`id` = '.(int)$_GET['val2'].' '.
			'LIMIT 1'
		);
        
        if (!$row) {
            go(_cfg('href').'/boards');
        }
        
        $currDate = new DateTime();
        $dbDate = new DateTime($row->added);
        $row->interval = $this->getAboutTime($currDate->diff($dbDate));
        
		$this->comments = Db::fetchRows('SELECT `bc`.`id`, `bc`.`user_id`, `bc`.`answer_to_id`, `bc`.`text`, `bc`.`added`, `bc`.`votes`, `bc`.`status`, `u`.`name`, `u`.`avatar`, `bc`.`edited` '.
			'FROM `boards_comments` AS `bc` '.
            'LEFT JOIN `users` AS `u` ON `bc`.`user_id` = `u`.`id` '.
			'WHERE `bc`.`board_id` = '.(int)$_GET['val2'].' '.
			'ORDER BY `id` DESC '
		);
        
        if ($this->comments) {
            foreach($this->comments as &$v) {
                $dbDate = new DateTime($v->added);
                $v->interval = $this->getAboutTime($currDate->diff($dbDate));
            }
            unset($v);
        }
		
		include_once _cfg('pages').'/'.get_class().'/board.tpl';
	}
    
    public function submitPage() {
        if (isset($_GET['val3']) && $this->logged_in) {
            $row = Db::fetchRow(
                'SELECT * '.
                'FROM `boards` '.
                'WHERE `id` = '.(int)$_GET['val3'].' AND '.
                '`user_id` = '.(int)$this->data->user->id.' AND '.
                '`status` != 1 '.
                'LIMIT 1'
            );
            
            if (!$row) {
                go(_cfg('href').'/boards');
                exit();
            }
        }
        include_once _cfg('pages').'/'.get_class().'/submit.tpl';
    }
	
	public function showTemplate() {
        
        if (isset($_GET['val2']) && $_GET['val2'] == 'submit' ) { //&& $this->logged_in
			$this->submitPage();
		}
		else if (isset($_GET['val1']) && $_GET['val1'] == 'boards' && isset($_GET['val2']) && $_GET['val2'] != 'page') {
			$this->getBoard();
		}
		else {
			$this->getBoardsList();
		}
	}
    
    public static function getSeo() {
        $board = Db::fetchRow('SELECT `id`, `title` '.
			'FROM `boards` '.
			'WHERE (`status` = 0 OR `status` = 2) AND `id` = '.(int)$_GET['val2'].' '.
			'ORDER BY `id` DESC '.
			'LIMIT 1'
		);
        
        $seo = array(
            'title' => ($board->title?strip_tags($board->title).' | ':null).'Boards',
            'ogDesc' => strip_tags($board->title),
        );
        
        return (object)$seo;
    }
    
    /*
    Function from AJAX class
    */
    public function vote($data) {
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
    
    public function submit($data) {
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

            Achievements::give(array(27,28,29));//I'm afraid of people (Post * comments on boards or articles).
            
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

            Achievements::give(array(27,28,29));//I'm afraid of people (Post * comments on boards or articles).
            
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
}