<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// ADDED: define path to temporary file directory
//define('PUBLIC_PATH', "X:/Program Files (x86)/wamp/www/Local_site/sportfast.com/public");
define('PUBLIC_PATH', $_SERVER['DOCUMENT_ROOT']); //FOR PRODUCTION

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

// Set timezone to westcoast
date_default_timezone_set('America/Los_Angeles');

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);


$application->bootstrap()
            ->run();
			
