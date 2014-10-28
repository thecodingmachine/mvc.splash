URL routing
=============================

Since version 5.0, splash has implemented a routing system based on [stackphp's](http://stackphp.com/) recommendations around the [HttpKernelInterface](https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpKernel/HttpKernelInterface.php).

The basics
---------------------

Splash now has a "router stack", which means Splash the instance will delegate the Request to the first router that will handle it, and eventually foward it to the next one (the "fallback router").
**Note:** A router is not inevitably the final handler of the request. Some router can, in fact, be considered as filters.

Splash's default router stack implementation
-------------------
Here is the splash instance view after installaion step :

![Default splash instance view](https://raw.githubusercontent.com/thecodingmachine/mvc.splash/5.0/doc/images/splash_instance.png "Default splash instance view")

If you look closer at the stack, here is what you will find out :

```php
ExceptionRouter // Surround the router stack with a try/catch statement, and handle Exceptions display
  |
  PhpVarsCheckRouter // Checks if max_input_vars and max_post_size have not been exeeded
    |
    SplashDefaultRouter // Main router (will find a matching Route (controller / actions), and return the HTML
      |
      NotFoundRouter // No router has been able to handle teh Request, return a 404 response
```

The Exception router should always be the fist one as it surrounds the underlying router stack calls for catching exceptions
The PhpVarsCheck router sould be placed at the begining of the stack, and at least before the "efective" routers
The NotFoundRouter sould always be the last one.

Adding your own router into the stack
------------------
To do so, you just need to create an instance that extends the HttpKernelInterface, and put the router where you want to.

