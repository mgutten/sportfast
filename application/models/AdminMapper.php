<?php

class Application_Model_AdminMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Admins';

	/**
	 * used to retrieve admin info (username, password)
	 */
	public function getAdminByUsername($username)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->where('username = ?', $username);
		
		$result = $table->fetchRow($select);
		
		if ($result) {
			return $result['password'];
		} else {
			return false;
		}
	}
	
	public function getCityData()
	{
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->setIntegrityCheck(false);
		
		// Total users
		$select->from(array('u' => 'users'),
					  array('COUNT(userID) as users'))
			   ->join(array('c' => 'cities'),
			   		  'c.cityID = u.cityID',
			   		  array('city',
					  		'cityID'))
			   ->where('u.fake = 0')
			   ->group('u.cityID')
			   ->order('COUNT(userID) DESC');
			   
		$results = $table->fetchAll($select);
		
		$returnArray = array();
		
		foreach ($results as $city) {
			$returnArray[$city->cityID] = array('city' => $city->city,
												'totalUsers' => $city->users);
		}
		
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		// Last 2 months
		$select->from(array('u' => 'users'),
					  array('COUNT(userID) as users'))
			   ->join(array('c' => 'cities'),
			   		  'c.cityID = u.cityID',
			   		  array('city',
					  		'cityID'))
			   ->where('u.joined > (now() - INTERVAL 2 MONTH)')
			   ->where('u.fake = 0')
			   ->group('u.cityID')
			   ->order('COUNT(userID) DESC');
			   
		$results = $table->fetchAll($select);
		
		foreach ($results as $city) {
			$returnArray[$city->cityID]['last2Months'] = $city->users;
		}
		
		return $returnArray;
		
	}
	
	/**
	 * get user ratings that have been flagged as incorrect
	 */
	public function getFlaggedRatings()
	{
		
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->setIntegrityCheck(false);
		
		
		$sql = "(SELECT ur.receivingUserID, 
						ROUND(AVG(r.value)) as avgSkill, 
						ROUND(AVG(r2.value)) as avgSportsmanship,
						ur.sportID
					FROM user_ratings ur
					INNER JOIN ratings r ON r.ratingID = ur.skill
					INNER JOIN ratings r2 ON r2.ratingID = ur.sportsmanship
					WHERE ur.incorrect = 0 
					GROUP BY ur.receivingUserID,ur.sportID)";
		
		$select->from(array('ur' => 'user_ratings'))
			   ->join(array('r' => 'ratings'),
			   		  'ur.skill = r.ratingID',
					  array('value as skillRating'))
			   ->join(array('r2' => 'ratings'),
			   		  'ur.sportsmanship = r2.ratingID',
					  array('value as sportsmanshipRating'))
			   ->joinLeft(array('ur2' => new Zend_Db_Expr($sql)),
			   		  'ur2.receivingUserID = ur.receivingUserID AND ur2.sportID = ur.sportID')
			   ->where('ur.incorrect = 1');
		
		$results = $table->fetchAll($select);
		
		$returnArray = array();
		foreach ($results as $result) {
			$returnArray[] = array('skill' => $result->skillRating,
								   'sportsmanship' => $result->sportsmanshipRating,
								   'avgSkill' => $result->avgSkill,
								   'avgSportsmanship' => $result->avgSportsmanship,
								   'userRatingID' => $result->userRatingID);
		}
		
		return $returnArray;
	}
					 
	
}
		
		
		