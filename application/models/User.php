<?php

class Application_Model_User extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_attribs     = array('userID' 		=> '',
								    'username' 		=> '',
								    'password' 		=> '',
								    'firstName' 	=> '',
								    'lastName' 		=> '',
								    'age' 			=> '',
									'streetAddress' => '',
									'cityID'		=> '',
									'weight' 		=> '',
									'height'		=> '',
									'sex' 			=> '',
									'lastRead' 		=> '',
									'active'		=> '',
									'verifyHash' 	=> '',
									'dob'			=> '',
									'city'			=> '',
									'sports'		=> array()
									);

	protected $_primaryKey = 'userID';	
	
	public function getSport($sport) 
	{
		if (!isset($this->_attribs['sports'][$sport])) {
			$this->_attribs['sports'][$sport] = new Application_Model_Sport();
		}
		return $this->_attribs['sports'][$sport];
	}
	
	public function getCity()
	{
		if (empty($this->_attribs['city'])) {
			$this->_attribs['city'] = new Application_Model_City();
		}
		return $this->_attribs['city'];
	}
	
	public function getUserSportsInfo()
	{
		return $this->getMapper()->getUserSportsInfo($this->userID,$this);
	}
	
	public function setLastRead()
	{
		$this->_attribs['lastRead'] = date("Y-m-d H:i:s", time());
		return $this;
	}
	
	public function getLastRead()
	{
		return $this->_attribs['lastRead'];
	}

	/*
	public function setDob($dob)
	{
		$this->_attribs['dob'] = $dob;
		return $this;
	}
	
	public function getDob()
	{
		return $this->_attribs['dob'];
	}


	public function setLastName($lastName)
	{
		$this->_attribs['lastName'] = $lastName;
		return $this;
	}
	
	public function getLastName()
	{
		return $this->_attribs['lastName'];
	}
	
	public function setAge($age)
	{
		$this->_attribs['age'] = $age;
		return $this;
	}
	
	public function getAge()
	{
		return $this->_attribs['age'];
	}
	
	public function setStreetAddress($streetAddress)
	{
		$this->_attribs['streetAddress'] = $streetAddress;
		return $this;
	}
	
	public function getStreetAddress()
	{
		return $this->_attribs['streetAddress'];
	}	
	
	public function setWeight($weight)
	{
		$this->_attribs['weight'] = $weight;
		return $this;
	}
	
	public function getWeight()
	{
		return $this->_attribs['weight'];
	}	
	
	public function setHeight($height)
	{
		$this->_attribs['height'] = $height;
		return $this;
	}
	
	public function getHeight()
	{
		return $this->_attribs['height'];
	}
	
	public function setSex($sex)
	{
		$this->_attribs['sex'] = $sex;
		return $this;
	}
	
	public function getSex()
	{
		return $this->_attribs['sex'];
	}	
	
	public function setCityID($cityID)
	{
		$this->_attribs['cityID'] = $cityID;
		return $this;
	}
	
	public function getCityID()
	{
		return $this->_attribs['cityID'];
	}	
		
	public function setVerifyHash($verifyHash)
	{
		$this->_attribs['verifyHash'] = $verifyHash;
		return $this;
	}
	
	public function getVerifyHash()
	{
		return $this->_attribs['verifyHash'];
	}	
	
	public function setActive($active)
	{
		$this->_attribs['active'] = $active;
		return $this;
	}
	
	public function getActive()
	{
		return $this->_attribs['active'];
	}
	
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
		// Hash password
		//$password = $this->getMapper()->hashPassword($password);
		
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
	
	public function setFirstName($username)
	{
		$this->_attribs['firstName'] = $username;
		return $this;
	}
	
	public function getFirstName()
	{
		return $this->_attribs['firstName'];
	}
	*/


}

