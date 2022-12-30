<?php

define('ROOT_PATH', __DIR__);

// Require Composer autoloader
require ROOT_PATH . '/app/vendor/autoload.php';
// Bootstrap App
require ROOT_PATH . '/app/bootstrap.php';

$responce = $app->process();
$responce->send();
