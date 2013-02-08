<?php

class Application_Model_UsersMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Users';
	
	public function getUserBy($column, $value)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->where($column . ' = ' . '?', $value)
			   ->limit(1);
		$results = $table->fetchAll($select);
		//$result = $result->current();
		$user = $this->createUserClasses($results);
			
		return $user;	
		//return $result;
	}
	
	public function createUserClasses($results) 
	{
		$users = array();
		
		foreach ($results as $result) {
			$user = new Application_Model_User();
			$user->setUsername($result->username)
				 ->setUserID($result->userID)
				 ->setPassword($result->password);
			
			if(count($results) <= 1) {
				$users = $user;
				break;
			}
			
			$users[] = $user;
		}
		
		return $users;
	}
	
}