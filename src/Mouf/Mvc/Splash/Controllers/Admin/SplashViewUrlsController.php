<?php

namespace Mouf\Mvc\Splash\Controllers\Admin;

use Mouf\ClassProxy;
use \Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Mvc\Splash\Services\SplashUrlsExporter;

/**
 * The controller that will display all the URLs managed by Splash.
 *
 * @Component
 */
class SplashViewUrlsController extends Controller
{
    /**
     * The template used by the Splash page.
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

    protected $splashUrlsList;
    protected $selfedit;

    /**
     * Displays the config page.
     *
     * @Action
     */
    public function defaultAction($selfedit = 'false')
    {
        $this->selfedit = $selfedit;

        $exporter = new ClassProxy(SplashUrlsExporter::class);
        $this->splashUrlsList = $exporter->exportRoutes();

        $this->content->addFile(__DIR__.'/../../../../../views/admin/splashUrlsList.php', $this);
        $this->template->toHtml();
    }
}
