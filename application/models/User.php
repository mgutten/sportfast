<?php

class Application_Model_User extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_attribs = array();
	protected $_primaryKey = 'userID';
				
		
	public function setUsername($username)
	{
		$this->_attribs['username'] = $username;
		return $this;
	}
	
	public function getUsername()
	{
		return $this->_attribs['username'];
	}

	public function setPassword($password)
	{
		$this->_attribs['password'] = $password;
		return $this;
	}
	
	public function getPassword()
	{
		return $this->_attribs['password'];
	}	

	public function setUserID($id)
	{
		$this->_attribs['userID'] = $id;
		return $this;
	}
	
	public function getUserID()
	{
		return $this->_attribs['userID'];
	}
	


}

