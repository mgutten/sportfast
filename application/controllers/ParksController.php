<?php

class ParksController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->view->narrowColumn = 'left';
		
		$uri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
		$this->view->currentURI = rtrim($uri,'/');
		
		$parkID = $this->getRequest()->getParam('id');
        $park = new Application_Model_Park();
		$park->getParkByID($parkID);
		
		$this->view->park = $park;
		$this->view->parkID = $parkID;
		
		
		$this->view->parkGames = $parkGames = $park->getParkGames();
		
		$this->view->parkRatings = $parkRatings = $park->getParkRatings();
		$this->view->numRatings  = $parkRatings->countRatings();
		$this->view->parkRatingWidth = $parkRatings->getStarWidth('quality') . '%';
		
		$defaultText = 'No ratings have been given.';
		if ($parkRatings->hasValue('ratings')) {
			// There are ratings
			$randomRating = $parkRatings->getRandomRating();
			if ($randomRating) {
				$ratingText = "\"" . $randomRating->comment . "\"";
			} else {
				$ratingText = $defaultText;
			}
		} else {
			$ratingText = $defaultText;
		}
		
		$this->view->ratingText = $ratingText;
		
		$this->view->parkLocation = $park->location;
		
		$parkStash = $park->getParkStash();

		if ($parkStash->parkStashID) {
			// Park stash exists
			$this->view->stashExists = true;
			$this->view->stashDescription = 'Certain parks have "stashes" where we have placed sports equipment for your use, free of charge.';
		} else {
			$this->view->stashDescription = 'No stash available.';
		}
		if ($park->getTotalBasketball() > 0) {
			// Basketball courts present
			$parkStash->addSport('basketball');
		}
		if ($park->tennis > 0) {
			// Tennis courts present
			$parkStash->addSport('tennis');
		}
		
		
		$this->view->parkStash = $parkStash;
		
		$this->view->nearbyParks = $park->getNearbyParks();
		
    }		
	
	public function ratingsAction()
    {
		$this->view->narrowColumn = 'right';
		$uri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
		$this->view->currentURI = rtrim($uri,'/');
		
		$parkID = $this->getRequest()->getParam('id');
        $park = new Application_Model_Park();
		$park->getParkByID($parkID);
		
		$this->view->park = $park;
		$this->view->parkID = $parkID;
		
		$this->view->ratings = $ratings = $park->getParkRatings();
		$this->view->numRatings = $ratings->countRatings();
		$this->view->ratingWidth = $ratings->getStarWidth('quality') . '%';
	}
	
	public function stashAction()
    {
		$this->view->narrowColumn = 'right';
		$uri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
		$this->view->currentURI = rtrim($uri,'/');
		
		$parkID = $this->getRequest()->getParam('id');
        $park = new Application_Model_Park();
		$park->getParkByID($parkID);
		
		$stash = $park->getParkStash();

		$this->view->stash = $stash;
		
		$this->view->stashLocation = $stash->location;
		
		$this->view->park = $park;
		$this->view->parkID = $parkID;
	}


}

