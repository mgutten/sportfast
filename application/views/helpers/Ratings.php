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
	
	public function loopUserRatings($ratings)
	{
		$output = '';
		$availableRatings = new Application_Model_RatingsMapper();
		$availableSkillRatings = $availableRatings->getAvailableRatings('user', 'skill');
		$availableSportsmanshipRatings = $availableRatings->getAvailableRatings('user', 'sportsmanship');
		
		foreach ($ratings as $rating) {
			$width = $rating->getStarWidth('quality') . '%';
			
			$class = '';
			
			if ($rating->date->format('U') > $this->_view->lastRating) {
				// New rating
				$class = 'light-back';
			}

			$output .= "<div class='rating-container width-100 clear " . $class . "'>";
			$output .= 		"<img src='/images/users/profile/pic/small/default.jpg' class='left' />";
			$output .=		"<div class='left rating-right'>";
			$output .=			"<p class='smaller-text left light'>Anonymous rated...</p>";
			$output .=			"<p class='smaller-text right light'>" . $rating->getTimeFromNow() . "</p>";
			//$output .=			$this->_view->ratingstar('small', $width, $this->_view->currentURI);
			$output .=			"<p class='clear dark heavy larger-text' tooltip='Anonymous rated this as your best skill.'>" . ucwords($rating->skiller) . "</p>";
			$output .=			$this->_view->ratingbar($rating->skillRatingName, $availableSkillRatings, 'skill');
			$output .=			$this->_view->ratingbar($rating->sportsmanshipRatingName, $availableSportsmanshipRatings, 'sportsmanship');
			$output .=			"<p class='clear larger-margin-top medium'>" . $rating->getQuotedComment() . "</p>";
			$output .=			($this->_view->isUser ? "<div class='clear-right pointer flag-incorrect' userRatingID='" . $rating->userRatingID . "'><p class='light clear-right smaller-text'>flag as incorrect</p><img src='/images/global/flag.png' class='right'/></div>" : '');
			$output .=		"</div>";
			$output .= "</div>";
		}

		
		return $output;
	}
			
	

}
