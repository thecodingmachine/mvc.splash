<?php

namespace Mouf\Mvc\Splash\Controllers\Admin;

use Mouf\Actions\InstallUtils;
use Mouf\Composer\ClassNameMapper;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Mvc\Splash\Services\SplashCreateControllerService;
use Mouf\Mvc\Splash\SplashGenerateService;
use Mouf\MoufManager;
use TheCodingMachine\Middlewares\CsrfHeaderCheckMiddleware;
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

    private function isMigratingFromSplash82(MoufManager $moufManager) : bool
    {
        return !$moufManager->has(CsrfHeaderCheckMiddleware::class);
    }

    private function isMigratingFromSplash83(MoufManager $moufManager) : bool
    {
        $allInstances = $moufManager->getInstancesList();
        return array_search('Mouf\\Mvc\\Splash\\Routers\\SplashDefaultRouter', $allInstances, true) !== false;
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
        if ($this->isMigratingFromSplash82($moufManager)) {
            $moufManager->removeComponent('Mouf\\Mvc\\Splash\\MiddlewarePipe');
        }
        if ($this->isMigratingFromSplash83($moufManager)) {
            //todo: implement
            $moufManager->removeComponent('splashDefaultRouter');
            $moufManager->removeComponent('Mouf\\Mvc\\Splash\\MiddlewarePipe');
            $moufManager->removeComponent('exceptionRouter');
            $moufManager->removeComponent('httpErrorsController');
            $moufManager->removeComponent('whoopsMiddleware');
            $moufManager->removeComponent('phpVarsCheckRouter');
        }
        if ($moufManager->has('whoopsMiddleware')) {
            // For migration purpose
            $moufManager->removeComponent('whoopsMiddleware');
        }

        // Let's create the required constants.
        $configManager = $moufManager->getConfigManager();
        $constants = $configManager->getMergedConstants();

        if (!isset($constants['ENABLE_CSRF_PROTECTION'])) {
            $configManager->registerConstant("ENABLE_CSRF_PROTECTION", "bool", true, "Set to true to enable the CSRF protection middleware. This will prevent any POST request from being performed from outside a web-page of your application. If you are working on an API to be used by third party servers, you might want to disable CSRF protection. For specific cases, please consider editing the 'TheCodingMachine\\Middlewares\\CsrfHeaderCheckMiddleware' instance instead.");

            $configPhpConstants = $configManager->getDefinedConstants();
            $configPhpConstants['ENABLE_CSRF_PROTECTION'] = true;
            $configManager->setDefinedConstants($configPhpConstants);
        }

        if (!isset($constants['CSRF_ALLOWED_DOMAIN_NAMES'])) {
            $configManager->registerConstant("CSRF_ALLOWED_DOMAIN_NAMES", "string", "", "A comma separated list of domain names for your application. The CSRF middleware can normally detect this automatically unless your application runs behind a proxy. In this case, you can use this config constant to enter the list of domain names from which a POST query is allowed to originate.");

            $configPhpConstants = $configManager->getDefinedConstants();
            $configPhpConstants['CSRF_ALLOWED_DOMAIN_NAMES'] = '';
            $configManager->setDefinedConstants($configPhpConstants);
        }

        $this->moufManager->rewriteMouf();



        // These instances are expected to exist when the installer is run.
        $bootstrapTemplate = $moufManager->getInstanceDescriptor('bootstrapTemplate');
        $block_content = $moufManager->getInstanceDescriptor('block.content');
        $psr_errorLogLogger = $moufManager->getInstanceDescriptor('psr.errorLogLogger');
        $annotationReader = $moufManager->getInstanceDescriptor('annotationReader');

        // Let's create the instances.
        $Zend_HttpHandlerRunner_RequestHandlerRunner = InstallUtils::getOrCreateInstance('Zend\\HttpHandlerRunner\\RequestHandlerRunner', 'Zend\\HttpHandlerRunner\\RequestHandlerRunner', $moufManager);
        $Mouf_Mvc_Splash_MiddlewarePipe = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\MiddlewarePipe', 'Mouf\\Mvc\\Splash\\MiddlewarePipe', $moufManager);
        $whoopsMiddleware = InstallUtils::getOrCreateInstance('whoopsMiddleware', 'Middlewares\\Whoops', $moufManager);
        $Mouf_Mvc_Splash_Controllers_HttpErrorsController = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Controllers\\HttpErrorsController', 'Mouf\\Mvc\\Splash\\Controllers\\HttpErrorsController', $moufManager);
        $Mouf_Mvc_Splash_Routers_NotFoundRouter = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Routers\\NotFoundRouter', 'Mouf\\Mvc\\Splash\\Routers\\NotFoundRouter', $moufManager);
        $Mouf_Mvc_Splash_Routers_ExceptionRouter = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Routers\\ExceptionRouter', 'Mouf\\Mvc\\Splash\\Routers\\ExceptionRouter', $moufManager);
        $Mouf_Mvc_Splash_Routers_PhpVarsCheckRouter = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Routers\\PhpVarsCheckRouter', 'Mouf\\Mvc\\Splash\\Routers\\PhpVarsCheckRouter', $moufManager);
        $TheCodingMachine_Splash_Routers_SplashRouter = InstallUtils::getOrCreateInstance('TheCodingMachine\\Splash\\Routers\\SplashRouter', 'TheCodingMachine\\Splash\\Routers\\SplashRouter', $moufManager);
        $TheCodingMachine_Splash_Services_ParameterFetcherRegistry = InstallUtils::getOrCreateInstance('TheCodingMachine\\Splash\\Services\\ParameterFetcherRegistry', 'TheCodingMachine\\Splash\\Services\\ParameterFetcherRegistry', $moufManager);
        $TheCodingMachine_Splash_Services_SplashRequestFetcher = InstallUtils::getOrCreateInstance('TheCodingMachine\\Splash\\Services\\SplashRequestFetcher', 'TheCodingMachine\\Splash\\Services\\SplashRequestFetcher', $moufManager);
        $Mouf_Mvc_Splash_Services_MoufExplorerUrlProvider = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Services\\MoufExplorerUrlProvider', 'Mouf\\Mvc\\Splash\\Services\\MoufExplorerUrlProvider', $moufManager);
        $TheCodingMachine_Splash_Services_ControllerRegistry = InstallUtils::getOrCreateInstance('TheCodingMachine\\Splash\\Services\\ControllerRegistry', 'TheCodingMachine\\Splash\\Services\\ControllerRegistry', $moufManager);
        $TheCodingMachine_Splash_Services_SplashRequestParameterFetcher = InstallUtils::getOrCreateInstance('TheCodingMachine\\Splash\\Services\\SplashRequestParameterFetcher', 'TheCodingMachine\\Splash\\Services\\SplashRequestParameterFetcher', $moufManager);
        $TheCodingMachine_Splash_Services_ControllerAnalyzer = InstallUtils::getOrCreateInstance('TheCodingMachine\\Splash\\Services\\ControllerAnalyzer', 'TheCodingMachine\\Splash\\Services\\ControllerAnalyzer', $moufManager);
        $Mouf_Mvc_Splash_Services_MoufControllerExplorer = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\Services\\MoufControllerExplorer', 'Mouf\\Mvc\\Splash\\Services\\MoufControllerExplorer', $moufManager);
        $splashCachePool = InstallUtils::getOrCreateInstance('splashCachePool', NULL, $moufManager);
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

return new Stash\\Pool($compositeDriver);');$whoopsConditionMiddleware = InstallUtils::getOrCreateInstance('whoopsConditionMiddleware', 'Mouf\\Mvc\\Splash\\ConditionMiddleware', $moufManager);
        $Middlewares_JsonPayload = InstallUtils::getOrCreateInstance('Middlewares\\JsonPayload', 'Middlewares\\JsonPayload', $moufManager);
        $Mouf_Mvc_Splash_CsrfHeaderConditionMiddleware = InstallUtils::getOrCreateInstance('Mouf\\Mvc\\Splash\\CsrfHeaderConditionMiddleware', 'Mouf\\Mvc\\Splash\\ConditionMiddleware', $moufManager);
        $TheCodingMachine_Middlewares_CsrfHeaderCheckMiddleware = InstallUtils::getOrCreateInstance('TheCodingMachine\\Middlewares\\CsrfHeaderCheckMiddleware', NULL, $moufManager);
        $TheCodingMachine_Middlewares_CsrfHeaderCheckMiddleware->setCode('return \\TheCodingMachine\\Middlewares\\CsrfHeaderCheckMiddlewareFactory::createDefault(explode(\',\', CSRF_ALLOWED_DOMAIN_NAMES));');$anonymousSapiStreamEmitter = $moufManager->createInstance('Zend\\HttpHandlerRunner\\Emitter\\SapiStreamEmitter');
        $anonymousToCondition = $moufManager->createInstance('Mouf\\Utils\\Common\\Condition\\ToCondition');
        $anonymousVariable = $moufManager->createInstance('Mouf\\Utils\\Value\\Variable');
        $anonymousToCondition2 = $moufManager->createInstance('Mouf\\Utils\\Common\\Condition\\ToCondition');
        $anonymousVariable2 = $moufManager->createInstance('Mouf\\Utils\\Value\\Variable');

// Let's bind instances together.
        if (!$Zend_HttpHandlerRunner_RequestHandlerRunner->getConstructorArgumentProperty('handler')->isValueSet()) {
            $Zend_HttpHandlerRunner_RequestHandlerRunner->getConstructorArgumentProperty('handler')->setValue($Mouf_Mvc_Splash_MiddlewarePipe);
        }
        if (!$Zend_HttpHandlerRunner_RequestHandlerRunner->getConstructorArgumentProperty('emitter')->isValueSet()) {
            $Zend_HttpHandlerRunner_RequestHandlerRunner->getConstructorArgumentProperty('emitter')->setValue($anonymousSapiStreamEmitter);
        }
        if (!$Zend_HttpHandlerRunner_RequestHandlerRunner->getConstructorArgumentProperty('serverRequestFactory')->isValueSet()) {
            $Zend_HttpHandlerRunner_RequestHandlerRunner->getConstructorArgumentProperty('serverRequestFactory')->setValue('return [\\Zend\\Diactoros\\ServerRequestFactory::class, \'fromGlobals\'];');
            $Zend_HttpHandlerRunner_RequestHandlerRunner->getConstructorArgumentProperty('serverRequestFactory')->setOrigin("php");
        }
        if (!$Zend_HttpHandlerRunner_RequestHandlerRunner->getConstructorArgumentProperty('serverRequestErrorResponseGenerator')->isValueSet()) {
            $Zend_HttpHandlerRunner_RequestHandlerRunner->getConstructorArgumentProperty('serverRequestErrorResponseGenerator')->setValue('return function (\\Throwable $e) {
    $generator = new \\Zend\\Stratigility\\Middleware\\ErrorResponseGenerator();
    return $generator($e, new \\Zend\\Diactoros\\ServerRequest(), new \\Zend\\Diactoros\\Response());
};');
            $Zend_HttpHandlerRunner_RequestHandlerRunner->getConstructorArgumentProperty('serverRequestErrorResponseGenerator')->setOrigin("php");
        }
        if (!$Mouf_Mvc_Splash_MiddlewarePipe->getConstructorArgumentProperty('middlewares')->isValueSet()) {
            $Mouf_Mvc_Splash_MiddlewarePipe->getConstructorArgumentProperty('middlewares')->setValue(array(0 => $Mouf_Mvc_Splash_Routers_ExceptionRouter, 1 => $whoopsConditionMiddleware, 2 => $Mouf_Mvc_Splash_Routers_PhpVarsCheckRouter, 3 => $Mouf_Mvc_Splash_CsrfHeaderConditionMiddleware, 4 => $Middlewares_JsonPayload, 5 => $TheCodingMachine_Splash_Routers_SplashRouter, 6 => $Mouf_Mvc_Splash_Routers_NotFoundRouter, ));
        }
        if (!$Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('template')->isValueSet()) {
            $Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('template')->setValue($bootstrapTemplate);
        }
        if (!$Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('contentBlock')->isValueSet()) {
            $Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('contentBlock')->setValue($block_content);
        }
        if (!$Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('debugMode')->isValueSet()) {
            $Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('debugMode')->setValue('DEBUG');
            $Mouf_Mvc_Splash_Controllers_HttpErrorsController->getConstructorArgumentProperty('debugMode')->setOrigin("config");
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
        if (!$TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('container')->isValueSet()) {
            $TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('container')->setValue('return $container;');
            $TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('container')->setOrigin("php");
        }
        if (!$TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('routeProviders')->isValueSet()) {
            $TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('routeProviders')->setValue(array(0 => $Mouf_Mvc_Splash_Services_MoufExplorerUrlProvider, ));
        }
        if (!$TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('parameterFetcherRegistry')->isValueSet()) {
            $TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('parameterFetcherRegistry')->setValue($TheCodingMachine_Splash_Services_ParameterFetcherRegistry);
        }
        if (!$TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('cachePool')->isValueSet()) {
            $TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('cachePool')->setValue($splashCachePool);
        }
        if (!$TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('log')->isValueSet()) {
            $TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('log')->setValue($psr_errorLogLogger);
        }
        if (!$TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('mode')->isValueSet()) {
            $TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('mode')->setValue('strict');
        }
        if (!$TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('debug')->isValueSet()) {
            $TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('debug')->setValue('DEBUG');
            $TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('debug')->setOrigin("config");
        }
        if (!$TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('rootUrl')->isValueSet()) {
            $TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('rootUrl')->setValue('return ROOT_URL;');
            $TheCodingMachine_Splash_Routers_SplashRouter->getConstructorArgumentProperty('rootUrl')->setOrigin("php");
        }
        if (!$TheCodingMachine_Splash_Routers_SplashRouter->getSetterProperty('setHttp400Handler')->isValueSet()) {
            $TheCodingMachine_Splash_Routers_SplashRouter->getSetterProperty('setHttp400Handler')->setValue($Mouf_Mvc_Splash_Controllers_HttpErrorsController);
        }
        if (!$TheCodingMachine_Splash_Services_ParameterFetcherRegistry->getConstructorArgumentProperty('parameterFetchers')->isValueSet()) {
            $TheCodingMachine_Splash_Services_ParameterFetcherRegistry->getConstructorArgumentProperty('parameterFetchers')->setValue(array(0 => $TheCodingMachine_Splash_Services_SplashRequestFetcher, 1 => $TheCodingMachine_Splash_Services_SplashRequestParameterFetcher, ));
        }
        if (!$TheCodingMachine_Splash_Services_ControllerRegistry->getConstructorArgumentProperty('controllerAnalyzer')->isValueSet()) {
            $TheCodingMachine_Splash_Services_ControllerRegistry->getConstructorArgumentProperty('controllerAnalyzer')->setValue($TheCodingMachine_Splash_Services_ControllerAnalyzer);
        }
        if (!$TheCodingMachine_Splash_Services_ControllerRegistry->getConstructorArgumentProperty('controllers')->isValueSet()) {
            $TheCodingMachine_Splash_Services_ControllerRegistry->getConstructorArgumentProperty('controllers')->setValue(array());
        }
        if (!$TheCodingMachine_Splash_Services_ControllerRegistry->getConstructorArgumentProperty('controllerDetector')->isValueSet()) {
            $TheCodingMachine_Splash_Services_ControllerRegistry->getConstructorArgumentProperty('controllerDetector')->setValue($Mouf_Mvc_Splash_Services_MoufControllerExplorer);
        }
        if (!$TheCodingMachine_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('container')->isValueSet()) {
            $TheCodingMachine_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('container')->setValue('return $container;');
            $TheCodingMachine_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('container')->setOrigin("php");
        }
        if (!$TheCodingMachine_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('parameterFetcherRegistry')->isValueSet()) {
            $TheCodingMachine_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('parameterFetcherRegistry')->setValue($TheCodingMachine_Splash_Services_ParameterFetcherRegistry);
        }
        if (!$TheCodingMachine_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('annotationReader')->isValueSet()) {
            $TheCodingMachine_Splash_Services_ControllerAnalyzer->getConstructorArgumentProperty('annotationReader')->setValue($annotationReader);
        }
        if (!$whoopsConditionMiddleware->getConstructorArgumentProperty('condition')->isValueSet()) {
            $whoopsConditionMiddleware->getConstructorArgumentProperty('condition')->setValue($anonymousToCondition);
        }
        if (!$whoopsConditionMiddleware->getConstructorArgumentProperty('ifMiddleware')->isValueSet()) {
            $whoopsConditionMiddleware->getConstructorArgumentProperty('ifMiddleware')->setValue($whoopsMiddleware);
        }
        if (!$Mouf_Mvc_Splash_CsrfHeaderConditionMiddleware->getConstructorArgumentProperty('condition')->isValueSet()) {
            $Mouf_Mvc_Splash_CsrfHeaderConditionMiddleware->getConstructorArgumentProperty('condition')->setValue($anonymousToCondition2);
        }
        if (!$Mouf_Mvc_Splash_CsrfHeaderConditionMiddleware->getConstructorArgumentProperty('ifMiddleware')->isValueSet()) {
            $Mouf_Mvc_Splash_CsrfHeaderConditionMiddleware->getConstructorArgumentProperty('ifMiddleware')->setValue($TheCodingMachine_Middlewares_CsrfHeaderCheckMiddleware);
        }
        $anonymousToCondition->getConstructorArgumentProperty('value')->setValue($anonymousVariable);
        $anonymousVariable->getConstructorArgumentProperty('value')->setValue('DEBUG');
        $anonymousVariable->getConstructorArgumentProperty('value')->setOrigin("config");
        $anonymousToCondition2->getConstructorArgumentProperty('value')->setValue($anonymousVariable2);
        $anonymousVariable2->getConstructorArgumentProperty('value')->setValue('ENABLE_CSRF_PROTECTION');
        $anonymousVariable2->getConstructorArgumentProperty('value')->setOrigin("config");

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
