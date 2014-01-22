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
						
				 
	
}
	
