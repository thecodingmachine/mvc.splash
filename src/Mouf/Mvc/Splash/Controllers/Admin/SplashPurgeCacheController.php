<?php

namespace Mouf\Mvc\Splash\Controllers\Admin;

use Mouf\InstanceProxy;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Mvc\Splash\Routers\SplashDefaultRouter;

/**
 * The controller that will purge the URLs cache.
 *
 * @Component
 */
class SplashPurgeCacheController extends Controller
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

    /**
     * Displays the config page.
     *
     * @Action
     * @Logged
     */
    public function defaultAction($selfedit = 'false')
    {
        $splashProxy = new InstanceProxy(SplashDefaultRouter::class, $selfedit == 'true');
        $splashProxy->purgeUrlsCache();

        $this->content->addFile(__DIR__.'/../../../../../views/admin/purgedCache.php', $this);
        $this->template->toHtml();
    }
}
