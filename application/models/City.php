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
		
		
	public function save()
	{
		if (!empty($this->_attribs['changedLocation'])) {
			// Location changed temporarily, do not save
			return;
		}
		return $this->getMapper()->save($this);
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
		
	
}
