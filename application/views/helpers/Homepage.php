<?php

class Application_View_Helper_Homepage 
{
	public $descriptionCount = 1;
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function homepage() 
	{
		//large images
		$output   = "<div id='homepage-large-img-holder'></div>
							<div id='homepage-large-img-container' class='absolute' holder='homepage-large-img-holder'>
							<img src='/images/homepage/large/soccer.jpg' class='homepage-large-img fade-current' id='homepage-large-img1' />
							<img src='/images/homepage/large/basketball.jpg' class='homepage-large-img fade-next' id='homepage-large-img2' />
							<img src='' class='homepage-large-img' id='homepage-large-img3' />
							<img src='' class='homepage-large-img' id='homepage-large-img4' />
							<div class='centered-body homepage-large-img-center-container'>
								<div id='homepage-large-img-dot-container'>
									<div class='homepage-large-img-dot homepage-large-img-dot-selected'></div>
									<div class='homepage-large-img-dot'></div>
									<div class='homepage-large-img-dot'></div>
									<div class='homepage-large-img-dot'></div>
								</div>
							</div>
						</div>";
						
		$textArray = array('image'	=> 'green_magnifying',
						   'header' => 'Find local pickup games, teams, and leagues.',
						   'text'   => "Create, find, and challenge other teams and players in your league.  Maybe you are looking for a 
						   				teammate to fill a gap, or just to get out and play a game you haven't played in years.  We are here for you.");
		
		$output   .= $this->homepageDescriptionContainer($textArray);
		
		$textArray = array('image'	=> '',
						   'header' => "See how you stack up against your area's best.",
						   'text'   => "Create, find, and challenge other teams and players in your league.  Maybe you are looking for a 
						   				teammate to fill a gap, or just to get out and play a game you haven't played in years.  We are here for you.");
										
		$output   .= $this->homepageDescriptionContainer($textArray);
		
		$textArray = array('image'	=> '',
						   'header' => "Organize your pre-existing games and teams.",
						   'text'   => "Create, find, and challenge other teams and players in your league.  Maybe you are looking for a 
						   				teammate to fill a gap, or just to get out and play a game you haven't played in years.  We are here for you.");
										
		$output   .= $this->homepageDescriptionContainer($textArray);
		
		$textArray = array('image'	=> '',
						   'header' => "Discover and rate local parks and venues.",
						   'text'   => "Create, find, and challenge other teams and players in your league.  Maybe you are looking for a 
						   				teammate to fill a gap, or just to get out and play a game you haven't played in years.  We are here for you.");
										
		$output   .= $this->homepageDescriptionContainer($textArray);		
		
		return $output;
	}
	
	public function homepageDescriptionContainer($textArray)
	{
		$output = "<a href='" . $this->_view->url(array('controller' => 'how',
														'action'	  => 'index')) . "' >
				   <div class='homepage-description-container white-back dropshadow pointer' id='homepage-description-container" . $this->descriptionCount . "'>
							<img src='/images/homepage/description/" . $textArray['image'] . ".jpg' id='homepage-description-img" . $this->descriptionCount . "' class='homepage-description-img pointer'/>
							<p class='center darkest homepage-description-header pointer'>" . $textArray['header'] . "</p>
							<p class='pointer'>" . $textArray['text'] . "
							</p>
				   </div></a>";
				   
		$this->descriptionCount++;
				   
		return $output;
	}
	
}