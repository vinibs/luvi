<?php
/**
 * App initializer
 * 
 * @author Vinicius Baroni Soares <hiviniciusbs@gmail.com>
 * @copyright 2020
 */

 // Loads all classes and config files
require_once __DIR__ . '/autoload.php';

use App\Core\Router as Router;

define('defaultLocale', $appConfig->language ?? 'en-en');

// Initializes a router object
$router = Router::getInstance();

// Loads the defined routes for the app
require_once __DIR__ . '/../routes.php';

// Calls the route processing
try {
    $router->process();
}
// If an exception is thrown, shows only its message
catch (\Exception $e) {
    echo $e->getMessage();
    die();
}