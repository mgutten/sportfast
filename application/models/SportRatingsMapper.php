<?php

class Application_Model_SportRatingsMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_UserSportRatings';
	
	/**
	 * calculate new average for user's sport based on db values of sport ratings
	 */
	public function saveAvg($userID, $sportID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->setIntegrityCheck(false);
		
		$select->from(array('usr' => 'user_sport_ratings'))
			   ->join(array('sr' => 'sport_ratings'),
			   		  'sr.sportRatingID = usr.sportRatingID')
			   ->where('usr.userID = ?', $userID)
			   ->where('sr.sportID = ?', $sportID);
			   
		$results = $table->fetchAll($select);
		
		$sportRatings = new Application_Model_SportRatings();
		foreach ($results as $result) {
			$sportRatings->addRating($result);
		}
		
		$avg = $sportRatings->calculateAvg();
		
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "UPDATE user_sports SET avgSkill = '" . $avg . "' 	
					WHERE userID = '" . $userID . "' 
						AND sportID = '" . $sportID . "'";
					
		$db->query($sql);
		
		return $avg;
	}
	
	/**
	 * save relative rating in db
	 */
	public function saveRelativeRating($relativeRating)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('usr' => 'user_sport_ratings'))
			   ->where('usr.userID = ?', $relativeRating->losingUserID)
			   ->where('usr.sportRatingID = ?', $relativeRating->sportRatingID);
	}
	
	/**
	 * get all potential ratings for sport
	 */
	public function getAllSportRatings($savingClass, $sportID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('sr' => 'sport_ratings'))
			   ->where('sr.sportID = ?', $sportID);
			   
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			$savingClass->addRating($result);
		}
		
		return $savingClass;
	}
	
	public function getUserRelativeRatings($userID, $daysBack = 7, $sports = false)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->setIntegrityCheck(false);
		
						
		$pickupGames = "(SELECT st.typeName, st.typeSuffix, og2.*
						FROM old_games og2
						INNER JOIN sport_types st ON st.typeID = og2.typeID)";
		
		$select->from(array('urr' => 'user_relative_ratings'))
			   ->join(array('sr' => 'sport_ratings'),
			   		  'sr.sportRatingID = urr.sportRatingID')
			   ->join(array('u1' => 'users'),
			   		  'u1.userID = urr.losingUserID',
					  array('firstName as losingFirstName',
					  		'lastName as losingLastName',
							'userID as losingUserID'))
			   ->join(array('u2' => 'users'),
			   		  'u2.userID = urr.actingUserID',
					  array('firstName as actingFirstName',
					  		'lastName as actingLastName',
							'userID as actingUserID'))
			   ->joinLeft(array('og' => new Zend_Db_Expr($pickupGames)),
			   			  'og.oldGameID = urr.oldGameID',
						  array('oldGameID', 
						  		 'gameID', 
								 'date as pickupDate',
								 'sport',
								 'sportID',
								 'typeName',
								 'typeSuffix'))
			   ->joinLeft(array('tg' => 'team_games'),
			   			  'tg.teamGameID = urr.teamGameID',
						  array('teamGameID', 
						  		 'teamID',
								 'opponent', 
								 'date as teamDate'))
			   ->where('urr.winningUserID = ?', $userID)
			   ->where('urr.dateHappened > (NOW() - INTERVAL ' . $daysBack . ' DAY)')
			   ->order('urr.dateHappened DESC');
				
		$results = $table->fetchAll($select);
		
		$returnArray = array();
		foreach ($results as $r) {
			
			$relativeRating = new Application_Model_RelativeRating($r);
			
			$sportRating = new Application_Model_SportRating($r);
			$relativeRating->sportRating = $sportRating;
			
			if (!is_null($r->oldGameID)) {
				// Is pickup
				$date = $r->pickupDate;
			} else {
				$date = $r->teamDate;
			}
			
			$game = new Application_Model_Game($r);
			$game->date = $date;
			$actingUser = $losingUser = new Application_Model_User();
			
			$actingUser->userID = $r->actingUserID;
			$actingUser->firstName = $r->actingFirstName;
			$actingUser->lastName = $r->actingLastName;
			
			$losingUser->userID = $r->losingUserID;
			$losingUser->firstName = $r->losingFirstName;
			$losingUser->lastName = $r->losingLastName;
			
			$relativeRating->actingUser = $actingUser;
			$relativeRating->losingUser = $losingUser;
			$relativeRating->game = $game;
			
			$sport = strtolower($game->sport);
			
			if (!isset($returnArray[$sport])) {
				$returnArray[$sport] = array();
			}
			
			if (!isset($returnArray[$sport][$r->ing])) {
				$returnArray[$sport][$r->ing] = array();
			}
			
			$returnArray[$sport][$r->ing][] = $relativeRating;
		}
		
		return $returnArray;
	}
	
	/**
	 * how many ratings (for each sport) have been given/received
	 * @returns Application_Model_Sports
	 */
	public function getUserGiveRatingsStats($userID, $sports = false)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->setIntegrityCheck(false);
		
		$ratingsGiven = "(SELECT COUNT(urr.actingUserID) as ratingsGiven,
								 urr.actingUserID,
								 sr.sportID,
								 s.sport
							FROM sport_ratings sr
							LEFT JOIN user_relative_ratings urr ON urr.sportRatingID = sr.sportRatingID 
							INNER JOIN sports s ON sr.sportID = s.sportID
							WHERE urr.actingUserID = '" . $userID . "'
								OR urr.actingUserID IS NULL
							GROUP BY sr.sportID)";
							
		$ratingsReceived = "(SELECT COUNT(urr.winningUserID) as ratingsReceived,
								 urr.winningUserID,
								 sr.sportID,
								 s.sport
							FROM sport_ratings sr
							LEFT JOIN user_relative_ratings urr ON urr.sportRatingID = sr.sportRatingID
							INNER JOIN sports s ON sr.sportID = s.sportID
							WHERE urr.winningUserID = '" . $userID . "'
								OR urr.winningUserID IS NULL
							GROUP BY sr.sportID)";
		
		$select->from(array('urr' => new Zend_Db_Expr($ratingsGiven)))
			   ->joinLeft(array('urr2' => new Zend_Db_Expr($ratingsReceived)),
			   					'urr2.sportID = urr.sportID');
								
		
		$results = $table->fetchAll($select);
		
		$sports = new Application_Model_Sports();
		
		foreach ($results as $result) {
			$sports->getSport($result->sport)->setTempAttrib('ratingsReceived', $result->ratingsReceived);
			$sports->getSport($result->sport)->setTempAttrib('ratingsGiven', $result->ratingsGiven);
		}
		
		return $sports;
	}
	
	/**
	 * penalize (or cancel if done for this game) player for not showing up to game
	 */
	public function noShow($sportRating)					
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('urr' => 'user_relative_ratings'))
			   ->where('urr.' . $sportRating->getIDType() . ' = ?', $sportRating->getTypeID())
			   ->where('urr.winningUserID = ?', $sportRating->winningUserID)
			   ->where('urr.noShow = "1"');
			   
		$results = $table->fetchAll($select);
		
		
		if (count($results) > 0) {
			// Has already been penalized for this game
			return false;
		}
		
		$data = array('winningUserID' => $sportRating->winningUserID,
					  'actingUserID' => $sportRating->actingUserID,
					  'sportRatingID' => $sportRating->sportRatingID,
					  $sportRating->getIDType() => $sportRating->getTypeID(),
					  'noShow' => '1',
					  'dateHappened' => $sportRating->dateHappened,
					  'locked' => '0',
					  'dateUnlocked' => $sportRating->dateUnlocked);
					  
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->insert('user_relative_ratings', $data);
		
		$userRelativeRatingID = $db->lastInsertId();
		
		// Save user's current ratings
		$this->userRatingSnapshot($sportRating->winningUserID, $userRelativeRatingID, $sportRating->sportRatingID, true);
		
		// Update values to show new, decreased value
		$decrease = -0.1;
		$this->updateRelativeRatingValue($sportRating->winningUserID, $decrease, $sportRating->sportRatingID, true);
	}
	
	public function getSportID($sportRatingID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('sr' => 'sport_ratings'))
			   ->where('sr.sportRatingID = ?', $sportRatingID);
			   
		$result = $table->fetchRow($select);
		
		return $result['sportID'];
	}
		
	/**
	 * save user's rating details in old_ tables
	 * @params ($all => should all sportRatingIDs be saved for this sport or just the one provided?)
	 */
	public function userRatingSnapshot($userID, $userRelativeRatingID, $sportRatingID, $all = false)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$sportID = $this->getSportID($sportRatingID);
		
		$oldUserSports = "INSERT INTO old_user_sports (oldUserSportID, userID, sportID, avgSkill, dateMoved) 
							(SELECT '', us.userID, us.sportID, us.avgSkill, NOW()
								FROM user_sports us
								WHERE us.sportID = '" . $sportID . "'
									AND us.userID = '" . $userID . "')";
		
							
		$db->query($oldUserSports);
		
		
		$oldUserSportRatings = "INSERT INTO old_user_sport_ratings (oldUserSportRatingID, userSportRatingID, sportRatingID, userID, 
																	userRelativeRatingID, value, lastChanged, dateMoved)
								(SELECT '', usr.userSportRatingID, usr.sportRatingID, usr.userID, '" . $userRelativeRatingID . "', 
										usr.value, usr.lastChanged, NOW()
									FROM user_sport_ratings usr 
									INNER JOIN sport_ratings sr ON sr.sportRatingID = usr.sportRatingID
									WHERE sr.sportID = '" . $sportID . "' ";
		if (!$all) {
			$oldUserSportRatings .= " AND usr.sportRatingID = '" . $sportRatingID . "'";
		}
		
		$oldUserSportRatings .= ")";
		
		$db->query($oldUserSportRatings);
		
									
	}
	
	/**
	 * update user's ratings for either a specific sportRatingID (if !$all) or to all sportRatingIDs for that sport
	 * @params ($change => +/- value to increase or decrease rating by,
	 *			$all => if true, apply to all sportRatingIDs for this sport)
	 */
	public function updateRelativeRatingValue($userID, $change, $sportRatingID, $all = false)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$sportID = $this->getSportID($sportRatingID);
		
		$update = "UPDATE user_sport_ratings usr
					INNER JOIN sport_ratings sr ON sr.sportRatingID = usr.sportRatingID
					SET usr.value = (usr.value + " . $change . "),
						usr.lastChange = " . $change . ",
						usr.lastChanged = NOW()
					WHERE
						usr.userID = '" . $userID . "'
						AND sr.sportID = '" . $sportID . "' ";
		
		if (!$all) {
			$update .= " AND usr.sportRatingID = '" . $sportRatingID . "' ";
		}
		
		$db->query($update);
		
		$this->saveAvg($userID, $sportID);
	}
	
}
	
