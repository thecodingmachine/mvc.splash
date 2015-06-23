Migrating from Splash 5 to Splash 7
-----------------------------------

In order to upgrade from Splash 5 to Splash 7, you need to perform the following steps:

- Update "mouf/mvc.splash" version to "~7.0" in your `composer.json` file.
- Run `php composer.phar update`
- Connect to Mouf UI (http://localhost/[yourproject]/vendor/mouf/mouf)
- Click on *Project > Installation tasks*
- There are 2 install tasks for "mouf/mvc.splash". Locate those in the table.
- Click on the **Reinstall** button for both tasks.
- In your controllers, stop using the `Request` and `Response` classes and start using PSR-7's `ServerRequestInterface` and `ResponseInterface`

You are done. Enjoy [the new features](http://mouf-php.com/stackphp-support-added-to-splash)!


Hey! What about Splash 6?
Mmmm... there was never a stable Splash 6. Splash 6 is a release targeting Mouf 2.1 that is not released as we write these lines.

Migrating from Splash 4 to Splash 5
-----------------------------------

In order to upgrade from Splash 4 to Splash 5, you need to perform the following steps:

- Update "mouf/mvc.splash" version to "~5.0" in your `composer.json` file.
- Run `php composer.phar update`
- Connect to Mouf UI (http://localhost/[yourproject]/vendor/mouf/mouf)
- Click on *Instances > View declared instances*
- Look for the "splash" instance.
- Click on it, then click on the "Delete" button
- Click on *Project > Installation tasks*
- There are 2 install tasks for "mouf/mvc.splash". Locate those in the table.
- Click on the **Reinstall** button for both tasks.

You are done. Enjoy [the new features](http://mouf-php.com/stackphp-support-added-to-splash)!