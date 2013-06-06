<?php

class Application_View_Helper_Faq
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function faq()
	{
		return $this;
	}
	
	public function create($question, $answer)
	{
		$output  = "<div class='clear width-100 margin-top'>";
		$output .=		"<p class='white-background clear heavy faq-question animate-darker medium pointer'>" . $question . "</p>";
		$output .=		"<div class='clear margin-top width-100 faq-answer-container'>
							<p class='darkest clear faq-answer hidden light-back'>" . $answer . "</p>
						</div>";
		$output .= "</div>";
		
		return $output;
		
	}
	


}