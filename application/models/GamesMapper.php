<?php

class Application_Model_GamesMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Games';
	
	/**
	 * Get all games that are happening during user's availability
	 * @params ($userClass   => user object,
	 *		    $savingClass => games object,
	 *		    $options		=> additional sql "where" constraints,
	 *		   	$points => array of upper and lower points of map area,
	 *			$day	=> what day to search by (default user availability),
	 *			$hour => array of what hour to search for (default user availability))
	 */
	public function findUserGames($userClass, $savingClass, $options = false, $points = false, $day = false, $hour = false)
	{
		$table    = $this->getDbTable();
		$select   = $table->select();
		$userID   = $userClass->userID;
		$cityID   = $userClass->getCity()->cityID;
		$gameIDs  = ($userClass->hasValue('games') ? $userClass->games->implodeIDs('games', 'gameID') : '');

		$gameIDs  = (!empty($gameIDs) ? '(' . $gameIDs . ')' : false);
		

		if ($points) {
			// Map was moved or points have been set to not be around user location
			$bounds['upper'] = $points[0];
			$bounds['lower'] = $points[1];
		} else {
			// Default location to search near is user's home location, look for games within $distance of user's home location
			$latitude = $userClass->location->latitude;
			$longitude = $userClass->location->longitude;
			$bounds = $this->getBounds($latitude, $longitude);
		}
		
		
		$select->setIntegrityCheck(false);
		$select->from(array('g'  => 'games'))
			   ->join(array('t' => 'sport_types'),
			   		  't.typeID = g.typeID');
		
		if ($day === false || is_array($day)) {
			// Need user's availability, join table
			$select->join(array('usa' => 'user_sport_availabilities'),
						  't.sportID = usa.sportID');
		}
					  
		$select->join(array('pl' => 'park_locations'),
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
			   ->where('g.public = "1"')
			   ->where('MBRContains(
								LINESTRING(
								' . $bounds["upper"] . ' , ' . $bounds["lower"] . '
								), pl.location
								)')
			   ->where('g.date > NOW()');
			   
		if ($gameIDs) {
			// User is in games, do not show those
			$select->where('g.gameID NOT IN ' . $gameIDs);
		}
			   
		if (($day === false || is_array($day)) ||
			($hour === false || is_array($hour))) {
			// add id column for user
			$select->where('usa.userID = ?', $userID);
		}
		if ($day === false) {
			// Default to user's availabilty
			$select->where('DATE_FORMAT(g.date,"%w") = usa.day');
		} elseif (is_array($day)) {
			$days  = '(';
			$days .= implode(',', $day);
			$days .= ')';
			$select->where('DATE_FORMAT(g.date,"%w") IN ' . $days);
		}
		
		if ($hour === false) {
			// Default to user's availability
			$select->where('HOUR(g.date) = usa.hour');
		} elseif (is_array($hour)) {
			$hours  = '(';
			$hours .= implode(',', $hour);
			$hours .= ')';
			$select->where('HOUR(g.date) IN ' . $hours);
		}
			   
								
		
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
	 * Get all games that match $options variable
	 * @params ($options   => array of options including:
	 *					sports => associative array of sport => type,
	 *					distance => distance to look from user's location,
	 *					time => time to look for ('user' for user availability, false for anytime),
	 *					age => array('lower' => lower average age limit, 'upper' => upper average age limit),
	 *					skill => array('lower' => lower average skill limit, 'upper' => upper average skill limit)
	 *			$userClass   => user class,
	 *		    $savingClass => games object,
	 */
	public function findGames($options, $userClass, $savingClass, $limit = 200)
	{
		$table    = $this->getDbTable();
		$select   = $table->select();
		$select->setIntegrityCheck(false);
		$userID   = $userClass->userID;
		$where = array();
		$having = array();
		$sportWhere = '';
		$counter = 0;
		
		foreach ($options['sports'] as $sport => $inner) {
			if ($counter != 0) {
				$sportWhere .= ' OR ';
			}
			
			$sportWhere .= "(st.sport = '" . $sport . "' ";
			
			if (!$inner) {
				// Not an array so no special types to look for
				$sportWhere .= ") ";
				$counter++;
				continue;
			}
			
			$sportWhere .= ' AND ';
			$innerCounter = 0;
			foreach ($inner as $typeName => $typeSuffix) {
				if ($innerCounter != 0) {
					$sportWhere .= ' OR ';
				}
				$sportWhere .= "(st.typeName = '" . $typeName . "' AND (";
				
				$typeCounter = 0;
				foreach ($typeSuffix as $key => $values) {
					if ($typeCounter != 0) {
						$sportWhere .= ' OR ';
					}
					$sportWhere .= "st.typeSuffix = '" . $key . "'";
					$typeCounter++;
				}
				
				$sportWhere .= '))';
				$innerCounter++;
			}
			$sportWhere .= ')';
			$counter++;
		}
		
		$where[] = $sportWhere;
		
		if (!empty($options['points'])) {
			// Map was moved or points have been set to not be around user location
			$bounds['upper'] = $options['points'][0];
			$bounds['lower'] = $options['points'][1];
		} else {
			// Default location to search near is user's home location, look for games within $distance of user's home location
			$latitude = $userClass->location->latitude;
			$longitude = $userClass->location->longitude;
			$bounds = $this->getBounds($latitude, $longitude);
		}
		
		if (!empty($options['skill'])) {
			$having[] = "avg(us.skillCurrent) >= '" . $options['skill']['lower'] . "' AND avg(us.skillCurrent) <= '" . $options['skill']['upper'] . "'";
		}
		
		if (!empty($options['age'])) {
			$having[] = "avg(u.age) >= '" . $options['age']['lower'] . "' AND avg(u.age) <= '" . $options['age']['upper'] . "'";
		}
		
		$select->from(array('g' => 'games'),
					  array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS g.*')))
			   ->join(array('st' => 'sport_types'),
			   		  'st.typeID = g.typeID')
			   ->join(array('pl' => 'park_locations'),
			   		  'pl.parkID = g.parkID',
					  array('AsText(location) as location'))
			   ->joinLeft(array('ug' => 'user_games'),
			   		  	  'ug.gameID = g.gameID',
						  array(''))
			   ->joinLeft(array('us' => 'user_sports'),
			   		  	  'us.userID = ug.userID AND us.sportID = g.sportID',
					 array('avg(us.skillCurrent) as averageSkill',
					 	   'avg(us.attendance) as averageAttendance',
						   'avg(us.sportsmanship) as averageSportsmanship',
						   'avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = st.sportID) as skillDifference',
						   '(COUNT(us.userID) + SUM(ug.plus)) as totalPlayers'
						   ))
			   ->joinLeft(array('u' => 'users'),
			   		  'u.userID = us.userID',
					  array('avg(u.age) as averageAge'));
					  
		if ($options['time'] == 'user') {
			// Use user availability
			$select->join(array('usa' => 'user_sport_availabilities'),
						  'st.sportID = usa.sportID');
			$where[] = "usa.userID = '" . $userID . "'";
			$where[] = "HOUR(g.date) = usa.hour";
			$where[] = 'DATE_FORMAT(g.date,"%w") = usa.day';
		}
			
		
		$select->where('g.date > NOW()')
			   ->where('g.public = "1"')
			   ->where('MBRContains(
								LINESTRING(
								' . $bounds["upper"] . ' , ' . $bounds["lower"] . '
								), pl.location
								)');
					   
		foreach ($where as $statement) {
			$select->where($statement);
		}
		if (count($having) > 0) {
			$statements = 'CASE WHEN COUNT(ug.userID) = 0 THEN 1=1 ELSE (';
			$counter = 0;
			foreach ($having as $statement) {
				if ($counter != 0) {
					$statements .= ' AND ';
				}
				$statements .= '(' . $statement . ')';
				$counter++;
			}
			$statements .= ') END';
			$select->having(new Zend_Db_Expr($statements));

		}
		
		$select->group('g.gameID');
		
		if (isset($options['order'])) {
			// Order by
			if ($options['order'] == 'players') {
				$select->order('(COUNT(us.userID) + SUM(ug.plus)) DESC');
			} elseif ($options['order'] == 'date') {
				$select->order('g.date ASC');
			}
		}
		
		$limitArray = explode(',',$limit);
		$totalLimit = trim($limitArray[0]);
		$offsetLimit = (isset($limitArray[1]) ? $limitArray[1] : 0);
		
		$select->limit($totalLimit,$offsetLimit);
	
		$results = $table->fetchAll($select);
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$totalRows = $db->fetchAll('SELECT FOUND_ROWS() as totalRows');
		$totalRows = $totalRows[0]['totalRows'];
		
		foreach ($results as $result) {
			$savingClass->addGame($result);
		}
		
		$savingClass->totalRows = $totalRows;
		
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
