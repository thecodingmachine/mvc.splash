Managing URL parameters
=======================

Default behaviour
-----------------

By default, when an action is created, each parameter of the function should be passed in the URL. Here is a sample:

```php
/**
 * My action with 2 compulsory parameters.
 *
 * @URL /test
 * @param string $var1
 * @param string $var2
 */
public function my_action($var1, $var2) { ... } 
```

In this action, both parameters are compulsory. If one of the parameters is not passed by the user, an error message is displayed.
Hopefully, you can get optional parameters using parameters default values:

```php
/**
 * My action with 1 compulsory parameter and one optional.
 *
 * @URL /test
 * @param string $var1
 * @param string $var2
 */
public function my_action($var1, $var2 = 42) { ... } 
```

In this sample, if the user does not pass the "var2" parameter in the URL, it will be equal to 42.
The URL might be: <code>http://[server-url]/[webapp-path]/test?var1=param1</code>


<!--
<h3>Parameters' origin</h3>
<p>In an action, you can pass additional annotations in order to modify the default mapping of parameters.</p>
<p>Here is a sample:</p>
<pre>
/**
 * My action with 1 compulsory parameter and one optional.
 *
 * @Action
 * @param int $userId (origin="request[user_id]")
 */
public function my_action($userId) { ... } 
</pre>

<p>The <code>@param</code> annotation is used to modify the default mapping.</p>
<p>The line <code>@param int $userId (origin="request[user_id]")</code> means:<br/>
Map argument "$userId" in the action to request parameter user_id, and must be an int value. Therefore, the URL to access the page will be: <code>http://[server-url]/[webapp-path]/my_controller/my_action?user_id=12</code></p>

<p>The "origin" in @param annotation can be:</p>
<ul>
  <li><b>request</b> (for instance: <code>@param int $userId (origin="request[user_id]")</code>). Get the parameter from the request</li>
  <li><b>session</b> (for instance: <code>@param int $userId (origin="session[user_id]")</code>). Get the parameter from the session</li>
  <li><b>url</b> (for instance: <code>@param int $userId (origin="url[0]")</code>). Get the parameter from the path in the URL. For instance, if the path is <code>http://[server-url]/[webapp-path]/my_controller/my_action/42</code>, the $userId will be 42.</li>


<p>You can also chain several origins. For instance:<br/>
<code>@param int $userId (origin="request[user_id]/session[user_id]")</code> will fetch the result from the user_id parameter in the request. If no such parameter exist, it will try
to fetch the parameter from the session.</p>
-->

Parameters' type
----------------

In classic PHP behaviour, you the <code>@param [type] $var</code> annotation only informs the user of the expected type of the parameter.
Using Splash, this annotation means a lot more because Splash will throw an Excpetion if the variable has an unexpected type.
For example, if you use the annotation <code>@param int $userId</code>, and the variable is in fact a string ('toto' for instance), an exception will be raised and an HTTP 500 page displayed.

Allowed parameter types are:

 - string
 - int
 - float
 - bool
 - array

*Note:* with the current version, if a parameter is not valid, an error screen is displayed. You cannot catch this error to provide custom behaviour in the current version.
However, you can override the way HTTP 500 errors are displayed (see the "Settings and error handling" part of this documentation).


Injection PSR-7 Request object as a parameter
---------------------------------------------

Splash 7+ has native support for PSR-7 RequestInterface and for ServerRequestInterface objects. The chosen implementation of these interface is the `ServerRequest` of Zend-Diactoros (https://github.com/zendframework/zend-diactoros/blob/master/doc/book/api.md#serverrequest-message)

This means that Splash will automatically inject a `ServerRequest` object into your action if your action expects a `RequestInterface` or a `ServerRequestInterface`  object:

```php
use Psr\Http\Message\ServerRequestInterface;
...

/**
 * My action with the request object filled
 *
 * @URL /test
 * @param ServerRequestInterface $request
 */
public function my_action(ServerRequestInterface $request) {
	$param = $request->getQueryParams()['param'];
	...
} 
```

Note: you should use the `ServerRequest` object instead of accessing directly `$_FILES`, `$_SERVER`, `$_COOKIES`, or HTTP headers.

<!--
<h3>Validators</h3>

<p>Splash can also provide validators for each parameters. A validator is a piece of code that will check the format of a parameter.
For instance, you can check that a parameter is a number or that a parameter is an e-mail address.</p>


<pre>
/**
 * @Action
 * @param string $email (origin="request[user_email]", validator="Email")
 */
public function sendMail($email) { ... }
</pre>

<p>In the sample above, the "user_email" parameter passed by the web page must be an Email.</p>

<p>Splash provides two filters:</p>
<ul>
  - Number: validates the value is a number
  - Email: validates the value is an email address

<p>You can provide your own validators by extending the ValidatorInterface.</p>
<p>
<b><u>Note:</u></b> In the previous version of Splash, the annotation @param was replaced by the @Var annotation, for instance:
<pre>@Var{email}(origin="request[user_email]", validator="Email")</pre>
<em>
As you see, the only differences are:
<ul>- <code>@param</code> is part of the PHP Doc autogenerated comments
- you no longer write the variable <code>{var_name}</code>, but <code>$var_name</code> directly
- there is a space between variable name and optional Origin and Validator settings.

</em>
</p>
-->
