<?php

class Application_Model_Item extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_ParksMapper';
	protected $_attribs     = array('itemID' 			=> '',
								    'qty' 				=> '',
								    'itemName' 			=> '',
								    'itemDescription' 	=> '',
									);

	protected $_primaryKey = 'itemID';
	
	public function getItemName()
	{
		if ($this->qty > 1) {
			return $this->_attribs['itemName'] . 's';
		} else {
			return $this->_attribs['itemName'];
		}
	}
	
}
	
	
