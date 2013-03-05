<?php

class Application_Model_Users extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_singleClass = 'Application_Model_User';
	
	/*
	public function save(Application_Model_DbTable_Users $guestbook)
	{
		$data = array(
			'email'		=> $guestbook->getEmail(),
			'comment'	=> $guestbook->getComment(),
			'created'	=> date('Y-m-d H:i:s'),
			);
			
		if (($id = $guestbook->getId()) === null) {
			unset($data['id']);
			$this->getDbTable()->insert($data);
		} else {
			$this->getDbTable->update($data, array('id = ?' => $id));
		}
	}
	*/
	
	
	public function __construct(array $options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		}
	}
	
	public function find($id) 
	{
		$result = $this->getMapper()->find($id, new $this->_singleClass());
		return $result;
	}
	
	public function fetchAll() 
	{
		return $this->getMapper()->fetchAll($this->_singleClass);
	}
	
	public function getUserBy($column, $value)
	{
		return $this->getMapper()->getUserBy($column, $value);
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

