<?php
namespace Mouf\Mvc\Splash\Controllers;

/**
 * Classes implementing this interface can be used when a HTTP 500 error is triggered.
 *
 * The class must be registered in the "splash" instance to be called.
 *
 * @author David Négrier
 */
interface Http500HandlerInterface
{

    /**
     * This function is called when a HTTP 404 error is triggered by the user.
     *
     * @param \Exception $exception
     */
    public function serverError(\Exception $exception);
}
