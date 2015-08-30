<?php

class Application_Model_SportRatings extends Application_Model_TypesAbstract
{
	protected $_mapperClass = 'Application_Model_SportRatingsMapper';
	
	protected $_attribs     = array('ratings' => array(),
									);
									
	protected $_primaryKey = 'userSportRatingID';
	protected $_dbTable = 'Application_Model_DbTable_UserSportRatings';
	
	public function addRating($resultRow)
	{
		if (is_object($resultRow)) {
			$resultRow->toArray();
		}
		
		return $this->_attribs['ratings'][$resultRow['sportRatingID']] = new Application_Model_SportRating($resultRow);
	}
	
	/**
	 * get all potential ratings for a sport
	 */
	public function getAllSportRatings($sportID)
	{
		return $this->getMapper()->getAllSportRatings($this, $sportID);
	}
	
	
	/**
	 * get past ratings where $userID won from $daysBack days ago for $sports
	 */
	public function getUserRelativeRatings($userID, $daysBack = 7, $sports = false)
	{
		return $this->getMapper()->getUserRelativeRatings($userID, $daysBack, $sports);
	}
	
	
	/**
	 * get past ratings where $userID won from $daysBack days ago for $sports
	 */
	public function getUserGiveRatingsStats($userID, $sports = false)
	{
		return $this->getMapper()->getUserGiveRatingsStats($userID, $sports);
	}
	
	public function getAll()
	{
		return $this->_attribs['ratings'];
	}
	
	public function getTopSkills($numReturned = 3)
	{
		$ratings = $this->orderByValue();
			
		
		$returnArray = array();
		
		for ($i = 0; $i < $numReturned; $i++) {
			array_push($returnArray, $ratings[$i]);
		}
		
		return $returnArray;
	}
	
	/**
	 * get avgSkill of sport
	 */
	public function calculateAvg()
	{
		$numIncluded = 4;
		
		$ratings = $this->getTopSkills($numIncluded);
		
		$total = 0;
		foreach ($ratings as $rating) {
			$total += $rating->value;
		}
		
		$avg = $total/$numIncluded;
		
		return round($avg, 1);
	}
	
	/**
	 * save avgSkill to db of sport
	 */
	public function saveAvg($userID, $sportID)
	{
		return $this->getMapper()->saveAvg($userID, $sportID);
	}
	
	/**
	 * order array of sportRatings by value
	 */
	public function orderByValue()
	{
		$ratings = $this->ratings;
		
		usort($ratings, array('Application_Model_SportRatings', 'valueSort')); 
		
		return $ratings;
	}
	
	/* sort by value of sportRating */
	private static function valueSort($a,$b) 
	{
		$a = $a->value;
		$b = $b->value;
		
       	if ($a == $b) {
			return 0;
		}
		
		return ($a < $b ? 1 : -1);
	}

}
	
