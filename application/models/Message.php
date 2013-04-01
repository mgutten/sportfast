<?php

class Application_Model_Message extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_MessagesMapper';
	
	protected $_attribs     = array('teamMessageID' => '',
									'teamID'		=> '',
									'userID'		=> '',
									'message'		=> '',
									'dateHappened'  => '',
									'userName'		=> ''
									);
									
	protected $_primaryKey = 'teamMessageID';
	
}
