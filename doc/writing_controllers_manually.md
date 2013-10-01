Writing controllers, manually
=============================

In this document, we will see how to create a controller without using the controller creation wizard.
You will also learn more about what makes a controller.

What is a controller?
---------------------

In Splash, a controller is a class that contains a number of _Actions_.
_Actions_ are methods that can be directly accessed from the browser.

There are several ways to declare a method to be an action. The most common ways are:
 - The *@URL* annotation</li>
 - The *@Action* annotation</li>


The @URL annotation
-------------------

This is the preferred way of declaring an action:

```php
<?php
namespace Test\Controllers;

use Mouf\Mvc\Splash\Controllers\Controller;

/**
 * This is my test controller.
 */
class MyController extends Controller {
	
	/**
	 * My first action.
	 *
	 * @URL /path/to/my/action
	 * @param string $var1
	 * @param string $var2
	 */
	public function my_url($var1, $var2) {
		 echo "<html><head></head>";
		 echo "<body>";
		 echo "var1 value is ".htmlentities($var1)." and var2 value is ".htmlentities($var2);
		 echo "</body>";
	}
}
?>
```

Note: this class must respect the PSR-0 in ordre to be reachable by composer's autoload mechnism.
Therefore, if you defined the "Test" namespace to be reachable from the directory "src", 
the filename for this class has to be "src/Test/Controllers/MyController.php".

First thing you can see: the MyController class extends the *Controller* class provided by Splash.

The *@URL* annotation points to the web path the action is bound to.

The action takes 2 parameters: var1 and var2. This means that the page needs both parameters passed 
either in GET or POST.

In order to test this, we must first create an instance of the controller in Mouf.
We will do this using the Mouf User Interface.

First, click the green *Purge code cache* button in Mouf's menu. This will make sure Mouf will scan the source code
directory and find our new "MyController" class.
Now, click the *Instances / Create a new instance* menu item, and fill the instance details.

![Create an instance](https://raw.github.com/thecodingmachine/mvc.splash/4.0/doc/images/create_instance.png)

In this sample, we are creating a "myController" instance whose class is "MyController".

*Troubleshooting:* For a number of reasons, the MyController class might not appear in the list of classes.
Here is a list of actions you can take to understand where the problem comes from:
 1- Be sure you purged the code cache, and refresh the page.
 2- If the class does not appear, it is likely there is a problem. In the Mouf's "Project" menu, select *Analyze classes*. Try to find your class in the list. Mouf will notify you if it sees an error in your class. 
 3- If your class does not appear at all in the *Analyze classes* page, it is likely that the Composer autoloader cannot find your class. Double check the namespace, the file name, the directory name and your autoload settings in *composer.json*. Also, run the "php composer dumpautoload" to be sure Composer regenerates its autoloader.

We just created a new controller, that contains a new route to an action. Each time a route is created in Splash,
it is whise to purge the cache. So just press the big red "Purge cache" button.  

Now, let's test our code.
By browsing to http://localhost/{my_app}/path/to/my/action?var1=42&var2=24, we should see the message displayed!

Done? Then let's move on! 
 
The @Get / @Post annotations
----------------------------

We might decide that an action should always be called via GET, or via POST (or PUT or DELETE if you want to provide REST services).
Splash makes that very easy to handle. You can just add a @Get or @Post annotation (or @Put or @Delete). Here is a sample:

```php
<?php
namespace Test\Controllers;

use Mouf\Mvc\Splash\Controllers\Controller;

/**
 * This is a sample user controller.
 *
 */
class UserController extends Controller {
	
	/**
	 * Viewing the user is performed by a @Get.
	 *
	 * @URL /user
	 * @Get
	 * @param string $id
	 */
	public function viewUser($id) {
		 echo "Here, we might put the form for user ".htmlentities($id);
	}

	/**
	 * Modifying the user is performed by a @Post.
	 *
	 * @URL /user
	 * @Post
	 * @param string $id
	 * @param string $name
	 * @param string $email
	 */
	public function editUser($id, $name, $email) {
		 echo "Here, we might put the code to change the user object.";
	}

}
```

In the exemple above (a sample controller to view/modify users), the "/user" URL is bound to 2 different methods
based in the HTTP method used to access this URL.

Parameterized URLs
------------------

You can put parameters in the URLs and fetch them very easily:

```php
<?php
/**
 * This is a sample user controller.
 *
 */
class UserController extends Controller {
	
	/**
	 * Viewing the user is performed by a @Get.
	 *
	 * @URL /user/{id}/view
	 * @Get
	 * @param string $id
	 */
	public function viewUser($id) {
		 echo "Here, we might put the form for user ".htmlentities($id);
	}
}
?>
```

Do you see the @URL annotation? The {id} part is a placeholder that will be replaced by any value found in the URL.
So for instance, if you access http://[server]/[appname]/user/42/view, the $id parameter will be filled with "42". 

The @Action annotation
----------------------

The @Action parameter can replace the @URL parameter.
You simply put a @Action annotation in your method. The URLs to access a @Action method are always:

	http://[server-url]/[webapp-path]/[mouf-controller-instance-name]/[action-name]?[action-parameters]

Here is a sample:

```php
<?php
/**
 * This is my test controller.
 *
 */
class MyController extends Controller {
	
	/**
	 * My first action.
	 *
	 * @Action
	 * @param string $var1
	 * @param string $var2
	 */
	public function my_action($var1, $var2) {
		 echo "<html><head></head>";
		 echo "<body>";
		 echo "var1 value is ".htmlentities($var1)." and var2 value is ".htmlentities($var2);
		 echo "</body>";
	}
}
?>
```

The *my_action* method is a Splash action. You know this because there is a @Action annotation in the PHPDoc comment of the method.

Now, we can access the example page using this URL:
	http://[server-url]/[webapp-path]/my_controller/my_action?var1=42&var2=toto

Default actions
---------------

Sometimes, when using @Action annotations, we might want to have a URL that is a bit shorter than /my_webapp/my_controller/my_action.
Splash supports a special method called "index". If no action is provided in the URL, the index method will be called instead.

```php
<?php
/**
 * This is my test controller.
 *
 * @Component
 */
class MyController extends Controller {
	
	/**
	 * The action called if no action is provided in the URL.
	 *
	 * @Action
	 */
	public function index() {
		 echo "This is the index";
	}
}
?>
```

The test page can be accessed using the URL:
	http://[server-url]/[webapp-path]/my_controller/.

