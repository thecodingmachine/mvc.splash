<?php

use Mouf\MoufUtils;
use Mouf\MoufManager;

require_once 'Mouf/Mvc/Splash/Controllers/Admin/SplashViewUrlsController.php';

$moufManager = MoufManager::getMoufManager();

$moufManager->declareComponent('splashGenerateService', 'Mouf\\Mvc\\Splash\\SplashGenerateService', true);

$moufManager->declareComponent('splashApacheConfig', 'Mouf\\Mvc\\Splash\\Controllers\\Admin\\SplashAdminApacheConfigureController', true);
$moufManager->bindComponent('splashApacheConfig', 'template', 'moufTemplate');
$moufManager->bindComponents('splashApacheConfig', 'content', 'block.content');
$moufManager->bindComponent('splashApacheConfig', 'splashGenerateService', 'splashGenerateService');

$moufManager->declareComponent('splash8install', 'Mouf\\Mvc\\Splash\\Controllers\\Admin\\SplashInstallController', true);
$moufManager->bindComponent('splash8install', 'template', 'moufInstallTemplate');
$moufManager->bindComponents('splash8install', 'content', 'block.content');
$moufManager->bindComponent('splash8install', 'splashGenerateService', 'splashGenerateService');

$moufManager->declareComponent('splashpurgecache', 'Mouf\\Mvc\\Splash\\Controllers\\Admin\\SplashPurgeCacheController', true);
$moufManager->bindComponent('splashpurgecache', 'template', 'moufTemplate');
$moufManager->bindComponents('splashpurgecache', 'content', 'block.content');

$moufManager->declareComponent('splashViewUrls', 'Mouf\\Mvc\\Splash\\Controllers\\Admin\\SplashViewUrlsController', true);
$moufManager->bindComponent('splashViewUrls', 'template', 'moufTemplate');
$moufManager->bindComponents('splashViewUrls', 'content', 'block.content');

MoufUtils::registerMainMenu('mvcMainMenu', 'MVC', null, 'mainMenu', 100);
MoufUtils::registerMenuItem('mvcSplashSubMenu', 'Splash MVC', null, 'mvcMainMenu', 45);
MoufUtils::registerMenuItem('mvcSplashPurgeCacheItem', 'Purge URLs cache', 'splashpurgecache/', 'mvcSplashSubMenu', 0);
MoufUtils::registerMenuItem('mvcSplashAdminApacheConfig2Item', 'Configure Apache redirection', 'splashApacheConfig/', 'mvcSplashSubMenu', 45);
MoufUtils::registerMenuItem('mvcSplashAdminUrlsListMenuItem', 'View URLs', 'splashViewUrls/', 'mvcSplashSubMenu', 10);
