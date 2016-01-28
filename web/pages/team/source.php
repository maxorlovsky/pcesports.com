<?php

class team extends System
{
    public $team;
    public $data;

	public function __construct($params = array()) {
        parent::__construct();
	}

	public function showTemplate() {
        $this->team = Db::fetchRow(
            'SELECT `t`.*, COUNT(`tu`.`user_id`) AS `members_count` '.
            'FROM `teams` AS `t` '.
            'LEFT JOIN `teams2users` AS `tu` ON `tu`.`team_id` = `t`.`id` '.
            'WHERE LOWER(`name`) = "'.Db::escape(strtolower(urldecode($_GET['val2']))).'" '.
            'AND `t`.`status` = 0 '.
            'LIMIT 1 '
        );

        if (!$this->team) {
            include_once _cfg('pages').'/404/error.tpl';
        }
        else {
            $this->team->members = Db::fetchRows(
                'SELECT `u`.*, `t2u`.`title`, `s`.`name` AS `summoner`, `s`.`division`, `s`.`league` '.
                'FROM `teams2users` AS `t2u` '.
                'LEFT JOIN `users` AS `u` ON `t2u`.`user_id` = `u`.`id` '.
                'LEFT JOIN `summoners` AS `s` ON `u`.`id` = `s`.`user_id` AND `s`.`approved` = 1 '.
                'WHERE `t2u`.`team_id` = '.$this->team->id
            );

            $isMember = 0;
            $requstToJoin = 0;

            foreach($this->team->members as $v) {
                if ($v->id == $this->data->user->id) {
                    $isMember = 1;
                    continue;
                }
            }

            if ($isMember === 0) {
                $q = Db::query(
                    'SELECT * FROM `teams2users_queue` '.
                    'WHERE `team_id` = '.(int)$this->team->id.' '.
                    'AND `user_id` = '.(int)$this->data->user->id.' '.
                    'LIMIT 1'
                );

                //Not added to group, adding request
                if ($q->num_rows !== 0) {
                    $requstToJoin = 1;
                }
            }

            if ($this->team->user_id_captain == $this->data->user->id) {
                $requestsCount = Db::fetchRow(
                    'SELECT COUNT(`user_id`) AS `count` FROM `teams2users_queue` '.
                    'WHERE `team_id` = '.(int)$this->team->id
                );
            }

            //Checks if this is the captain of a team and trying to enter manage page
            if (isset($_GET['val3']) && $_GET['val3'] == 'manage' && $this->team->user_id_captain == $this->data->user->id) {
                $requestsRow = Db::fetchRows(
                    'SELECT `u`.`id`, `u`.`name`, `u`.`avatar`, `t2uq`.`added_date` '.
                    'FROM `teams2users_queue` AS `t2uq` '.
                    'LEFT JOIN `users` AS `u` ON `u`.`id` = `t2uq`.`user_id` '.
                    'WHERE `team_id` = '.(int)$this->team->id
                );

                include_once _cfg('pages').'/'.get_class().'/manage.tpl';
            }
            else {
                include_once _cfg('pages').'/'.get_class().'/index.tpl';
            }
        }
	}

    public static function getSeo() {
        $team = Db::fetchRow(
            'SELECT * FROM `teams` '.
            'WHERE LOWER(`name`) = "'.Db::escape(strtolower(urldecode($_GET['val2']))).'" '.
            'AND `status` = 0 '.
            'LIMIT 1 '
        );
        
        $seo = array(
            'title' => $team->name.' | Team profile',
            //'ogImg' => ($news->extension?_cfg('imgu').'/blog/small-'.$news->id.'.'.$news->extension:null),
            //'ogDesc' => strip_tags($news->short_english),
        );
        
        return (object)$seo;
    }

    public function editTeam($data) {
        if (!$this->logged_in) {
            return $this->errorLogin();
        }

        $error = array();
        $object = new stdClass();

        if ($data['description'] && strlen($data['description']) > 500) {
            $error['description'] = t('team_description_is_too_big');
        }

        if ($error) {
            return $this->errorMessage($error);
        }
        
        Db::query(
            'UPDATE `teams` SET '.
            '`description` = "'.Db::escape_tags($data['description']).'", '.
            '`website` = "'.Db::escape_tags($data['website']).'", '.
            '`facebook` = "'.Db::escape_tags($data['facebook']).'", '.
            '`twitter` = "'.Db::escape_tags($data['twitter']).'", '.
            '`twitch_tv` = "'.Db::escape_tags($data['twitch_tv']).'" '.
            'WHERE `id` = '.(int)$data['team_id'].' '.
            'AND `user_id_captain` = '.(int)$this->data->user->id.' '
        );
        
        $object->status = 200;
        $object->message = t('information_updated');

        return $object;
    }

    public function requestJoin($data) {
        $q = Db::query(
            'SELECT * FROM `teams2users_queue` '.
            'WHERE `team_id` = '.(int)$data['id'].' '.
            'AND `user_id` = '.(int)$this->data->user->id.' '.
            'LIMIT 1'
        );

        //Not added to group, adding request
        if ($q->num_rows === 0) {
            $message = t('request_to_join_team_sent');
            $buttonMessage = t('cancel_join_minus');
            $buttonHint = t('join_team_request_cancel_hint');
            Db::query(
                'INSERT INTO `teams2users_queue` '.
                'SET `team_id` = '.(int)$data['id'].', '.
                '`user_id` = '.(int)$this->data->user->id
            );
        }
        //Already added, canceling request
        else {
            $message = t('request_to_join_team_canceled');
            $buttonMessage = t('join_team_plus');
            $buttonHint = t('join_team_request_hint');
            Db::query(
                'DELETE FROM `teams2users_queue` '.
                'WHERE `team_id` = '.(int)$data['id'].' '.
                'AND `user_id` = '.(int)$this->data->user->id.' '.
                'LIMIT 1 '
            );
        }

        $answer = '1;'.$message.';'.$buttonMessage.';'.$buttonHint;

        return $answer;
    }

    public function accept($data) {
        $row = Db::fetchRow('SELECT `user_id_captain`, COUNT(*) AS `count` FROM `teams` WHERE `id` = '.(int)$data['id'].' AND `status` = 0');
        if ($row->user_id_captain != $this->data->user->id) {
            return '0;Sorry, not a captain';
        }

        if ($row->count >= 7) {
            return '0;'.t('too_much_members_in_team');
        }

        $row = Db::fetchRow(
            'SELECT * FROM `teams2users_queue` '.
            'WHERE `team_id` = '.(int)$data['id'].' '.
            'AND `user_id` = '.(int)$data['user_id'].' '.
            'LIMIT 1'
        );

        if (!$row) {
            return '0;Record not found';
        }

        Db::query(
            'DELETE FROM `teams2users_queue` '.
            'WHERE `team_id` = '.(int)$data['id'].' '.
            'AND `user_id` = '.(int)$data['user_id'].' '.
            'LIMIT 1 '
        );

        Db::query(
            'INSERT INTO `teams2users` '.
            'SET `team_id` = '.(int)$data['id'].', '.
            '`user_id` = '.(int)$data['user_id']
        );

        return '1;1';
    }

    public function reject($data) {
        $team = Db::fetchRow('SELECT `user_id_captain` FROM `teams` WHERE `id` = '.(int)$data['id'].' AND `status` = 0');
        if ($team->user_id_captain != $this->data->user->id) {
            return '0;Sorry, not a captain';
        }

        $row = Db::fetchRow(
            'SELECT * FROM `teams2users_queue` '.
            'WHERE `team_id` = '.(int)$data['id'].' '.
            'AND `user_id` = '.(int)$data['user_id'].' '.
            'LIMIT 1'
        );

        if (!$row) {
            return '0;Record not found';
        }

        Db::query(
            'DELETE FROM `teams2users_queue` '.
            'WHERE `team_id` = '.(int)$data['id'].' '.
            'AND `user_id` = '.(int)$data['user_id'].' '.
            'LIMIT 1 '
        );

        return '1;1';
    }

    public function leave($data) {
        Db::query(
            'DELETE FROM `teams2users` '.
            'WHERE `team_id` = '.(int)$data['id'].' '.
            'AND `user_id` = '.(int)$this->data->user->id.' '.
            'LIMIT 1 '
        );

        return '1;1';
    }

    public function remove($data) {
        $team = Db::fetchRow('SELECT `user_id_captain` FROM `teams` WHERE `id` = '.(int)$data['id'].' AND `status` = 0');
        if ($team->user_id_captain != $this->data->user->id) {
            return '0;Sorry, not a captain';
        }

        $row = Db::fetchRow(
            'SELECT * FROM `teams2users` '.
            'WHERE `team_id` = '.(int)$data['id'].' '.
            'AND `user_id` = '.(int)$data['user_id'].' '.
            'LIMIT 1'
        );

        if (!$row) {
            return '0;Record not found';
        }

        Db::query(
            'DELETE FROM `teams2users` '.
            'WHERE `team_id` = '.(int)$data['id'].' '.
            'AND `user_id` = '.(int)$data['user_id'].' '.
            'LIMIT 1 '
        );

        return '1;1';
    }

    public function changeCaptain($data) {
        $team = Db::fetchRow('SELECT `user_id_captain` FROM `teams` WHERE `id` = '.(int)$data['id'].' AND `status` = 0');
        if ($team->user_id_captain != $this->data->user->id) {
            return '0;Sorry, not a captain';
        }

        $row = Db::fetchRow(
            'SELECT * FROM `teams2users` '.
            'WHERE `team_id` = '.(int)$data['id'].' '.
            'AND `user_id` = '.(int)$data['user_id'].' '.
            'LIMIT 1'
        );

        if (!$row) {
            return '0;Record not found';
        }

        Db::query(
            'UPDATE `teams` SET '.
            '`user_id_captain` = '.(int)$data['user_id'].' '.
            'WHERE `id` = '.(int)$data['id'].' '.
            'AND `status` = 0 '.
            'LIMIT 1'
        );

        Db::query(
            'UPDATE `teams2users` SET '.
            '`title` = "Member" '.
            'WHERE `team_id` = '.(int)$data['id'].' '.
            'AND `user_id` = '.(int)$this->data->user->id.' '.
            'LIMIT 1'
        );

        Db::query(
            'UPDATE `teams2users` SET '.
            '`title` = "Captain" '.
            'WHERE `team_id` = '.(int)$data['id'].' '.
            'AND `user_id` = '.(int)$data['user_id'].' '.
            'LIMIT 1'
        );

        return '1;1';
    }
    
}