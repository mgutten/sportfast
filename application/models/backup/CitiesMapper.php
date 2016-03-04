<?php
class Application_Model_CitiesMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Cities';

	public function save($savingClass, $loopSave = true)
	{
		return $savingClass;
	}
	
	public function getCityFromZipcode($zipcode, $savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		$select->from(array('c'  => 'cities'))
			   ->join(array('z' => 'zipcodes'),
			   		  'c.cityID = z.cityID')
		       ->where('z.zipcode = ?', $zipcode)
			   ->where('z.zipcodeType = "STANDARD"');
			   
		$results = $table->fetchAll($select);
		$result = $results->current();

		$savingClass->setAttribs($result);
			
		return $savingClass;
	}
	
	/**
	 * get city where name is like $city
	 * @params($city => city name,
	 *		   $state => state,
	 *		   $savingClass => cities model)
	 */
	public function getCitiesLikeName($city, $state, $savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		$select->from(array('c'  => 'cities'))
		       ->where('c.city LIKE "' . $city . '%"');
		if ($state) {
			$select->where('c.state = "' . $state . '"');
		}
		$select->limit(5);
			   
		$results = $table->fetchAll($select);

		foreach ($results as $result) {
			$savingClass->addCity($result);
		}
			
		return $savingClass;
	}

	public function getCitiesLike($zipcodeOrCity, $state, $savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);	
			
		if (preg_match('/^[0-9]+$/', $zipcodeOrCity)) {
			// Zipcode
			$select->from(array('z' => 'zipcodes'))
				   ->join(array('c' => 'cities'),
				   		  'z.cityID = c.cityID')
				   ->where('z.zipcode LIKE "' . $zipcodeOrCity . '%"');
		} else {
			// City name
			$select->from(array('z' => 'zipcodes'))
				   ->join(array('c' => 'cities'),
				   		  'z.cityID = c.cityID')
				   ->where('c.city LIKE "' . $zipcodeOrCity . '%"')
				   ->where('z.zipcodeType = "STANDARD"');
		}
		
		if ($state) {
			$select->where('c.state = "' . $state . '"');
		}
		$select->group('c.city')
			   ->limit(5);
			   
		$results = $table->fetchAll($select);

		foreach ($results as $result) {
			$savingClass->addCity($result);
		}
			
		return $savingClass;
	}
	
	/**
	 * used to get all zipcodes within the given bounds
	 * @returns cityIDs
	 */
	public function getZipcodesWithin($latitude, $longitude)
	{
		$table = $this->getDbTable();
		
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('z' => 'zipcodes'))
			   ->where($this->getAreaWhere('POINT(' . $longitude['upper'] . ',' . $latitude['upper'] . ')', 'POINT(' . $longitude['lower'] . ',' . $latitude['lower'] . ')', 'z.location'))
			   ->where('z.zipcodeType != "PO BOX"');
			   
		$zipcodes = $table->fetchAll($select);
		
		$returnArray = array();
		foreach ($zipcodes as $zipcode) {
			$returnArray[] = $zipcode->cityID;
		}
		
		return $returnArray;
	}
		


	
}
