<?php

class Application_Model_OtherSport extends Application_Model_Sport
{
	protected $_mapperClass = 'Application_Model_SportsMapper';
	protected $_dbTable		= 'Application_Model_DbTable_UserOtherSports';
	
	protected $_attribs     = array('userOtherSportID' => '',
									'sport'		   => '',
									'userID'	   => ''
									);
									
	protected $_primaryKey = 'userOtherSportID';	
	protected $_overwriteKeys = array('userID');
	
	public function save($loopSave = false)
	{
		echo $this->sport;
		$this->getMapper()->save($this, $loopSave);
	}
	
}

