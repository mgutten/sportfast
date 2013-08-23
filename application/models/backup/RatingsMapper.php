<?php

class Application_Model_RatingsMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_UserRatings';
	
	/*
	public function save($savingClass, $loopSave = false)
	{
		if ($savingClass->isUser()) {
			// Is user rating
			$table = 'Application_Model_DbTable_UserRatings';
		} else {
			$table = 'Application_Model_DbTable_ParkRatings';
		}
		
		$this->setDbTable($table);
				
		parent::save($savingClass, $loopSave);
	}
	*/
	
	/**
	 * get all available ratings that user/park could be rated
	 * @params ($type => 'user' or 'park',
	 *			$ratingType => 'skill', 'sportsmanship', 'quality' etc)
	 * @returns array of available ratings
	 */
	public function getAvailableRatings($type, $ratingType)
	{
		$this->setDbTable('Application_Model_DbTable_Ratings');
		$table  = $this->getDbTable();
		$select = $table->select()
						->where('type = ?',$type)
						->where('ratingType = ?', $ratingType)
						->order('value ASC');
						
		$results = $table->fetchAll($select);
		
		$returnArray = array();
		foreach ($results as $result) {
			$returnArray[$result->ratingName] = array('ratingName' => $result->ratingName,
													  'value' => $result->value,
													  'ratingDescription' => $result->ratingDescription);
		}
		
		return $returnArray;
	}
	
	
	/**
	 * get user ratings for use with chart on ratings page
	 * @params ($interval => # of months back to retrieve information (e.g. get 4 months of data separated by month)
	 * @returns array of month => value
	 */
	public function getUserRatingsForChart($userID, $sportID, $interval = 4)
	{
		$table = $this->getDbTable();
		
		//for ($i = $interval; $i > 0; $i--) {
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('us' => 'user_sports'))
			   ->joinLeft(array('ur' => 'user_ratings'),
						  'us.userID = ur.receivingUserID AND us.sportID = ur.sportID')
			   ->where('us.userID = ?', $userID)
			   ->where('us.sportID = ?' , $sportID)
			   ->where('DATE_FORMAT(ur.dateHappened, %m) >= DATE_FORMAT((now() - INTERVAL ' . $interval . ' MONTHS), %m)');
			   
		$results = $table->fetchAll($select);
		
		$ratings = new Application_Model_Ratings();
		foreach ($results as $result) {
			$ratings->skillInitial = $result->skillInitial;
			$ratings->addRating($result);
		}
		
		return $ratings;
		//}
	}
	 
			
				   
	
	
	/**
	 * check to see if there was already an unsucessful (success = 0) rating for a park from a particular game (only allow 1 unsucessful rating per game)
	 */
	public function getUnsuccessfulParkRating($parkID, $gameID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('pr' => 'park_ratings'))
			   ->where('pr.parkID = ?', $parkID)
			   ->where('pr.gameID = ?', $gameID)
			   ->where('pr.success = ?', '0');
			   
		$results = $table->fetchRow($select);
		
		return $results;
	}
	
	/**
	 * retrieve number value of rating level
	 * @params ($level => 'beginner', 'good', etc
	 *			$type => 'user', 'park',
	 *			$ratingType => 'skill', 'sportsmanship'
	 */
	public function getValueFromRating($level, $type, $ratingType)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from('ratings')
			   ->where('type = ?', $type)
			   ->where('ratingType = ?', $ratingType)
			   ->where('ratingName = ?', $level);
			   
		$result = $table->fetchRow($select);
		
		return $result['value'];
	}
	
	/**
	 * set userRatingID to incorrect to be reviewed
	 */
	public function setUserRatingIncorrect($userRatingID)
	{
		$this->setDbTable('Application_Model_DbTable_UserRatings');
		
		$table = $this->getDbTable();
		return $table->update(array('incorrect' => 1), array('userRatingID = ?' => $userRatingID));
	}
		
		
}
