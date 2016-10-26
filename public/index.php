<?php

use Dotenv\Dotenv;
use Zend\Mvc\Application;

chdir(dirname(__DIR__));

// composer autoloading
include __DIR__ . '/../vendor/autoload.php';

// load .env file
$dotenv = new Dotenv('.');
$dotenv->load();

// retrieve configuration
$config = require __DIR__ . '/../config/application.config.php';

// run the application
Application::init($config)->run();
