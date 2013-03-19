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
		       ->where('z.zipcode = ?', $zipcode);
			   
		$results = $table->fetchAll($select);
		$result = $results->current();

		$savingClass->setAttribs($result);
			
		return $savingClass;
	}
	
}
