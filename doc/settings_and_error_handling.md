Configuring Splash
------------------

You can configure Splash using the "splash" instance. The Splash class requires 2 properties to be filled:
 - *defaultTemplate*: This is the default HTML template that Splash will use to display error messages.
 - *log*: This is the logger used by Splash.
TODO: change this

Hopefully, Splash comes with a template (the BootstrapTemplate, based on Twitter Bootstrap) and a logger (named ErrorLogLogger). The ErrorLogLogger writes logs to the default php_error.log file
(using the error_log PHP function).

<p>The install process binds by default the SplashTemplate and the ErrorLogLogger to Splash. If you want to change the default template,
you can just bind another template (or write your own). The default template is used on all HTTP 404 and 500 pages.</p>

<img src="images/configure_splash.jpg" alt="" />
