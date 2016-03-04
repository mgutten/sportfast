<?php

class Application_Model_SportType extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_dbTable		= 'Application_Model_DbTable_UserSportTypes';
	
	protected $_attribs     = array('typeID'			=> '',
									'userID'			=> '',
									'typeName' 			=> '',
									'typeSuffix' 		=> 'null',
									'typeDescription' 	=> '',
									'userSportTypeID'	=> '',
									'sportID'			=> '',
									'sport'				=> ''
									);
	protected $_primaryKey = 'userSportTypeID';	
	protected $_overwriteKeys = array('userID');
	
	public function save($loopSave = false)
	{
		
		if (empty($this->typeID)) {
			$this->typeID = $this->getMapper()->getForeignID('Application_Model_DbTable_SportTypes', 'typeID',array('sportID'    => $this->sportID,
																													'typeName'   => $this->typeName,
																													'typeSuffix' => $this->typeSuffix));
		}

		parent::save($loopSave);
	}
	
	public function getTypeName()
	{
		return ucwords($this->_attribs['typeName']);
	}
	
	public function getTypeSuffix()
	{
		return ucwords($this->_attribs['typeSuffix']);
	}
	
}
