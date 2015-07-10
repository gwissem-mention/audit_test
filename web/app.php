<?php

die("Site en maintenance, la plateforme sera rétablie dans 5 minutes.");

use Symfony\Component\HttpFoundation\Request;

define('__WEB_DIRECTORY__', __DIR__);
define('__ROOT_DIRECTORY__', __DIR__ . '/..');

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

// Use APC for autoloading to improve performance.
// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
