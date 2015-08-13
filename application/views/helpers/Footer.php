<?php

class Application_View_Helper_Footer extends Zend_View_Helper_Abstract
{
	private $_view;
	
	public function setView(Zend_View_Interface $view) 
	{
		$this->_view = $view;
	}
	
	public function footer($links) 
	{
		$output = '<div class="footer-section-links medium">';
		$keys = array_keys($links);
		$count = 0;
		
		foreach($links as $link) {
										   
			if ($link['action'] == 'index') {
				$link['action'] = '';
			}
			$output .= '<div class="footer-section-url-container"><a href="/' . $link['controller'] . '/' . $link['action'] . '" class="footer-section-url">'
				     . $keys[$count]
					 . '</a>';
					 
			if ($count < count($links) - 1) {
				$output .= ' /&nbsp;';
			}
			$output .= '</div>';
			$count++;
		}
		
		$output .= '</div>';
		
		return $output;
	}
	

}
