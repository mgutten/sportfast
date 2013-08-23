<?php

class Application_Model_Admin extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_AdminMapper';
	protected $_attribs     = array('adminID' 		=> '',
								    'username' 		=> '',
									'password'		=> '');
									
	public function login ($username, $password)
	{
		$storedPassword = $this->getMapper()->getAdminByUsername($username);
		
		return $this->checkPassword($password, $storedPassword, $this->adminID);
	}
	
	
	/**
     * Create a hash (encrypt) of a plain text password.
     *
     * @param string $password Plain text user password to hash
     * @return string The hash string of the password
     */
    public function hashPassword($password) {
        return $this->hasher()->HashPassword($password);
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
        // Check if we are still using regular MD5 (32 chars)
		
        if (strlen($hash) <= 32) {
            $check = ($hash == md5($password));
            if ($check && $user_id) {
                // Rehash using new PHPass-generated hash
                $this->setPassword($password, $user_id);
                $hash = $this->hashPassword($password);
            }
        }
 
        $check = $this->hasher()->CheckPassword($password, $hash);
 		
        return $check;
    }
  
    /**
     * Checks for Webjawns_PasswordHash in registry. If not present, creates PHPass object.
     *
     * @uses Zend_Registry
     * @uses Webjawns_PasswordHash
     *
     * @return Webjawns_PasswordHash PHPass
     */
    public function hasher() {
	
        if (!Zend_Registry::isRegistered('my_hasher')) {
            Zend_Registry::set('my_hasher', new My_Auth_PasswordHash(8, false));
        }
        return Zend_Registry::get('my_hasher');
    }
	
}
		
