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
}
