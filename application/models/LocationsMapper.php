<?php

class Application_Model_LocationsMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Zipcodes';
	
		
	public function getLocationByZipcode($zipcode, $savingClass) 
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->from(array('z'  => 'zipcodes'), 
					  array('AsText(location) as location'))
			   ->where('zipcode = ?', $zipcode)
			   ->limit(1);
					  
		$results = $table->fetchRow($select);
		
		$savingClass->setAttribs($results);
		
		return $savingClass;
					  
	}
	
	public function getLocationByCityID($cityID, $savingClass) 
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->from(array('z'  => 'zipcodes'), 
					  array('AsText(location) as location'))
			   ->where('z.cityID = ?', $cityID)
			   ->where('z.zipcodeType = "STANDARD"')
			   ->order('z.main DESC')
			   ->limit(1);
	
		$results = $table->fetchRow($select);
		
		$savingClass->setAttribs($results);
		
		return $savingClass;
					  
	}
	
	/**
	 * get cityID (zipcode nearest) $location
	 * @params($location => "POINT(long lat)" str)
	 */
	public function getCityIDByLocation($location) 
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('z'  => 'zipcodes'))
			   ->join(array('c' => 'cities'),
			   		  'c.cityID = z.cityID')
			   ->where('z.zipcodeType != ?', 'PO BOX')
			   ->order("GLength(LineStringFromWKB(
									  LineString(
										GeomFromText('" . $location . "'), 
										z.location
										)
									)
								) ASC)")
			   ->limit(1);
				  
		$results = $table->fetchRow($select);
		
		return $results['cityID'];
					  
	}
	
}
					  

	
