<?php

class Application_Model_SportsMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Sports';
	
	
	public function getAllSportsInfo($savingClass, $asClasses = false)
	{
		$table   = $this->getDbTable();
		// setIntegrityCheck(false) to allow join
		$select  = $table->select()->setIntegrityCheck(false);
		$select->from(array('s' => 'sports'),
                      array('sport'))
               ->join(array('sp' => 'sport_positions'),
                    	    's.sportID = sp.sportID')
			   ->join(array('st' => 'sport_types'),
			   				's.sportID = st.sportID');
		
		
		$results = $table->fetchAll($select);
		
		if ($asClasses) {
			// Create sport models, not array
			foreach ($results as $result) {
				$sport = $savingClass->addSport($result);
				$sport->getPosition($result->positionName)->setAttribs($result);
				$sport->getType($result->typeID)->setAttribs($result);
			}
			
			return $savingClass;
		}
		
				
		$sports = array();
		for($i = 0; $i < count($results); $i++) {
			// Shorten current results array
			$current = $results[$i];
			
			$sport = $current['sport'];
			if (!isset($sports[$sport])) {
				$sports[$sport] = array();
			}
			
			if ($current['positionName'] !== null) {
				// This sport has positions
				$sports[$sport]['position'][$current['positionAbbreviation']] = array('name'        => $current['positionName'],
																					  'description' => $current['positionDescription']);
			}
			if ($current['typeSuffix'] !== null) {
				// This sport has different types
				$sports[$sport]['type'][$current['typeName']][$current['typeSuffix']]['description'] = $current['typeDescription'];
			}
			
			$sports[$sport]['gameRosterLimit'] = $current['gameRosterLimit'];
			$sports[$sport]['teamRosterLimit'] = $current['teamRosterLimit'];
		}
		
		return $sports;
	}

	
	/**
	 * get skills (shooter/shooting, quick/quickness, etc) from db
	 * @returns associative array of skiller and skilling
	 */
	public function getSkills($sportID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from('sport_skills')
			   ->where('sportID = ?', $sportID);
			   
		$results = $table->fetchAll($select);
		
		$returnArray = array();
		foreach ($results as $result) {
			$returnArray[$result->sportSkillID] = array('skiller' => $result->skiller,
														'skilling' => $result->skilling);
		}
		
		return $returnArray;
	}
			   
		
	
	/* used in usersMapper
	public function getUserSportsInfo($userID, $modelClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->setIntegrityCheck(false);
		$select->from(array('s'  => 'sports'))
			   ->join(array('sp' => 'sport_positions'),
			   		  's.sportID = sp.sportID')
			   ->join(array('usp' => 'user_sport_positions'),
			   		  'usp.positionID = sp.positionID')
			   ->join(array('us' => 'user_sports'),
			   		  'sp.sportID = us.sportID',
					  array('skillCurrent', 'sportsmanship', 'attendance'))
		       ->where('usp.userID = ?', $userID);
			   
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			
		}
			
		return $modelClass;

	}
	*/
	
	
	/*
	public function createClasses($results, $modelClass) 
	{
		$classes = array();
		
		foreach ($results as $result) {
			$class = new $modelClass->_singleClass();
			
			if(count($results) <= 1) {
				$classes = $class;
				break;
			}
			
			$classes[] = $class;
		}
		
		return $classes;
	}
	*/
	
	/**
	 * get sport info (minPlayers, rosterLimit, etc) for sportID
	 * @params ($type => 'game' or 'team')
	 */
	public function getSportInfo($sportID)
	{
		$table = $this->getDbTable();		
		$select = $table->select();
		$select->setIntegrityCheck(false);
			
		
		$select->from(array('st' => 'sport_types'),
					  array('gameRosterLimit', 'teamRosterLimit', 'minPlayers'))
			   ->where('st.sportID = ?', $sportID);
		
		
		$result = $table->fetchRow($select);
		
		return $result->toArray();
	}
	
	/**
	 * get user's old data for sport including total games played
	 */
	public function getUserSportData($userID, $sportID)
	{
		$table = $this->getDbTable();		
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		// Total pickup games
		$select->from(array('oug' => 'old_user_games'),
					  array('COUNT(oug.oldUserGameID) as totalGames'))
			   ->join(array('og' => 'old_games'),
			   		  'og.oldGameID = oug.oldGameID',
					  array(''))
			   ->where('og.sportID = ?', $sportID)
			   ->where('YEAR(og.date) = YEAR(CURDATE())')
			   ->where('oug.confirmed = ?', 1)
			   ->where('oug.userID = ?', $userID);
			   			   
		$result = $table->fetchRow($select);
		
		$returnArray = array();
		$returnArray['totalPickupGames'] = $result['totalGames'];
		
		
		// Total pickup games percentage
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$position = "(SELECT COUNT(*) FROM 
							(SELECT COUNT(oug2.oldUserGameID) as count
							  FROM old_user_games oug2
							  INNER JOIN old_games og2 ON og2.oldGameID = oug2.oldGameID
							 WHERE oug2.confirmed = '1'
								AND YEAR(og2.date) = YEAR(CURDATE())
								AND og2.sportID = '" . $sportID . "'
							GROUP BY oug2.userID) as t
						WHERE t.count > " . $result['totalGames'] . ")";
						
		$totalUsers = "(SELECT oug3.userID 
						FROM `old_user_games` AS `oug3` 
						INNER JOIN `old_games` AS `og3` ON og3.oldGameID = oug3.oldGameID 
						WHERE (og3.sportID = '" . $sportID . "') 
							AND (YEAR(og3.date) = YEAR(CURDATE())) 
							AND (oug3.confirmed = 1) 
						GROUP BY oug3.userID)";
		
		$select->from(array('oug' => new Zend_Db_Expr($totalUsers)),
					  array('COUNT(oug.userID) as totalUsers',
					  		new Zend_Db_Expr($position) . ' as position'));
		
			   			   
		$result = $table->fetchRow($select);
		
		$position = $result['position'];
		$totalUsers = ($result['totalUsers'] == 0 ? 1 : $result['totalUsers']);
		$percentage = ceil($position/$totalUsers * 100);
		$returnArray['pickupGamesPercentage'] = ($percentage == 0 ? 1 : $percentage);
		
		
		// Total team games
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('utg' => 'user_team_games'),
					  array('COUNT(utg.userTeamGameID) as totalGames'))
			   ->join(array('tg' => 'team_games'),
			   		  'tg.teamGameID = utg.teamGameID')
			   ->join(array('t' => 'teams'),
			   		  'utg.teamID = t.teamID',
					  array(''))
			   ->where('t.sportID = ?', $sportID)
			   ->where('YEAR(tg.date) = YEAR(CURDATE())')
			   ->where('utg.confirmed = ?', 1)
			   ->where('utg.userID = ?', $userID);
			   
		$result = $table->fetchRow($select);
		$returnArray['totalTeamGames'] = $result['totalGames'];
		
		
		// Total team games percentage
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$position = "(SELECT COUNT(*) FROM 
							(SELECT COUNT(utg2.userTeamGameID) as count
							  FROM user_team_games utg2
							  INNER JOIN team_games tg2 ON tg2.teamGameID = utg2.teamGameID
							  LEFT JOIN teams t2 ON t2.teamID = tg2.teamID
							  LEFT JOIN old_teams ot2 ON ot2.teamID = tg2.teamID
							 WHERE utg2.confirmed = '1'
								AND YEAR(tg2.date) = YEAR(CURDATE())
								AND (CASE WHEN t2.teamID IS NULL 
										THEN ot2.sportID = '" . $sportID . "'
										ELSE t2.sportID = '" . $sportID . "' END)
							GROUP BY utg2.userID) as t
						WHERE t.count > " . $result['totalGames'] . ")";
						
		$totalUsers = "(SELECT utg3.userID 
						FROM `user_team_games` AS `utg3` 
						INNER JOIN team_games tg3 ON tg3.teamGameID = utg3.teamGameID
						LEFT JOIN teams t3 ON t3.teamID = tg3.teamID
						LEFT JOIN old_teams ot3 ON ot3.teamID = tg3.teamID 
						WHERE utg3.confirmed = '1'
								AND YEAR(tg3.date) = YEAR(CURDATE())
								AND (CASE WHEN t3.teamID IS NULL 
										THEN ot3.sportID = '" . $sportID . "'
										ELSE t3.sportID = '" . $sportID . "' END)
						GROUP BY utg3.userID)";
		
		$select->from(array('oug' => new Zend_Db_Expr($totalUsers)),
					  array('COUNT(oug.userID) as totalUsers',
					  		new Zend_Db_Expr($position) . ' as position'));
		
			   			   
		$result = $table->fetchRow($select);
		
		$position = $result['position'];
		$totalUsers = ($result['totalUsers'] == 0 ? 1 : $result['totalUsers']);
		$percentage = ceil($position/$totalUsers * 100);
		$returnArray['teamGamesPercentage'] = ($percentage == 0 ? 1 : $percentage);
		/*
		// Total ratings
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('ur' => 'user_ratings'),
					  array('COUNT(ur.userRatingID) as totalRatings'))
			   ->where('ur.givingUserID = ?', $userID)
			   ->where('ur.sportID = ?', $sportID);
			   
		$result = $table->fetchRow($select);
		$returnArray['totalRatings'] = $result['totalRatings'];
		*/
		
		// Decisiveness
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$decided = "(SELECT COUNT(oug2.oldUserGameID) as decided,
							oug2.userID
							  FROM old_user_games oug2
							  INNER JOIN old_games og2 ON og2.oldGameID = oug2.oldGameID
							 WHERE (oug2.confirmed = '1'
							 		OR oug2.confirmed = '0')
								AND YEAR(og2.date) = YEAR(CURDATE())
								AND og2.sportID = '" . $sportID . "'
								AND oug2.userID = '" . $userID . "')";
						
		$maybe = "(SELECT COUNT(oug3.oldUserGameID) as maybe,
						  oug3.userID
							  FROM old_user_games oug3
							  INNER JOIN old_games og3 ON og3.oldGameID = oug3.oldGameID
							 WHERE (oug3.confirmed = '2')
								AND YEAR(og3.date) = YEAR(CURDATE())
								AND og3.sportID = '" . $sportID . "'
								AND oug3.userID = '" . $userID . "')";
		
		$select->from(array('oug' => new Zend_Db_Expr($decided)))
			   ->joinLeft(array('x' => new Zend_Db_Expr($maybe)),
			   		  'x.userID = "' . $userID . '"');

		$result = $table->fetchRow($select);
		$returnArray['decisiveness'] = array('decided' => $result['decided'],
											 'maybe'   => (is_null($result['maybe']) ? 0 : $result['maybe']));
		
		
		// Most played with player
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$numWeeks = 8;
		
		$select->from(array('oug' => 'old_user_games'),
					  array('oug.userID, (COUNT(oug.userID)/' . $numWeeks . ') as times'))
			   ->join(array('og' => 'old_games'),
			   		  'og.oldGameID = oug.oldGameID')
			   ->join(array('u' => 'users'),
			   		  'u.userID = oug.userID',
					  array('u.firstName',
					  		'u.lastName'))
			   ->where('oug.oldGameID IN (SELECT oug.oldGameID FROM old_user_games oug
			   								INNER JOIN old_games og ON og.oldGameID = oug.oldGameID
											WHERE oug.userID = "' . $userID . '"
												AND og.sportID = "' . $sportID . '")')
			   ->where('oug.userID != ?', $userID)
			   ->where('og.date > (NOW() - INTERVAL ' . $numWeeks . ' WEEK)')
			   ->group('oug.userID')
			   ->order('COUNT(oug.userID) DESC')
			   ->limit('5');
			   
		$results = $table->fetchAll($select);
		
		if (count($results) == 0) {
			$returnArray['mostPlayer'][]['name'] = false;
		} else {
			foreach ($results as $result) {
				if (!$result) {
					continue;
				} else {
					$user = new Application_Model_User($result);
					$user->setTempAttrib('times', round($result['times'], 1));
					$returnArray['mostPlayer'][] = $user;
					
				}
			}
		}
		
		// Number of different players
		/*
		$select = $select2 = $table->select();
		$select->setIntegrityCheck(false);
		$select2->setIntegrityCheck(false);
		
		$select2->from(array('oug' => 'old_user_games'),
					   array('COUNT(*) as times'))
			    ->where('oug.oldGameID IN (SELECT oldGameID FROM old_user_games WHERE userID = "' . $userID . '")')
			    ->where('oug.userID != ?', $userID)
			    ->group('oug.userID')
			    ->order('COUNT(oug.oldUserGameID) DESC')
			    ->limit('1');
		*/
		
		$select = "SELECT COUNT(*) as players
					FROM (SELECT '1' 
							FROM `old_user_games` AS `oug`
							INNER JOIN old_games og ON oug.oldGameID = og.oldGameID
							WHERE (oug.oldGameID IN (SELECT oldGameID FROM old_user_games WHERE userID = '" . $userID . "')) 
								AND (oug.userID != '" . $userID . "')
								AND og.sportID = '" . $sportID . "' 
							GROUP BY `oug`.`userID` 
							ORDER BY COUNT(oug.oldUserGameID) DESC) t";
						
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$result = $db->fetchRow($select);
		
		$returnArray['totalPlayers'] = $result['players'];
		
		/*
		// Number of calories burned (data comes from 75 minutes of play)
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('s' => 'sports'))
			   ->where('s.sportID = ?', $sportID);
			   
		$result = $table->fetchRow($select);
		
		$returnArray['calories'] = ($result['caloriesPerGame'] * $returnArray['totalGames']);
		*/
		
		return $returnArray;

			
	}
}