<?php

class SignupController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->view->narrowColumn = 'right';
		$signupSession = new Zend_Session_Namespace('signup');

		
		// Create general signup form
		$form = new Application_Form_Signup();
		if (!empty($signupSession)) {
			// Session not empty, populate form
			foreach($signupSession as $key => $val) {
				$form->populate(array($key => $val));
			}
		}
		
		$messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $messages = $messenger->getMessages('signupError');
		if (!empty($messages[0])) {
			// There were errors sent from validation
			$this->view->errors = $messages[0];
		}
			
		
        $this->view->form = $form;
		
		// Create hidden element form
		$sportForm = new Application_Form_SignupSportForm();
		$this->view->signupSportForm = $sportForm;
		
		// Retrieve all available sports, positions, and types
		$sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo();
		
		/* testing retrieving all of user's information 
		$user = new Application_Model_User();
		$user->userID = '11';
		$user->getUserSportsInfo();
		echo $user->getSport('football')->getPosition('quarterback')->positionAbbreviation;
		*/
		
	
		
    }
	
	public function testAction()
	{
		/* HOW TO CREATE A DROPDOWN */
        $dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		$this->view->dropdown = $dropdown->dropdown('cat',array(array('text'  => 'Basketball',
																				   'image' => '/images/global/sports/icons/small/basketball.png',
																				   'color' => 'medium'),
																			 'Football'));
																			 
		$this->view->dropdown2 = $dropdown->dropdown('meow',array(array('text'  => 'Basketball',
																				   'image' => '/images/global/sports/icons/small/basketball.png',
																				   'color' => 'medium'),
																			 'Football',
																			 'Tennis',
																			 'Any'));
																			 
		$typeModel = new Application_Model_SportPosition();
		$typeModel->positionAbbreviation = 'WR';
		$typeModel->sportID  = '2';
		$typeModel->save();
	}
	
	public function validateAction()
	{
		
		$fail = false;
		$form = new Application_Form_Signup();
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			$post = $request->getPost();

			if (!$form->isValid($post)) {
				// Form failed validation, redirect back with error
				Zend_Session::namespaceUnset('signup');
				$signupSession = new Zend_Session_Namespace('signup');
				foreach ($request->getPost() as $key => $val) {
					$signupSession->$key = trim($val);
				}
				
				$errors = array();
				foreach ($form->getMessages() as $section => $errorType) {
					foreach ($errorType as $val) {
						$errors[$section] = str_replace('Value', ucwords($section), $val);
					}
				}
				$this->_helper->FlashMessenger->addMessage($errors, 'signupError');
				
				$this->_redirect('/signup');
				return;
			} else {
				// Form is valid
				$user 	  = new Application_Model_User();
				$userInfo = $user->getAttribs();
				
				// Get user location
				if ($post['noAddress']) {
					// No address provided
					$location = new Application_Model_Location();
					$location->getLocationByZipcode($post['zipcode']);
					$user->userLocation = $location;
				} else {
					// userLocation is set, user latitude and longitude is stored in userLocation
					$location = new Application_Model_Location();
					$location->location = $post['userLocation'];
					$user->userLocation = $location;
				}

				
				// Set city obj for user
				$user->cityID = $user->getCity()
								     ->getCityFromZipcode($post['zipcode'])
									 ->cityID;
				
				// Set verifyHash for user
				$user->verifyHash = md5($_POST['email']);
				
				// Set lastRead to curdate
				$user->setLastReadCurrent();
				
				// Set joined
				$user->setCurrent('joined');
				
				// Convert dob inputs to db format
				$post['dobYear'] = ($post['dobYear'] < date('y') ? '20' : '19') . $post['dobYear'];
				$post['dob'] = $post['dobYear'] . '-' . $post['dobMonth'] . '-' . $post['dobDay'];
				
				foreach ($post as $key => $val) {
					// Update all of User's attribs with appropriate post data
					if ($key == 'signupPassword') {
						// Hash password
						$user->password = $user->hashPassword($val);
						continue;
					}
					
					if ($key == 'userLocation') {
						// Already set location above
						continue;
					}
					
					if ($key == 'email') {
						$key = 'username';
					}
					if (isset($userInfo[$key])) {
						// Key is in userInfo array
						$user->$key = trim(strtolower($val));
					}
				}
				
				$sports = new Application_Model_Sports();
				$sportsArray = $sports->getAllSportsInfo();
				
				foreach ($sportsArray as $sport => $section) {
					if ($post[$sport] !== 'true') {
						// Sport was not selected
						continue;
					}
					
					$sportModel = $user->getSport($sport);
					
					$sportModel->often = $post[$sport . 'Often'];
					$sportModel->sport = $sport;
					
					// Convert rating from slider (0-6) to meaningful rating (64-100)
					$sportModel->skillInitial = $sportModel->convertSliderToRating($post[$sport . 'Rating']); 
					$sportModel->skillCurrent = $sportModel->skillInitial;
					
					$sportModel->sportsmanship = 80;
					$sportModel->attendance	   = 100;
					
					$formats = explode(',', $post[$sport . 'What']);
					
					foreach	($formats as $format) {
						// Loop through and create user format selection (e.g. Pickup, League, Weekend Tournament)
						 $formatModel = $sportModel->getFormat($format);
						 $formatModel->format = strtolower($format);

					}

					
					if (!empty($post[$sport . 'Type'])) {
						// Type is set
						$types = explode(',', $post[$sport . 'Type']);
						$typeNames = array();
						foreach ($types as $type) {	
							// Loop through types and create models
							$type = strtolower($type);
							if (!empty($sportsArray[$sport]['type'][$type])) {
								// $type is typeName
								$typeNames[] = $type;
								//$typeModel   = $sportModel->getType($type);
								//$typeModel->typeName = $type;
							} else {
								// $type is typeSuffix
								$typeSuffixes[] = $type;
							}
						}
						
						foreach ($typeNames as $typeName) {
							foreach ($typeSuffixes as $typeSuffix) {
								// Create new type model foreach typeName/typeSuffix combo
								$typeModel = $sportModel->getType($typeName);
								$typeModel->typeName   = $typeName;
								$typeModel->typeSuffix = $typeSuffix;
							}
						}
					} else {
						// No type set, create type for base type of "pickup"
						$typeModel = $sportModel->getType('pickup');
						$typeModel->typeName = 'pickup';
					}
					
					
					if (!empty($post[$sport . 'Position'])) {
						// Position is set
						$positions = explode(',', $post[$sport . 'Position']);
						foreach ($positions as $position) {	
							// Loop through types and create models
							$positionModel = $sportModel->getPosition($position);
							$positionModel->positionAbbreviation = $position;
						}

					} else {
						// No position set, create base position "null" for sportID
						$positionModel = $sportModel->getPosition('null');
					}
					
					
					for ($i = 0; $i < 6; $i++) {
						if (empty($post[$sport . 'Availability' . $i])) {
							// Day has no availabilities saved
							continue;
						}
						$hours = explode(',', $post[$sport . 'Availability' . $i]);
												
						foreach ($hours as $hour) {
							$availabilityModel = $sportModel->setAvailability($i, $hour);
							$availabilityModel->day  = $i;
							$availabilityModel->hour = $hour;
						}
						
					}
					
				}
				
				$user->save(true);
				
				$subject  = 'Sportfast Account Verification';
				$message  = '<html>
								<head>
								</head>
								<body>
									Thank you for joining the Sportfast team!  You are almost done.  To verify your account, please click on the link below.<br>
						     		<a href="sportfast.com/signup/verify/' . $user->verifyHash . '"> CLICK HERE TO VERIFY </a>
							    </body>
						     </html>';
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";	 
				
				
				if(mail($user->username, $subject, $message, $headers)) {
					echo 'An email verification has been sent to ' . $user->username . '.';
				} else {
					echo 'An error occured.  Email could not be sent to ' . $user->username . '.';
				}
				
			}
		}
		
		
		if (!empty($post['fileName'])) {
			// User has uploaded an image
			
			$userID = $user->userID;
			
			// Save profile pic to permanent location
			//$src = PUBLIC_PATH . $_POST['fileName'];
			$fileInfo = array();
			$fileInfo['src']   = $_POST['fileName'];
			$fileInfo['fileX'] = $_POST['fileX'];
			$fileInfo['fileY'] = $_POST['fileY'];
			$fileInfo['fileHeight'] = $_POST['fileHeight'];
			$fileInfo['fileWidth'] = $_POST['fileWidth'];
			
			$images = Zend_Controller_Action_HelperBroker::getStaticHelper('CreateImages');

			$images->createimages($fileInfo, $userID);
		}
		
	}
	
	public function verifyAction()
	{
		$verifyHash = $this->getRequest()->getParam('verifyHash');
		
		$user = new Application_Model_User();
		
		$verified = $user->verify($verifyHash);
		
		
		if ($verified) {
			// Success
			$auth = Zend_Auth::getInstance();
			
			// if success, $verified stores userID
			$user->getUserBy('u.userID',$verified);
			$user->login();
			
			$auth->getStorage()->write($user);
			
			$session = new Zend_Session_Namespace('first_visit');
			$session->firstVisit = true;
			
			$notification = new Application_Model_Notification();
			$notification->receivingUserID = $user->userID;
			$notification->action = 'message';
			$notification->details = 'sportfast';
			$notification->cityID = $user->city->cityID;
			$notification->save();
			
			$messages = new Application_Model_Messages();
			$messageGroupID = $messages->messageGroupExists($user->userID, 0);
			
			$message = new Application_Model_Message();
			$message->messageGroupID = $messageGroupID;
			$message->sendingUserID = 0;
			$message->receivingUserID = $user->userID;
			$message->message = file_get_contents(PUBLIC_PATH . '/txt/welcome.txt');
			
			$message->save();
			
			
			$this->_redirect('/');
		} else {
			// Failure
			$this->view->narrowColumn = false;
		}
	}


}


