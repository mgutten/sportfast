<?php

class Application_Model_GamesMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Games';
	
	/**
	 * Get all games that are happening during user's availability
	 * @params($userClass   => user object,
	 *		   $savingClass => games object,
	 *		   $options		=> additional sql "where" constraints)
	 */
	public function findUserGames($userClass, $savingClass, $options = false, $points = false)
	{
		$table    = $this->getDbTable();
		$select   = $table->select();
		$userID   = $userClass->userID;
		$cityID   = $userClass->getCity()->cityID;
		
		if ($points) {
			// Map was moved or points have been set to not be around user location
			$upperPoint = $points[0];
			$lowerPoint = $points[1];
		} else {
			// Default location to search near is user's home location, look for games within $distance of user's home location
			$latitude = $userClass->location->latitude;
			$longitude = $userClass->location->longitude;
			$bounds = $this->getBounds($latitude, $longitude);
		}
		
		
		$select->setIntegrityCheck(false);
		$select->from(array('g'  => 'games'))
			   ->join(array('t' => 'sport_types'),
			   		  't.typeID = g.typeID')
			   ->join(array('usa' => 'user_sport_availabilities'),
			   		  't.sportID = usa.sportID')
			   ->join(array('pl' => 'park_locations'),
			   		  'pl.parkID = g.parkID',
					  array('AsText(location) as location'))
			   ->joinLeft(array('ug' => 'user_games'),
			   		 'ug.gameID = g.gameID',
					 array(''))
			   ->joinLeft(array('us' => 'user_sports'),
			   		 'ug.userID = us.userID AND us.sportID = t.sportID',
					 array('avg(us.skillCurrent) as averageSkill',
					 	   'avg(us.attendance) as averageAttendance',
						   'avg(us.sportsmanship) as averageSportsmanship',
						   'avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID) as skillDifference',
						   '(COUNT(us.userID) + SUM(ug.plus)) as totalPlayers'
						   ))
			   //->where('g.cityID = ?', $cityID)
			   ->where('usa.userID = ?', $userID)
			   ->where('DATE_FORMAT(g.date,"%w") = usa.day')
			   ->where('HOUR(g.date) = usa.hour')
			   ->where('g.public = "1"')
			   ->where('MBRContains(
								LINESTRING(
								' . $bounds["upper"] . ' , ' . $bounds["lower"] . '
								), pl.location
								)')
			   ->where('g.date > NOW()');
								
		
		if ($options) {
			// Additional options are set
			foreach ($options as $option) {
				$select->where($option);
			}
		}
		
		$select->group('g.gameID')
			   ->order('abs(avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID)) ASC');
		
		$results = $table->fetchAll($select);

		foreach ($results as $result) {
			$savingClass->addGame($result);
		}
		
		return $savingClass;
	}
	
	/**
	 * get game info from db
	 * @params ($gameID => gameID
	 *			$savingClass => game model)
	 */
	public function getGameByID($gameID, $savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('g'  => 'games'))
			   ->join(array('st' => 'sport_types'),
			   		  'g.typeID = st.typeID')
			   ->join(array('pl' => 'park_locations'),
			   		  'g.parkID = pl.parkID',
					  array('AsText(location) as location'))
			   ->joinLeft(array('ug' => 'user_games'),
			   		 'ug.gameID = g.gameID',
					 array(''))
			   ->joinLeft(array('us' => 'user_sports'),
			   		 'ug.userID = us.userID AND us.sportID = g.sportID',
					 array('avg(us.skillCurrent) as averageSkill',
					 	   'avg(us.attendance) as averageAttendance',
						   'avg(us.sportsmanship) as averageSportsmanship',
						   '(COUNT(us.userID) + SUM(ug.plus)) as totalPlayers'
						   ))
				->where('g.gameID = ?', $gameID)
				->limit(1);
				
		$result = $table->fetchRow($select);
		
		$savingClass->setAttribs($result);
		$savingClass->park->setAttribs($result);
		$savingClass->park->location->setAttribs($result);
		$savingClass->type->setAttribs($result);
		
		// Get game's players
		$sportID = $savingClass->sportID;
		$select = $table->select();
		$select->setIntegrityCheck(false);	
			
		$select->from(array('ug' => 'user_games'))
			   ->join(array('u' => 'users'),
			   		  'ug.userID = u.userID')
			   ->join(array('us' => 'user_sports'),
			   		  'ug.userID = us.userID AND us.sportID = "' . $sportID . '"')
			   ->join(array('s' => 'sports'),
			   		  's.sportID = ' . $sportID)
			   ->where('ug.gameID = ?', $gameID);
			   
		$players = $table->fetchAll($select);

		foreach ($players as $player) {
			$savingClass->addPlayer($player)
						->getSport($player->sport)
						->setAttribs($player);
				
		}
		return $savingClass;
				
	}
	
	/**
	 * save user confirmation (confirmed or not) to db for pickup game
	 * @params ($userID => user's id,
	 *			$typeID => gameID,
	 *			$inOrOut=> 0 for not in, 1 for in
	 *			$insertOrUpdate => "insert" or "update"
	 */
	public function savePickupGameConfirmation($userID, $typeID, $inOrOut, $insertOrUpdate)
	{
		$this->setDbTable('Application_Model_DbTable_UserGames');
		$table    = $this->getDbTable();
		$insertOrUpdate = strtolower($insertOrUpdate);
	
		if ($insertOrUpdate == 'update') {
			$data  = array('confirmed' => $inOrOut);
			$where = array(
						'userID = "' . $userID . '"',
						'gameID = "' . $typeID . '"'
					);
			$table->update($data, $where);
			
		} elseif ($insertOrUpdate == 'insert') {
			$data = array(
						'userID' => $userID,
						'gameID' => $typeID,
						'confirmed' => $inOrOut
						);
			$table->insert($data);
		}
		
		return $this;
		
	}

	/**
	 * save user confirmation (confirmed or not) to db for team game
	 * @params ($userID => user's id,
	 *			$typeID => gameID,
	 *			$inOrOut=> 0 for not in, 1 for in
	 *			$insertOrUpdate => "insert" or "update"
	 */
	public function saveTeamGameConfirmation($userID, $typeID, $inOrOut, $insertOrUpdate, $teamID)
	{
		$this->setDbTable('Application_Model_DbTable_UserTeamGames');
		$table    = $this->getDbTable();
		$insertOrUpdate = strtolower($insertOrUpdate);
		
		
		if ($insertOrUpdate == 'update') {
			$data  = array('confirmed' => $inOrOut);
			$where = array(
						'userID = "' . $userID . '"',
						'teamGameID = "' . $typeID . '"',
						'teamID = "' . $teamID . '"'
					);
					
			$table->update($data, $where);
			
		} elseif ($insertOrUpdate == 'insert') {
			$data = array(
						'userID' => $userID,
						'teamGameID' => $typeID,
						'teamID'	 => $teamID,
						'confirmed'  => $inOrOut
						);
			$table->insert($data);
		}
		
		return $this;
		
	}
	
	/**
	 * check db for league location based on either locationName or address
	 * @params ($locationName => name of location,
	 *			$address	  => address of location)
	 * @returns leagueLocationID (int)
	 */
	public function searchDbForLeagueLocation($locationName = false, $address = false, $cityID = false)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$cityIDRange = ($cityID ? $this->getCityIdRange($cityID) : false);
		
		$select = 'SELECT * FROM league_locations
					WHERE ';
		
		$post = ' ';
		if ($cityIDRange) {
			// cityID was given
			$select .= ' cityID IN ' . $cityIDRange . ' ';
			if ($locationName || $address) {
				$select .= ' AND (';
				$post = ') ';
			}
		}

		if ($locationName) {

			$select .= 'locationName LIKE "%' . $locationName . '%"';
		}
		
		if ($address) {
			// Address is set
			if ($locationName) {
				$select .= ' OR ';
			}
			$select .= 'streetAddress LIKE "%' . $address . '%"';
		}
		
		$select .= $post . ' ORDER BY ABS(' . $cityID . ' - cityID) LIMIT 1';
		

		$result = $db->fetchAll($select);
		
		if (empty($result)) {
			// No current location exists with that name/address, add it to the db as temporary
			$leagueLocationID = $this->addLeagueLocation($locationName, $address, $cityID, true);
		} else {
			// We found a match for league location
			$leagueLocationID = $result[0]['leagueLocationID'];
		}
		
		return $leagueLocationID;
		
	}
	


	/**
	 * add league location to db
	 * @params ($locationName => name of location,
	 *			$address => street address,
	 *			$temporary => is this a temporary or a permanent addition (stored in db as temporary))
	 */
	public function addLeagueLocation($locationName, $address, $cityID, $temporary)
	{
		$this->setDbTable('Application_Model_DbTable_LeagueLocations');
		
		$table = $this->getDbTable();
		
		$city = $this->getForeignID('Application_Model_DbTable_Cities', 'city', array('cityID' => $cityID));
		
		$data = array('locationName'  => $locationName,
					  'streetAddress' => $address,
					  'cityID'		  => $cityID,
					  'city'		  => $city,
					  'temporary'	  => $temporary);

		
		return $table->insert($data);
	}
	
	/**
	 * update league location if it is a temporary location
	 */
	public function updateLeagueLocation($locationID, $data)
	{
		$this->setDbTable('Application_Model_DbTable_LeagueLocations');
		$table = $this->getDbTable();
		
		$where = array('leagueLocationID = ?' => $locationID,
					   'temporary = ?'	=> 1);
	   		
		return $table->update($data, $where);
	}
}
