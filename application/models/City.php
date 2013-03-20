<?php

class Application_Model_City extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_CitiesMapper';
	protected $_attribs     = array('cityID' => '',
									'city' 	 => '',
									'state'  => '',
									'active' => '',
									'changedLocation' => false
									);
	protected $_primaryKey = 'cityID';
		
	
	public function __construct($resultRow = false) 
	{
		if ($resultRow) {
			$this->setAttribs($resultRow);
		}
	}
	
	public function save()
	{
		if ($this->_attribs['changedLocation']) {
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
