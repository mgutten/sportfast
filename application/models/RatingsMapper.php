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
