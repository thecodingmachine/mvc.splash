<?php
// First thing first, let's include the Mouf configuration:
// (only if we are not in admin mode)
/*require_once dirname(__FILE__).'/../../../../mouf/MoufManager.php';
if (!MoufManager::hasHiddenInstance()) {
	require_once dirname(__FILE__).'/../../../../Mouf.php';
}*/
use Mouf\MoufManager;

if (function_exists('apache_getenv')) {
	define('ROOT_URL', apache_getenv("BASE")."/");
}

//require_once __DIR__.'/../../../autoload.php';
require_once __DIR__.'/../../../../mouf/Mouf.php';


$splash = MoufManager::getMoufManager()->getInstance('splash');

if (!isset($splashUrlPrefix)) {
	$splashUrlPrefix = ROOT_URL;
}

$splash->route($splashUrlPrefix);

?>