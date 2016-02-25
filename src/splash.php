<?php
// First thing first, let's include the Mouf configuration:
// (only if we are not in admin mode)
/*require_once dirname(__FILE__).'/../../../../mouf/MoufManager.php';
if (!MoufManager::hasHiddenInstance()) {
    require_once dirname(__FILE__).'/../../../../Mouf.php';
}*/
use Mouf\MoufManager;
use Zend\Diactoros\Server;
use Mouf\Mvc\Splash\Splash;

if (isset($_SERVER['BASE'])) {
    define('ROOT_URL', $_SERVER['BASE']."/");
} else {
    define('ROOT_URL', "/");
}

//require_once __DIR__.'/../../../autoload.php';
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

// json_decode postdata if content_type is json (application/json, ...)
/*if ($request->getContentType() === 'json') {
    $postdata = file_get_contents("php://input");
    $postdata = json_decode($postdata, true);
    $request->request = new ParameterBag($postdata);
}*/

// TODO: add a finalhandler for 404 management (or not...)
$server->listen();
