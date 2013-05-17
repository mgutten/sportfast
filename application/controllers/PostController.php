<?php

class PostController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
	
	public function ratingAction()
	{
		$post = $this->getRequest()->getPost();
		$type = $post['type'];
		
		$rating = new Application_Model_Rating();
		$rating->comment = $post['comment'];
		$rating->setDateHappenedCurrent();
		$rating->userID = $this->view->user->userID;
		
		if ($type == 'park') {
			// Is park
			$rating->setPark();
			$rating->quality = $post['rating'];
			$rating->parkID  = $post['typeID'];
		}
		
		$rating->save();
		
		$lastURL = $_SERVER['HTTP_REFERER'];
		$this->_redirect($lastURL);
	}
	
	public function messageAction()
	{
		$post = $this->getRequest()->getPost();
		
		$message = new Application_Model_Message();
		$message->message = $post['postMessage'];
		$message->type    = $post['messageType'];
		$message->messageGroupID = $post['messageGroupID'];
		$message->read    = '0';
		$message->sendingUserID = $this->view->user->userID;
		$message->receivingUserID = $post['receivingUserID'];
		$message->setCurrent('dateHappened');
		
		$message->save();
		
		$notification = new Application_Model_Notification();
		$notification->action = 'message';
		$notification->actingUserID = $this->view->user->userID;
		$notification->receivingUserID = $post['receivingUserID'];
		$notification->cityID = $this->view->user->city->cityID;
		
		$notification->save();
		
		$lastURL = $_SERVER['HTTP_REFERER'];
		$this->_redirect($lastURL);
		
	}
		


}

