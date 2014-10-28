Settings and error handling 
---------------------------

You can configure Splash using the "splash" instance. Here is what the "splash" instance looks by default:
![Splash instance](https://raw.githubusercontent.com/thecodingmachine/mvc.splash/4.3/doc/images/splash_instance.png)

The Splash class requires 2 properties to be filled:
 - *log*: This is the logger used by Splash.
 - *http404Handler*: The class in charge of rendering HTTP 404 errors. Bind to your own class to override default behaviour.
 - *http500Handler*: The class in charge of rendering HTTP 500 errors. Bind to your own class to override default behaviour.
 - *cacheService*: The cache service used to store the routes.
 - *supportsHttps*: Whether your server should support HTTPS or not.
 
Note: the DEBUG_MODE config constant is used to decide whether stack traces should be displayed or not in case an exception is thrown.

If you take a look at the *http404Handler* and *http500Handler*, you will notice they use a template, based on Bootstrap.
You can of course change this template to your own template or a special template in order to provide a consistant user experience.
