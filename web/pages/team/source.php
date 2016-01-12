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

            //Checks if this is the captain of a team and trying to enter manage page
            if (isset($_GET['val3']) && $_GET['val3'] == 'manage' && $this->team->user_id_captain == $this->data->user->id) {
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
}