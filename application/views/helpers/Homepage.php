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
							<div id='homepage-large-img-container' class='absolute dropshadow' holder='homepage-large-img-holder'>
							<img src='/images/homepage/large/soccer.jpg' class='homepage-large-img fade-current' id='homepage-large-img1' />
							<img src='/images/homepage/large/basketball.jpg' class='homepage-large-img fade-next' id='homepage-large-img2' />
							<img src='/images/homepage/large/court.jpg' class='homepage-large-img' id='homepage-large-img3' />
							<img src='/images/homepage/large/league.jpg' class='homepage-large-img' id='homepage-large-img4' />
							<div class='centered-body homepage-large-img-center-container'>
								<div id='homepage-large-img-dot-container'>
									<div class='homepage-large-img-dot homepage-large-img-dot-selected'></div>
									<div class='homepage-large-img-dot'></div>
									<div class='homepage-large-img-dot'></div>
									<div class='homepage-large-img-dot'></div>
								</div>
								<a href='/how' id='homepage-learn'></a>
							</div>
							
						</div>";
						
		$textArray = array('image'	=> 'green_magnifying',
						   'header' => 'Find local pickup games, teams, and leagues.',
						   'text'   => "Sportfast doesn't just give you a list and expect you to find a game or team.  Our system actively matches you
						   				with other players in your area based on your age, skill, and availability, giving you the most competitive games possible.");
		
		$output   .= $this->homepageDescriptionContainer($textArray);
		
		$textArray = array('image'	=> 'ratings',
						   'header' => "Get rated and track your improvement.",
						   'text'   => "Ever wonder how you stack up compared to other players in your area?  With Sportfast, you can easily track your ratings 
						   				and pinpoint your strengths and weaknesses for all of your favorite sports.");
										
		$output   .= $this->homepageDescriptionContainer($textArray);
		
		$textArray = array('image'	=> 'location',
						   'header' => "Discover local parks and venues.",
						   'text'   => "We take the time to carefully find and review each park, school, and gym to bring you the best quality courts and fields your
						   				city has to offer.  Users are constantly rating these parks so you can quickly find the highest-rated spots.");
										
		$output   .= $this->homepageDescriptionContainer($textArray);
		
		$textArray = array('image'	=> 'clipboard',
						   'header' => "Organize your pre-existing games and teams.",
						   'text'   => "Sportfast makes managing your weekly pickup game or league team easy.  Send automatic reminders for upcoming games.  See who's going.  Find more players.
						   				Track game stats.  It's all simple and easy with Sportfast.");
										
		$output   .= $this->homepageDescriptionContainer($textArray);
		
		
				
		return $output;
	}
	
	public function homepageDescriptionContainer($textArray)
	{
		$output = "<a href='" . $this->_view->url(array('controller' => 'how',
														   'action'		=> 'index')) . "' class='homepage-description-container white-back dropshadow pointer' id='homepage-description-container" . $this->descriptionCount . "'>
							<img src='/images/homepage/description/" . $textArray['image'] . ".png' id='homepage-description-img" . $this->descriptionCount . "' class='homepage-description-img'/>
							<p class='center darkest homepage-description-header bold'>" 
							. $textArray['header'] 
							. "</p>
							<p class='clear darkest justify'>" 
							. $textArray['text'] 
							. "<img src='/images/global/body/single_arrow.png' class='right margin-top indent'/><span class='medium right'>more</span></p>
							
				   </a>";
				   
		$this->descriptionCount++;
				   
		return $output;
	}
	
}