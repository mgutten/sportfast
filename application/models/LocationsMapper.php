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
	
}
					  

	
