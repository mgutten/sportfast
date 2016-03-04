<?php
class Application_Model_Availability extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_dbTable		= 'Application_Model_DbTable_UserSportAvailabilities';
	
	protected $_attribs     = array('userID'				 => '',
									'sportID' 				 => '',
									'day' 					 => '',
									'hour' 					 => '',
									'userSportAvailabilityID'=> ''
									);
	protected $_primaryKey = 'userSportAvailabilityID';	
	protected $_overwriteKeys = array('userID','sportID');
	
	
	public function __construct($day = false, $hour = false, $parentClass = false)
	{
		if (isset($parentClass->userID)) {
			$this->userID = $parentClass->userID;
		}
		if (isset($parentClass->sportID)) {
			$this->sportID = $parentClass->sportID;
		}
		
		$this->day  = $day;
		$this->hour = $hour;
	}
	
	
}