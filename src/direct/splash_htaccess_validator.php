<?php
// This file validates that a .htaccess file is defined at the root of the project.
// If not, an alert is raised.
require_once dirname(__FILE__)."/../../../../../mouf/Mouf.php";

$jsonObj = array();

if (file_exists(ROOT_PATH.".htaccess")) {
	$jsonObj['code'] = "ok";
	$jsonObj['html'] = "Splash .htaccess file found";
} else {
	$jsonObj['code'] = "warn";
	$jsonObj['html'] = "Unable to find Splash .htaccess file. You should <a href='".MOUF_URL."splashApacheConfig/'>configure the Apache redirection</a>.";
}

echo json_encode($jsonObj);
exit;

?>