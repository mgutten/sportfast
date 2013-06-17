<?php

class Application_Model_UsersMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Users';
	
	public function getUserBy($column, $value, $savingClass = false)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('u' => 'users'))
               ->joinLeft(array('c' => 'cities'),
                      'u.cityID = c.cityID',
					  array('cityID', 'city', 'state'))
			   ->joinLeft(array('ul' => 'user_locations'),
			   		  'ul.userID = u.userID',
					   array('AsText(location) as location',
					   		 'userLocationID'))
			   ->where($column . ' = ?', $value)
			   ->where('u.active = ?', 1)
			   ->limit(1);   
		
		$results = $table->fetchAll($select);
		
		$user = $this->createUserClasses($results, $savingClass);
		
		return $user;	
	}
	
	public function createUserClasses($results, $savingClass = false) 
	{
		$users = array();
		
		foreach ($results as $result) {
			if (!$savingClass) {
				// No saving class submitted, create new user
				$user = new Application_Model_User();
			} else {
				$user = $savingClass;
			}
			
			$user->getCity()->setAttribs($result);
			$user->getLocation()->setAttribs($result);
			$user->setAttribs($result);
			
			if(count($results) <= 1) {
				$users = $user;
				break;
			}
			
			$users[] = $user;
		}
		
		return $users;
	}
	
	/**
	 * test if emails exist in database, return array of info if do
	 * @params ($emails => array of emails)
	 */
	public function emailsExist($emails)
	{
		$emails = implode('","',$emails);
		
		$table = $this->getDbTable();
		$select = $table->select();
		$db = Zend_Db_Table::getDefaultAdapter();
		
		
		$select->from(array('u' => 'users'),
					  array('userID', 'username as email'))
			   ->where('u.username IN ("' . $emails . '")');
			   
		$results = $db->fetchAll($select); // Returns arrays
		
		return $results;
	}
	
	/**
	 * get user emails according to where statements in $where array
	 * @params ($userIDs => array of userIDs)
	 */
	public function getUserEmails($userIDs, $savingClass)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$userIDs = '("' . implode('","', $userIDs) . '")';
		
		$select->from(array('u' => 'users'),
					  array('username'))
			   ->where('u.userID IN ' . $userIDs);
			   
		$results = $table->fetchAll($select);
		
		$emails = array();
		foreach ($results as $result) {
			$emails[] = $result->username;
		}
		
		return $emails;
		
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
	public function findUsers($options, $userClass, $savingClass, $limit = 200)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$userID  = $userClass->userID;
		$where = array();
		$having = array();
		
		$select->setIntegrityCheck(false);
		// Default location to search near is user's home location, look for games within $distance of user's home location
		$latitude  = $userClass->getLocation()->getLatitude();
		$longitude = $userClass->getLocation()->getLongitude();
		$bounds = $this->getBounds($latitude, $longitude, 20);			   
		
		
		if (!empty($options['skill'])) {
			$having[] = "us.skillCurrent >= '" . $options['skill']['lower'] . "' AND us.skillCurrent <= '" . $options['skill']['upper'] . "'";
		}
		
		if (!empty($options['age'])) {
			$having[] = "u.age >= '" . $options['age']['lower'] . "' AND u.age <= '" . $options['age']['upper'] . "'";
		}
		
		if (!empty($options['looking'])) {
			$where[] = "usf.formatID = sf.formatID";
		}

		$select->from(array('s' => 'sports'),
					  array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.*')))
			   ->join(array('us' => 'user_sports'),
			   		 'us.sportID = s.sportID',
					 array('skillCurrent',
					 	   'attendance',
						   'sportsmanship'))
			   ->join(array('u' => 'users'),
			   		  'u.userID = us.userID')
			   ->join(array('ul' => 'user_locations'),
			   				'ul.userID = u.userID',
							array(''))
			   ->join(array('c' => 'cities'),
			   				'u.cityID = c.cityID')
			   ->join(array('sf' => 'sport_formats'),
			   		  'sf.format = "league"')
			   ->joinLeft(array('usf' => 'user_sport_formats'),
			   		  'usf.userID = u.userID AND usf.formatID = sf.formatID AND usf.sportID = us.sportID',
					  array('formatID'))
			   ->where('MBRContains(
									  LINESTRING(
									  ' . $bounds["upper"] . ' , ' . $bounds["lower"] . '
									  ), ul.location
								   )');
	
		$sportWhere = '';
		$counter = 0;
		
		if (!empty($options['sports'])) {
			foreach ($options['sports'] as $sport => $inner) {
				if ($counter != 0) {
					$sportWhere .= ' OR ';
				}
				
				$sportWhere .= "(s.sport = '" . $sport . "' )";
				$counter++;
			}
			
			$where[] = $sportWhere;
		}
		
		/*
		if (!empty($options['skill'])) {
			$having[] = "avg(us.skillCurrent) >= '" . $options['skill']['lower'] . "' AND avg(us.skillCurrent) <= '" . $options['skill']['upper'] . "'";
		}				  
		*/	
					   
		foreach ($where as $statement) {
			$select->where($statement);
		}
		
		foreach ($having as $statement) {
			$select->having($statement);
		}
		
		
		if (isset($options['order'])) {
			// Order by
			if ($options['order'] == 'skill') {
				$select->order('us.skillCurrent DESC');
			} elseif ($options['order'] == 'activity') {
				$select->order('u.lastActive DESC');
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
		
			$user = $savingClass->addUser($result);
			
			$user->getSport($result->sport)->setAttribs($result);
			
			if ($result->formatID !== null) {
				// User is looking for team
				$user->getSport($result->sport)->getFormat($result->format)->setAttribs($result);
			}
			
			$city = new Application_Model_City($result);
			$user->city = $city;
		}
		
		$savingClass->totalRows = $totalRows;
		
		return $savingClass;
		
	}
	
	/**
     * Find all events that user is scheduled for
     *
     * @params ($savingClass => user class)
     */
	public function getUserGames($savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->setIntegrityCheck(false);
		$select->from(array('ug' => 'user_games'))
			   ->join(array('g' => 'games'),
			   		  'ug.gameID = g.gameID')
			   ->join(array('st' => 'sport_types'),
			   		  'st.typeID = g.typeID')
			   ->join(array('ug2' => 'user_games'),
			   		  'ug2.gameID = ug.gameID',
					  array('(COUNT(ug2.userID) + SUM(ug2.plus)) as totalPlayers',
					  		'(SELECT COUNT(userID) FROM user_games WHERE gameID = ug.gameID AND confirmed = "1") AS confirmedPlayers'))
			   ->where('ug.userID = ?', $savingClass->userID)
			   ->where('g.date > CURDATE()')
			   ->group('ug.gameID');

		
		$results = $table->fetchAll($select);
		
		
		foreach ($results as $result) {
			//$savingClass->games->addGame($result, $byDay); true = by day
			$savingClass->games->addGame($result);
		}
		
		// Get team games also
		$select  = $table->select();
		
		$select->setIntegrityCheck(false);
		$select->from(array('ut' => 'user_teams'))
			   ->join(array('tg' => 'team_games'),
			   		  'ut.teamID = tg.teamID')
			   ->join(array('ll' => 'league_locations'),
			   		  'll.leagueLocationID = tg.leagueLocationID')
			   ->joinLeft(array('utg' => 'user_team_games'),
			   		  'utg.teamGameID = tg.teamGameID',
					  array('(SELECT COUNT(utg2.userID) FROM user_team_games as utg2 WHERE utg2.teamID = ut.teamID AND utg2.confirmed = 1 AND utg2.teamGameID = tg.teamGameID) as confirmedPlayers',
					  		'utg.confirmed as confirmed'))
			   ->where('ut.userID = ?', $savingClass->userID)
			   //->where('utg.userID = ?' ,  $savingClass->userID)
			   ->where('tg.date > CURDATE()')
			   ->group('tg.teamGameID');
		
		$results = $table->fetchAll($select);
		
		
		foreach ($results as $result) {
			$savingClass->games->addGame($result);
		}

		return $savingClass;
		
	}
	
	
	/**
     * Find all teams for user
     *
     * @params ($savingClass => where to save)
     */
	public function getUserTeams($savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->setIntegrityCheck(false);
		$select->from(array('ut' => 'user_teams'))
			   ->join(array('t' => 'teams'),
			   		  'ut.teamID = t.teamID')
			   ->join(array('ut2' => 'user_teams'),
			   		  'ut2.teamID = ut.teamID',
					  array('COUNT(ut2.userID) as totalPlayers'))
			   ->where('ut.userID = ?', $savingClass->userID)
			   ->group('ut.teamID');
		
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			$savingClass->teams->addTeam($result);
		}

		return $savingClass;
		
	}
	
	/**
     * Find all $types for user
     *
     * @params ($type => what type we are retrieving (friends, groups, teams, etc)
	 *			$savingClass => where to save)
     */
	public function getUserTypes($type, $savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$singular = rtrim($type,'s');
		
		$select->setIntegrityCheck(false);
		$select->from(array('ug' => 'user_' . $type . ''))
			   ->join(array('g' => $type),
			   		  'ug.' . $singular . 'ID = g.' . $singular . 'ID')
			   ->join(array('ug2' => 'user_' . $type . ''),
			   		  'ug2.' . $singular . 'ID = ug.' . $singular . 'ID',
					  array('COUNT(ug2.userID) as totalPlayers'))
			   ->where('ug.userID = ?', $savingClass->userID)
			   ->group('ug.' . $singular . 'ID');
		
		$results = $table->fetchAll($select);
		
		$method = 'add' . ucwords($singular);
		foreach ($results as $result) {
			$savingClass->$type->$method($result);
		}

		return $savingClass;
		
	}
	
	/**
     * Find all groups for user
     *
     * @params ($savingClass => where to save)
     
	public function getUserGroups($savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->setIntegrityCheck(false);
		$select->from(array('ug' => 'user_groups'))
			   ->join(array('g' => 'groups'),
			   		  'ug.groupID = g.groupID')
			   ->join(array('ug2' => 'user_groups'),
			   		  'ug2.groupID = ug.groupID',
					  array('COUNT(ug2.userID) as totalPlayers'))
			   ->where('ug.userID = ?', $savingClass->userID)
			   ->group('ug.groupID');
		
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			$savingClass->groups->addGroup($result);
		}

		return $savingClass;
		
	}
	*/
	
	/**
     * Find all friends for user
     *
     * @params ($savingClass => where to save)
     */
	public function getUserFriends($savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->setIntegrityCheck(false);
		$select->from(array('f' => 'friends'))
			   ->join(array('u' => 'users'),
			   		  '(u.userID = f.userID1 OR u.userID = f.userID2) AND u.userID != "' . $savingClass->userID . '"')
			   ->join(array('c' => 'cities'),
			   		  'u.cityID = c.cityID')
			   ->where('f.userID1 = "' . $savingClass->userID . '" OR  f.userID2 = "' . $savingClass->userID . '"')
			   ->where('u.active = 1');

		
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			/*if ($result->userID1 == $savingClass->userID) {
				// 1 is current user, save 2
				$userID = $result->userID2;
				$name = $result->userName2;
			} else {
				$userID = $result->userID1;
				$name = $result->userName1;
			}
			
			$nameParts = explode(' ', $name);
			$firstName = $nameParts[0];
			$lastName = $nameParts[1];
			*/
			
			$user = $savingClass->players->addUser($result);
			$user->getCity()->setAttribs($result);
		}

		return $savingClass;
		
	}
	
	
	/**
	 * remove all sport info for user
	 * @params ($andGames => remove user from games as well)
	 */
	public function removeSport($userID, $sportID, $andGames = true)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		if (empty($userID) || empty($sportID)) {
			return false;
		}
		
		$where = array('userID = ?' => $userID,
					   'sportID = ?' => $sportID);
		
		// Delete from user_sports
		
		$db->delete('user_sports', $where);
		$db->delete('user_sport_availabilities', $where);
		$db->delete('user_sport_formats', $where);
		
		$sql = "DELETE user_sport_positions FROM user_sport_positions INNER JOIN sport_positions ON sport_positions.positionID = user_sport_positions.positionID 
					WHERE user_sport_positions.userID='" . $userID . "' AND sport_positions.sportID='" . $sportID . "'";
		$db->query($sql);
		
		$sql = "DELETE user_sport_types FROM user_sport_types INNER JOIN sport_types ON sport_types.typeID = user_sport_types.typeID 
					WHERE user_sport_types.userID='" . $userID . "' AND sport_types.sportID='" . $sportID . "'";
		$db->query($sql);
		
		// Delete user_games
		if ($andGames) {
			$sql = "DELETE user_games FROM user_games INNER JOIN games ON games.gameID = user_games.gameID WHERE user_games.userID='" . $userID . "' AND games.sportID='" . $sportID . "'";
			echo $sql;
			return;
			$db->query($sql);
		}
		
		return true;
		
	}
	
	/**
	 * remove friend
	 */
	public function removeFriend($userID1, $userID2)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		if (empty($userID1) || empty($userID2)) {
			// Protect against empty variables
			return false;
		}
		
		$delete = 'DELETE 
					FROM friends
					WHERE (friends.userID1 = :userID1 AND friends.userID2 = :userID2) 
						OR (friends.userID1 = :userID2 AND friends.userID2 = :userID1)';
					
		return $db->query($delete,
							array(':userID1' => $userID1, 
								  ':userID2' => $userID2));
	}
	
	/**
	 * verify hash for user on signup
	 * @params ($savingClass => user model)
	 */
	public function verify($hash)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->from(array('u' => 'users'))
			   ->where('u.verifyHash = ?', $hash);
			   
		$result = $table->fetchRow($select);
		
		if ($result) {
			// Successful verification, change status to active
			$data = array('active' => 1);
			$where = array('userID = ?' => $result['userID']);
			
			$table->update($data, $where);
			
			return $result['userID'];
		} else {
			// Failed verification
			return false;
		}
	}
	
	
	/**
     * Find all of sports info for user (ie sports, types, positions, and availability
     *
     * @param ($userID => userID,
	 *		   $modelClass => userClass to save to)
     * 
     */
	public function getUserSportsInfo($userID, $modelClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		/* OLD CALL which lumped all results into many rows (duplicated data)..make separate calls instead
		$select->from(array('s'  => 'sports'))
			   ->join(array('sp' => 'sport_positions'),
			   		  's.sportID = sp.sportID')
			   ->join(array('usp' => 'user_sport_positions'),
			   		  'usp.positionID = sp.positionID')
			   ->join(array('st' => 'sport_types'),
			   		  'st.sportID = s.sportID')
			   ->join(array('ust' => 'user_sport_types'),
			   		  'ust.typeID = st.typeID')
			   ->join(array('us' => 'user_sports'),
			   		  'us.sportID = s.sportID AND us.userID = usp.userID') 
			   ->join(array('usa' => 'user_sport_availabilities'),
			   		  'usa.sportID = s.sportID')
			   ->join(array('usf' => 'user_sport_formats'),
			   		  'usf.sportID = s.sportID AND usf.userID = usp.userID')
			   ->where('usp.userID = ?', $userID);
		*/
		$select->from(array('s'  => 'sports'))
			   ->join(array('us' => 'user_sports'),
			   		  'us.sportID = s.sportID')
			   ->where('us.userID = ?', $userID);
		
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			/* Loop through each sport and make respective call to retrieve positions, availabilities, etc */
			$sport = $result->sport;
			$sportID = $result->sportID;
			
			$sportModel = $modelClass->getSport($sport);
			$sportModel->setAttribs($result);
			
			// Types (eg pickup, rally)
			$select  = $table->select();
			$select->setIntegrityCheck(false);
			$select->from(array('ust' => 'user_sport_types'))
				   ->join(array('st' => 'sport_types'),
			   		  'st.typeID = ust.typeID')				   
				   ->where('ust.userID = ?', $userID)
				   ->where('st.sportID = ?', $sportID);

			$types = $table->fetchAll($select);
			foreach ($types as $type) {
				$sportModel->setType($type->typeName)->setAttribs($type);
			}
			
			// Positions (eg WR,PG)
			$select  = $table->select();
			$select->setIntegrityCheck(false);
			$select->from(array('usp' => 'user_sport_positions'))
				   ->join(array('sp' => 'sport_positions'),
			   		  'sp.positionID = usp.positionID')
				   ->where('usp.userID = ?', $userID)
				   ->where('sp.sportID = ?', $sportID);
				   
			$positions = $table->fetchAll($select);
			foreach ($positions as $position) {
				$sportModel->getPosition($position->positionName)->setAttribs($position);
			}
			
			// Availabilities (eg monday at 2pm)
			$select  = $table->select();
			$select->setIntegrityCheck(false);
			$select->from(array('usa' => 'user_sport_availabilities'))
				   ->where('usa.userID = ?', $userID)
				   ->where('usa.sportID = ?', $sportID);
				   
			$availabilities = $table->fetchAll($select);
			foreach ($availabilities as $availability) {
				$sportModel->setAvailability($availability->day, $availability->hour)->setAttribs($availability);
			}
			
			// Formats (eg pickup, leagues)
			$select  = $table->select();
			$select->setIntegrityCheck(false);
			$select->from(array('usf' => 'user_sport_formats'))
				   ->where('usf.userID = ?', $userID)
				   ->where('usf.sportID = ?', $sportID);
				   
			$formats = $table->fetchAll($select);
			/*$formatArray = array();
			foreach ($formats as $format) {
				$formatArray[] = $format->format;
			}
			$sportModel->formats = $formatArray;
			*/
			
			foreach ($formats as $format) {
				$sportModel->getFormat($format->format)->setAttribs($format);
			}
			

			//$sportModel->getType($result->typeName)->setAttribs($result);
			//$sportModel->getPosition($result->positionName)->setAttribs($result);
			//$sportModel->setAvailability($result->day, $result->hour)->setAttribs($result);	
			
		}
			$modelClass->sortSportsByOverall();

		return $modelClass;

	}
	
	/**
	 * get all of user's ratings
	 * @params($savingClass => user model)
	 */
	public function getUserRatings($savingClass)
	{
	 	$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->setIntegrityCheck(false);
		$select->from(array('ur'  => 'user_ratings'))
			   ->join(array('r' => 'ratings'),
			   		  'ur.sportsmanship = r.ratingID',
					  array('r.value as sportsmanshipValue',
					  		'r.ratingName as sportsmanshipRatingName'))
			   ->join(array('r2' => 'ratings'),
			   		  'ur.attendance = r2.ratingID',
					  array('r2.value as attendanceValue'))
			   ->join(array('r3' => 'ratings'),
			   		  'ur.skill = r3.ratingID',
					  array('r3.value as skillValue',
					  		'r3.ratingName as skillRatingName'))
			   ->joinLeft(array('ss' => 'sport_skills'),
			   		  'ur.bestSkill = ss.sportSkillID',
					  array('skiller', 'skilling'))
			   ->where('ur.receivingUserID = ?', $savingClass->userID)
			   ->order('ur.dateHappened DESC');
		
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			$sport = strtolower($result->sport);
			$savingClass->getSport($sport)->ratings->addRating($result);
		}

		return $savingClass;
	}
	
	/**
	 * get notifications for user given in $savingClass
	 * @params ($userID 	 => userClass model,
	 			$savingClass => where to save the information (Notifications object),
				$onlyNew	 => only select new notifications )
	 */
	public function getUserNotifications($userClass, $savingClass, $onlyNew = false)
	{		
		/* MUST RETRIEVE GAMEIDS, TEAMIDS, AND GROUPIDS FOR ALL GAMES, TEAMS, GROUPS THAT USER ($userClass) is currently in */
		$db = Zend_Db_Table::getDefaultAdapter();   
		
		$lastRead = $userClass->lastRead;
		$gameIDs  = ($userClass->hasValue('games') ? $userClass->games->implodeIDs('games', 'gameID') : '');
		$teamIDs  = ($userClass->hasValue('teams') ? $userClass->teams->implodeIDs('teams') : '');

		$names	  = array('game','team');
		$userID   = $userClass->userID;
		
		/*$select = "SELECT `nl`.*, 
						  `n`.*, 
						  `u`.firstName as actingFirstName, 
						  `u`.lastName as actingLastName, 
						  `u2`.firstName as receivingFirstName, 
						  `u2`.lastName as receivingLastName, 
						  `u`.userID as actingUserID,
						  `u2`.userID as receivingUserID,
						  COALESCE(`ga`.sport,`t`.sport,`ur`.sport) as sport,
						  COALESCE(`ga`.date,`ur`.dateHappened) as date, 
						  ga.parkName, 
						  ga.parkID, 
						  ga.date, 
						  t.teamName, 
						  gr.groupName
					 FROM `notification_log` AS `nl`
					 INNER JOIN `notifications` AS `n` ON n.notificationID = nl.notificationID
					 LEFT JOIN `users` AS `u` ON u.userID = nl.actingUserID
					 LEFT JOIN `users` AS `u2` ON u2.userID = nl.receivingUserID
					 LEFT JOIN `games` AS `ga` ON ga.gameID = nl.gameID
					 LEFT JOIN `teams` AS `t` ON t.teamID = nl.teamID
					 LEFT JOIN `groups` AS `gr` ON gr.groupID = nl.groupID
					 LEFT JOIN `user_ratings` AS `ur` ON ur.userRatingID = nl.ratingID 
					 WHERE ((nl.receivingUserID = " . $userID . ") OR
					 	(nl.actingUserID =  " . $userID . " AND n.action = 'friend') 
						SELECT `nl`.*, 
						  `n`.*, 
						  COUNT(nl.notificationID) as likeNotifications,
						  `u`.firstName as actingFirstName, 
						  `u`.lastName as actingLastName, 
						  `u2`.firstName as receivingFirstName, 
						  `u2`.lastName as receivingLastName, 
						  `u`.userID as actingUserID,
						  `u2`.userID as receivingUserID,
						  COALESCE(`ga`.sport,`t`.sport,`ur`.sport) as sport,
						  COALESCE(`ga`.date,`ur`.dateHappened) as date, 
						  ga.parkName, 
						  ga.parkID, 
						  ga.date, 
						  t.teamName
					 FROM `notification_log` AS `nl`
					 INNER JOIN `notifications` AS `n` ON n.notificationID = nl.notificationID
					 LEFT JOIN `users` AS `u` ON u.userID = nl.actingUserID
					 LEFT JOIN `users` AS `u2` ON u2.userID = nl.receivingUserID
					 LEFT JOIN `games` AS `ga` ON ga.gameID = nl.gameID
					 LEFT JOIN `teams` AS `t` ON t.teamID = nl.teamID
					 LEFT JOIN `user_ratings` AS `ur` ON ur.userRatingID = nl.ratingID 
					 WHERE ((nl.receivingUserID = " . $userID . ") OR
					 	(nl.actingUserID =  " . $userID . " AND n.action = 'friend')";
						 */
						 
		// Group by # of players
		$select = "(SELECT `nl`.gameID,
							`nl`.teamID,
							`nl`.parkID,
							`nl`.notificationLogID, 
						   MAX(nl.dateHappened) as dateHappened,
						  `n`.*, 
						  COUNT(nl.notificationID) as likeNotifications,
						  `u`.firstName as actingFirstName, 
						  `u`.lastName as actingLastName, 
						  `u2`.firstName as receivingFirstName, 
						  `u2`.lastName as receivingLastName, 
						  `u`.userID as actingUserID,
						  `u2`.userID as receivingUserID,
						  COALESCE(`ga`.sport,`t`.sport,`ur`.sport) as sport,
						  COALESCE(`ga`.date,`ur`.dateHappened) as date, 
						  ga.parkName, 
						  ga.parkID, 
						  ga.date, 
						  t.teamName
					 FROM `notification_log` AS `nl`
					 INNER JOIN `notifications` AS `n` ON n.notificationID = nl.notificationID
					 LEFT JOIN `users` AS `u` ON u.userID = nl.actingUserID
					 LEFT JOIN `users` AS `u2` ON u2.userID = nl.receivingUserID
					 LEFT JOIN `games` AS `ga` ON ga.gameID = nl.gameID
					 LEFT JOIN `teams` AS `t` ON t.teamID = nl.teamID
					 LEFT JOIN `user_ratings` AS `ur` ON ur.userRatingID = nl.ratingID 
					 WHERE (((nl.receivingUserID = " . $userID . ") OR
					 	(nl.actingUserID =  " . $userID . " AND n.action = 'friend' AND n.type IS NULL) ";
		
		$counter = 0;
		$success = false;
		foreach ($names as $name) {
			$nameCombo = $name . 'IDs';
			if (!empty($$nameCombo)) {
				if ($counter == 0) {
					// First successful group
					$select .= "OR ((";
				} else {
					$select .= " OR ";
				}
				
				$select .= "nl." . $name . "ID IN (" . $$nameCombo . ")";
				if (!$success) {
					$success = true;
				}
				$counter++;
			}
		}
		
		if ($success) {
			// One of $names had a value
			$select .= ") AND (n.action != 'create' AND nl.receivingUserID IS NULL AND nl.actingUserID != " . $userID . "))";
		} 
		
		$select .= ") AND n.action = 'join') ";
		
		
		if ($onlyNew) {
			// Select only notifications since last read
			$select .= " AND nl.dateHappened > '" . $lastRead . "' ";
		} else {
			// Select old notifications
			$select .= " AND nl.dateHappened <= '" . $lastRead . "' ";
		}
	
		$select .= " GROUP BY nl.notificationID, nl.gameID, nl.teamID) UNION ";
		$select .= "(SELECT `nl`.gameID,
							`nl`.teamID,
							`nl`.parkID,
							`nl`.notificationLogID,
						 `nl`.dateHappened as dateHappened, 
						  `n`.*, 
						  " . new Zend_Db_Expr('1') . " as likeNotifications,
						  `u`.firstName as actingFirstName, 
						  `u`.lastName as actingLastName, 
						  `u2`.firstName as receivingFirstName, 
						  `u2`.lastName as receivingLastName, 
						  `u`.userID as actingUserID,
						  `u2`.userID as receivingUserID,
						  COALESCE(`ga`.sport,`t`.sport,`ur`.sport) as sport,
						  COALESCE(`ga`.date,`ur`.dateHappened) as date, 
						  ga.parkName, 
						  ga.parkID, 
						  ga.date, 
						  t.teamName
					 FROM `notification_log` AS `nl`
					 INNER JOIN `notifications` AS `n` ON n.notificationID = nl.notificationID
					 LEFT JOIN `users` AS `u` ON u.userID = nl.actingUserID
					 LEFT JOIN `users` AS `u2` ON u2.userID = nl.receivingUserID
					 LEFT JOIN `games` AS `ga` ON ga.gameID = nl.gameID
					 LEFT JOIN `teams` AS `t` ON t.teamID = nl.teamID
					 LEFT JOIN `user_ratings` AS `ur` ON ur.userRatingID = nl.ratingID 
					 WHERE ((nl.receivingUserID = " . $userID . ") OR
					 	(nl.actingUserID =  " . $userID . " AND ((n.action = 'friend' AND n.type IS NULL)
							OR (n.action = 'join' AND n.type = 'team'))) "; // include case when user requests to join team and is accepted, notify user they were accepted
		
		$counter = 0;
		$success = false;
		foreach ($names as $name) {
			$nameCombo = $name . 'IDs';
			if (!empty($$nameCombo)) {
				if ($counter == 0) {
					// First successful group
					$select .= "OR ((";
				} else {
					$select .= " OR ";
				}
				
				$select .= "nl." . $name . "ID IN (" . $$nameCombo . ")";
				if (!$success) {
					$success = true;
				}
				$counter++;
			}
		}
		
		if ($success) {
			// One of $names had a value
			$select .= ") AND (n.action != 'create' AND nl.receivingUserID IS NULL AND nl.actingUserID != " . $userID . "))";
		} 
		
		$select .= " AND n.action != 'join') ";
		
		
		if ($onlyNew) {
			// Select only notifications since last read
			$select .= " AND nl.dateHappened > '" . $lastRead . "' ";
		} else {
			// Select old notifications
			$select .= " AND nl.dateHappened <= '" . $lastRead . "' ";
		}
		$select .= " ) ORDER BY dateHappened DESC";
		
		if (!$onlyNew) {
			$select .= " LIMIT 10";
		}
		
		
		$results = $db->fetchAll($select);

		foreach ($results as $result) {
			$notification = $savingClass->addNotification($result);
			$notification->parentUserID = $userClass->userID;
		}

	}
	
	/**3
	 * get user's teams, friends, and groups
	 * @params($savingClass => user model)
	 */
	public function getUserFriendsGroupsTeams($savingClass)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$userID  = $savingClass->userID;

		
		$friends = "SELECT IF(f.userID1 = '" . $userID . "', f.userID2, f.userID1) AS `id`, 
						   IF(f.userID1 = '" . $userID . "', f.userName2, f.userName1) AS `name`, 
						  'users' AS `prefix`,
						  '' AS picture 
						FROM `friends` AS `f`
						INNER JOIN `users` u1 ON f.userID1 = u1.userID
						INNER JOIN `users` u2 ON f.userID2 = u2.userID
					   WHERE (u1.active = 1 AND u2.active = 1) 
					   		AND (f.userID1 = '" . $userID . "' OR f.userID2 = '" . $userID . "')";
					   
		$teams  = "SELECT `ut`.`teamID` AS `id`, 
						  `t`.`teamName` AS `name`, 
						  'teams' AS `prefix`,
						  `t`.`picture` AS picture 
						FROM `user_teams` AS `ut` 
					INNER JOIN teams as `t` ON t.teamID = ut.teamID
					WHERE ut.userID = '" . $userID . "'";
					   
		/*$groups  = "SELECT `ug`.`groupID` AS `id`, 
						  `g`.`groupName` AS `name`, 
						  'groups' AS `prefix` FROM `user_groups` AS `ug` 
					INNER JOIN groups as `g` ON g.groupID = ug.groupID
					WHERE ug.userID = '" . $userID . "'";*/
		

					  
		$select  = $friends . " UNION "
				 . $teams;
		
		$results = $db->fetchAll($select);
		
		foreach ($results as $result) {
			$type = $result['prefix'];
			if ($type == 'users') {
				// Friend
				$name = explode(' ', $result['name']);
				$result['firstName'] = $name[0];
				$result['lastName']  = $name[1];
				$result['userID']    = $result['id'];
				$savingClass->players->addUser($result);
			} elseif ($type == 'teams') {
				// Team
				$result['teamName'] = $result['name'];
				$result['teamID']   = $result['id'];
				$savingClass->teams->addTeam($result);
			} /*elseif ($type == 'groups') {
				// Group
				$result['groupName'] = $result['name'];
				$result['groupID']   = $result['id'];
				$savingClass->groups->addGroup($result);
			}*/
		}
				
				
	}
	
	
	/**
	 * get available users in an area at a given time
	 * @params ($datetime => datetime object of time to look for,
	 *			$sportID => id of sport,
	 *			$location => location model to search near)
	 */
	public function getAvailableUsers($datetime, $sportID, $location, $savingClass = false)
	{
	 	$table   = $this->getDbTable();
		$select  = $table->select();
		
		$formattedDate = $datetime->format('Y-m-d');// Format datetime into useable date format for sql (YYYY-MM-DD)
		
		$games = "(SELECT `us`.userID, `us`.often, `ug`.userID as backupID, `g`.sportID
					FROM `user_sports` AS `us` 
					LEFT JOIN `user_games` AS `ug` ON ug.userID = us.userID 
					INNER JOIN `games` AS `g` ON g.gameID = ug.gameID 
					WHERE g.sportID = '" . $sportID . "'
					GROUP BY us.userID
					HAVING MIN(ABS(DATEDIFF(g.date,'" . $formattedDate . "'))) > us.often
					)";
		
		$select->setIntegrityCheck(false);
		$select->from(array('usa'  => 'user_sport_availabilities'))
			   ->join(array('us' => 'user_sports'),
			   		  'usa.userID = us.userID AND usa.sportID = us.sportID')
			   ->join(array('g' => new Zend_Db_Expr($games)),
			   		  'g.userID = usa.userID')
			   ->where('usa.day = ?', $datetime->format('w'))
			   ->where('usa.hour = ?', $datetime->format('G'))
			   ->where('usa.sportID = ?', $sportID)
			   ->group('usa.userID');
			   
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			$savingClass->addUser($result);
		}
		
		return $savingClass;
		
	}
			   
		
	/** 
	 * get subscribed games for user
	 */
	public function getSubscribedGames($userID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('gs' => 'game_subscribers'))
			   ->join(array('g' => 'games'),
			   		  'gs.gameID = g.gameID')
			   ->join(array('st' => 'sport_types'),
			   		  'st.typeID = g.typeID')
			   ->where('gs.userID = ?', $userID);
			   
		$results = $table->fetchAll($select);
		
		$games = new Application_Model_Games();
		
		foreach ($results as $result) {
			$games->addGame($result);
		}
		
		return $games;
	}
	
	public function getLastGame($userID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('og' => 'old_games'))
			   ->join(array('oug' => 'old_user_games'),
			   		  'og.gameID = oug.gameID')
			   ->where('og.date > (now() - INTERVAL 1 WEEK) AND og.date < now()')
			   ->where('og.canceled = ?', 0)
			   ->where('oug.userID = ?', $userID)
			   ->order('og.date desc')
			   ->limit(1);
		
				 
		$result = $table->fetchRow($select);
		
		if (!$result) {
			return false;
		}
		
		$game = new Application_Model_Game($result);
		$gameID = $game->gameID;

		// Test if user has already rated for this game
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('ur' => 'user_ratings'))
			   ->where('ur.givingUserID = ?', $userID)
			   ->where('ur.gameID = ?', $gameID);
			   
		$result = $table->fetchRow($select);

		if ($result) {
			// Rating was found
			return false;
		}
		
		
		// Get all players
		/*
		$select = $table->select();
		$select->setIntegrityCheck(false);

		$select->from(array('ug' => 'user_games'))
			   ->join(array('u' => 'users'),
			   		  'ug.userID = u.userID')
			   ->where('ug.gameID = ?', $gameID)
			   ->where('u.fake != ?', 1);
		*/
		// Only get players that user has not rated recently
		$sql = "SELECT `oug`.*, `u`.* 
					FROM `old_user_games` AS `oug` 
					INNER JOIN `users` AS `u` ON oug.userID = u.userID 
					INNER JOIN `user_sports` AS `us` ON (us.sportID = '" . $game->sportID . "' AND us.userID = oug.userID)
					WHERE (oug.gameID = '" . $game->gameID . "') 
						AND (u.fake != 1) 
						AND u.userID != '" . $userID . "'
						AND u.userID NOT IN (SELECT receivingUserID 
												FROM user_ratings 
												WHERE givingUserID = '" . $userID . "' 
													AND dateHappened > NOW() - INTERVAL 1 MONTH
													AND sportID = '" . $game->sportID . "'
											)";
		
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$results = $db->query($sql);
		
		foreach ($results as $result) {
			$game->addPlayer($result);
		}
		
		return $game;
	}
		
		
	/**
	 * set skillCurrent, sportsmanship, or attendance based on all reviews
	 */
	public function setUserRating($rating, $sportID, $userID)
	{
		
		$db = Zend_Db_Table::getDefaultAdapter();
		
		if (empty($sportID) || empty($userID)) {
			return false;
		}
		
		$rating = strtolower($rating);
		if ($rating == 'skill') {
			$pre = '+ us.skillInitial';
			$final = 'skillCurrent';
			$post = 'skill';
			$plus = '+ 1';
		} else {
			$post = $final = $rating;
			$plus = $pre = '';
		}
	
		
		$sql = "UPDATE user_sports as us
				INNER JOIN (SELECT FLOOR((SUM(r.value)" . $pre . ")/(COUNT(ur." . $post . ")" . $plus . ")) as ratingValue,
									us.userSportID 
					FROM user_sports us 
						INNER JOIN user_ratings ur ON us.userID = ur.receivingUserID AND us.sportID = ur.sportID 
						INNER JOIN ratings r ON r.ratingID = ur." . $post . " 
					WHERE us.userID = '" . $userID . "' AND us.sportID = '" . $sportID . "') as us2 ON us.userSportID = us2.userSportID 
					SET us." . $final . " = us2.ratingValue WHERE us.userID = '" . $userID . "' AND us.sportID = '" . $sportID . "'";
	
		$results = $db->query($sql);		
		
	}
	
	
	/**
     * Reset user class to home location
     * @params ($savingClass => user class)
     */
	public function resetHomeLocation($savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$userID  = $savingClass->userID;
		
		$select->setIntegrityCheck(false);
		$select->from(array('ul'  => 'user_locations'),
					  array('AsText(ul.location) as location'))
				->join(array('u' => 'users'),
			   		  'u.userID = ul.userID')
			    ->join(array('c' => 'cities'),
			   		  'c.cityID = u.cityID')
				->where('u.userID = ?', $userID)
				->limit(1);
		
		$result = $table->fetchRow($select);
		
		$city = new Application_Model_City($result);
		$location = new Application_Model_Location($result);
		$savingClass->city = $city;
		$savingClass->location = $location;
		
		return $savingClass;
	}
	
	/**
	 * get number of users in given area as well as # of people who joined since user was last active
	 * @returns associative array of totalUsers and newUsers
	 */
	public function getUsersInArea($userID, $latitude, $longitude, $lastActive = false)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$bounds = $this->getBounds($latitude, $longitude, 12);
		
		$select->from(array('ul' => 'user_locations'),
					  array(''))
			   ->join(array('u' => 'users'),
			   		  'ul.userID = u.userID',
					  array('COUNT(u.userID) as totalUsers'))
			   ->where($this->getAreaWhere($bounds['upper'], $bounds['lower'], 'ul.location'));
		
		$result = $table->fetchRow($select);
		
		if ($result['totalUsers'] > 100) {
			// More than 90 users in given area, minimum alert limit has been reached
			return false;
		}
		
		$returnArray = array();
		$returnArray['totalUsers'] = $result['totalUsers'];
		
		if ($lastActive) {
			$select = $table->select();
			$select->setIntegrityCheck(false);
			
			$select->from(array('ul' => 'user_locations'),
						  array(''))
				   ->join(array('u' => 'users'),
						  'ul.userID = u.userID',
						  array('COUNT(u.userID) as newUsers'))
				   ->where($this->getAreaWhere($bounds['upper'], $bounds['lower'], 'ul.location'))
				   ->where('u.joined > "' . $lastActive . '"');
			
			$result = $table->fetchRow($select);
			
			$returnArray['newUsers'] = $result['newUsers'];
		}
		
		return $returnArray;
		
	}

		
		
	
	/**
     * Create a hash (encrypt) of a plain text password.
     *
     * @param string $password Plain text user password to hash
     * @return string The hash string of the password
     */
    public function hashPassword($password) {
        return $this->hasher()->HashPassword($password);
    }
 
    /**
     * Compare the plain text password with the $hashed password.
     *
     * @param string $password
     * @param string $hash The hashed password
     * @param int $user_id The user row ID
     * @return bool True if match, false if no match.
     */
    public function checkPassword($password, $hash, $user_id = '') {
        // Check if we are still using regular MD5 (32 chars)
		
        if (strlen($hash) <= 32) {
            $check = ($hash == md5($password));
            if ($check && $user_id) {
                // Rehash using new PHPass-generated hash
                $this->setPassword($password, $user_id);
                $hash = $this->hashPassword($password);
            }
        }
 
        $check = $this->hasher()->CheckPassword($password, $hash);
 		
        return $check;
    }
  
    /**
     * Checks for Webjawns_PasswordHash in registry. If not present, creates PHPass object.
     *
     * @uses Zend_Registry
     * @uses Webjawns_PasswordHash
     *
     * @return Webjawns_PasswordHash PHPass
     */
    public function hasher() {
	
        if (!Zend_Registry::isRegistered('my_hasher')) {
            Zend_Registry::set('my_hasher', new My_Auth_PasswordHash(8, false));
        }
        return Zend_Registry::get('my_hasher');
    }

}