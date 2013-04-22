<?php



class Application_Controller_Helper_CreateImages extends Zend_Controller_Action_Helper_Abstract

{

    public function createimages($fileInfo, $userID)
    {
		
        $src = PUBLIC_PATH . $fileInfo['src'];
		
		$image = Zend_Controller_Action_HelperBroker::getStaticHelper('ImageManipulator');
		$image->load($src);
		/* Base profile img size is 199 x 160 */
		$image->resize('199','160', array('x' => $fileInfo['fileX'],
										  'y' => $fileInfo['fileY'],
										  'fileHeight' => $fileInfo['fileHeight'],
										  'fileWidth'  => $fileInfo['fileWidth']));
		
		// Large img															   
		$image->save(PUBLIC_PATH . '/images/users/profile/pic/large/' . $userID . '.jpg');
		unlink($src); // Delete tmp img
		$image->scale(60); // Medium img
		$image->save(PUBLIC_PATH . '/images/users/profile/pic/medium/' . $userID . '.jpg');
		$image->scale(47); // Small img
		$image->save(PUBLIC_PATH . '/images/users/profile/pic/small/' . $userID . '.jpg');
		$image->scale(65); // Tiny img
		$image->save(PUBLIC_PATH . '/images/users/profile/pic/tiny/' . $userID . '.jpg');
		
    }

}