<?php

class Application_Model_GamesMapper extends Application_Model_TypesMapperAbstract
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
			   		 'ug.gameID = g.gameID AND ug.confirmed = 1',
					 array(''))
			   ->joinLeft(array('us' => 'user_sports'),
			   		 'ug.userID = us.userID AND us.sportID = t.sportID',
					 array('avg(us.skillCurrent) as averageSkill',
					 	   'avg(us.attendance) as averageAttendance',
						   'avg(us.sportsmanship) as averageSportsmanship',
						   'avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID) as skillDifference',
						   '(COUNT(us.userID) + SUM(ug.plus)) as totalPlayers'
						   ))
			   ->join(array('ust' => 'user_sport_types'),
			   			  'ust.typeID = g.typeID AND ust.userID = "' . $userID . '"')
			   //->where('g.cityID = ?', $cityID)
			   ->where('g.public = "1"')
			   ->where('g.canceled = ?', '0')
			   ->where('MBRContains(
								LINESTRING(
								' . $bounds["upper"] . ' , ' . $bounds["lower"] . '
								), pl.location
								)')
			   ->where('g.date > (NOW() + INTERVAL ' . $this->getTimeOffset() . ' HOUR)')
			   ->where('g.date < (NOW() + INTERVAL 6 DAY)');
			   
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
		
		$select->having('(g.rosterLimit > totalPlayers OR totalPlayers IS NULL)')
			   ->group('g.gameID')
			   ->order('abs(avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID)) ASC');
		
		$results = $table->fetchAll($select);

		foreach ($results as $result) {
			$savingClass->addGame($result);
		}
		
		return $savingClass;
	}
	
	/**
	 * Simple get query to find games that match $where parameters
	 * @parameters ($where => non-associative array of where conditions)
	 */
	public function getGames($where, $userClass = false, $savingClass)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->setIntegrityCheck(false);
		
		if ($userClass) {
			$where[] = 'g.cityID IN ' . $this->getCityIdRange($userClass->city->cityID);
		}
		
		$select->from(array('g' => 'games'))
			   ->join(array('st' => 'sport_types'),
			   		  'g.typeID = st.typeID')
			   ->joinLeft(array('ug' => 'user_games'),
			   		  	  'ug.gameID = g.gameID',
						  array('COUNT(ug.userID) as totalPlayers'));
		
		foreach ($where as $statement) {
			$select->where($statement);
		}
		
		$select->group('g.gameID');
		$select->order('COUNT(ug.userID) DESC');
		
		$games = $table->fetchAll($select);
		
		foreach ($games as $game) {
			$savingClass->addGame($game);
		}
		
		return $savingClass;
	}
		
		

	/**
	 * Get all games that match $options variable (for Find controller)
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
		$this->getTimeOffset();
		
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
			
		
		$select->where('g.date > (NOW() + INTERVAL ' . $this->getTimeOffset() . ' HOUR)')
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
		$select->having('(g.rosterLimit > totalPlayers OR totalPlayers IS NULL)');
		
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
			   		  'g.typeID = st.typeID',
					  array('typeName',
					  		'typeSuffix'))
			   ->join(array('p' => 'parks'),
			   		  'p.parkID = g.parkID',
					  array('specialNotes',
					  		'parkType',
							'stash'))
			   ->join(array('pl' => 'park_locations'),
			   		  'g.parkID = pl.parkID',
					  array('AsText(location) as location'))
			   ->joinLeft(array('gc' => new Zend_Db_Expr("(SELECT GROUP_CONCAT(gc2.userID separator ',') as captainsFirst, gc2.gameID as gameID FROM game_captains gc2 WHERE gc2.gameID = '" . $gameID . "')")),
			   	     'gc.gameID = g.gameID',
					 array('captainsFirst as captainsSecond'))
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
		
		
		if (empty($result['gameID'])) {
			// No game found
			return false;
		}
		
		$savingClass->setAttribs($result);
		$savingClass->park->setAttribs($result);
		$savingClass->park->location->setAttribs($result);
		$savingClass->type->setAttribs($result);
		
		$captains = explode(',', $result['captainsSecond']);
		
		foreach ($captains as $captain) {
			$savingClass->addCaptain($captain);
		}
		
		$this->getGamePlayers($savingClass);
				
		return $savingClass;
				
	}
	
	/**
	 * get team game info from db
	 * @params ($teamGameID => teamGameID
	 *			$savingClass => game model)
	 */
	public function getTeamGameByID($teamGameID, $savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('tg'  => 'team_games'))
			   ->join(array('t' => 'teams'),
			          't.teamID = tg.teamID')
			   ->join(array('ll' => 'league_locations'),
			   		  'll.leagueLocationID = tg.leagueLocationID')
			   ->where('tg.teamGameID = ?', $teamGameID)
			   ->limit(1);
			   
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			$savingClass->setAttribs($result);
		}
		
		return $savingClass;
	}
	
	/**
	 * get game's players
	 * @params ($onlyReal => return only real players? (boolean))
	 */
	public function getGamePlayers($savingClass, $onlyReal = false)
	{
		if (!$savingClass->hasValue('gameID') || !$savingClass->hasValue('sportID')) {
			return false;
		}
		
		// Get game's players
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
			
		$gameID = $savingClass->gameID;
		
		$select->from(array('ug' => 'user_games'))
			   ->join(array('g' => 'games'),
			   		  'g.gameID = ug.gameID',
					  array(''))
			   ->join(array('u' => 'users'),
			   		  'ug.userID = u.userID')
			   ->joinLeft(array('us' => 'user_sports'),
			   		  'ug.userID = us.userID AND us.sportID = g.sportID',
					  array('us.sportID',
					  		'us.skillInitial',
							'us.often',
							'us.skillCurrent',
							'us.attendance',
							'us.sportsmanship'))
			   ->join(array('s' => 'sports'),
			   		  's.sportID = g.sportID')
			   /*->joinLeft(array('gc' => 'game_captains'),
			   		  'ug.gameID = gc.gameID AND ug.userID = gc.userID',
					  array('userID as captain'))*/
			   ->where('ug.gameID = ?', $gameID)
			   ->group('ug.userID');
		
		$players = $table->fetchAll($select);

		foreach ($players as $player) {
			
			if ($onlyReal && $player->fake == '1') {
				continue;
			} elseif ($player->fake == '1') {
				// Fake player
				$middleSkill = round(($savingClass->maxSkill + $savingClass->minSkill)/2);
				$skill = mt_rand($middleSkill - 2, $middleSkill + 2);
				
				$player->skillCurrent = $skill;
				$player->attendance   = '90';
				$player->sportsmanship = '80';
			}
			
			$savingClass->addPlayer($player)
						->getSport($player->sport)
						->setAttribs($player);
						
			if ($player->confirmed == '1') {
				$savingClass->addConfirmedPlayer($player->userID);
			} elseif ($player->confirmed == '0') {
				$savingClass->addNotConfirmedPlayer($player->userID);
			} elseif ($player->confirmed == '2') {
				// Maybe
				$savingClass->addMaybeConfirmedPlayer($player->userID);
			}
			
			/*
			if ($player->subscribed !== null) {
				// player is subscribed
				$savingClass->addSubscriber($player->userID);
			}
			

			if ($player->captain !== null) {
				$savingClass->addCaptain($player->userID);
			}
			*/	
		}
		
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('gs' => 'game_subscribers'))
			   ->where('gs.gameID = ?', $gameID)
			   ->where('gs.doNotEmail = ?', 0);
			   
		$players = $table->fetchAll($select);
		
		foreach ($players as $player) {
			$savingClass->addSubscriber($player->userID);
		}
		
		return $savingClass;
	}
	
	/**
	 * remove subcriber from game (in db)
	 */
	public function unsubscribe($userID, $gameID) 
	{
		$table = new Application_Model_DbTable_GameSubscribers();
		
		if (empty($userID) || empty($gameID)) {
			return false;
		}
		
		$where = array('userID = ?' => $userID,
						'gameID = ?' => $gameID);
						
		return $table->update(array('doNotEmail' => '1'), $where);
		
	}
	
	/**
	 * save user confirmation (confirmed or not) to db for pickup game
	 * @params ($userID => user's id,
	 *			$gameID => gameID,
	 *			$confirmed => 0 = not, 1 = in, 2 = maybe
	 *			$insertOrUpdate => "insert" or "update"
	 */
	public function savePickupGameConfirmation($gameID, $userID, $confirmed, $recurring = false)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "INSERT INTO user_games (gameID, userID, confirmed) VALUES (" . $gameID . ", " . $userID . ", '" . $confirmed . "')
		  		ON DUPLICATE KEY UPDATE confirmed = '" . $confirmed . "'";
		
		
		$result = $db->query($sql);
		
		// If rowCount = 1 row was inserted, elseif rowCount = 2 row was updated
		$rowCount = $result->rowCount();
		
		if ($rowCount == 1) {
			// Row was inserted, not updated
			$notifications = new Application_Model_Notifications();
			
			$notifications->deleteAll(array('nl.actingUserID' => $userID,
											'nl.gameID'		  => $gameID,
											'n.action'		  => 'leave',
											'n.type'		  => 'game'));
											
			$notifications->deleteAll(array('nl.actingUserID' => $userID,
											'nl.gameID'		  => $gameID,
											'n.action'		  => 'join',
											'n.type'		  => 'game'));
			
			if ($confirmed == '1' ||
				$confirmed == '2') {
					// Is "in" or "maybe"
					$notification = new Application_Model_Notification();
					
					$notification->actingUserID = $userID;
					$notification->gameID = $gameID;
					$notification->action = 'join';
					$notification->type = 'game';
					
					$notification->save();
				}
		}
		
		if ($recurring) {
			// Add user to subscribers list as default
			$sql = "INSERT INTO game_subscribers (gameID, userID, doNotEmail) VALUES (" . $gameID . ", " . $userID . ", '0')
		  		ON DUPLICATE KEY UPDATE gameID = " . $gameID;
				
			$db->query($sql);
		}
	
		
		/*$insertOrUpdate = strtolower($insertOrUpdate);
	
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
		*/
		
		return $this;
		
	}

	/**
	 * save user confirmation (confirmed or not) to db for team game
	 * @params ($userID => user's id,
	 *			$typeID => teamGameID,
	 *			$inOrOut=> 0 for not in, 1 for in
	 *			$insertOrUpdate => "insert" or "update"
	 */
	public function saveTeamGameConfirmation($userID, $typeID, $inOrOut)
	{
		
		
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "INSERT INTO user_team_games (userID, teamGameID, confirmed) VALUES (:userID,:teamGameID,:confirmed)
				  ON DUPLICATE KEY UPDATE confirmed = :confirmed;";
		
		$values = array('teamGameID' => $typeID,
						'userID'	 => $userID,
						'confirmed'  => $inOrOut);
		
		$db->query($sql, $values);
		
		
		/*
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
		*/
		
		return $this;
		
	}
	
	/**
	 * get teamID from teamGameID
	 */
	public function getTeamIDFromTeamGameID($teamGameID)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$sql = "SELECT teamID FROM team_games WHERE teamGameID = ? LIMIT 1";
		
		$results = $db->query($sql, array($teamGameID));
		
		foreach ($results as $result) {
			$teamID = $result['teamID'];
		}
		
		return $teamID;
	}
	
	/**
	 * check if user has responded to an upcoming team game
	 * @params ($userID => user's id,
	 *			$teamGameID => teamGameID,
	 */
	public function checkTeamGameConfirmation($userID, $teamGameID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('utg' => 'user_team_games'))
			   ->where('utg.userID = ?', $userID)
			   ->where('utg.teamGameID = ?', $teamGameID);
			   
	}
	
	/**
	 * get history data for chart of subscribe game
	 * @params ($interval => how many months to look back
	 *			$userID	=> userID of user to see how many games they've played in)
	 */
	public function getHistoryData($gameID, $interval = 6, $userID = false)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('og' => 'old_games'))
			   ->where('og.gameID = ?', $gameID)
			   ->where('og.date > (now() - INTERVAL ' . $interval . ' MONTH)')
			   ->order('og.date ASC');
			   
		$results = $table->fetchAll($select);
		
		$games = array();
		foreach ($results as $result) {
			$game = new Application_Model_Game($result);
			$games[] = $game;
		}
		
		// Get other individual values for this game
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('og' => 'old_games'),
					  array('MAX(og.totalPlayers) as maxPlayers',
					  		'COUNT(og.gameID) as totalGames',
							'MIN(og.date) as firstGame',
							'SUM(IF(og.minPlayers > og.totalPlayers, 1, 0)) AS failedGames'))
			   ->where('og.gameID = ?', $gameID);
		
		$result = $table->fetchRow($select);
		
		$returnArray = array();
		
		$returnArray['games'] = $games;
		$returnArray['maxPlayers'] = $result['maxPlayers'];
		$returnArray['totalGames'] = $result['totalGames'];
		$returnArray['failedGames'] = $result['failedGames'];
		$returnArray['successGames'] = $result['totalGames'] - $result['failedGames'];
		
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $result['firstGame']);
		$returnArray['firstGame'] = $date;
		
		// Get subscribers
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('gs' => 'game_subscribers'),
					  array('COUNT(gs.userID) as totalSubscribers'))
			   ->where('gs.gameID = ?', $gameID);
			   
		$result = $table->fetchRow($select);
		
		$returnArray['totalSubscribers'] = $result['totalSubscribers'];
		
		// Get most regular person
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('oug' => 'old_user_games'),
					  array('COUNT(oug.userID) as count',
					  		'userID'))
			   ->join(array('u' => 'users'),
			   		  'u.userID = oug.userID',
					  array('u.firstName', 'u.lastName'))
			   ->where('oug.gameID = ?', $gameID)
			   ->where('oug.confirmed = ?', 1)
			   ->group('oug.userID')
			   ->order('COUNT(oug.userID) DESC')
			   ->limit(20);
			   
		$results = $table->fetchAll($select);
		
		$returnArray['mostRegular'] = array();
		if ($results) {
			// Is a player
			foreach ($results as $result) {
				$user = new Application_Model_User($result);
				$returnArray['mostRegular'][] = array('user' => $user,
													  'count' => $result['count']);
			}
		}
		
		if ($userID) {
			// Get user's attended games
			$select = $table->select();
			$select->setIntegrityCheck(false);
			
			$select->from(array('oug' => 'old_user_games'),
						  array('COUNT(oug.oldGameID) as count'))
				   ->where('oug.userID = ?', $userID)
				   ->where('oug.gameID = ?', $gameID);
				   
			$result = $table->fetchRow($select);
			
			$returnArray['userCount'] = $result['count'];
		}
		
		return $returnArray;
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
	
	/**
	 * add user to game
	 */
	public function addUserToPickupGame($gameID, $userID, $confirmed = false)
	{
		
		if (empty($gameID) || empty($userID)) {
			return false;
		}
		
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		/*
		$select->from(array('ug' => 'user_games'))
			   ->where('ug.userID = ?', $userID)
			   ->where('ug.gameID = ?', $gameID);
		*/
			   
		$select->from(array('g' => 'games'))
			   ->joinLeft(array('ug' => 'user_games'),
			   			  'ug.gameID = g.gameID AND ug.userID = "' . $userID . '"')
			   ->where('g.gameID = ?', $gameID);
			   
		$result = $table->fetchRow($select);
		
		$recurring = $result['recurring'];
		
		$already = false;
		if (!is_null($result['userID'])) {
			// Already in game
			$already = true;
		}
		
		if (!$already) {
		
			$select = $table->select();
			$select->setIntegrityCheck(false);
			
			$select->from(array('g' => 'games'))
				   ->joinLeft(array('ug' => 'user_games'),
						  'ug.gameID = g.gameID AND ug.confirmed = 1',
						  array('COUNT(ug.userID) as totalPlayers'))
				   ->where('g.gameID = ?', $gameID)
				   ->group('g.gameID');
			
			$result = $table->fetchRow($select);
			
			if (($result['rosterLimit'] <= $result['totalPlayers']) && 
				 $confirmed == '1') {
				// Game is full
				return 'full';
			}
		}
		
		$data = array('userID' => $userID,
					  'gameID' => $gameID,
					  'confirmed' => $confirmed);
		$update = $insert = false;
		$db = Zend_Db_Table::getDefaultAdapter();
		
		if ($already) {
			$update = true;
			$db->update('user_games', $data, array('userID = ?' => $userID,
												   'gameID = ?' => $gameID));
		} else {
			$insert = true;
			$db->insert('user_games', $data);
		}
		
		
		if ($recurring == '1') {
			// Add user to subscribers list as default
			$sql = "INSERT INTO game_subscribers (gameID, userID, doNotEmail) VALUES (" . $gameID . ", " . $userID . ", '0')
		  		ON DUPLICATE KEY UPDATE gameID = " . $gameID;
				
			$db->query($sql);
		}
		
		if ($insert) {
			// Row was inserted, not updated, create notifications
			$notifications = new Application_Model_Notifications();
			
			$notifications->deleteAll(array('nl.actingUserID' => $userID,
											'nl.gameID'		  => $gameID,
											'n.action'		  => 'leave',
											'n.type'		  => 'game'));
											
			$notifications->deleteAll(array('nl.actingUserID' => $userID,
											'nl.gameID'		  => $gameID,
											'n.action'		  => 'join',
											'n.type'		  => 'game'));
			
			if ($confirmed == '1' ||
				$confirmed == '2') {
					// Is "in" or "maybe"
					$notification = new Application_Model_Notification();
					
					$notification->actingUserID = $userID;
					$notification->gameID = $gameID;
					$notification->action = 'join';
					$notification->type = 'game';
					
					$auth = Zend_Auth::getInstance();
					
					if ($auth->hasIdentity()) {
						// User logged in, get their cityID
						$notification->cityID = $auth->getIdentity()->cityID;
					}
					
					$notification->save();
				}
		}
		
		
		return false;
	}
	
	/**
	 * add user to team game
	 */
	public function addUserToTeamGame($teamGameID, $userID, $confirmed)
	{
		if (empty($teamGameID) || empty($userID)) {
			return false;
		}
		$this->setDbTable('Application_Model_DbTable_UserTeamGames');
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		/*
		$select->from(array('ug' => 'user_games'))
			   ->where('ug.userID = ?', $userID)
			   ->where('ug.gameID = ?', $gameID);
		*/
		
		$select->from(array('utg' => 'user_team_games'))
			   ->where('utg.userID = ?', $userID)
			   ->where('utg.teamGameID = ?', $teamGameID);

			   
		$result = $table->fetchRow($select);
		
		$already = false;
		if ($result) {
			// Already in game
			$already = true;
		}
		
		
		$data = array('userID' => $userID,
					  'teamGameID' => $teamGameID,
					  'confirmed' => $confirmed);
		$update = $insert = false;
		$db = Zend_Db_Table::getDefaultAdapter();
		
		if ($already) {
			$update = true;
			$db->update('user_team_games', $data, array('userID = ?' => $userID,
												   		'teamGameID = ?' => $teamGameID));
		} else {
			$insert = true;
			$db->insert('user_team_games', $data);
		}

		
		return false;
		/*
		if (empty($gameID) || empty($userID)) {
			return false;
		}
		
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('utg' => 'user_team_games'))
			   ->where('utg.userID = ?', $userID)
			   ->where('utg.teamGameID = ?', $teamGameID);
			   
		$result = $table->fetchRow($select);
		
		if ($result) {
			// Already in game
			return 'already';
		}
		
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('utg' => 'user_team_games'))
			   ->where('utg.teamGameID = ?', $teamGameID);
			   
		$result = $table->fetchRow($select);
		
		$teamID = $result['teamID'];
		
		$data = array('userID' => $userID,
					  'teamGameID' => $gameID,
					  'confirmed' => '1',
					  'teamID' => $teamID);
					  
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$db->insert('user_team_games', $data);
		
		return false;
		*/
	}
	
	
	/**
	 * get game subscribers (used on games/subscribers page)
	 */
	public function getGameSubscribers($gameID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$oldUserGames = "SELECT COUNT(oug2.userID) as totalGames, oug2.userID, MAX(og.date) as date
						 FROM old_user_games oug2
						 INNER JOIN old_games og ON og.oldGameID = oug2.oldGameID
						 WHERE oug2.gameID = '" . $gameID . "' AND
						 	oug2.confirmed = '1'
						 GROUP BY oug2.userID";
		
		$select->from(array('gs' => 'game_subscribers'))
			   ->join(array('u' => 'users'),
			   		  'u.userID = gs.userID')
			   ->joinLeft(array('oug' => new Zend_Db_Expr('(' . $oldUserGames . ')')),
			   		  'oug.userID = u.userID',
					  array('totalGames', 'date'))
			   ->where('gs.gameID = ?', $gameID);

		
		$results = $table->fetchAll($select);
		
		$users = new Application_Model_Users();
		
		foreach ($results as $result) {
			
			$user = $users->addUser($result);
			
			$user->setTempAttrib('doNotEmail', $result->doNotEmail);
			
			if (!is_null($result->totalGames)) {
				$user->setTempAttrib('totalGames', $result->totalGames);
				$user->setTempAttrib('lastPlayed', $result->date);
			} else {
				$user->setTempAttrib('totalGames', 0);
			}
			
			
		}
		
		return $users;
		
	}
		
	
	/**
	 * get game captains
	 */
	public function getGameCaptains($gameID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('gc' => 'game_captains'))
			   ->where('gc.gameID = ?', $gameID);
			   
		$captains = $table->fetchAll($select);
		
		$returnArray = array();
		
		foreach ($captains as $captain) {
			$returnArray[] = $captain->userID;
		}
		
		return $returnArray;
	}
	
	/**
	 * save sent invites to db
	 */
	public function saveInvites($emails, $actingUserID, $gameID)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "INSERT INTO game_invites 
					(gameInviteID, email, actingUserID, gameID, firstSent) 
				VALUES ";
		
		$counter = 0;
		foreach ($emails as $email) {
			if ($counter != 0) {
				$sql .= ',';
			}
			$sql .= "('', '" . $email . "', " . $actingUserID . ", " . $gameID . ", CURDATE())";
			$counter++;
		}
		
		$db->query($sql);
	}
					
	
	/**
	 * delete game
	 */
	public function delete($gameModel)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		if (!$gameModel->isTeamGame()) {
			// Not team game
			$gameID = $gameModel->gameID;
			
			$where = array('gameID = ?' => $gameID);
			
			if (empty($gameID)) {
				// Safety check to make sure gameID is set before continuing
				return false;
			}
			
			$this->move($gameID);
	
			$db->delete('games', $where);
			$db->delete('game_captains', $where);
			$db->delete('game_messages', $where);
		} else {
			// Is team game
			$gameID = $gameModel->teamGameID;
			
			$where = array('teamGameID = ?' => $gameID);
			
			if (empty($gameID)) {
				// Safety check to make sure gameID is set before continuing
				return false;
			}
	
			$db->delete('team_games', $where);
		}
		
	}
	
	/**
	 * move deleted rows from active table to "old" table
	 */
	public function move($id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "INSERT INTO old_games
					(SELECT g.*, (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR), (SELECT COUNT(ug.userID) FROM user_games as ug WHERE ug.gameID = '" . $id . "')
					FROM games as g
					WHERE g.gameID = '" . $id . "')";
					
		$db->query($sql);
	}

}
