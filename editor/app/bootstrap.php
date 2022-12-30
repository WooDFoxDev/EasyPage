<?php

use Easypage\Kernel\Core;
use Easypage\Kernel\DotEnv;

// Load common functions
require_once(ROOT_PATH . '/app/common.php');

// Load environment variables
DotEnv::load(ROOT_PATH . '/app/app.env');

/**
 * It is possible to configure App Core here, with examples below
 * Or let App configure itself
 * 
 * Setup session
 * $session = Session::getInstance();
 * 
 * Setup data storage
 * It is possible to set custom storage
 * Or even skip configuration for now, default storage will be initialized like shown:
 * $storage = new FileStorage;
 * $storage->setStorage($_ENV['STORAGE_PATH']);
 * 
 * Initialize request
 * You can customize request before app, or skip that in bootstrapper
 * $request = new Request();
 * $request->captureRequest();
 * 
 * Setup view controller
 * $view = EPView::getInstance();
 * $view->setTemplatesPath($_ENV['VIEW_PATH']);
 * $view->setCachePath($_ENV['CACHE_PATH']);
 * $view->setCacheEnabled($_ENV['VIEW_CACHE']);
 */

// Instantiate app core 
$app = Core::createInstance();
