<?php
use Mouf\MoufManager;

use Mouf\MoufUtils;

// This file validates that a "splash" instance exists.
// If not, an alert is raised.
if (!isset($_REQUEST["selfedit"]) || $_REQUEST["selfedit"]!="true") {
	require_once '../../../../../mouf/Mouf.php';
} else {
	require_once '../../mouf/Mouf.php';
}

// Note: checking rights is done after loading the required files because we need to open the session
// and only after can we check if it was not loaded before loading it ourselves...
MoufUtils::checkRights();

$jsonObj = array();

$instanceExists = MoufManager::getMoufManager()->instanceExists('splash');

if ($instanceExists) {
	$jsonObj['code'] = "ok";
	$jsonObj['html'] = "'splash' instance found";
} else {
	$jsonObj['code'] = "warn";
	$jsonObj['html'] = "Unable to find the 'splash' instance. Click here to <a href='".ROOT_URL."mouf/mouf/newInstance?instanceName=splash&instanceClass=Splash'>create an instance of the Splash class named 'splash'</a>.";
}

echo json_encode($jsonObj);
exit;

?>