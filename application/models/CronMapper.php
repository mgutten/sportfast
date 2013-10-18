<?php

class Application_Model_CronMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Users';

	
	/**
	 * move any games that are in the past to the "old_games" table or update them to next week if recurring
	 */
	public function moveGamesToOld()
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$testGameUpdate = "SELECT MAX(gameID) as top FROM games 
							HAVING top = (SELECT gameID FROM games WHERE parkID = 0 AND sportID = 0 AND public = 0)";
		
		$results = $db->query($testGameUpdate);
		
		if (!$results) {
			// Max gameID != the holder game for bug fix, continue to move bug fix gameID to maxID
		
			// BUG FIX to move empty holder game in db to new position as max(id)
			$moveGame = "UPDATE games SET gameID = ((
														SELECT gameID
														FROM (SELECT MAX(gameID) as gameID FROM games) AS g2
														
													) + 1) 
							WHERE parkID = 0 AND sportID = 0 AND public = 0";
			$db->query($moveGame);
		}
		
		// Copy from games -> old_games
		$insertGames = "INSERT INTO old_games (gameID, parkID, parkName, backupParkID, backupParkName, 
											   public, sport, sportID, typeID, rosterLimit, maxSkill, 
											   minSkill, maxAge, minAge, recurring, date, city, cityID, 
											   minPlayers, canceled, cancelReason, remove, sportfastCreated, totalPlayers, movedDate) 
								(SELECT g.gameID, g.parkID, g.parkName, g.backupParkID, g.backupParkName,
									    g.public, g.sport, g.sportID, g.typeID, g.rosterLimit, g.maxSkill,
										g.minSkill, g.maxAge, g.minAge, g.recurring, g.date, g.city, g.cityID,
										g.minPlayers, g.canceled, g.cancelReason, g.remove, g.sportfastCreated, COUNT(ug.userID), (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR)
									FROM games g 
									LEFT JOIN user_games ug ON (ug.gameID = g.gameID AND ug.confirmed = '1')
									WHERE g.date < (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR) 
									AND g.gameID != (SELECT MAX(gameID) FROM games)
									AND g.parkID != 0
									AND g.sportID != 0
									GROUP BY g.gameID)";
		$db->query($insertGames);
		$oldGameID = $db->lastInsertId();
		
		if ($oldGameID) {
		
			// Copy from user_games -> old_user_games					
			$insertUserGames = "INSERT INTO old_user_games (oldGameID, gameID, userID, confirmed, plus)
									(SELECT og.oldGameID, ug.gameID, ug.userID, ug.confirmed, ug.plus
										FROM user_games ug
										INNER JOIN (SELECT * 
														FROM old_games 
														WHERE movedDate BETWEEN (SELECT movedDate - INTERVAL 1 MINUTE FROM old_games WHERE oldGameID = '" . $oldGameID . "')
																		 AND (SELECT movedDate + INTERVAL 1 MINUTE FROM old_games WHERE oldGameID = '" . $oldGameID . "')
													) og ON og.gameID = ug.gameID)";
													
			$db->query($insertUserGames);
			
			// Delete from games table (non-recurring games)
			$deleteGames = "DELETE FROM games 
								WHERE games.gameID IN (SELECT gameID 
														FROM old_games 
														WHERE movedDate = (SELECT movedDate 
																			FROM old_games 
																			WHERE oldGameID = '" . $oldGameID . "')
															AND (recurring = 0)
													   )";
			$db->query($deleteGames);
			
			// Delete from game_messages
			$deleteMessages = "DELETE FROM game_messages 
								WHERE gameID IN (SELECT gameID 
														FROM old_games 
														WHERE movedDate = (SELECT movedDate 
																			FROM old_games 
																			WHERE oldGameID = '" . $oldGameID . "')
													   )";
			
			$db->query($deleteMessages);
			
			// Delete game notifications
			$deleteNotifications = "DELETE FROM notification_log 
								WHERE gameID IN (SELECT gameID 
														FROM old_games 
														WHERE movedDate = (SELECT movedDate 
																			FROM old_games 
																			WHERE oldGameID = '" . $oldGameID . "')
													   )";
			
			$db->query($deleteNotifications);
		}
		
		// Delete from games table any games labeled "remove"
		$deleteGames = "DELETE FROM games 
							WHERE remove IS NOT NULL 
								AND remove < now()
								AND remove != '0000-00-00'";
		$db->query($deleteGames);
		
		// Update any recurring games
		$updateGames = "UPDATE games SET date = DATE_ADD(date,INTERVAL 1 WEEK),
										 canceled = 0,
										 cancelReason = '' 
							WHERE date < (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR)
								AND recurring = 1
								AND (remove IS NULL OR
									 remove = '0000-00-00')";
							
		$db->query($updateGames);
		
		// Delete user_games
		$deleteUserGames = "DELETE FROM user_games 
							WHERE user_games.gameID IN (SELECT gameID 
													FROM old_games 
													WHERE movedDate = (SELECT movedDate 
																		FROM old_games 
																		WHERE oldGameID = '" . $oldGameID . "')
												   )";
												   
		$db->query($deleteUserGames);
									
		
	}
	
	/**
	 * send follow up email to users who signed up but did not verify
	 */
	public function getUnverifiedUsers()
	{
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->setIntegrityCheck(false);
		
		$select->from(array('u' => 'users'))
			   ->where('DATE(u.joined) = DATE(CURDATE() - INTERVAL 1 DAY)')
			   ->where('u.active = 0');
			   	
		$results = $table->fetchAll($select);
		
		$users = array();
		
		foreach ($results as $result) {
			$user = new Application_Model_User($result);
			
			$users[] = $user;
		}
		
		return $users;
		
	}
	
	
	/**
	 * update the age of users based on dob in db
	 */
	public function updateAge()
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		// Update age for those whose birthday is today
		$sql = "UPDATE users 
					SET age = (YEAR(now()) - YEAR(dob) - (DATE_FORMAT(now(), '%m%d') < DATE_FORMAT(dob, '%m%d')))
					WHERE  DATE_FORMAT(dob, '%m%d') = DATE_FORMAT(now(), '%m%d')";
		
		$db->query($sql);
	}
	
	/**
	 * remove fake users from games that have enough players
	 * REQS: > 50% of minPlayers req (total, fake included), remove 1 fake player for each player past 50% mark
	 */
	public function removeFakeUsers()
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		// Update age for those whose birthday is today
		$sql = "SELECT ug.gameID, ug.userID, g.minPlayers, g.totalPlayers FROM user_games ug 
					INNER JOIN users u ON ug.userID = u.userID
					INNER JOIN (SELECT g.gameID, (COUNT(ug.userID) + SUM(ug.plus)) as totalPlayers, ug.plus, g.minPlayers  
									FROM games g  
									INNER JOIN user_games ug ON ug.gameID = g.gameID
									GROUP BY ug.gameID
									HAVING (totalPlayers) > FLOOR(.5 * (g.minPlayers))
								) g ON g.gameID = ug.gameID 
					WHERE u.fake = 1 ORDER BY RAND()";
		
		$results = $db->fetchAll($sql);
		
		if ($results) {
			// There are fake users present, determine how many to remove
			$gameID = $results[0]['gameID'];
			$minPlayers = $results[0]['minPlayers'];
			$totalPlayers = $results[0]['totalPlayers'];
			
			$removePlayers = $totalPlayers - floor(.5 * $minPlayers);
			
			$limit = ($removePlayers > count($results) ? count($results) : $removePlayers);
			
			for ($i = 0; $i < $limit; $i++) {
				if (empty($results[$i]['userID']) ||
					empty($gameID)) {
						continue;
					}
				$db->delete('user_games', array(
								'gameID = ?' => $gameID,
								'userID = ?' => $results[$i]['userID']
							));
			}
		}
	}
	
	public function removeInactiveUsers()
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$data = array('active' => 0);
		$where = array('lastActive < (now() - INTERVAL 60 DAY)',
					   'fake = ?' => 0);
		
		$db->update('users', $data, $where);
			   
	}
	
	public function removeInactiveTeams()
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$where = array('t.lastActive < (now() - INTERVAL 60 DAY)');
		
		$this->moveToOldTeams($where, true);
			   
	}
	
	/**
	 * get users to warn them about impending inactivity and removal
	 */
	public function getInactiveUsers()
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('u' =>'users'),
					  array('u.*','DATEDIFF(now(), u.lastActive) as dateDiff'))
			   ->where('u.lastActive > (now() - INTERVAL 52 DAY) AND u.lastActive < (now() - INTERVAL 45 DAY)')
			   ->where('u.fake = 0')
			   ->where('u.active = 1');
			   
			   
		$results = $table->fetchAll($select);
		
		$returnArray = array();
		foreach ($results as $result) {
			$returnArray[] = array('userID' => $result->userID,
								   'firstName'	=> $result->firstName,
								   'username' => $result->username,
								   'lastActive' => $result->dateDiff);
		}
		
		return $returnArray;
	}
	
	/**
	 * internal function to move teams to old_teams $where
	 */
	public function moveToOldTeams($where, $delete = true)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		// Copy from teams -> old_teams
		$insertTeams = "INSERT INTO old_teams (teamID, sportID, sport, rosterLimit, teamName, public, city, cityID, 
											   minSkill, maxSkill, minAge, maxAge, picture, remove, lastActive, movedDate) 
								(SELECT t.teamID, t.sportID, t.sport, t.rosterLimit, t.teamName, t.public, t.city, t.cityID, 
								 t.minSkill, t.maxSkill, t.minAge, t.maxAge, t.picture, t.remove, t.lastActive, (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR)
									FROM teams t 
									WHERE ";
		$counter = 0;	
		$where[] = "t.teamID != (SELECT MAX(teamID) FROM teams)"; // Do not remove max value from teams						
		foreach ($where as $statement) {
			if ($counter != 0) {
				$insertTeams .= " AND ";
			}
			$insertTeams .= $statement;
		}
		
		$insertTeams .= ')';

		$db->query($insertTeams);
		
		if ($delete) {
			// Delete the teams that were moved
			$oldTeamID = $db->lastInsertId();
			
			$deleteTeams = "DELETE FROM teams 
								WHERE teams.teamID IN (SELECT teamID 
													FROM old_teams
													WHERE movedDate = (SELECT movedDate 
																		FROM old_teams 
																		WHERE oldTeamID = '" . $oldTeamID . "')
												   )";
			$db->query($deleteTeams);
		}
		
	}
	
	/**
	 * update status of games 2 hours before game time (canceled or happening)
	 * @returns array of canceled => and on => arrays for each game happening in 2 hours
	 */
	public function updateGameStatus()
	{
		// Only run next morning game status at 7:59pm for games UP TO 8:59am
		$db = Zend_Db_Table::getDefaultAdapter();
		$statement = "SELECT g.gameID, g.sportID, g.minPlayers, (COUNT(ug.userID) + SUM(ug.plus)) as totalPlayers, 
							 g.sport, g.parkName, g.date
						FROM games g
						LEFT JOIN (SELECT u2.userID, ug2.gameID, ug2.plus FROM users u2
									INNER JOIN user_games ug2 ON u2.userID = ug2.userID 
									WHERE u2.fake = 0 AND ug2.confirmed = 1) ug ON g.gameID = ug.gameID
						WHERE CASE WHEN (HOUR((NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR)) >= 19 AND MINUTE((NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR)) > 31) THEN HOUR(TIMEDIFF(g.date, (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR))) <= 13 AND g.date >= (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR) ELSE (HOUR(TIMEDIFF(g.date, (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR))) = 1 and MINUTE(TIMEDIFF(g.date, (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR))) > 29) AND g.date >= (NOW() + INTERVAL " . $this->getTimeOffset() . " HOUR) END
							AND g.canceled = 0
						GROUP BY g.gameID";
		
						
		$results = $db->fetchAll($statement);
		
		$returnArray = array('canceled' => array(),
							 'on'		=> array());
							 
		foreach ($results as $result) {
			if ($result['minPlayers'] > $result['totalPlayers']) {
				// Not enough players for game, cancel
				$returnArray['canceled'][$result['gameID']] = new Application_Model_Game($result);
				$returnArray['canceled'][$result['gameID']]->cancelReason = 'Not enough players';
			} else {
				// Game is on
				$returnArray['on'][$result['gameID']] = new Application_Model_Game($result);
			}
		}
		
		if ($returnArray['canceled']) {
			// Values set to be canceled
			$statement = "UPDATE games SET canceled = 1,
										   cancelReason = 'Not enough players'
							WHERE gameID IN (";
							
			$gameIDs = implode(',',array_keys($returnArray['canceled']));
			
			$statement .= $gameIDs;
			
			$statement .= ')';
			
			$db->query($statement);
		}
		
		// Loop through games and get players
		foreach ($returnArray as $section) {
			foreach ($section as $game) {
				if ($game->hasValue('totalPlayers')) {
					$game->getGamePlayers(true);
				}
			}
		}
		
		return $returnArray;
		
	}
				
	/**
	 * get users who are on a team that has a game tomorrow (and have not responded yet)
	 */				
	public function getUserTeamGames($days = 2)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('tg' => 'team_games'))
			   ->join(array('t' => 'teams'),
			   		  't.teamID = tg.teamID')
			   ->join(array('l' => 'league_locations'),
			   		  'l.leagueLocationID = tg.leagueLocationID')
			   ->join(array('ut' => 'user_teams'),
			   		  'tg.teamID = ut.teamID')
			   ->join(array('u' => 'users'),
			   		  'u.userID = ut.userID')
			   ->joinLeft(array('utg' => 'user_team_games'),
			   			  'utg.userID = ut.userID AND tg.teamGameID = utg.teamGameID',
						  array())
			   ->where('DATE(DATE_ADD(tg.date, INTERVAL -' . $days . ' DAY)) = CURDATE()')
			   ->where('(TIME_TO_SEC( TIMEDIFF( tg.date, (NOW() + INTERVAL ' . $this->getTimeOffset() . ' HOUR))) /3600) > 2')
			   ->where('utg.teamGameID IS NULL');
		
		
		$results = $table->fetchAll($select);
		
		$returnArray = array();
		
		foreach ($results as $result) {
			if (!isset($returnArray[$result->teamGameID])) {
				// New game
				$team = new Application_Model_Team();
				$team->teamID = $result->teamID;
				$team->teamName = $result->teamName;
				$game = $team->games->addGame($result);
				$game->teamGameID = $result->teamGameID;
				$game->date = $result->date;
				
				$returnArray[$result->teamGameID] = $team;
			}
							
			$returnArray[$result->teamGameID]->players->addUser($result);
		}
		
		return $returnArray;

	}
	
	/**
	 * get users who are subscribed to a game that is happening tomorrow (and have not responded yet)
	 */				
	public function getUserSubscribedGames()
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('g' => 'games'))
			   ->join(array('gs' => 'game_subscribers'),
			   		  'g.gameID = gs.gameID')
			   ->join(array('u' => 'users'),
			   		  'u.userID = gs.userID')
			   ->joinLeft(array('ug' => 'user_games'),
			   			  'ug.userID = gs.userID AND g.gameID = ug.gameID',
						  array('ug.confirmed'))
			   ->where('DATE(DATE_ADD(g.date, INTERVAL -1 DAY)) = CURDATE()')
			   ->where('g.sendReminder = HOUR(NOW() + INTERVAL ' . $this->getTimeOffset() . ' HOUR)')
			   ->where('g.recurring = 1')
			   ->where('ug.userGameID IS NULL')
			   ->where('gs.doNotEmail = 0');
		
		$results = $table->fetchAll($select);
		
		$returnArray = array();
		
		foreach ($results as $result) {
			if (!isset($returnArray[$result->gameID])) {
				// New game
				$game = new Application_Model_Game($result);
				
				$returnArray[$result->gameID] = $game;
			}
			
			if ($result->confirmed == '2') {
				$returnArray[$result->gameID]->addMaybeConfirmedPlayer($result->userID);
			}
							
			$returnArray[$result->gameID]->players->addUser($result);
		}
		
		return $returnArray;
	}
	
	
	/* BEGIN SECTION FOR CREATING GAMES */
	
	/**
	 * get all of the non-temporary parks in the db as well as time availability
	 */
	public function getParksCreateGames()
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "SELECT p.parkID, 
					   p.fieldLights,
					   p.basketballLights,
					   p.tennisLights,
					   HOUR(p.openTime) as openTime,
					   HOUR(p.closeTime) as closeTime,
					   (p.basketballOutdoor) as basketball,
					   p.field,
					   p.tennis,
					   p.volleyball,
					   p.basketballPrivate,
					   p.fieldPrivate,
					   p.tennisPrivate,
					   p.volleyballPrivate,
					   p.parkType,
					   ps.sport,
					   AsText(pl.location) as location
				FROM parks p
				LEFT JOIN (SELECT ps.parkID,
							  	  spi.sport
							FROM park_stashes ps 
							INNER JOIN stash_items si ON si.itemID = ps.itemID
							INNER JOIN sport_items spi ON spi.itemID = si.itemID
							) ps on ps.parkID = p.parkID
				INNER JOIN park_locations pl ON pl.parkID = p.parkID
				WHERE p.temporary = 0
					AND p.cost = 0
					AND p.membershipRequired = 0
					AND p.active = 1";
		
				
		$results = $db->fetchAll($sql);
		
		
		return $results;
	}
	
	/**
	 * get park unavailabilities and return as array
	 */
	public function getParkUnavailabilities()
	{
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->setIntegrityCheck(false);
		
		$select->from(array('pu' => 'park_unavailabilities'))
			   ->where('CASE WHEN MONTH(pu.fromMonth) < MONTH(pu.toMonth)
			   				THEN MONTH(now() + INTERVAL 3 DAY) BETWEEN MONTH(pu.fromMonth) AND MONTH(pu.toMonth) 
							ELSE MONTH(now() + INTERVAL 3 DAY) >= MONTH(pu.fromMonth) OR MONTH(now() + INTERVAL 3 DAY) <= MONTH(pu.toMonth)
						END');
		
		$results = $table->fetchAll($select);
		
		$returnArray = array();
		foreach ($results as $result) {
			if (!isset($returnArray[$result->parkID])) {
				// Set outer parkID array
				$returnArray[$result->parkID] = array('fromMonth' => $result['fromMonth'],
													  'toMonth' => $result['toMonth']);
			}
			if (!isset($returnArray[$result->parkID][$result->day])) {
				// Set outer parkID array
				$returnArray[$result->parkID][$result->day] = array();
			}
			
			$returnArray[$result->parkID][$result->day][$result->hour] = true;
		}
		
		return $returnArray;
	}
			
	
	/**
	 * test park to see if game is needed
	 * @params ($parkID => park to test,
	 *			$sport => sport to test,
	 *			$timeslot => timeslot to test
	 *			$daysInFuture => how many days in the future are we looking to test/create this game,
	 *			$type => array of typeSuffix => and typeName => (used special for tennis) optional)
	 */
	public function testParkGame($parkID, $sport, $timeslot, $usedPlayers = false, $daysInFuture = 3, $type = false)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$fieldSports = array('soccer',
							 'volleyball',
							 'football',
							 'ultimate');
		
		$sportID = $this->getSportID($sport);
		
		$sql = "SELECT gameRosterLimit,
						minPlayers
				FROM sport_types
				WHERE sportID = '" . $sportID . "'";
				
		if ($type) {
			// Get roster limit for specified type
			$sql .= " AND typeName = '" . $type['typeName'] . "'
						AND typeSuffix = '" . $type['typeSuffix'] . "'";
		}

		$result = $db->fetchRow($sql);

		$rosterLimit = $result['gameRosterLimit'];
		$minPlayers = $result['minPlayers'];
		
		$date = DateTime::createFromFormat('U', strtotime('+' . $daysInFuture . ' days'));
		$dayOfWeek = $date->format('w');

		// Get players
		$sql = "SELECT ul.userID, u.username, u.noEmail, u.age FROM user_locations ul
				INNER JOIN users u ON ul.userID = u.userID
				INNER JOIN user_sport_availabilities usa ON (usa.userID = ul.userID AND usa.sportID = '" . $sportID . "')
				INNER JOIN (SELECT us.userID, us.skillCurrent, DATEDIFF(now(),MAX(og.date)) as lastGame, MAX(og.date) as date, us.often FROM user_sports us
							LEFT JOIN old_user_games oug ON us.userID = oug.userID
							LEFT JOIN old_games og ON oug.oldGameID = og.oldGameID
							LEFT JOIN user_games ug ON us.userID = ug.userID
							LEFT JOIN games g ON ug.gameID = g.gameID
							WHERE us.sportID = '" . $sportID . "' 
								AND (og.sportID = '" . $sportID . "'
									OR og.sportID IS NULL)
								AND ((g.sportID = '" . $sportID . "' and ug.userID IS NULL) 
										OR g.sportID IS NULL
										OR g.sportID != '" . $sportID . "')
							GROUP BY us.userID
							HAVING lastGame >= us.often OR date IS NULL
							) us ON (us.userID = ul.userID) 
				INNER JOIN user_sport_formats usf ON usf.userID = ul.userID AND usf.sportID = '" . $sportID . "' ";
							
		if ($type) {
			// Type has been included 
			$sql .= " INNER JOIN (SELECT ust.userID FROM user_sport_types ust 
									INNER JOIN sport_types st ON ust.typeID = st.typeID
									WHERE st.typeName = '" . $type['typeName'] . "' 
										AND st.typeSuffix = '" . $type['typeSuffix'] . "'
										AND st.sportID = '" . $sportID . "') ust ON ust.userID = ul.userID ";
		}
		
		$sql .=		"WHERE (GLength(
									LineStringFromWKB(
									  LineString(
										ul.location, 
										(SELECT location FROM park_locations WHERE parkID = '" . $parkID . "')
									  )
									 )
									) * (5/8 * 100) < 6)
						AND usa.day = '" . $dayOfWeek . "'
						AND usa.hour = '" . $timeslot . "'
						AND usf.format = 'pickup' 
						AND u.fake = 0 ";
						
		if ($usedPlayers) {
			// Players have already been used, do not include in results
			$sql .= " AND ul.userID NOT IN (" . implode(',', $usedPlayers) . ") ";
		}
		
		$sql .= "GROUP BY ul.userID
				 ORDER BY us.skillCurrent " . (mt_rand(0,1) == 1 ? 'ASC' : 'DESC'); // Order by skill and then randomly decide whether we should order by desc or asc
		
		$users = $db->fetchAll($sql);
		
		$playersNeeded = $rosterLimit * 4;  // Need 4 times rosterLimit for game to happen
		
		if (count($users) >= $playersNeeded) {
			// Success!  Enough users for a game
			// Now must test whether there are any games happening nearby and at the same time
			$sql = "SELECT g.parkID 
					FROM games g
					INNER JOIN park_locations pl ON pl.parkID = g.parkID
					WHERE (GLength(
									LineStringFromWKB(
									  LineString(
										pl.location, 
										(SELECT location FROM park_locations WHERE parkID = '" . $parkID . "')
									  )
									 )
									) * (5/8 * 100) < 4)
						AND g.sportID = '" . $sportID . "'
						AND g.public = 1
						AND (DAY(g.date) = DAY(now() + INTERVAL 3 day) 
								AND (HOUR(g.date) >= '" . ($timeslot - 1) . "' 
										AND HOUR(g.date) <= '" . ($timeslot + 1) . "')
							)";
			
							
			$games = $db->fetchAll($sql);
			
			if ($games) {
				// There are games
				$numGames = count($games);
			} else {
				$numGames = 0;
			}
			
			// Number of players to remove from count of $users since some will hypothetically go to these other games
			$removePlayers = $numGames * $playersNeeded;
			
					
			for ($i = 0; $i < count($users); $i++) {
				if ($i < $removePlayers) {
					// Move users to used list if there is already a game that they can attend
					$usedPlayers[] = $users[$i]['userID'];
					unset($users[$i]);
				}
			}
			
			
			if (count($users) >= $playersNeeded) {
				// Second check success!  Still enough players to create game, test to split into age groups
				$younger = $older = array();
				for ($i = $removePlayers; $i < count($users); $i++) {
					if ($users[$i]['age'] < 40) {
						$younger[$i] = $users[$i];
					} else {
						$older[$i] = $users[$i];
					}
				}
				
				$ageGroups = array('younger', 'older');
				$randomKey = mt_rand(0, (count($ageGroups) - 1));
				$secondKey = ($randomKey == 0 ? 1 : 0);
				$reference = $ageGroups[$randomKey];
				$secondReference = $ageGroups[$secondKey];
				
				$youngerGroup = $olderGroup = false;
				
				if (count($$reference) >= $playersNeeded) {
					// Game should be for either younger or older depending on which was chosen
					$finalUsers = $$reference;
					$youngerGroup = true;
				} elseif (count($$secondReference) >= $playersNeeded) {
					$finalUsers = $$secondReference;
					$olderGroup = true;
				} else {
					$finalUsers = $users;
				}
				
				
				if ($sport == 'basketball') {
					// Check for basketball courts
					$court = '(p.basketballOutdoor + p.basketballIndoor)';
				} elseif (in_array($sport, $fieldSports)){
					$court = 'p.field';
				} else {
					$court = 'p.' . $sport;
				}
				
				// Retrieve backup park, if there is one
				$sql = "SELECT p.parkID, p.parkName
						FROM parks p
						LEFT JOIN (SELECT ps.parkID,
							  	  		  spi.sport
							FROM park_stashes ps 
							INNER JOIN stash_items si ON si.itemID = ps.itemID
							INNER JOIN sport_items spi ON spi.itemID = si.itemID
							WHERE spi.sportID = '" . $sportID . "'
							) ps on ps.parkID = p.parkID
						INNER JOIN park_locations pl ON p.parkID = pl.parkID
						LEFT JOIN park_unavailabilities pu ON (p.parkID = pu.parkID 
																AND (CASE WHEN MONTH(pu.fromMonth) < MONTH(pu.toMonth)
																	THEN MONTH(now() + INTERVAL 3 DAY) BETWEEN MONTH(pu.fromMonth) AND MONTH(pu.toMonth) 
																	ELSE MONTH(now() + INTERVAL 3 DAY) >= MONTH(pu.fromMonth) OR MONTH(now() + INTERVAL 3 DAY) <= MONTH(pu.toMonth)
																END)
																AND pu.day = '" . $dayOfWeek . "'
																AND pu.hour = '" . $timeslot . "')
						WHERE (GLength(
									LineStringFromWKB(
									  LineString(
										pl.location, 
										(SELECT location FROM park_locations WHERE parkID = '" . $parkID . "')
									  )
									 )
									) * (5/8 * 100) < 3)
						AND p.parkID != '" . $parkID . "'
						AND " . $court . " > 0
						AND cost = 0
						AND pu.parkID IS NULL
						AND (CASE WHEN p.parkType = 'school' 
									  AND (" . $timeslot . " > 7 AND " . $timeslot . " < 14)
									  AND (" . $dayOfWeek . " > 0 AND " . $dayOfWeek . " < 6)
									  AND (MONTH(now() + INTERVAL 3 DAY) > 8 OR MONTH(now() + INTERVAL 3 DAY) < 6)
							THEN p.parkType != 'school'
							ELSE 1 = 1 END)
						ORDER BY (GLength(
									LineStringFromWKB(
									  LineString(
										pl.location, 
										(SELECT location FROM park_locations WHERE parkID = '" . $parkID . "')
									  )
									 )
									)) ASC LIMIT 1";
									
				$backupPark = $db->fetchRow($sql);
				
				if ($backupPark) {
					$backupParkName = $backupPark['parkName'];
					$backupParkID   = $backupPark['parkID'];
				} else {
					$backupParkName = '';
					$backupParkID   = '';
				}
				
				if (!$type) {
					$whereType = array('sportID' => $sportID);
					$type['typeName'] = 'pickup';
					$type['typeSuffix'] = '';
				} else {
					$whereType = array('sportID' => $sportID,
									   'typeName' => $type['typeName'],
									   'typeSuffix' => $type['typeSuffix']);
				}
				
				$parkName = $this->getValue('parkName', 'parks', array('parkID' => $parkID));
				$typeID   = $this->getForeignID('Application_Model_DbTable_SportTypes', 'typeID', $whereType);
				$cityID   = $this->getForeignID('Application_Model_DbTable_Parks', 'cityID', array('parkID' => $parkID));
				
				$data = array('parkID' => $parkID,
							  'parkName' => $parkName,
							  'backupParkID' => $backupParkID,
							  'backupParkName' => $backupParkName,
							  'public' => 1,
							  'sport'  => $sport,
							  'sportID' => $sportID,
							  'typeID' => $typeID,
							  'typeName' => $type['typeName'],
							  'typeSuffix' => $type['typeSuffix'],
							  'rosterLimit' => $rosterLimit,
							  'maxSkill' => 100,
							  'minSkill' => 70,
							  'maxAge' => 100,
							  'minAge' => 16,
							  'recurring' => 0,
							  'date' => $date->format('Y-m-d') . ' ' . ($timeslot < 10 ? '0' . $timeslot : $timeslot) . ':00:00',
							  'cityID' => $cityID,
							  'minPlayers' => $minPlayers,
							  'sportfastCreated' => 1
						);
						
				$game = new Application_Model_Game($data);
				
				$game->save();
				
				//$gameID = $db->insert('games', $data);
				
				// Add fake users to game
				$sql = "INSERT INTO user_games (gameID, userID) 
							(SELECT '" . $game->gameID . "', u.userID 
								FROM users u 
								WHERE u.fake = 1 ";
								
				if ($youngerGroup) {
					// Is younger game (18-39)
					$sql .= " AND u.age < 40 ";
				} elseif ($olderGroup) {
					$sql .= " AND u.age >= 40 ";
				}
				
				$sql .=	" ORDER BY RAND() LIMIT " . (floor($minPlayers/2) - 1) . ")";
				
				$db->query($sql);
				
				// Insert which users were invited to which game to db
				$sql = "INSERT INTO sportfast_user_game_invites (sportfastUserGameID, gameID, userID) VALUES ";
				$notification = new Application_Model_Notification();
				$notification->action = 'create';
				$notification->type = 'game';
				$notification->details = 'sportfast';
				$notification->gameID = $game->gameID;
				
				$counter = 0;
				foreach ($finalUsers as $user) {
					if ($counter == $playersNeeded) {
						break;
					}
					// Now move all used players into used array, and also into "to be informed" array
					/*
					if (!isset($users[$i])) {
						// Protect against empty values
						continue;
					}
					*/
					if ($counter != 0) {
						// Not first
						$sql .= ',';
					}
						
					
					$sql .= "('', " . $game->gameID . ", " . $user['userID'] . ")";
					
					$game->players->addUser($user);
					$usedPlayers[] = $user['userID'];
					
					$notification->receivingUserID = $user['userID'];
					$notification->save();
					$counter++;
				}
				
				$db->query($sql);
				
				return array('usedPlayers' => $usedPlayers,
							 'game'	   => $game);
			} else {
				// Now not enough players, do not create
				return array('usedPlayers' => $usedPlayers);
			}
				
			 
		} else {
			// Not enough users for a game, do not create
			return false;
		}
		
	}
						
	
}