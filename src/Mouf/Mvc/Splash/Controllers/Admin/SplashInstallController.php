<?php

namespace Mouf\Mvc\Splash\Controllers\Admin;

use Mouf\Actions\InstallUtils;
use Mouf\Composer\ClassNameMapper;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Mvc\Splash\Services\SplashCreateControllerService;
use Mouf\Mvc\Splash\SplashGenerateService;
use Mouf\MoufManager;
use Mouf\Mvc\Splash\Controllers\Controller;

/**
 * The controller used in the Splash install process.
 */
class SplashInstallController extends Controller
{
    public $selfedit;

    /**
     * The active MoufManager to be edited/viewed.
     *
     * @var MoufManager
     */
    public $moufManager;

    /**
     * The service in charge of generating files.
     *
     * @Property
     * @Compulsory
     *
     * @var SplashGenerateService
     */
    public $splashGenerateService;

    /**
     * The template used by the install page.
     *
     * @Property
     * @Compulsory
     *
     * @var TemplateInterface
     */
    public $template;

    /**
     * @var HtmlBlock
     */
    public $content;

    /**
     * Displays the first install screen.
     *
     * @Action
     * @Logged
     *
     * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only)
     */
    public function defaultAction($selfedit = 'false')
    {
        $this->selfedit = $selfedit;

        if ($selfedit == 'true') {
            $this->moufManager = MoufManager::getMoufManager();
        } else {
            $this->moufManager = MoufManager::getMoufManagerHiddenInstance();
        }

        $this->content->addFile(__DIR__.'/../../../../../views/admin/installStep1.php', $this);
        $this->template->toHtml();
    }

    /**
     * Skips the install process.
     *
     * @Action
     * @Logged
     *
     * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only)
     */
    public function skip($selfedit = 'false')
    {
        InstallUtils::continueInstall($selfedit == 'true');
    }

    protected $sourceDirectory;
    protected $controllerNamespace;
    protected $viewDirectory;

    /**
     * Displays the second install screen.
     *
     * @Action
     * @Logged
     *
     * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only)
     */
    public function configure($selfedit = 'false')
    {
        $this->selfedit = $selfedit;

        if ($selfedit == 'true') {
            $this->moufManager = MoufManager::getMoufManager();
        } else {
            $this->moufManager = MoufManager::getMoufManagerHiddenInstance();
        }

        $classNameMapper = ClassNameMapper::createFromComposerFile(__DIR__.'/../../../../../../../../../composer.json');
        $namespaces = $classNameMapper->getManagedNamespaces();
        if ($namespaces) {
            $rootNamespace = $namespaces[0];
        } else {
            set_user_message('<strong>Warning</strong> : Mouf could not find a PSR-0 or PSR-4 autoloader configured
                in your composer.json file. Therefore, unless you are using your own autoloader, it is likely that mouf will be unable to find the Splash Controllers.
                <br/>You should :
                <ol>
                    <li><a href="http://getcomposer.org/doc/04-schema.md#psr-0" target="_blank">Configure PSR-4 in your composer.json</a></li>
                    <li>Regenerate your composer autoloader : <pre>php composer.phar dumpautoload</pre></li>
                    <li>Refresh this page</li>
                </ol>');
            $rootNamespace = '';
        }

        $this->controllerNamespace = $this->moufManager->getVariable('splashDefaultControllersNamespace');
        if ($this->controllerNamespace == null) {
            $this->controllerNamespace = $rootNamespace.'Controllers';
        }
        $this->viewDirectory = $this->moufManager->getVariable('splashDefaultViewsDirectory');
        if ($this->viewDirectory == null) {
            $this->viewDirectory = 'views/';
        }

        $this->content->addFile(__DIR__.'/../../../../../views/admin/installStep2.php', $this);
        $this->template->toHtml();
    }

    /**
     * Are we coming from Splash 8.0 or 8.1?
     *
     * @return bool
     */
    private function isMigratingFromSplash80(MoufManager $moufManager) : bool
    {
        // The ErrorRouter class has been removed. Let's check if we use it. If yes, we must migrate.
        $allInstances = $moufManager->getInstancesList();
        return array_search('Mouf\\Mvc\\Splash\\Routers\\ErrorRouter', $allInstances, true) !== false;
    }

    private function removeErrorRouters(MoufManager $moufManager)
    {
        $allInstances = $moufManager->getInstancesList();
        foreach ($allInstances as $instanceName => $className) {
            if ($className === 'Mouf\\Mvc\\Splash\\Routers\\ErrorRouter') {
                $moufManager->removeComponent($instanceName);
            }
        }
    }

    /**
     * This action generates the TDBM instance, then the DAOs and Beans.
     *
     * @Action
     *
     * @param string $controllernamespace
     * @param string $viewdirectory
     * @param string $selfedit
     */
    public function generate($controllernamespace, $viewdirectory, $selfedit = 'false')
    {
        $this->selfedit = $selfedit;

        if ($selfedit == 'true') {
            $this->moufManager = MoufManager::getMoufManager();
        } else {
            $this->moufManager = MoufManager::getMoufManagerHiddenInstance();
        }

        $controllernamespace = trim($controllernamespace, '/\\');
        $controllernamespace .= '\\';
        $viewdirectory = trim($viewdirectory, '/\\');
        $viewdirectory .= '/';

        $this->moufManager->setVariable('splashDefaultControllersNamespace', $controllernamespace);
        $this->moufManager->setVariable('splashDefaultViewsDirectory', $viewdirectory);

        // Let's start by performing basic checks about the instances we assume to exist.
        if (!$this->moufManager->instanceExists('bootstrapTemplate')) {
            $this->displayErrorMsg("The install process assumes there is a template whose instance name is 'bootstrapTemplate'. Could not find the 'bootstrapTemplate' instance.");

            return;
        }

        $moufManager = $this->moufManager;

        // Are we coming from Splash 5? If yes, let's delete all instances and recreate them.
        if ($moufManager->has('splash')) {
            $moufManager->removeComponent('splash');
            $moufManager->removeComponent('exceptionRouter');
            $moufManager->removeComponent('httpErrorsController');
            $moufManager->removeComponent('whoopsMiddleware');
            $moufManager->removeComponent('phpVarsCheckRouter');
            $moufManager->removeComponent('splashDefaultRouter');
            $moufManager->removeComponent('notFoundRouter');
            $moufManager->removeComponent('splashCacheApc');
            $moufManager->removeComponent('splashCacheFile');
        }
        // Are we coming from Splash 7? If yes, let's delete all instances and recreate them.
        if ($moufManager->has('moufExplorerUrlProvider')) {
            $moufManager->removeComponent('splashMiddleware');
            $moufManager->removeComponent('exceptionRouter');
            $moufManager->removeComponent('httpErrorsController');
            $moufManager->removeComponent('whoopsMiddleware');
            $moufManager->removeComponent('phpVarsCheckRouter');
            $moufManager->removeComponent('splashDefaultRouter');
            $moufManager->removeComponent('notFoundRouter');
            $moufManager->removeComponent('splashCacheApc');
            $moufManager->removeComponent('splashCacheFile');
            $moufManager->removeComponent('moufExplorerUrlProvider');
        }
        if ($this->isMigratingFromSplash80($moufManager)) {
            $moufManager->removeComponent('splashMiddleware');
            $moufManager->removeComponent('exceptionRouter');
            $moufManager->removeComponent('httpErrorsController');
            $moufManager->removeComponent('whoopsMiddleware');
            $this->removeErrorRouters($moufManager);
        }
        if ($moufManager->has('whoopsMiddleware')) {
            // For migration purpose
            $moufManager->removeComponent('whoopsMiddleware');
        }

        // These instances are expected to exist when the installer is run.
        $bootstrapTemplate = $moufManager->getInstanceDescriptor('bootstrapTemplate');
        $block_content = $moufManager->getInstanceDescriptor('block.content');
        $psr_errorLogLogger = $moufManager->getInstanceDescriptor('psr.errorLogLogger');
        $annotationReader = $moufManager->getInstanceDescriptor('annotationReader');

        // Let's create the instances.
        $whoopsMiddleware = InstallUtils::getOrCreateInstance('whoopsMiddleware', 'Middlewares\\Whoops', $moufManager);
        $Mouf_Mvc_Splash_SplashMiddleware = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\SplashMiddleware', 'Mouf\\Mvc\\Splash\\SplashMiddleware', $moufManager);
        $Mouf_Mvc_Splash_Controllers_HttpErrorsController = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Controllers\\HttpErrorsController', 'Mouf\\Mvc\\Splash\\Controllers\\HttpErrorsController', $moufManager);
        $Mouf_Mvc_Splash_Routers_NotFoundRouter = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Routers\\NotFoundRouter', 'Mouf\\Mvc\\Splash\\Routers\\NotFoundRouter', $moufManager);
        $Mouf_Mvc_Splash_Routers_ExceptionRouter = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Routers\\ExceptionRouter', 'Mouf\\Mvc\\Splash\\Routers\\ExceptionRouter', $moufManager);
        $Mouf_Mvc_Splash_Routers_PhpVarsCheckRouter = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Routers\\PhpVarsCheckRouter', 'Mouf\\Mvc\\Splash\\Routers\\PhpVarsCheckRouter', $moufManager);
        $Mouf_Mvc_Splash_Routers_SplashDefaultRouter = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Routers\\SplashDefaultRouter', 'Mouf\\Mvc\\Splash\\Routers\\SplashDefaultRouter', $moufManager);
        $Mouf_Mvc_Splash_Services_ParameterFetcherRegistry = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Services\\ParameterFetcherRegistry', 'Mouf\\Mvc\\Splash\\Services\\ParameterFetcherRegistry', $moufManager);
        $Mouf_Mvc_Splash_Services_SplashRequestFetcher = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Services\\SplashRequestFetcher', 'Mouf\\Mvc\\Splash\\Services\\SplashRequestFetcher', $moufManager);
        $Mouf_Mvc_Splash_Services_MoufExplorerUrlProvider = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Services\\MoufExplorerUrlProvider', 'Mouf\\Mvc\\Splash\\Services\\MoufExplorerUrlProvider', $moufManager);
        $Mouf_Mvc_Splash_Services_ControllerRegistry = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Services\\ControllerRegistry', 'Mouf\\Mvc\\Splash\\Services\\ControllerRegistry', $moufManager);
        $Mouf_Mvc_Splash_Services_SplashRequestParameterFetcher = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Services\\SplashRequestParameterFetcher', 'Mouf\\Mvc\\Splash\\Services\\SplashRequestParameterFetcher', $moufManager);
        $Mouf_Mvc_Splash_Services_ControllerAnalyzer = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Services\\ControllerAnalyzer', 'Mouf\\Mvc\\Splash\\Services\\ControllerAnalyzer', $moufManager);
        $Mouf_Mvc_Splash_Services_MoufControllerExplorer = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Services\\MoufControllerExplorer', 'Mouf\\Mvc\\Splash\\Services\\MoufControllerExplorer', $moufManager);
        $Psr7Middlewares_Middleware_Payload = InstallUtils::getOrCreateInstance('Psr7Middlewares\\Middleware\\Payload', 'Psr7Middlewares\\Middleware\\Payload', $moufManager);
        $splashCachePool = InstallUtils::getOrCreateInstance('splashCachePool', null, $moufManager);
        $splashCachePool->setCode('$drivers = [
    new Stash\\Driver\\Ephemeral()
];

if (Stash\\Driver\\Apc::isAvailable()) {
    $drivers[] = new Stash\\Driver\\Apc([
        \'namespace\' => SECRET
    ]);
}

$drivers[] = new Stash\\Driver\\FileSystem([
    \'path\' => sys_get_temp_dir().\'/splash-\'.SECRET
]);

$compositeDriver = new Stash\\Driver\\Composite([\'drivers\'=>$drivers]);

return new Stash\\Pool($compositeDriver);');
        $anonymousRouter = $moufManager->createInstance('Mouf\\Mvc\\Splash\\Routers\\Router');
        $anonymousRouter1 = $moufManager->createInstance('Mouf\\Mvc\\Splash\\Routers\\Router');
        $anonymousRouter2 = $moufManager->createInstance('Mouf\\Mvc\\Splash\\Routers\\Router');
        $anonymousRouter3 = $moufManager->createInstance('Mouf\\Mvc\\Splash\\Routers\\Router');
        $anonymousErrorRouter = $moufManager->createInstance('Mouf\\Mvc\\Splash\\Routers\\Router');
        $anonymousToCondition = $moufManager->createInstance('Mouf\\Utils\\Common\\Condition\\ToCondition');
        $anonymousVariable = $moufManager->createInstance('Mouf\\Utils\\Value\\Variable');
        $anonymousErrorRouter2 = $moufManager->createInstance('Mouf\\Mvc\\Splash\\Routers\\Router');

// Let's bind instances together.
        if (!$Mouf_Mvc_Splash_SplashMiddleware->getConstructorArgumentProperty('routers')->isValueSet()) {
            $Mouf_Mvc_Splash_SplashMiddleware->getConstructorArgumentProperty('routers')->setValue(array(0 => $anonymousErrorRouter2, 1 => $anonymousErrorRouter, 2 => $anonymousRouter, 3 => $anonymousRouter1, 4 => $anonymousRouter2, 5 => $anonymousRouter3));
        }
        if (!$Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('template')->isValueSet()) {
            $Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('template')->setValue($bootstrapTemplate);
        }
        if (!$Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('contentBlock')->isValueSet()) {
            $Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('contentBlock')->setValue($block_content);
        }
        if (!$Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('debugMode')->isValueSet()) {
            $Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('debugMode')->setValue('DEBUG');
            $Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('debugMode')->setOrigin('config');
        }
        if (!$Mouf_Mvc_Splash_Routers_NotFoundRouter->getConstructorArgumentProperty('pageNotFoundController')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_NotFoundRouter->getConstructorArgumentProperty('pageNotFoundController')->setValue($Mouf_Mvc_Splash_Controllers_HttpErrorsController);
        }
        if (!$Mouf_Mvc_Splash_Routers_NotFoundRouter->getConstructorArgumentProperty('log')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_NotFoundRouter->getConstructorArgumentProperty('log')->setValue($psr_errorLogLogger);
        }
        if (!$Mouf_Mvc_Splash_Routers_ExceptionRouter->getConstructorArgumentProperty('errorController')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_ExceptionRouter->getConstructorArgumentProperty('errorController')->setValue($Mouf_Mvc_Splash_Controllers_HttpErrorsController);
        }
        if (!$Mouf_Mvc_Splash_Routers_ExceptionRouter->getConstructorArgumentProperty('log')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_ExceptionRouter->getConstructorArgumentProperty('log')->setValue($psr_errorLogLogger);
        }
        if (!$Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('container')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('container')->setValue('return $container;');
            $Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('container')->setOrigin('php');
        }
        if (!$Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('routeProviders')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('routeProviders')->setValue(array(0 => $Mouf_Mvc_Splash_Services_MoufExplorerUrlProvider));
        }
        if (!$Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('parameterFetcherRegistry')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('parameterFetcherRegistry')->setValue($Mouf_Mvc_Splash_Services_ParameterFetcherRegistry);
        }
        if (!$Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('cachePool')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('cachePool')->setValue($splashCachePool);
        }
        if (!$Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('log')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('log')->setValue($psr_errorLogLogger);
        }
        if (!$Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('mode')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('mode')->setValue('strict');
        }
        if (!$Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('debug')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('debug')->setValue('DEBUG');
            $Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('debug')->setOrigin('config');
        }
        if (!$Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('rootUrl')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('rootUrl')->setValue('return ROOT_URL;');
            $Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getConstructorArgumentProperty('rootUrl')->setOrigin('php');
        }
        if (!$Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getSetterProperty('setHttp400Handler')->isValueSet()) {
            $Mouf_Mvc_Splash_Routers_SplashDefaultRouter->getSetterProperty('setHttp400Handler')->setValue($Mouf_Mvc_Splash_Controllers_HttpErrorsController);
        }
        if (!$Mouf_Mvc_Splash_Services_ParameterFetcherRegistry->getConstructorArgumentProperty('parameterFetchers')->isValueSet()) {
            $Mouf_Mvc_Splash_Services_ParameterFetcherRegistry->getConstructorArgumentProperty('parameterFetchers')->setValue(array(0 => $Mouf_Mvc_Splash_Services_SplashRequestFetcher, 1 => $Mouf_Mvc_Splash_Services_SplashRequestParameterFetcher));
        }
        if (!$Mouf_Mvc_Splash_Services_ControllerRegistry->getConstructorArgumentProperty('controllerAnalyzer')->isValueSet()) {
            $Mouf_Mvc_Splash_Services_ControllerRegistry->getConstructorArgumentProperty('controllerAnalyzer')->setValue($Mouf_Mvc_Splash_Services_ControllerAnalyzer);
        }
        if (!$Mouf_Mvc_Splash_Services_ControllerRegistry->getConstructorArgumentProperty('controllers')->isValueSet()) {
            $Mouf_Mvc_Splash_Services_ControllerRegistry->getConstructorArgumentProperty('controllers')->setValue(array());
        }
        if (!$Mouf_Mvc_Splash_Services_ControllerRegistry->getConstructorArgumentProperty('controllerDetector')->isValueSet()) {
            $Mouf_Mvc_Splash_Services_ControllerRegistry->getConstructorArgumentProperty('controllerDetector')->setValue($Mouf_Mvc_Splash_Services_MoufControllerExplorer);
        }
        if (!$Mouf_Mvc_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('container')->isValueSet()) {
            $Mouf_Mvc_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('container')->setValue('return $container;');
            $Mouf_Mvc_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('container')->setOrigin('php');
        }
        if (!$Mouf_Mvc_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('parameterFetcherRegistry')->isValueSet()) {
            $Mouf_Mvc_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('parameterFetcherRegistry')->setValue($Mouf_Mvc_Splash_Services_ParameterFetcherRegistry);
        }
        if (!$Mouf_Mvc_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('annotationReader')->isValueSet()) {
            $Mouf_Mvc_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('annotationReader')->setValue($annotationReader);
        }
        $anonymousRouter->getConstructorArgumentProperty('middleware')->setValue($Mouf_Mvc_Splash_Routers_PhpVarsCheckRouter);
        $anonymousRouter1->getConstructorArgumentProperty('middleware')->setValue('return $container->get(\'Psr7Middlewares\\\\Middleware\\\\Payload\');');
        $anonymousRouter1->getConstructorArgumentProperty('middleware')->setOrigin("php");
        $anonymousRouter2->getConstructorArgumentProperty('middleware')->setValue($Mouf_Mvc_Splash_Routers_SplashDefaultRouter);
        $anonymousRouter3->getConstructorArgumentProperty('middleware')->setValue($Mouf_Mvc_Splash_Routers_NotFoundRouter);
        $anonymousErrorRouter->getConstructorArgumentProperty('middleware')->setValue('return $container->get(\'whoopsMiddleware\');');
        $anonymousErrorRouter->getConstructorArgumentProperty('middleware')->setOrigin('php');
        $anonymousErrorRouter->getConstructorArgumentProperty('enableCondition')->setValue($anonymousToCondition);
        $anonymousToCondition->getConstructorArgumentProperty('value')->setValue($anonymousVariable);
        $anonymousVariable->getConstructorArgumentProperty('value')->setValue('DEBUG');
        $anonymousVariable->getConstructorArgumentProperty('value')->setOrigin('config');
        $anonymousErrorRouter2->getConstructorArgumentProperty('middleware')->setValue($Mouf_Mvc_Splash_Routers_ExceptionRouter);

        // Let's rewrite the MoufComponents.php file to save the component
        $this->moufManager->rewriteMouf();

        if (!$this->moufManager->instanceExists('rootController')) {
            $splashGenerateService = new SplashCreateControllerService();
            $splashGenerateService->generate($this->moufManager, 'RootController', 'rootController',
                $controllernamespace, false, true, false,
                array(
                    array(
                        'url' => '/',
                        'method' => 'index',
                        'view' => 'twig',
                        'twigFile' => 'views/root/index.twig',
                        'anyMethod' => 'true',
                        'getMethod' => 'false',
                        'postMethod' => 'false',
                        'putMethod' => 'false',
                        'deleteMethod' => 'false',
                    ),
                )
                );

            // Let's overwrite the generated Twig file.
            file_put_contents(ROOT_PATH.'../../../'.$viewdirectory.'root/index.twig', '
<h1>Hello {{message}}!</h1>
<h2>Welcome to Splash</h2>
<p>This file is your welcome page. It is generated by the '.$controllernamespace.'RootController class and the '.$viewdirectory.'root/index.php file. Please feel free to customize it.</p>');
        }

        $this->moufManager->rewriteMouf();

        InstallUtils::continueInstall($selfedit == 'true');
    }

    /**
     * Write .htaccess.
     *
     * @Action
     *
     * @param string $selfedit
     */
    public function writeHtAccess($selfedit = 'false')
    {
        if ($selfedit == 'true') {
            $moufManager = MoufManager::getMoufManager();
        } else {
            $moufManager = MoufManager::getMoufManagerHiddenInstance();
        }

        $this->exludeExtentions = $moufManager->getVariable('splashexludeextentions');
        $this->exludeFolders = $moufManager->getVariable('splashexludefolders');
        if (empty($this->exludeExtentions)) {
            $this->exludeExtentions = array('js', 'ico', 'gif', 'jpg', 'png', 'css', 'woff', 'ttf', 'svg', 'eot', 'map');
        }
        if (empty($this->exludeFolders)) {
            $this->exludeFolders = array('vendor');
        }

        $this->splashGenerateService->writeHtAccess($this->exludeExtentions, $this->exludeFolders);

        InstallUtils::continueInstall($selfedit == 'true');
    }

    protected $errorMsg;

    private function displayErrorMsg($msg)
    {
        $this->errorMsg = $msg;
        $this->content->addFile(__DIR__.'/../../../../../views/admin/installError.php', $this);
        $this->template->toHtml();
    }
}
