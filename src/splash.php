<?php

// First thing first, let's include the Mouf configuration:
// (only if we are not in admin mode)
use Mouf\MoufManager;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Server;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;

if (isset($_SERVER['BASE'])) {
    define('ROOT_URL', $_SERVER['BASE'].'/');
} else {
    define('ROOT_URL', '/');
}

require_once __DIR__.'/../../../../mouf/Mouf.php';

/** @var RequestHandlerRunner $server */
$server = MoufManager::getMoufManager()->get(RequestHandlerRunner::class);

$server->run();
