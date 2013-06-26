<?php

class Application_Model_AdminMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Admins';

	/**
	 * used to retrieve admin info (username, password)
	 */
	public function getAdminByUsername($username)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		
		$select->where('username = ?', $username);
		
		$result = $table->fetchRow($select);
		
		if ($result) {
			return $result['password'];
		} else {
			return false;
		}
	}
	
}
		
		
		