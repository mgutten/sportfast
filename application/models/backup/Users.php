<?php

class Application_Model_Users extends Application_Model_TypesAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_singleClass = 'Application_Model_User';
	
	protected $_attribs     = array('users'	=> '',
									'totalRows' => '');
									
	protected $_primaryKey  = 'userID';
	
	
	
	public function addUser($resultRow)
	{
		//$user = $this->_attribs['users'][] = new Application_Model_User($resultRow);

		$user = $this->_attribs['users'][] = new Application_Model_User($resultRow);
		return $user;
	}
	
	public function getAllUserLocations($upper = false, $lower = false)
	{
		return $this->getMapper()->getAllUserLocations($upper, $lower, $this);
	}
	
	public function getAllUsersStats()
	{
		return $this->getMapper()->getAllUsersStats();
	}
	
	public function getUsersInArea($userID, $latitude, $longitude, $lastActive = false)
	{
		return $this->getMapper()->getUsersInArea($userID, $latitude, $longitude, $lastActive);
	}
	
	public function getAvailableUsers($datetime, $sportID, $location)
	{
		return $this->getMapper()->getAvailableUsers($datetime, $sportID, $location, $this);
	}
	
	public function findUsers($options, $userClass, $limit)
	{
		return $this->getMapper()->findUsers($options, $userClass, $this, $limit);
	}
	
	public function getUserEmails($userIDs)
	{
		return $this->getMapper()->getUserEmails($userIDs, $this);
	}
	
	public function emailsExist($emails)
	{
		return $this->getMapper()->emailsExist($emails);
	}
	
	public function getUser($userID)
	{
		if ($user = $this->userExists($userID)) {
			// User exists
			return $user;
		} else {
			return false;
		}
	}
	
	public function countUsers()
	{
		if (!$this->hasValue('users')) {
			// Empty
			return 0;
		} else {
			$count = 0;
			foreach ($this->_attribs['users'] as $user) {
				$count += 1 + $user->plus;
			}
			
			return $count;
		}
	}
	
	
	/**
	 * does user with userID exist in array of users
	 * @returns false if does not exist, true if does
	 */
	public function userExists($userID)
	{
		if ($this->hasValue('users')) {
			// There are users
			foreach ($this->_attribs['users'] as $user) {
				if ($user->userID == $userID) {
					return $user;
				}
			}
		} else {		
			return false;
		}
	}
	
	/**
     * Create a hash (encrypt) of a plain text password.
     *
     * @param string $password Plain text user password to hash
     * @return string The hash string of the password
     */
    public function hashPassword($password) {
        return $this->getMapper()->hashPassword($password);
    }
 
    /**
     * Compare the plain text password with the $hashed password.
     *
     * @param string $password
     * @param string $hash The hashed password
     * @param int $user_id The user row ID
     * @return bool True if match, false if no match.
     */
    public function checkPassword($password, $hash, $user_id = '') {
		
       return $this->getMapper()->checkPassword($password, $hash, $user_id);
    }
 

}

