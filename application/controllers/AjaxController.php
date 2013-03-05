<?php

class AjaxController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
	{
		// Test if is AJAX call
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$request = $this->getRequest();
		
		if (!$request->isXmlHttpRequest()) {
			// Not an ajax call
			$this->_redirect('/');
		}
	}


    public function indexAction()
    {
        // action body
    }
	
	public function createBasicDropdownAction()
	{
		
		$request = $this->getRequest();
		$post	  = $request->getPost();
		$id       = $post['id'];
		$selected = $post['selected'];
		$options  = $post['options'];
		$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		echo $dropdown->dropdown($id, $options);
		
	}
	
	public function uploadTempPictureAction()
	{
		$targetPath   = PUBLIC_PATH . "/images/tmp/profile/pic/";
		$pathInfo     = pathinfo(basename($_FILES['profilePic']['name']));
		$targetPath  .= uniqid() . basename($_FILES['profilePic']['name']); 
		//$targetPath  .= uniqid() . $pathInfo['basename']; 
		
		$imgSize = getimagesize($_FILES['profilePic']['tmp_name']);
		
		if (empty($imgSize)) {
			// File is not an image
			echo 'errorFormat';
			return;
		}
		
		
		if(move_uploaded_file($_FILES['profilePic']['tmp_name'], $targetPath)) {
			// File uploaded

			// Resize image
			$image = Zend_Controller_Action_HelperBroker::getStaticHelper('ImageManipulator');
			
			$image->load($targetPath);
			if ($image->getRatio() >= 1.26) {
				// image is too wide
				// 400 and 200 are from signup-import-alert-img ratio
				$image->resizeToWidth(450);
			} elseif ($image->getRatio() < 1.26) {
				// image is too tall
				$image->resizeToHeight(360);
			}
			$image->save($targetPath);
				
			// ALTER TARGETPATH FOR DEVELOPMENT
			$targetPath = str_replace(PUBLIC_PATH,'',$targetPath);
			$targetPath = str_replace(array('.gif','.png'), '.jpg', $targetPath);

			echo $targetPath;
		} else{
			echo "errorUpload";
		}
		
	}



}

