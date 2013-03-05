<?php

class Application_Model_City extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_CitiesMapper';
	protected $_attribs     = array('cityID' => '',
									'city' 	 => '',
									'state'  => '',
									'active' => ''
									);
	protected $_primaryKey = 'cityID';
		
	
	public function __construct($resultRow = false) 
	{
		if ($resultRow) {
			$this->setAllAttribs($resultRow);
		}
	}
	
	public function setAllAttribs($resultRow)
	{
		foreach ($this->getAttribs() as $key => $attrib) {
				$this->$attrib = $resultRow->$attrib;
			}
	}
	
	public function getCityFromZipcode($zipcode)
	{
		return $this->getMapper()->getCityFromZipcode($zipcode, $this);
	}
	
	
}
