<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();

$response = $kernel->handle($request);

$response->setPublic();
$date = new DateTime();
$date->modify('+600 seconds');

$response->setExpires($date);
$response->send();
$kernel->terminate($request, $response);
