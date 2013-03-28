<?php

class Application_Model_Users extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_singleClass = 'Application_Model_User';
	
	protected $_attribs     = array('users'	=> '');
	
	
	public function addUser($resultRow)
	{
		$user = $this->_attribs['users'][] = new Application_Model_User($resultRow);
		return $user;
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
					return true;
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

