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

		$user = $this->createUserClasses($results);
		
		return $user;	
	}
	
	public function createUserClasses($results) 
	{
		$users = array();
		
		foreach ($results as $result) {
			$user = new Application_Model_User();
			/*foreach ($user->getAttribs() as $attribute => $val) {
				$user->$attribute = $result->$attribute;
			}*/
				$user->setAttribs($result);
					 
			
			if(count($results) <= 1) {
				$users = $user;
				break;
			}
			
			$users[] = $user;
		}
		
		return $users;
	}
	
	/**
     * Find all of sports info for user (ie sports, types, positions, and availability
     *
     * @param string $password Plain text user password to hash
     * @return string The hash string of the password
     */
	public function getUserSportsInfo($userID, $modelClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->setIntegrityCheck(false);
		$select->from(array('s'  => 'sports'))
			   ->join(array('sp' => 'sport_positions'),
			   		  's.sportID = sp.sportID')
			   ->join(array('usp' => 'user_sport_positions'),
			   		  'usp.positionID = sp.positionID')
			   ->join(array('st' => 'sport_types'),
			   		  'st.sportID = s.sportID')
			   ->join(array('ust' => 'user_sport_types'),
			   		  'ust.typeID = st.typeID')
			   ->join(array('us' => 'user_sports'),
			   		  'us.sportID = s.sportID') 
			   ->join(array('usa' => 'user_sport_availabilities'),
			   		  'usa.sportID = s.sportID') 
			   ->where('usp.userID = ?', $userID);

		
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			$sport = $result->sport;
			
			$sportModel = $modelClass->getSport($sport);
			$sportModel->setAttribs($result);
			$sportModel->getType($result->typeName)->setAttribs($result);
			$sportModel->getPosition($result->positionName)->setAttribs($result);
			$sportModel->setAvailability($result->day, $result->hour)->setAttribs($result);
			
			
		}
			
		return $modelClass;

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