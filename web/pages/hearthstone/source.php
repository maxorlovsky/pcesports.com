<?php

class hearthstone
{
	public $teamsCount;
	public $currentTournament = 4;
	public $teamsPlaces;
	public $participants;
	
	public function __construct($params = array()) {

	}
	
	public function participantPage() {
		$verified = 0;
		$regged = 0;
		
		if (isset($_GET['val3']) && $_GET['val3'] == 'exit') {
			unset($_SESSION['participant']);
			go(_cfg('href').'/hearthstone');
		}
		
		if (isset($_GET['val3']) && $_GET['val3'] == 'leave' && isset($_SESSION['participant']) && $_SESSION['participant']->id) {
			Db::query('UPDATE `teams` SET `deleted` = 1 '.
			'WHERE `game` = "hs" AND '.
			'`id` = '.(int)$_SESSION['participant']->id.' AND '. 
			'`link` = "'.Db::escape($_SESSION['participant']->link).'" ');
			
			$apiArray = array(
				'_method' => 'delete',
			);
			$this->runChallongeAPI('tournaments/pentaclick-hs'.(int)$this->currentTournament.'/participants/'.$_SESSION['participant']->challonge_id.'.post', $apiArray);
			
			unset($_SESSION['participant']);
			
			go(_cfg('href').'/hearthstone');
		}
		
		if (!isset($_GET['val4']) && !$_GET['val4'] && !$_SESSION['participant'] && !$_SESSION['participant']->id) {
			go(_cfg('href').'/hearthstone');
		}
		
		if (isset($_SESSION['participant'])) {
			$id = $_SESSION['participant']->id;
			$code = $_SESSION['participant']->link;
		}
		else {
			$id = (int)$_GET['val3'];
			$code = $_GET['val4'];
		}
		
		$row = Db::fetchRow(
			'SELECT * '.
			'FROM `teams` AS `t` '.
			'WHERE '.
			'`t`.`tournament_id` = '.(int)$this->currentTournament.' AND '.
			'`t`.`game` = "hs" AND '.
			'`t`.`id` = '.Db::escape($id).' AND '.
			'`t`.`link` = "'.Db::escape($code).'" AND '.
			'`t`.`deleted` = 0 AND '.
			'`t`.`ended` = 0'
		);
		if ($row && $row->approved == 0) {
			//Not approved, registration open, approving and adding to brackets
			$row->challonge_id = $this->approveRegisterPlayer($row);
			$verified = 1;
			$regged = 1;
			
			$_SESSION['participant'] = $row;
		}
		else if ($row && $row->approved == 1) {
			$verified = 1;
			
			$_SESSION['participant'] = $row;
		}
		
		if ($verified == 1) {
			$_SESSION['participant'] = $row;
			
			include_once _cfg('pages').'/'.get_class().'/participant-page.tpl';
		}
		else {
			include_once _cfg('pages').'/'.get_class().'/participant-error.tpl';
		}
	}
	
	protected function approveRegisterPlayer($row) {
		//Generating other IDs for different environment
		if (_cfg('env') == 'prod') {
			$participant_id = $row->id + 100000;
		}
		else if (_cfg('env') == 'test') {
			$participant_id = $row->id + 50000;
		}
		else {
			$participant_id = $row->id;
		}
		
		Db::query('UPDATE `teams` '.
			'SET `approved` = 1 '.
			'WHERE `tournament_id` = '.(int)$this->currentTournament.' '.
			'AND `game` = "hs" '.
			'AND `id` = '.$row->id
		);
		
		$apiArray = array(
			'participant_id' => $participant_id,
			'participant[name]' => $row->name,
		);
		
		//Adding team to Challonge bracket
		$this->runChallongeAPI('tournaments/pentaclick-hs'.(int)$this->currentTournament.'/participants.post', $apiArray);
		
		//Registering ID, becaus Challonge idiots not giving an answer with ID
		$answer = $this->runChallongeAPI('tournaments/pentaclick-hs'.(int)$this->currentTournament.'/participants.json');
		array_reverse($answer, true);
		
		foreach($answer as $f) {
			if ($f->participant->name == $row->name) {
				Db::query('UPDATE `teams` '.
					'SET `challonge_id` = '.(int)$f->participant->id.' '.
					'WHERE `tournament_id` = '.(int)$this->currentTournament.' '.
					'AND `game` = "hs" '.
					'AND `id` = '.$row->id
				);
				$challonge_id = (int)$f->participant->id;
				break;
			}
		}
		
		/*sendMail('pentaclickesports@gmail.com',
		($game=='hs'?'Player':'Team').' added. PentaClick eSports.',
		'Participant was added!!!<br />
    	Date: '.date('d/m/Y H:i:s').'<br />'.
		($game=='hs'?'BattleTag':'TeamName').': <b>'.$team->name.'</b><br>
    	IP: '.$_SERVER['REMOTE_ADDR']);*/
		
		return $challonge_id;
	}
	
	public function getTournamentData($id) {
		if (file_exists(_cfg('pages').'/'.get_class().'/tournament-'.$id.'.tpl')) {
			$rows = Db::fetchRows('SELECT `name` '.
				'FROM `teams` '.
				'WHERE `game` = "hs" AND `approved` = 1 AND `tournament_id` = '.(int)$id.' AND `deleted` = 0 '.
				'ORDER BY `id` ASC'
			);

			$this->participants = $rows;
			
			include_once _cfg('pages').'/'.get_class().'/tournament-'.$id.'.tpl';
			include_once _cfg('pages').'/'.get_class().'/footer.tpl';
		}
		else {
			include_once  _cfg('pages').'/404/error.tpl';
		}
	}
	
	public function getTournamentList() {
		$rows = Db::fetchRows('SELECT `tournament_id`, COUNT(`tournament_id`) AS `value`'.
			'FROM `teams` '.
			'WHERE `game` = "hs" AND `approved` = 1 AND `deleted` = 0 '.
			'GROUP BY `tournament_id` '.
			'ORDER BY `id` DESC'
		);
		foreach($rows as $v) {
			$this->teamsCount[$v->tournament_id] = $v->value;
		}
		
		$rows = Db::fetchRows('SELECT `tournament_id`, `name`, `place` '.
			'FROM `teams` '.
			'WHERE `game` = "hs" AND `place` != 0 '.
			'ORDER BY `tournament_id`, `place`'
		);
		
		$placesArray = array();
		$previousTournamentId = 0;
		if ($rows) {
			foreach($rows as $v) {
				if ($v->tournament_id != $previousTournamentId) {
					$placesArray[$v->tournament_id][$v->place] = $v->name;
				}
			}
		}
		
		$this->teamsPlaces = $placesArray;
		
		$this->eventDates[1] = '01.03.2014';
		$this->eventDates[2] = '15.03.2014';
		$this->eventDates[3] = '19.04.2014';
		$this->eventDates[4] = '24.05.2014';
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'League of Legends';
		
		return $seo;
	}
	
	public function showTemplate() {
		
		if (isset($_GET['val2']) && $_GET['val2'] == 'participant') {
			$this->participantPage();
		}
		else if (isset($_GET['val2']) && is_numeric($_GET['val2'])) {
			$this->getTournamentData((int)$_GET['val2']);
		}
		else {
			$this->getTournamentList();
		}
	}
	
	public function runChallongeAPI($apiAdditionalData, $apiArray = array(), $apiGetUrl = '') {
		$startTime = microtime(true);
		$error = '';
	
		$apiUrl = 'https://api.challonge.com/v1/';
		$apiUrl .= $apiAdditionalData;
		$apiUrl .= '?api_key=5Md6xHmc7hXIEpn87nf6z13pIik1FRJY7DpOSoYa';
		if ($apiGetUrl) {
			$apiUrl .= '&'.$apiGetUrl;
		}
	
		$apiUrlLog = $apiUrl;
		if ($apiArray) {
			foreach($apiArray as $k => $v) {
				$apiUrlLog .= '&'.$k.'='.$v;
			}
		}
	
		Db::query(
		'INSERT INTO `challonge_requests` SET '.
		' `timestamp` = NOW(), '.
		' `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'", '.
		' `data` = "'.$apiUrlLog.'"'
				);
	
		$lastId = Db::lastId();
	
		$ch = curl_init();
	
		//---
		curl_setopt($ch, CURLOPT_URL, $apiUrl); // set url to post to
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
		curl_setopt($ch, CURLOPT_TIMEOUT, 60); // times out after 119s
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		if ($apiArray) {
			curl_setopt($ch, CURLOPT_POST, 1); //POST
			curl_setopt($ch, CURLOPT_POSTFIELDS, $apiArray); // add POST fields
		}
		else {
			curl_setopt($ch, CURLOPT_POST, 0); //GET
		}
	
		$response = curl_exec($ch); // run the whole process
		//dump(curl_error($ch));
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
		curl_close($ch);
	
		if ($http_status == 401) {
			$error = 'Invalid API key';
		}
		else if ($http_status == 404 ) {
			$error = 'Object not found within your account scope';
		}
		else if ($http_status == 422) {
			$error = 'Validation error(s) for create or update method';
		}
	
		$endTime = microtime(true);
		$duration = $endTime - $startTime; //calculates total time taken
	
		if ($apiArray) {
			$response = 'POST';
		}
	
		Db::query(
		'UPDATE `challonge_requests` SET '.
		' `response` = "'.($error?$error:Db::escape($response)).'", '.
		' `time` = "'.(float)$duration.'" '.
		' WHERE id='.$lastId
		);
	
		if ( $error )
		{
			return false;
		}
	
		if ($response == 'POST') {
			return true;
		}
	
		$response = json_decode($response); //
	
		return $response;
	}
}