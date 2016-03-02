Welcome to the Splash MVC framework
===================================

What is Splash?
---------------

Splash is a MVC PHP framework. It is making a heavy use of annotations, and of [the Mouf dependency injection framework](http://www.mouf-php.com).
You might want to use Splash in order to separate cleanly the controllers (that perform the actions required when you navigate your web application) and the view (that generates and displays the HTML that makes your web pages).

Installation
------------

[See Installation instructions](doc/install.md)

Getting started
---------------

##[Check out the video tutorial!](doc/writing_controllers.md)

The first thing you should learn when using Splash is how to write a controller.
Read the [writing controllers guide](doc/writing_controllers.md).

Advanced topics
---------------

- The [managing URL parameters page](doc/url_parameters.md) explains how to map parameters from the request to parameters of the action.
- The [views section](doc/views.md) explains how Splash does (not) handle the views.
- The [route handling page](doc/url_routing.md) explains how Splash will route a request, and especially HTTP 404/500 error messages.
- The [filters guide](doc/filters.md) explains how to use and write filters in your actions.
