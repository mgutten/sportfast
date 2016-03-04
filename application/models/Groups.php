<?php

class Application_Model_Groups extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_GroupsMapper';
	
	protected $_attribs     = array('groups' => '');
	
	/**
	 * add group to stack
	 * @params($resultRow => array returned from mapper)
	 */
	public function addGroup($resultRow)
	{
		$group = $this->_attribs['groups'][] = new Application_Model_Group($resultRow);
		return $group;
	}
									
}
