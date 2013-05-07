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
	 * set userRatingID to incorrect to be reviewed
	 */
	public function setUserRatingIncorrect($userRatingID)
	{
		$this->setDbTable('Application_Model_DbTable_UserRatings');
		
		$table = $this->getDbTable();
		return $table->update(array('incorrect' => 1), array('userRatingID = ?' => $userRatingID));
	}
		
		
}
