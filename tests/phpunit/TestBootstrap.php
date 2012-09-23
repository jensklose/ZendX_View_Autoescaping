<?php
/**
 * don't forget 
 * composer install
 * @see http://getcomposer.org/
 */
// set application env
define('APPLICATION_ENV', 'testing');

// define Paths
define ('PATH_INSTALL', dirname(dirname(dirname(__FILE__))));
define ('PATH_TESTS', PATH_INSTALL . '/tests');
// define your ZF path here

require_once PATH_INSTALL . '/vendor/autoload.php';

// Create application for autoloading
new Zend_Application(
    APPLICATION_ENV
);

// load beans for testing environment
require_once PATH_TESTS . '/phpunit/ZendX/TestBean.php';
require_once PATH_TESTS . '/phpunit/ZendX/TestSubBean.php';