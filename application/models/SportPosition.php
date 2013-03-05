<?php

class Application_Model_SportPosition extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_dbTable		= 'Application_Model_DbTable_UserSportPositions';	
	
	protected $_attribs     = array('positionID'			=> '',
								    'userID'				=> '',
									'sportID'				=> '',
									'positionName' 			=> 'null',
									'positionAbbreviation' 	=> 'null',
									'positionDescription' 	=> '',
									'userSportPositionID'		=> ''
									);
	protected $_primaryKey = 'userSportPositionID';	
	protected $_overwriteKeys = array('userID');
	
	
	public function save()
	{
		if (empty($this->positionID)) {
			// Fill foreign key before save
			$this->positionID = $this->getMapper()
									 ->getForeignID('Application_Model_DbTable_SportPositions', 'positionID',array('sportID'    		  => $this->sportID,
																												   'positionAbbreviation' => $this->positionAbbreviation));
		}
		
		parent::save($this);
	}
	
	public function testDelete()
	{
		return $this->getMapper()->testDelete($this);
	}
	
}