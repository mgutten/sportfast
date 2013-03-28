<?php

class Application_Model_UsersMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Users';
	
	public function getUserBy($column, $value, $savingClass = false)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		/*$select->where($column . ' = ' . '?', $value)
			   ->limit(1);
			   */
		$select->from(array('u' => 'users'))
               ->join(array('c' => 'cities'),
                      'u.cityID = c.cityID')
			   ->join(array('ul' => 'user_locations'),
			   		  'ul.userID = u.userID',
					   array('AsText(location) as location'))
			   ->where($column . ' = ?', $value)
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
     * Find all events that user is scheduled for
     *
     * @params ($savingClass => where to save)
     */
	public function getUserGames($savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->setIntegrityCheck(false);
		$select->from(array('ug' => 'user_games'))
			   ->join(array('g' => 'games'),
			   		  'ug.gameID = g.gameID')
			   ->join(array('ug2' => 'user_games'),
			   		  'ug2.gameID = ug.gameID',
					  array('COUNT(ug2.userID) as totalPlayers',
					  		'(SELECT COUNT(userID) FROM user_games WHERE gameID = ug.gameID AND confirmed = "1") AS confirmedPlayers'))
			   ->where('ug.userID = ?', $savingClass->userID)
			   ->where('g.date > CURDATE()')
			   ->group('ug.gameID');
		
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			$savingClass->games->addGame($result, true);
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
     */
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
			   ->where('f.userID1 = "' . $savingClass->userID . '" OR  f.userID2 = "' . $savingClass->userID . '"');
		
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			$savingClass->friends->addUser($result);
		}

		return $savingClass;
		
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
				$sportModel->getType($type->typeName)->setAttribs($type);
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
			$formatArray = array();
			foreach ($formats as $format) {
				$formatArray[] = $format->format;
			}
			$sportModel->formats = $formatArray;
			

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
					  array('r.value as sportsmanshipValue'))
			   ->join(array('r2' => 'ratings'),
			   		  'ur.attendance = r2.ratingID',
					  array('r2.value as attendanceValue'))
			   ->join(array('r3' => 'ratings'),
			   		  'ur.skill = r3.ratingID',
					  array('r3.value as skillValue'))
			   ->join(array('ss' => 'sport_skills'),
			   		  'ur.bestSkill = ss.sportSkillID',
					  array('skiller', 'skilling'))
			   ->where('ur.receivingUserID = ?', $savingClass->userID);
		
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
		$gameIDs  = ($userClass->hasValue('games') ? $userClass->games->implodeIDs('games') : '');
		$teamIDs  = ($userClass->hasValue('teams') ? $userClass->teams->implodeIDs('teams') : '');

		$names	  = array('game','team','group');
		$userID   = $userClass->userID;
		
		$select = "SELECT `nl`.*, 
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
					 	(nl.actingUserID =  " . $userID . " AND n.action = 'friend') ";
		
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
			$select .= ") AND n.action != 'create')";
		} 
		
		$select .= ") ";
		
		
		if ($onlyNew) {
			// Select only notifications since last read
			$select .= "AND nl.dateHappened > '" . $lastRead . "' ";
		} else {
			// Select old notifications
			$select .= "AND nl.dateHappened <= '" . $lastRead . "' ";
		}
		
		$select .= "ORDER BY nl.dateHappened DESC";
		
		if (!$onlyNew) {
			$select .= " LIMIT 10";
		}
		
		
		
		$results = $db->fetchAll($select);

		foreach ($results as $result) {
			$savingClass->addNotification($result);
		}

	}
	
	/**
	 * get user's teams, friends, and groups
	 * @params($savingClass => user model)
	 */
	public function getUserFriendsGroupsTeams($savingClass)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$userID  = $savingClass->userID;

		
		$friends = "SELECT IF(f.userID1 = '" . $userID . "', f.userID2, f.userID1) AS `id`, 
						   IF(f.userID1 = '" . $userID . "', f.userName2, f.userName1) AS `name`, 
						  'users' AS `prefix` FROM `friends` AS `f` 
					   WHERE (f.userID1 = '" . $userID . "' OR f.userID2 = '" . $userID . "')";
					   
		$teams  = "SELECT `ut`.`teamID` AS `id`, 
						  `t`.`teamName` AS `name`, 
						  'teams' AS `prefix` FROM `user_teams` AS `ut` 
					INNER JOIN teams as `t` ON t.teamID = ut.teamID
					WHERE ut.userID = '" . $userID . "'";
					   
		$groups  = "SELECT `ug`.`groupID` AS `id`, 
						  `g`.`groupName` AS `name`, 
						  'groups' AS `prefix` FROM `user_groups` AS `ug` 
					INNER JOIN groups as `g` ON g.groupID = ug.groupID
					WHERE ug.userID = '" . $userID . "'";
		

					  
		$select  = $friends . " UNION "
				 . $teams . " UNION "
				 . $groups;
		
		$results = $db->fetchAll($select);
		
		foreach ($results as $result) {
			$type = $result['prefix'];
			if ($type == 'users') {
				// Friend
				$name = explode(' ', $result['name']);
				$result['firstName'] = $name[0];
				$result['lastName']  = $name[1];
				$result['userID']    = $result['id'];
				$savingClass->friends->addUser($result);
			} elseif ($type == 'teams') {
				// Team
				$result['teamName'] = $result['name'];
				$result['teamID']   = $result['id'];
				$savingClass->teams->addTeam($result);
			} elseif ($type == 'groups') {
				// Group
				$result['groupName'] = $result['name'];
				$result['groupID']   = $result['id'];
				$savingClass->groups->addGroup($result);
			}
		}
				
				
	}
		
		
		
	
	/**
     * Reset user class to home location
     * @params ($savingClass => where to save)
     */
	public function resetHomeLocation($savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->setIntegrityCheck(false);
		$select->from(array('ul'  => 'user_locations'),
					  array('AsText(ul.location) as location'))
				->join(array('u' => 'users'),
			   		  'u.userID = ul.userID')
			    ->join(array('c' => 'cities'),
			   		  'c.cityID = u.cityID')
				->limit(1);
		
		$result = $table->fetchRow($select);
		
		$city = new Application_Model_City($result);
		$location = new Application_Model_Location($result);
		$savingClass->city = $city;
		$savingClass->location = $location;
		
		return $savingClass;
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