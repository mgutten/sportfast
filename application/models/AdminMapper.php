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
			   ->group('u.cityID')
			   ->order('COUNT(userID) DESC');
			   
		$results = $table->fetchAll($select);
		
		foreach ($results as $city) {
			$returnArray[$city->cityID]['last2Months'] = $city->users;
		}
		
		return $returnArray;
		
	}
					 
	
}
		
		
		