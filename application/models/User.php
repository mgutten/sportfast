<?php

class Application_Model_User extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_username;
	protected $_password;
	protected $_userID;
				
		
	public function setUsername($username)
	{
		$this->_username = $username;
		return $this;
	}
	
	public function getUsername()
	{
		return $this->_username;
	}

	public function setPassword($password)
	{
		$this->_password = $password;
		return $this;
	}
	
	public function getPassword()
	{
		return $this->_password;
	}	

	public function setUserID($id)
	{
		$this->_userID = $id;
		return $this;
	}
	
	public function getUserID()
	{
		return $this->_userID;
	}	


}

