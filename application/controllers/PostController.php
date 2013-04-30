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
		//$this->_redirect($lastURL);
	}


}

