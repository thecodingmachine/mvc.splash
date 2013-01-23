Splash installation guide
=========================

Dependencies
------------

Splash comes as a *Composer* package and requires the "Mouf" framework to run.
The first step is therefore to [install Mouf](http://www.mouf-php.com/).

Once Mouf is installed, you can process to the Splash installation.

Requirements
------------

For Splash to work, you will need an Apache server, with the *rewrite_module* enabled.

Install Splash
--------------

Edit your *composer.json* file, and add a dependency on *mouf/mvc.splash*.

A minimal *composer.json* file might look like this:

	{
	    "require": {
	        "mouf/mouf": "~2.0",
	        "mouf/mvc.splash": "~4.0"
	    },
	    "autoload": {
	        "psr-0": {
	            "Test": "src/"
	        }
	    },
	    "minimum-stability": "dev"
	}

As explained above, Splash is a package of the Mouf framework. Mouf allows you (amoung other things) to visualy "build" your project's dependencies and instances.

To install the dependency, run
	php composer.phar install

This *composer.json* file assumes that you will put your code in the "src" directory, and that you will use the "Test" namespace and respect the PSR-0 naming scheme.
Be sure to create those directories (src/Test) before running the install process.
If you do not understand what "namespace" or "PSR-0" means, *stop right now*, and head over the [autoloading section of Composer](http://getcomposer.org/doc/01-basic-usage.md#autoloading) and the [PSR-0 documentation](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).
	
At this point, the Splash packages should be downloaded and installed (and Mouf should be set up). Start the Mouf admin interface at http://localhost/{yourproject}/vendor/mouf/mouf
There is an install process to run, so just run it.

![Splash install screenshot](https://raw.github.com/thecodingmachine/mvc.splash/4.0/doc/images/install_splash.png)
![Splash install screenshot](https://raw.github.com/thecodingmachine/mvc.splash/4.0/doc/images/install_splash_2.png)

The Splash install process will:
 - Create a "splash" instance of the "Splash" class. The "splash" instance contains the global configuration for Splash (default error handler for 404/500 errors, etc...).
 - Create an Apache .htaccess file that will route the requests to Splash
 - Create a default RootController class to handle the requests to the root of your web application
 - Create a default HTML view for the RootController

The install process does its best to use your namespace for the RootController, and it asks you where the files should go:
![Splash install screenshot](https://raw.github.com/thecodingmachine/mvc.splash/4.0/doc/images/install_splash_3.png)


Configure apache redirection
----------------------------

When Splash is installed, a MVC menu appears in Mouf.<br/>

<img src="images/splash_menu.png" alt="Splash Menu" /><br/>

The *Configure Apache redirection" menu helps you configuring what files should be handled by Splash and what files should be ignored.
By default, resource files (images, js, css...) are ignored. 

Purging the cache
-----------------

Splash comes with a caching system used to speed it up.
When you modify a route (for instance, when you create a new controller), please be sure to purge the cache (using MVC/Splash/Purge URLs cache or the big "Purge cache" button) to take your modifications into account.
