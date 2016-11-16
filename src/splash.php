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

$splash = MoufManager::getMoufManager()->getInstance(\Mouf\Mvc\Splash\SplashMiddleware::class);

/* @var $splash Splash */
$server = Server::createServer($splash, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

$server->listen(new NoopFinalHandler());
