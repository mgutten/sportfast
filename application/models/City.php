<?php

class Application_Model_City extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_CitiesMapper';
	protected $_attribs     = array('cityID' => '',
									'city' 	 => '',
									'state'  => '',
									'active' => '',
									'changedLocation' => ''
									);
	protected $_primaryKey = 'cityID';
		
		
	public function save($loopSave = false)
	{
		if (!empty($this->_attribs['changedLocation'])) {
			// Location changed temporarily, do not save
			return;
		}
		return $this->getMapper()->save($this, $loopSave);
	}
	
	public function setCity($city) {
		$this->_attribs['city'] = ucwords($city);
		return $this;
	}
	
	public function setState($state) {
		$this->_attribs['state'] = ucwords($state);
		return $this;
	}
	
	public function getCityFromZipcode($zipcode)
	{
		return $this->getMapper()->getCityFromZipcode($zipcode, $this);
	}
	
	/**
	 * used to create fake users for testing
	 */
	public function getZipcodesWithin($latitude, $longitude)
	{
		return $this->getMapper()->getZipcodesWithin($latitude, $longitude);
	}
		
	
}
