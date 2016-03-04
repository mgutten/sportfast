<?php

class Application_Model_SportFormat extends Application_Model_ModelAbstract
{
	protected $_mapperClass   = 'Application_Model_UsersMapper';
	protected $_dbTable		  = 'Application_Model_DbTable_UserSportFormats';	
	
	protected $_attribs       = array('userID'				=> '',
									  'sportID'				=> '',
									  'formatID'			=> '',
									  'format'				=> '',
									  'userSportFormatID'	=> ''
									);
	protected $_primaryKey 	  = 'userSportFormatID';
	protected $_overwriteKeys = array('userID','sportID');
	
	
	public function save($loopSave = false)
	{
		if (empty($this->formatID)) {
			// Fill foreign key before save
			$this->formatID = $this->getMapper()
									 ->getForeignID('Application_Model_DbTable_SportFormats', 'formatID',array('format' => $this->format));
		}
		
		parent::save($this, $loopSave);
	}
}
