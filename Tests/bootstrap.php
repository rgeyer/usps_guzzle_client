<?php

require_once 'vendor/autoload.php';

// Autoload classes for guzzle-usps
spl_autoload_register(function($class) {
    if (0 === strpos($class, 'Guzzle\Usps\\')) {
        $path = implode('/', array_slice(explode('\\', $class), 2)) . '.php';
        require_once __DIR__ . '/../' . $path;
        return true;
    }
});

// Register services with the GuzzleTestCase
\Guzzle\Tests\GuzzleTestCase::setMockBasePath(__DIR__ . DIRECTORY_SEPARATOR . 'mock');

// Create a service builder to use in the unit tests
\Guzzle\Tests\GuzzleTestCase::setServiceBuilder(\Guzzle\Service\Builder\ServiceBuilder::factory(array(
    'test.guzzle-usps' => array(
        'class' 	=> 'Guzzle\Usps\UspsClient',
    		'params' 	=> array(
    			'username'	=> $_SERVER['USPS_USERNAME'],
    			'version'		=> '1.0'
    		)
    )
)));

date_default_timezone_set('America/Los_Angeles');