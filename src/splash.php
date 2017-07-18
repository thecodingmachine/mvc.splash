<?php

// First thing first, let's include the Mouf configuration:
// (only if we are not in admin mode)
use Mouf\MoufManager;
use Zend\Diactoros\Server;
use Mouf\Mvc\Splash\Splash;
use Zend\Stratigility\NoopFinalHandler;

if (isset($_SERVER['BASE'])) {
    define('ROOT_URL', $_SERVER['BASE'].'/');
} else {
    define('ROOT_URL', '/');
}

require_once __DIR__.'/../../../../mouf/Mouf.php';

$server = MoufManager::getMoufManager()->get(Server::class);

$server->listen(new NoopFinalHandler());
