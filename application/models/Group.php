<?php

class Application_Model_Group extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_GroupssMapper';
	
	protected $_attribs     = array('groupID'   => '',
									'groupName' => '',
									'public'	=> '',
									'cityID'	=> ''
									);
									
	protected $_primaryKey = 'groupID';
	
	public function getProfilePic($size, $id = false, $type = 'groups') 
	{
		return parent::getProfilePic($size, $this->groupID, $type);
	}
	
}
