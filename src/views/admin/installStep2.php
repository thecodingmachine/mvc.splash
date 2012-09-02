<?php /* @var $this SplashInstallController */ ?>
<h1>Setting up Splash</h1>

<p>By clicking the link below, you will automatically generate the RootController for your application. This controller will be written in the directory you write below.
The directory is relative to the root of your web application.</p>

<form action="generate" method="post">
<input type="hidden" id="selfedit" name="selfedit" value="<?php echo plainstring_to_htmlprotected($this->selfedit) ?>" />

<div>
<label>Main controllers directory:</label><input type="text" name="controllerdirectory" value="<?php echo plainstring_to_htmlprotected($this->controllerDirectory) ?>"></input>
</div>
<div>
<label>Main views directory:</label><input type="text" name="viewdirectory" value="<?php echo plainstring_to_htmlprotected($this->viewDirectory) ?>"></input>
</div>

<div>
	<button name="action" value="generate" type="submit">Install Splash</button>
</div>
</form>