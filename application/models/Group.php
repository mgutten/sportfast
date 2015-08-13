<?php

class Application_Model_Group extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_GroupsMapper';
	
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
	
	/**
	 * get group info from db
	 * @params ($groupID => groupID)
	 */
	public function getGroupByID($groupID)
	{
		return $this->getMapper()->getGroupByID($groupID, $this);
	}
	
}
