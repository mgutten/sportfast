<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
       	/* fixed in bootstrap //$this->_helper->layout->setLayout('global/logout');*/
		//cancel standard white back
		$this->view->whiteBacking = false;	
		
				
    }


}

