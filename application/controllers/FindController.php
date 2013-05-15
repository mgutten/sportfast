<?php

class FindController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->_forward('games');
    }
	
	
	public function gamesAction()
    {
		$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		$this->view->lookingFor = $dropdown->dropdown('looking-for', array('Games','Teams','Players','Parks'), 'Games');
		
		//$this->view->sports = $sports = $this->view->user->getSportNames();
		$this->view->types = $types  = $this->view->user->getSportTypes();
		$this->view->skill = $skill = array('lower' => 64,
								   		    'upper' => 100);
		$this->view->age   = $age   = array('lower' => 17,
								   			'upper' => 70);
		$this->view->time  = $time  = 'user';
		
		$options = array();
		$options['sports'] = $types;
		$options['skill']  = $skill;
		$options['age']    = $age;
		$options['time']   = $time;

		
		$findMatches = Zend_Controller_Action_HelperBroker::getStaticHelper('FindMatches');
		$matches = $findMatches->findmatches('games',$options, $this->view->user, '30,0');
		
		$this->view->matches = $matches->sortByMatch(0, 30);

		$this->view->numMatches = $matches->totalRows;
		
		$sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo();
		
		$form = new Application_Form_General();
		$this->view->inputText = $form->text;
		$this->view->checkbox  = $form->checkbox;
		
		$session = new Zend_Session_Namespace('findGames');
		
		if (empty($session->visited)) {
			// Has not visited this page in this session
			$this->view->topAlert = true;
			$session->visited = true;
		}
		
		
    }
	
	public function teamsAction()
    {
		$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		$this->view->lookingFor = $dropdown->dropdown('looking-for', array('Games','Teams','Players','Parks'), 'Teams');
		
		//$this->view->sports = $sports = $this->view->user->getSportNames();
		$this->view->types = $types  = $this->view->user->getSportTypes();
		$this->view->skill = $skill = array('lower' => 64,
								   		    'upper' => 100);
		$this->view->age   = $age   = array('lower' => 17,
								   			'upper' => 70);
		$this->view->time  = $time  = 'user';
		
		$options = array();
		$options['sports'] = $types;
		$options['skill']  = $skill;
		$options['age']    = $age;
		$options['time']   = $time;

		
		$findMatches = Zend_Controller_Action_HelperBroker::getStaticHelper('FindMatches');
		$matches = $findMatches->findmatches('teams',$options, $this->view->user, '30,0');
		
		$this->view->matches = $matches->sortByMatch(0, 30);

		$this->view->numMatches = $matches->totalRows;
		
		$sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo();
		
		$form = new Application_Form_General();
		$this->view->inputText = $form->text;
		$this->view->checkbox  = $form->checkbox;
    }
	
	public function playersAction()
    {
		$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		$this->view->lookingFor = $dropdown->dropdown('looking-for', array('Games','Teams','Players','Parks'), 'Players');
		
		//$this->view->sports = $sports = $this->view->user->getSportNames();
		$this->view->types = $types  = $this->view->user->getSportTypes();
		$this->view->skill = $skill = array('lower' => 64,
								   		    'upper' => 100);
		$this->view->age   = $age   = array('lower' => 17,
								   			'upper' => 70);
		$this->view->time  = $time  = 'user';
		
		$options = array();
		$options['sports'] = array('basketball' => false);
		$options['skill']  = $skill;
		$options['age']    = $age;
		$options['time']   = $time;
		$options['order'] = 'activity';

		
		$findMatches = Zend_Controller_Action_HelperBroker::getStaticHelper('FindMatches');
		$matches = $findMatches->findmatches('players',$options, $this->view->user, '30,0');
		
		//$this->view->matches = $matches->sortByMatch(0, 30);
		
		$this->view->matches = $matches->getAll();

		$this->view->numMatches = $matches->totalRows;
		
		$sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo();
		
		$form = new Application_Form_General();
		$this->view->inputText = $form->text;
		$this->view->checkbox  = $form->checkbox;
    }
	
	public function parksAction()
    {
		$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		$this->view->lookingFor = $dropdown->dropdown('looking-for', array('Games','Teams','Players','Parks'), 'Parks');
		
		$this->view->types = $types  = $this->view->user->getSportTypes();
		
		$options = array();
		$options['courts'] = '';
		$options['stash']  = '';
		$options['type']   = '';
		$options['order']  = 'distance';

		
		$findMatches = Zend_Controller_Action_HelperBroker::getStaticHelper('FindMatches');
		$matches = $findMatches->findmatches('parks',$options, $this->view->user, '30,0');
		
		$this->view->matches = $matches->sortByMatch(0, 30);

		$this->view->numMatches = $matches->totalRows;
		
		$sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo();
		$this->view->courts = array('Basketball', 'Volleyball','Tennis','Field');
		
		$form = new Application_Form_General();
		$this->view->inputText = $form->text;
		$this->view->checkbox  = $form->checkbox;
	}
	
	public function searchAction()
	{
		$session = new Zend_Session_Namespace('searchTerm');
		
		$searchTerm = $session->searchTerm;
		
		$cityID  = $this->view->user->city->cityID;
		
		$search  = new Application_Model_Search();
		$results = $search->getSearchResults($searchTerm, $cityID);
		
		$this->view->results = $results;
		$this->view->searchTerm = $searchTerm;
		
		$form = new Application_Form_HeaderSearch();
		$form->setName('headerSearchMain');
		$form->headerSearchBar->setAttribs(array('class' => 'dropshadow'));
		
		$this->view->form = $form;
	
	}
	
	public function searchTermAction()
	{
		$post = $this->getRequest()->getPost();
		if (empty($post['headerSearchBar'])) {
			// No search term was used, redirect to home page
			$this->_redirect('/');
		}
		
		$session = new Zend_Session_Namespace('searchTerm');
		$session->searchTerm = $post['headerSearchBar'];
		
		$this->_redirect('/find/search');
	}


}

