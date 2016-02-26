<?php
// First thing first, let's include the Mouf configuration:
// (only if we are not in admin mode)
use Mouf\MoufManager;
use Zend\Diactoros\Server;
use Mouf\Mvc\Splash\Splash;

if (isset($_SERVER['BASE'])) {
    define('ROOT_URL', $_SERVER['BASE']."/");
} else {
    define('ROOT_URL', "/");
}

require_once __DIR__.'/../../../../mouf/Mouf.php';

$splash = MoufManager::getMoufManager()->getInstance('splashMiddleware');

// Decode json parameters for POST request
if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], 'json')) {
    $postdata = file_get_contents("php://input");
    $postdata = json_decode($postdata, true);
} else {
	$postdata = $_POST;
}
/** @var $splash Splash */
$server = Server::createServer($splash, $_SERVER, $_GET, $postdata, $_COOKIE, $_FILES);

$server->listen();
