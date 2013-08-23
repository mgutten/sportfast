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
		
		// Total games
		$select->from(array('oug' => 'old_user_games'),
					  array('COUNT(oug.oldUserGameID) as totalGames'))
			   ->join(array('og' => 'old_games'),
			   		  'og.oldGameID = oug.oldGameID',
					  array(''))
			   ->where('og.sportID = ?', $sportID)
			   ->where('oug.userID = ?', $userID);
			   
		$result = $table->fetchRow($select);
		
		$returnArray = array();
		$returnArray['totalGames'] = $result['totalGames'];
		
		
		// Total ratings
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('ur' => 'user_ratings'),
					  array('COUNT(ur.userRatingID) as totalRatings'))
			   ->where('ur.givingUserID = ?', $userID)
			   ->where('ur.sportID = ?', $sportID);
			   
		$result = $table->fetchRow($select);
		$returnArray['totalRatings'] = $result['totalRatings'];
		
		
		// Most played with player
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('oug' => 'old_user_games'),
					  array('oug.userID, COUNT(oug.userID) as times'))
			   ->join(array('u' => 'users'),
			   		  'u.userID = oug.userID',
					  array('u.firstName',
					  		'u.lastName'))
			   ->where('oug.oldGameID IN (SELECT oug.oldGameID FROM old_user_games oug
			   								INNER JOIN old_games og ON og.oldGameID = oug.oldGameID
											WHERE oug.userID = "' . $userID . '"
												AND og.sportID = "' . $sportID . '")')
			   ->where('oug.userID != ?', $userID)
			   ->group('oug.userID')
			   ->order('COUNT(oug.userID) DESC')
			   ->limit('3');
			   
		$results = $table->fetchAll($select);
		
		if (count($results) == 0) {
			$returnArray['mostPlayer'][]['name'] = false;
		} else {
			foreach ($results as $result) {
				if (!$result) {
					continue;
				} else {
					$returnArray['mostPlayer'][] = array('name' => ucwords($result['firstName']) . ' ' . ucwords($result['lastName'][0]),
														 'times' => $result['times']);
					
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
		
		// Number of calories burned (data comes from 75 minutes of play)
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('s' => 'sports'))
			   ->where('s.sportID = ?', $sportID);
			   
		$result = $table->fetchRow($select);
		
		$returnArray['calories'] = ($result['caloriesPerGame'] * $returnArray['totalGames']);
		
		return $returnArray;

			
	}
}