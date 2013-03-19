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
	
	/**
	 * reset user's lastRead column of db to current time (ie after click on notifications button)
	 */
	public function resetNotificationsAction()
	{
		
		$user = $this->view->user;
		
		$user->setLastReadCurrent()
			 ->save(false);
			 
		$user->notifications->moveUnreadToRead();
		
		
	}
		
	/** 
	 * create and return full html of dropdown
	 * @params(id 		=> what is the id of the dropdown,
	 *		   selected => which option is selected first,
	 *		   options  => array of options)
	 * @return html version of dropdown
	 */
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
	
	/**
	 * upload temp picture for use in previews, etc
	 * @params (profilePic => input type file)
	 * @return (type of error if error OR path to temp img (str))
	 */
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

	
	/**
	 * get and return matches based on user's request/info
	 * @params (profilePic => input type file)
	 * @return (type of error if error OR path to temp img (str))
	 */
	public function getMatchesAction()
	{
		$post = $this->getRequest()->getPost();
		$matches = new Application_Model_Matches();
		
		if (in_array('games',$post['types'])) {
			// Games are selected
			$options = array();
			if (!empty($post['sports'])) {
				// Sports is not empty
				$sportStr  = implode("','",$post['sports']);
				$options[] = "g.sport IN ('" . $sportStr  . "')";
			}
			$games = new Application_Model_Games();
			$games->findUserGames($this->view->user, $options);
			$matches->addMatches($games->games);
		}
		if (in_array('teams',$post['types'])) {
			// Teams are selected
			$options = array();
			if (!empty($post['sports'])) {
				// Sports is not empty
				$sportStr  = implode("','",$post['sports']);
				$options[] = "t.sport IN ('" . $sportStr  . "')";
			}
			$teams = new Application_Model_Teams();
			$teams->findUserTeams($this->view->user, $options);
			$matches->addMatches($teams->teams);
		}
		
		$matches->sortByMatch();
		$this->view->matches = $matches->matches;
		
		$output = array();
		$memberHomepage = $this->view->getHelper('memberHomepage');
		$output[0] = $memberHomepage->buildFindBody();
		
		if (isset($matches->matches[0])) {
			// Matches exist
			foreach ($matches->matches as $match) {
				if (get_class($match) == 'Application_Model_Game') {
					// Get latitude and longitude
					$location = $match->getPark()->getLocation();
					$output[1][] = array($location->getLatitude(), $location->getLongitude());
				}
				
			}
		} else {
			$output[1][] = '';
		}
		
		echo json_encode($output);
		
		return;
		$jsonArray = array();
		
		foreach ($matches->matches as $match) {
			if (get_class($match) == 'Application_Model_Game') {
				// Get latitude and longitude
				$match->getPark()->getLocation()->parseLocation();
			}
			$jsonArray[] = $match->jsonSerialize();
		}
		
		echo json_encode($jsonArray);
		
	}
	
	/**
	 * get either new or old newsfeed data depending on $_POST['oldOrNew'] var
	 */
	public function getNewNewsfeedAction()
	{
		$newsfeed = new Application_Model_Notifications();
		$memberHomepage = $this->view->getHelper('memberHomepage');
		
		$request = $this->getRequest();
		if ($request->getPost('oldOrNew') == 'new') {
			// Get new notifications
			$newsfeed->getNewsfeed($this->view->user->city->cityID, true);
		} else {
			// Get old notifications
			$numNewsfeeds = $request->getPost('numNewsfeeds');
			$newsfeed->getNewsfeed($this->view->user->city->cityID, false, $numNewsfeeds . ',10'); 
		}
		$jsonArray = array();
		foreach ($newsfeed->read as $notification) {
			$jsonArray[] = $memberHomepage->createNotification($notification);
		}
		
		echo json_encode($jsonArray);
	}
	
	/**
	 * get city and state from db
	 */
	public function getCityStateAction()
	{
		$zipcode = $this->getRequest()->getPost('zipcode');
		
		$city = new Application_Model_City();
		$city->getCityFromZipcode($zipcode);
		
		echo ucwords($city->city) . ', ' . strtoupper($city->state);
	}


}

