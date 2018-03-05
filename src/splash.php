<?php

// First thing first, let's include the Mouf configuration:
// (only if we are not in admin mode)
use Mouf\MoufManager;
use Zend\Diactoros\Server;

if (isset($_SERVER['BASE'])) {
    define('ROOT_URL', $_SERVER['BASE'].'/');
} else {
    define('ROOT_URL', '/');
}

require_once __DIR__.'/../../../../mouf/Mouf.php';

$server = MoufManager::getMoufManager()->get(Server::class);

//todo: find a replacment for Zend NoopFinalHandler
$server->listen();
