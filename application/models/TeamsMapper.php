<?php

class Application_Model_TeamsMapper extends Application_Model_TypesMapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Teams';
	
	/**
	 * Get all teams that meet user needs
	 * @params($userClass   => user object,
	 *		   $savingClass => teams object,
	 *		   $options		=> additional sql "where" constraints)
	 */
	
	public function findUserTeams($userClass, $savingClass, $options = false)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$userID  = $userClass->userID;
		$teamIDs = ($userClass->hasValue('teams') ? $userClass->teams->implodeIDs('teams') : '');
		
		// Default location to search near is user's home location, look for games within $distance of user's home location
		$latitude  = $userClass->getLocation()->getLatitude();
		$longitude = $userClass->getLocation()->getLongitude();
		$bounds = $this->getBounds($latitude, $longitude);	
		
		$zipcodeQuery = '(SELECT cityID FROM zipcodes as z2
							WHERE MBRContains(
												LINESTRING(
												' . $bounds["upper"] . ' , ' . $bounds["lower"] . '
												), z2.location
											 )
							GROUP BY cityID)';
		
		$select = $table->select();
		$select->setIntegrityCheck(false);
		/*$select->from(array('t'  => 'teams'))
			   ->join(array('tll' => 'team_leagues'),
			   		 'tll.teamID = t.teamID')
			   ->join(array('ll' => 'league_levels'),
			   		 'll.leagueLevelID = tll.leagueLevelID')
			   ->join(array('uus' => 'user_sports'),
			   		 'uus.sportID = t.sportID AND uus.userID = "' . $userID . '"',
					 array('skillCurrent as userSkill'))
			   ->joinLeft(array('ut' => 'user_teams'),
			   		 'ut.teamID = t.teamID',
					 array(''))
			   ->joinLeft(array('us' => 'user_sports'),
			   		 'ut.userID = us.userID AND us.sportID = t.sportID',
					 array('avg(us.skillCurrent) as averageSkill',
					 	   'avg(us.attendance) as averageAttendance',
						   'avg(us.sportsmanship) as averageSportsmanship',
						   'avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID) as skillDifference',
						   'COUNT(us.userID) as totalPlayers'
						   ))
			   ->where('t.public = "1"')
			   ->where('uus.skillCurrent >= ll.minSkill AND uus.skillCurrent <= ll.maxSkill')
			   ->where('ll.cityID = ?', $userClass->city->cityID);*/
			   
		

		$select->from(array('t'  => 'teams'))
			   ->join(array('uus' => 'user_sports'),
			   		 'uus.sportID = t.sportID AND uus.userID = "' . $userID . '"',
					 array('skillCurrent as userSkill'))
			   ->join(array('z' => new Zend_Db_Expr($zipcodeQuery)),
			   				'z.cityID = t.cityID')
			   ->joinLeft(array('ut' => 'user_teams'),
			   		 'ut.teamID = t.teamID',
					 array(''))
			   ->joinLeft(array('us' => 'user_sports'),
			   		 'ut.userID = us.userID AND us.sportID = t.sportID',
					 array('avg(us.skillCurrent) as averageSkill',
					 	   'avg(us.attendance) as averageAttendance',
						   'avg(us.sportsmanship) as averageSportsmanship',
						   'avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID) as skillDifference',
						   'COUNT(us.userID) as totalPlayers'
						   ))
			   ->where('t.public = "1"')
			   ->where('uus.skillCurrent >= t.minSkill AND uus.skillCurrent <= t.maxSkill');

		
		
		if ($options) {
			// Additional options are set
			foreach ($options as $option) {
				$select->where($option);
			}
		}
		
		if (!empty($teamIDs)) {
			// Do not select teams that are already the user's
			$select->where('t.teamID NOT IN (' . $teamIDs . ')');
		}
		
		$select->group('t.teamID');
			   //->order('abs(avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID)) ASC');

		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			$savingClass->addTeam($result);
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
	public function findTeams($options, $userClass, $savingClass, $limit = 200)
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
		
		$zipcodeQuery = '(SELECT cityID FROM zipcodes as z2
							WHERE MBRContains(
												LINESTRING(
												' . $bounds["upper"] . ' , ' . $bounds["lower"] . '
												), z2.location
											 )
							GROUP BY cityID)';		   
		

		$select->from(array('t'  => 'teams'),
					  array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS t.*')))
			   ->joinLeft(array('uus' => 'user_sports'),
			   		 'uus.sportID = t.sportID AND uus.userID = "' . $userID . '"',
					 array('skillCurrent as userSkill'))
			   ->join(array('z' => new Zend_Db_Expr($zipcodeQuery)),
			   				'z.cityID = t.cityID')
			   ->joinLeft(array('ut' => 'user_teams'),
			   		 'ut.teamID = t.teamID',
					 array(''))
			   ->joinLeft(array('us' => 'user_sports'),
			   		 'ut.userID = us.userID AND us.sportID = t.sportID',
					 array('avg(us.skillCurrent) as averageSkill',
					 	   'avg(us.attendance) as averageAttendance',
						   'avg(us.sportsmanship) as averageSportsmanship',
						   'avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID) as skillDifference',
						   'COUNT(us.userID) as totalPlayers'
						   ))
			   ->where('t.public = "1"')
			   ->where('CASE WHEN uus.skillCurrent IS NOT NULL THEN uus.skillCurrent >= t.minSkill AND uus.skillCurrent <= t.maxSkill ELSE 1 END');


		$sportWhere = '';
		$counter = 0;
		
		foreach ($options['sports'] as $sport => $inner) {
			if ($counter != 0) {
				$sportWhere .= ' OR ';
			}
			
			$sportWhere .= "(t.sport = '" . $sport . "' )";
			$counter++;
		}
		
		$where[] = $sportWhere;
		
		/*
		if (!empty($options['skill'])) {
			$having[] = "avg(us.skillCurrent) >= '" . $options['skill']['lower'] . "' AND avg(us.skillCurrent) <= '" . $options['skill']['upper'] . "'";
		}				  
		*/	
					   
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

		
		$select->group('t.teamID');
			   //->order('abs(avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID)) ASC');
		
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
			$savingClass->addTeam($result);
		}
		
		$savingClass->totalRows = $totalRows;
		
		return $savingClass;
		
	}
	
	/**
	 * test if user has been invited to a particular team
	 */
	public function isInvited($userID, $teamID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('nl' => 'notification_log'))
			   ->join(array('n' => 'notifications'),
			   		  'n.notificationID = nl.notificationID')
			   ->where('n.action = ?', 'invite')
			   ->where('n.type = ?', 'team')
			   ->where('nl.receivingUserID = ?', $userID)
			   ->where('nl.teamID = ?', $teamID);
		
		$result = $table->fetchRow($select);
		
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * is user invited to team game
	 */
	public function isInvitedToGame($userID, $teamGameID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->setIntegrityCheck(false);
		
		$select->from(array('nl' => 'notification_log'))
			   ->join(array('n' => 'notifications'),
			   		  'n.notificationID = nl.notificationID')
			   ->where('n.action = ?', 'invite')
			   ->where('n.type = ?', 'teamgame')
			   ->where('nl.teamGameID = ?', $teamGameID)
			   ->where('nl.receivingUserID = ?', $userID);

		$results = $table->fetchAll($select);
		
		if (isset($results[0])) {
			return true;
		} else {
			return false;
		}
		
	}
	
	/**
	 * add reserve player to team in db
	 */
	public function addReserve($userID, $teamModel)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		if (empty($userID)) {
			return;
		}
		
		$teamReserveID = $db->insert('team_reserves', array('teamID' => $teamModel->teamID,
														    'userID' => $userID));
															
		return $teamReserveID;
										   
	}
	
	/**
	 * add reserve player to team in db
	 */
	public function removeReserve($userID, $teamModel)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		if (empty($userID) || !$teamModel->hasValue('teamID')) {
			return false;
		}


		$db->delete('team_reserves', array('teamID = ?' => $teamModel->teamID,
										   'userID = ?' => $userID));
															
										   
	}
		
	
	
	/**
	 * delete game
	 */
	public function delete($teamModel)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$teamID = $teamModel->teamID;
		
		
		
		$where = array('teamID = ?' => $teamID);
		
		if (empty($teamID)) {
			// Safety check to make sure gameID is set before continuing
			return false;
		}
		
		
		
		$this->move($teamID);
		
		$db->delete('teams', $where);
		$db->delete('team_captains', $where);
		$db->delete('team_messages', $where);
		
	}
	
	/**
	 * move deleted rows from active table to "old" table
	 */
	public function move($id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "INSERT INTO old_teams
					(SELECT t.*, (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR)
					FROM teams as t
					WHERE t.teamID = '" . $id . "')";
					
		$db->query($sql);
	}
		
	
	/**
	 * get team info from db
	 * @params ($teamID => teamID
	 *			$savingClass => team model)
	 */
	public function getTeamByID($teamID, $savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('t'  => 'teams'))
			   ->joinLeft(array('ut' => 'user_teams'),
			   		 'ut.teamID = t.teamID',
					 array('COUNT(ut.userID) as totalPlayers'))
			   ->joinLeft(array('us' => 'user_sports'),
			   		 'ut.userID = us.userID AND us.sportID = t.sportID',
					 array('avg(us.skillCurrent) as averageSkill',
					 	   'avg(us.attendance) as averageAttendance',
						   'avg(us.sportsmanship) as averageSportsmanship'
						   ))
				->where('t.teamID = ?', $teamID)
				->limit(1);
					   
		$team = $table->fetchRow($select);
		
		if (empty($team['teamID'])) {
			// No game found
			return false;
		}

		$savingClass->setAttribs($team);
		
		// Get all leagues
		/*
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('tll'  => 'team_leagues'))
			   ->join(array('ll' => 'league_levels'),
			   		 'tll.leagueLevelID = ll.leagueLevelID')
			   ->join(array('l' => 'leagues'),
			   		 'l.leagueID = ll.leagueID')
				->where('tll.teamID = ?', $teamID);
		
					   
		$leagues = $table->fetchAll($select);
		
		foreach ($leagues as $league) {
			$savingClass->leagues->addLeague($league);
		}
		*/
		// Get all players
		$sportID = $savingClass->sportID;
		$select = $table->select();
		$select->setIntegrityCheck(false);	
			
		$select->from(array('ut' => 'user_teams'))
			   ->joinLeft(array('tc' => 'team_captains'),
			   			  'tc.userID = ut.userID AND tc.teamID = ut.teamID',
						  array('userID as captain',
						  		'creator'))
			   ->join(array('u' => 'users'),
			   		  'ut.userID = u.userID')
			   ->joinLeft(array('us' => 'user_sports'),
			   		  'ut.userID = us.userID AND us.sportID = "' . $sportID . '"',
					  array('us.skillInitial',
					  		'us.often',
							'us.skillCurrent',
							'us.attendance',
							'us.sportsmanship',
							'us.noRatings'))
			   ->joinLeft(array('s' => 'sports'),
			   		  's.sportID = ' . $sportID)
			   ->where('ut.teamID = ?', $teamID)
			   ->group('ut.userID');
		
			
		$players = $table->fetchAll($select);

		foreach ($players as $player) {
			$savingClass->addPlayer($player)
						->getSport($player->sport)
						->setAttribs($player);
			
			if ($player->captain != null) {
				// Player is team captain
				$savingClass->addCaptain($player->userID, $player->creator);
			}
				
		}
		
		// Get reserves	
		$select = $table->select();
		$select->setIntegrityCheck(false);	
			
		$select->from(array('ut' => 'team_reserves'))
			   ->join(array('u' => 'users'),
			   		  'u.userID = ut.userID')
			   ->where('ut.teamID = ?', $teamID)
			   ->group('ut.userID');
		
		$reserves = $table->fetchAll($select);
		
		foreach ($reserves as $reserve) {

			$savingClass->reserves->addUser($reserve);
		}
								
		// Get all games as well as players playing/played in those games
		$select = $table->select();
		$select->setIntegrityCheck(false);	
			
		$select->from(array('tg' => 'team_games'))
			   ->join(array('ll' => 'league_locations'),
			   		  'll.leagueLocationID = tg.leagueLocationID')
			   ->joinLeft(array('utg' => 'user_team_games'),
			   		 'tg.teamGameID = utg.teamGameID',
					 array('utg.userID','utg.confirmed'))
			   ->where('tg.teamID = ?', $teamID)
			   ->where('tg.date > DATE_SUB(NOW(), INTERVAL 4 WEEK)')
			   ->order('tg.date ASC');
		
		
		$games = $table->fetchAll($select);
		
		foreach ($games as $game) {
			$gameModel = $savingClass->addGame($game);
			
			if ($game->userID !== NULL) {
				if ($game->confirmed == '1') {
					// Player is going
					
					$gameModel->addConfirmedPlayer($game->userID);	
				} elseif ($game->confirmed == '0') {
					// Player is confirmed not going
					$gameModel->addNotConfirmedPlayer($game->userID);
				} elseif ($game->confirmed == '2') {
					// Player is confirmed maybe going
					$gameModel->addMaybeConfirmedPlayer($game->userID);
				}
			}
			
			/* bug fix to show all games on calendar for team page */
			$gameModel->confirmed = '';
			$gameModel->teamPage = '1';
			
		}
		
		// Get team record
		$select = $table->select();
		$select->setIntegrityCheck(false);	
			
		$select->from(array('tg' => 'team_games'),
					  array('tg.winOrLoss'))
			   ->where('tg.teamID = ?', $teamID);
			   
		$results = $table->fetchAll($select);
		
		$recordArray = array('W' => array(),
							 'L' => array(),
							 'T' => array()
							 );
		
		foreach ($results as $result) {
			if ($result->winOrLoss !== NULL) {
				$recordArray[$result->winOrLoss][] = true;
			}
		}
		
		$wins = count($recordArray['W']);
		$losses = count($recordArray['L']);
		$ties = count($recordArray['T']);
		
		$savingClass->wins = $wins;
		$savingClass->losses = $losses;
		$savingClass->ties = $ties;	
		
		return $savingClass;

	}
	
	/**
	 * move team_games => old_team_games
	 */
	public function moveTeamGames($teamID)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		if (!$teamID) {
			return false;
		}
		
		$insertGames = "INSERT INTO old_team_games (oldTeamGameID, teamID, opponent, date, winOrLoss, leagueLocationID, movedDate) 
								(SELECT tg.teamGameID, tg.teamID, tg.opponent, tg.date, tg.winOrLoss, tg.leagueLocationID, (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR)
									FROM team_games tg 
									WHERE tg.teamID = '" . $teamID . "'
										AND tg.date < (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR))";
		
		$db->query($insertGames);
							
	}
	
	public function deleteTeamGames($teamID)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		if (!$teamID) {
			return false;
		}
		
		$deleteGames = "DELETE FROM team_games 
							WHERE team_games.teamID = '" . $teamID . "'";
							
		$db->query($deleteGames);
	}
	
	/**
	 * add user to team
	 */
	public function addUserToTeam($userID, $savingClass)
	{

		$this->setDbTable('Application_Model_DbTable_UserTeams');
		
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->where('userID = ?', $userID)
			   ->where('teamID = ?', $savingClass->teamID);
			   
		$result = $table->fetchRow($select);
		
		if (!empty($result['teamID'])) {
			// Already exists
			return true;
		}
		
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->setIntegrityCheck(false);
		
		$select->from(array('t' => 'teams'))
			   ->join(array('ut' => 'user_teams'),
			   		  't.teamID = ut.teamID',
					  array('COUNT(ut.userID) as totalPlayers'))
			   ->join(array('u' => 'users'),
			   		  'u.userID = ut.userID',
					  array(''))
			   ->where('t.teamID = ?', $savingClass->teamID);
			  
			   
		$result = $table->fetchRow($select);
		
		if ($result['rosterLimit'] <= $result['totalPlayers']) {
			// Full team
			return 'full';
		}
		
		$data = array('userID' => $userID,
					  'teamID' => $savingClass->teamID);
					  
		$this->getDbTable()->insert($data);
		

		// Row was inserted, not updated, create notifications
		$teamID = $savingClass->teamID;
		$notifications = new Application_Model_Notifications();
		
		$notifications->deleteAll(array('nl.actingUserID' => $userID,
										'nl.teamID'		  => $teamID,
										'n.action'		  => 'leave',
										'n.type'		  => 'team'));
										
		$notifications->deleteAll(array('nl.actingUserID' => $userID,
										'nl.teamID'		  => $teamID,
										'n.action'		  => 'join',
										'n.type'		  => 'team'));
		

		$notification = new Application_Model_Notification();
		
		$notification->actingUserID = $userID;
		$notification->teamID = $teamID;
		$notification->action = 'join';
		$notification->type = 'team';
		
		$auth = Zend_Auth::getInstance();
		
		if ($auth->hasIdentity()) {
			// User logged in, get their cityID
			$notification->cityID = $auth->getIdentity()->cityID;
		} elseif ($savingClass->hasValue('cityID')) {
			$notification->cityID = $savingClass->cityID;
		}
		
		$notification->save();

		
		return false;
	}
		
		
	
	/**
	 * get team captains
	 */
	public function getTeamCaptains($teamID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('tc' => 'team_captains'))
			   ->where('tc.teamID = ?', $teamID);
			   
		$captains = $table->fetchAll($select);
		
		$returnArray = array();
		
		foreach ($captains as $captain) {
			$returnArray[] = $captain->userID;
		}
		
		return $returnArray;
	}
	
	/**
	 * get pending invites sent from $userID user
	 * @params ($userID => actingUserID,
	 *			$team => Application_Model_Team)
	 * @returns array of emails and numInvites
	 */
	public function getPendingInvites($userID, $team)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('ti' => 'team_invites'))
			   ->joinLeft(array('u' => 'users'),
			   			  'u.username = ti.email')
			   ->joinLeft(array('ut' => 'user_teams'),
			   		  	  'ut.userID = u.userID AND ut.teamID = "' . $team->teamID . '"',
						  array('userTeamID'))
			   ->where('ti.teamID = ?', $team->teamID)
			   ->where('ti.actingUserID = ?', $userID)
			   ->group('ti.email')
			   ->order('ti.firstSent DESC');
		 
		$results = $table->fetchAll($select);
		
		$returnArray = array('joined' => array('members' => array(),
											   'notMembers' => array()),
							 'notJoined' => array());
		foreach ($results as $result) {
			$user = new Application_Model_User();
			
			$user->username = $result->email;
			$user->setTempAttrib('numInvites', $result->numInvites);
			$user->setTempAttrib('firstSent', $result->firstSent);
			
			/*
			$array = array('email' 		=> $result->email,
						   'numInvites' => $result->numInvites,
						   'firstSent'  => $result->firstSent);
			*/
			 
			if ($result->userID != NULL) {
				$user->userID = $result->userID;
				$user->firstName = $result->firstName;
				$user->lastName = $result->lastName;
				
				if ($result->userTeamID != NULL) {
					// Is member
					$returnArray['joined']['members'][] = $user;
				} else {
					$returnArray['joined']['notMembers'][] = $user;
				}
			} else {
				$returnArray['notJoined'][] = $user;
			}
		}
		
		return $returnArray;
	}
	
	/**
	 * save sent invites to db
	 */
	public function saveInvites($emails, $actingUserID, $teamID)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "INSERT INTO team_invites 
					(teamInviteID, email, actingUserID, teamID, firstSent) 
				VALUES ";
		
		$counter = 0;
		foreach ($emails as $email) {
			if ($counter != 0) {
				$sql .= ',';
			}
			$sql .= "('', '" . $email . "', " . $actingUserID . ", " . $teamID . ", CURDATE())";
			$counter++;
		}
		
		$sql .= " ON DUPLICATE KEY UPDATE numInvites = numInvites + 1";
		
		$db->query($sql);
	}
			
}
