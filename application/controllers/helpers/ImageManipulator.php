<?php
class Application_Controller_Helper_ImageManipulator extends Zend_Controller_Action_Helper_Abstract
{

    var $image; 
	var $image_type;  
	 
	function load($filename) 
	{   
		$image_info = getimagesize($filename); 
		$this->image_type = $image_info[2]; 
		if( $this->image_type == IMAGETYPE_JPEG ) {   
			$this->image = imagecreatefromjpeg($filename); 
		} elseif( $this->image_type == IMAGETYPE_GIF ) {   
			$this->image = imagecreatefromgif($filename);
			$this->save($this->image); // Convert img to jpg
			unlink($filename); // Destroy old gif
		} elseif( $this->image_type == IMAGETYPE_PNG ) {   
			$this->image = imagecreatefrompng($filename); 
			$this->save($this->image); // Convert img to jpg
			unlink($filename); // Destroy old png
		} 
	} 
	
	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) 
	{   
		$filename = str_replace(array('.gif','.png'), '.jpg', $filename);
		
		if( $image_type == IMAGETYPE_JPEG ) { 
			imagejpeg($this->image,$filename,$compression); 
		} elseif( $image_type == IMAGETYPE_GIF ) {   
			imagegif($this->image,$filename); 
		} elseif( $image_type == IMAGETYPE_PNG ) { 			
			imagepng($this->image,$filename); 
		} 
		if( $permissions != null) {   
		chmod($filename,$permissions); 
		} 
	} 
	
	function output($image_type=IMAGETYPE_JPEG) 
	{   
		if( $image_type == IMAGETYPE_JPEG ) { 
			imagejpeg($this->image); 
		} elseif( $image_type == IMAGETYPE_GIF ) {   
			imagegif($this->image); 
		} elseif( $image_type == IMAGETYPE_PNG ) {  
			imagepng($this->image); 
		} 
	} 
	
	function getWidth() 
	{   
		return imagesx($this->image); 
	} 
	
	function getHeight() 
	{   
		return imagesy($this->image); 
	} 
	
	function getRatio()
	{
		return $this->getWidth()/$this->getHeight();
	}
	
	function resizeToHeight($height) 
	{   
		$ratio = $height / $this->getHeight(); 
		$width = $this->getWidth() * $ratio; 
		$this->resize($width,$height); 
	}   
	
	function resizeToWidth($width) 
	{ 
		$ratio = $width / $this->getWidth(); 
		$height = $this->getheight() * $ratio; 
		$this->resize($width,$height); 
	}   
	function scale($scale) 
	{ 
		$width = $this->getWidth() * $scale/100; 
		$height = $this->getheight() * $scale/100; 
		$this->resize($width,$height); 
	}   
	
	function resize($width, $height, $crop = false) 
	{ 
		if (!$crop) {
			$crop['x'] = 0;
			$crop['y'] = 0;
			$crop['fileHeight'] = $this->getHeight();
			$crop['fileWidth']  = $this->getWidth();
		}	
		
		$new_image = imagecreatetruecolor($width, $height);
		
		if( $this->image_type == IMAGETYPE_GIF || $this->image_type == IMAGETYPE_GIF ) { 

			$current_transparent = imagecolortransparent($this->image); 
			if($current_transparent != -1) { 
				$transparent_color = imagecolorsforindex($this->image, $current_transparent); 
				$current_transparent = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']); 
				imagefill($new_image, 0, 0, $current_transparent); 
				imagecolortransparent($new_image, $current_transparent); 
			} elseif( $this->image_type == IMAGETYPE_PNG) { 
				imagealphablending($new_image, false); 
				$color = imagecolorallocatealpha($new_image, 0, 0, 0, 127); 
				imagefill($new_image, 0, 0, $color); 
				imagesavealpha($new_image, true); 
			} 
		}
		
		imagecopyresampled($new_image, $this->image, 0, 0, $crop['x'], $crop['y'], $width, $height, $crop['fileWidth'], $crop['fileHeight']); 
		$this->image = $new_image;
	}
	

}