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
		$matches = $findMatches->findmatches('parks',$options, $this->view->user, '30,0');
		
		$this->view->matches = $matches->sortByMatch(0, 30);

		$this->view->numMatches = $matches->totalRows;
		
		$sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo();
		
		$form = new Application_Form_General();
		$this->view->inputText = $form->text;
		$this->view->checkbox  = $form->checkbox;
	}


}

