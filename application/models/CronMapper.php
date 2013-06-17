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
		
		// Copy from games -> old_games
		$insertGames = "INSERT INTO old_games (gameID, parkID, parkName, backupParkID, backupParkName, 
											   public, sport, sportID, typeID, rosterLimit, maxSkill, 
											   minSkill, maxAge, minAge, recurring, date, city, cityID, 
											   minPlayers, canceled, cancelReason, remove, totalPlayers, movedDate) 
								(SELECT g.gameID, g.parkID, g.parkName, g.backupParkID, g.backupParkName,
									    g.public, g.sport, g.sportID, g.typeID, g.rosterLimit, g.maxSkill,
										g.minSkill, g.maxAge, g.minAge, g.recurring, g.date, g.city, g.cityID,
										g.minPlayers, g.canceled, g.cancelReason, g.remove, COUNT(ug.userID), now()
									FROM games g 
									LEFT JOIN user_games ug ON ug.gameID = g.gameID 
									WHERE g.date < now() 
									GROUP BY g.gameID)";
		$db->query($insertGames);
		$oldGameID = $db->lastInsertId();
		
		// Copy from user_games -> old_user_games					
		$insertUserGames = "INSERT INTO old_user_games (oldGameID, gameID, userID, plus)
								(SELECT og.oldGameID, ug.gameID, ug.userID, ug.plus
									FROM user_games ug
									INNER JOIN (SELECT * 
													FROM old_games 
													WHERE movedDate = (SELECT movedDate 
																		FROM old_games 
																		WHERE oldGameID = '" . $oldGameID . "')
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
		
		// Delete from games table any games labeled "remove"
		$deleteGames = "DELETE FROM games 
							WHERE remove IS NOT NULL 
								AND remove < now()";
		$db->query($deleteGames);
		
		// Update any recurring games
		$updateGames = "UPDATE games SET date = DATE_ADD(date,INTERVAL 1 WEEK),
										 canceled = 0,
										 cancelReason = '' 
							WHERE date < now()
								AND recurring = 1
								AND (remove IS NULL)";
							
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
					INNER JOIN (SELECT g.gameID, COUNT(ug.userID ) as totalPlayers, ug.plus, g.minPlayers  
									FROM games g  
									INNER JOIN user_games ug ON ug.gameID = g.gameID  
									HAVING (COUNT(ug.userID) + ug.plus) > FLOOR(.5 * (g.minPlayers))
								) g ON g.gameID = ug.gameID 
					WHERE u.fake = 1 ORDER BY RAND()
			";
		
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
								 t.minSkill, t.maxSkill, t.minAge, t.maxAge, t.picture, t.remove, t.lastActive, NOW()
									FROM teams t 
									WHERE ";
		$counter = 0;							
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
		$db = Zend_Db_Table::getDefaultAdapter();
		$statement = "SELECT g.gameID, g.sportID, g.minPlayers, (COUNT(ug.userID) + SUM(ug.plus)) as totalPlayers, 
							 g.sport, g.parkName, g.date 
						FROM games g
						LEFT JOIN (SELECT u2.userID, ug2.gameID, ug2.plus FROM users u2
									INNER JOIN user_games ug2 ON u2.userID = ug2.userID 
									WHERE fake = 0) ug ON g.gameID = ug.gameID
						WHERE HOUR(TIMEDIFF(date, now())) > 2
							AND g.canceled = 0
						GROUP BY g.gameID";
		
						
		$results = $db->fetchAll($statement);
		
		$returnArray = array('canceled' => array(),
							 'on'		=> array());
							 
		foreach ($results as $result) {
			if ($result['minPlayers'] > $result['totalPlayers']) {
				// Not enough players for game, cancel
				$returnArray['canceled'][$result['gameID']] = new Application_Model_Game($result);
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
			
			//$db->query($statement);
			echo $statement;
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
				
						
						
		
		
	
}