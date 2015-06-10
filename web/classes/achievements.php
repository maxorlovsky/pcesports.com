<?php
class Achievements extends System
{
    public function __construct() {
        parent::__construct();
    }

    // Initiate class, adding first achievements to user
    // If it was initiated, just checking achievement and confirming them
    public function init() {
    	//Achievement not initiated, initiating on the first run
    	if ($this->data->user->achievements_initiate == 0) {
    		$this->fetchNewAchievements();
    		return true;
		}

		// Checking timestamp, running check every hour
		if ($this->data->user->achievements_initiate < time()) {
			echo $this->confirmingAchievements();
			return true;
		}

		return false;
    }

    public static function give($achievementId) {
    	$achievement = new Achievements();
    	return $achievement->addAchievementTick($achievementId);
    }

    // Adding +1 tick to specific achievement to logged in user
    public function addAchievementTick($achievementId) {
    	if (!$achievementId || !$this->data->user->id) {
    		return false;
    	}

    	if (is_array($achievementId)) {
    		foreach($achievementId as $v) {
    			$row = Db::fetchRow(
		    		'SELECT * '.
		    		'FROM `users_achievements` '.
		    		'WHERE `achievement_id` = '.(int)$v.' AND '.
		    		'`user_id` = '.(int)$this->data->user->id.' AND  '.
		    		'`done` = 0 '
				);

				if ($row) {
					Db::query(
						'UPDATE `users_achievements` '.
						'SET `current` = `current` + 1 '.
						'WHERE `achievement_id` = '.(int)$v.' AND '.
						'`user_id` = '.(int)$this->data->user->id.' AND '.
						'`done` = 0 '
					);
				}
				else {
					Db::query(
						'INSERT INTO `users_achievements` '.
						'SET `achievement_id` = '.(int)$v.', '.
						'`current` = 1, '.
						'`user_id` = '.(int)$this->data->user->id
					);
				}
    		}
    	}
    	else {
	    	$row = Db::fetchRow(
	    		'SELECT * '.
	    		'FROM `users_achievements` '.
	    		'WHERE `achievement_id` = '.(int)$achievementId.' AND '.
	    		'`user_id` = '.(int)$this->data->user->id.' AND  '.
	    		'`done` = 0 '
			);

			if ($row) {
				Db::query(
					'UPDATE `users_achievements` '.
					'SET `current` = `current` + 1 '.
					'WHERE `achievement_id` = '.(int)$achievementId.' AND '.
					'`user_id` = '.(int)$this->data->user->id.' AND '.
					'`done` = 0 '
				);
			}
			else {
				Db::query(
					'INSERT INTO `users_achievements` '.
					'SET `achievement_id` = '.(int)$achievementId.', '.
					'`current` = 1, '.
					'`user_id` = '.(int)$this->data->user->id
				);
			}
		}

		return true;
    }

    public static function giveLogin($uid) {
		$achievementId = array(12,13,14);

		foreach($achievementId as $v) {
			$row = Db::fetchRow(
	    		'SELECT * '.
	    		'FROM `users_achievements` '.
	    		'WHERE `achievement_id` = '.(int)$v.' AND '.
	    		'`user_id` = '.(int)$uid.' AND  '.
	    		'`done` = 0 '
			);

			if ($row) {
				Db::query(
					'UPDATE `users_achievements` '.
					'SET `current` = `current` + 1 '.
					'WHERE `achievement_id` = '.(int)$v.' AND '.
					'`user_id` = '.(int)$uid.' AND '.
					'`done` = 0 '
				);
			}
			else {
				Db::query(
					'INSERT INTO `users_achievements` '.
					'SET `achievement_id` = '.(int)$v.', '.
					'`current` = 1, '.
					'`user_id` = '.(int)$uid
				);
			}
		}

		return true;
    }

    // This function just check current "non-closed" achievements of user
    // and closing it with message display
    // If criteria matched, on next run function will run again
    private function confirmingAchievements() {
    	$nextCheckTimespan = 3600;

    	$achievements = Db::fetchRows(
    		'SELECT `ua`.`current`, `a`.* '.
    		'FROM `users_achievements` AS `ua` '.
    		'LEFT JOIN `achievements` AS `a` ON `ua`.`achievement_id` = `a`.`id`'.
    		'WHERE `ua`.`user_id` = '.(int)$this->data->user->id.' AND '.
    		'`ua`.`done` = 0 '
		);
    	
    	$points = 0;
    	$returnObject = new stdClass();
    	//Looping and searching if criteria match
		foreach($achievements as $f) {
			//Criteria match, can close achievement
			if ($f->current >= $f->requirement) {
				//Closing achievement
				Db::query(
					'UPDATE `users_achievements` '.
					'SET `done` = 1, '.
					'`date` = NOW() '.
					'WHERE `achievement_id` = '.(int)$f->id.' AND '.
					'`user_id` = '.(int)$this->data->user->id.' AND '.
					'`done` = 0 '
				);

				$returnObject->id = $f->id;
				$returnObject->name = $f->name;
				$returnObject->description = $f->description;
				if ($f->image) {
					$f->image = _cfg('img').'/achievements/'.$f->image;
				}
				$returnObject->image = $f->image;
				$returnObject->points = $f->points;

				//Adding points to user and
				Db::query('UPDATE `users` '.
					'SET `experience` = `experience` + '.(int)$returnObject->points.' '.
					'WHERE `id` = '.(int)$this->data->user->id
				);

				return json_encode($returnObject);
			}
		}
		
		//Nothing, just updating timespan for next achievement check and returning false
		Db::query('UPDATE `users` '.
			'SET `achievements_initiate` =  '.(time()+$nextCheckTimespan).' '.
			'WHERE `id` = '.(int)$this->data->user->id
		);

		return false;
    }

    // This function must run only once, to initiate achievements and set
    // add registration/social networks achievements to the user.
    private function fetchNewAchievements() {
    	Db::query('INSERT INTO `users_achievements` SET '.
    		'`user_id` = '.(int)$this->data->user->id.', '.
    		'`achievement_id` = 1, '.
    		'`current` = 1 '
		);

		$socialAchievements = array(
    		'fb' => 2, //Facebook connection achievement
    		'tw' => 3, //Twitter achievement
    		'vk' => 4, //VK achievement
    		'gp' => 5, //Google+ achievement
    		'tc' => 6, //Twitch achievement
    		'bn' => 7, //Battle.Net achievement
		);

		foreach($this->data->user->socials->connected as $f) {
			Db::query('INSERT INTO `users_achievements` SET '.
	    		'`user_id` = '.(int)$this->data->user->id.', '.
	    		'`achievement_id` = '.(int)$socialAchievements[$f].', '.
	    		'`current` = 1 '
			);
		}

		Db::query('UPDATE `users` SET '.
    		'`achievements_initiate` = 1 '.
    		'WHERE `id` = '.(int)$this->data->user->id
		);
    }

}