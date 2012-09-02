<h1>Filters</h1>

<h2>Using filters</h2>

<p>Splash supports the notion of <em>"Filter"</em>. A filter is a piece of code that can be run before or after an action.</p>
<p>There could be many reason why you want to run a filter:</p>
<ul>
  <li>Check a user is logged before starting an action</li>
  <li>Check the action is run on an SSL channel</li>
  <li>Initialize some frameworks, ...</li>
</ul>

<p>Below is a sample filter:</p>
<pre>
/**
 * @Action
 * @Logged
 * @RequireHttps("yes")
 */
function deleteUser($password) { ... }
</pre>

<p>This sample provides 2 filters:</p>
<ul>
  <li><b>@Logged</b> is used by Splash to check that the user is logged. If not, the user is sent to the login page.</li>
  <li><b>@RequireHttps</b> is used by Splash to make sure the action is run on an HTTPS channel. If not, an error message is displayed.</li>
</ul>

<p>Please note the <b>@RequireHttps</b> annotation accepts one parameter. This parameter can be:</p>
<ul>
 <li>By passing @RequireHttps("yes"), an Exception is thrown if the action is called in HTTP.</li>
 <li>By passing @RequireHttps("no"), no test is performed.</li>
 <li>By passing @RequireHttps("redirect"), the call is redirected to HTTPS. This does only work with GET requests.</li>
</ul>

<p>There is a third default filter worth mentionning:</p>
<p>The <b>@RedirectToHttp</b> filter will bring the user back to HTTP if the user is in HTTPS. The port can be specified in parameter if needed. The filter
works only with GET requests. If another type of request is performed (POST), an exception will be thrown.</p>

<h2>Developing your own filters</h2>

<p>You can of course develop your own filters. Your filters should extend the AbstractFilter class, and you should register your filters using the command:</p>
<pre>FilterUtils::registerFilter("MyFilterClassName");</pre>