<?php

use Symfony\Component\HttpFoundation\Request;

date_default_timezone_set( 'UTC' );
require_once __DIR__ . '/../app/ErrorHandler.php';
ErrorHandler::register();

$loader = require_once __DIR__.'/../var/bootstrap.php.cache';

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
