<?php

class Application_View_Helper_Ratings
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function ratings()
	{
		return $this;
	}
	
	public function loopParkRatings($ratings)
	{
		$output = '';
		foreach ($ratings as $rating) {
			$user = $rating->user;
			$width = $rating->getStarWidth('quality') . '%';
			
			$output .= "<div class='rating-container width-100 clear'>";
			$output .= 		"<a href='/users/" . $user->userID . "' class='left'><img src='" . $user->getProfilePic('small') . "' class='left' /></a>";
			$output .=		"<div class='left rating-right'>";
			$output .=			"<p class='smaller-text left light'>" . $user->shortName . " played " . strtolower($rating->sport) . " here...</p>";
			$output .=			"<p class='smaller-text right light'>" . $rating->getTimeFromNow() . "</p>";
			$output .=			$this->_view->ratingstar('small', $width, $this->_view->currentURI);
			$output .=			"<p class='clear larger-margin-top medium'>" . $rating->getQuotedComment() . "</p>";
			$output .=		"</div>";
			$output .= "</div>";
		}

		
		return $output;
	}
			
	

}
