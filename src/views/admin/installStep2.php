<?php /* @var $this SplashInstallController */ ?>
<h1>Setting up Splash</h1>

<p>By clicking the link below, you will automatically generate the RootController for your application. This controller will be written in the directory you write below.
The directory is relative to the root of your web application.</p>

<form action="generate" method="post" class="form-horizontal">
<input type="hidden" id="selfedit" name="selfedit" value="<?php echo plainstring_to_htmlprotected($this->selfedit) ?>" />

<div class="control-group">
	<label class="control-label">Source code directory:</label>
	<div class="controls">
		<input type="text" name="sourcedirectory" value="<?php echo plainstring_to_htmlprotected($this->sourceDirectory) ?>"></input>
		<span class="help-block">This is the directory containing your source code (it should be configured in your the "autoload" section of your <em>composer.json</em> file.</span>
	</div>
</div>
<div class="control-group">
	<label class="control-label">Main controllers namespace:</label>
	<div class="controls">
		<input type="text" name="controllernamespace" value="<?php echo plainstring_to_htmlprotected($this->controllerNamespace) ?>"></input>
		<span class="help-block">The default namespace for the controllers. Be sure to type a namespace that is registered in the "autoload" section of your <em>composer.json</em> file. Otherwise, the composer autoloader will fail to load your classes.</span>
	</div>
</div>
<div class="control-group">
	<label class="control-label">Main views directory:</label>
	<div class="controls">
		<input type="text" name="viewdirectory" value="<?php echo plainstring_to_htmlprotected($this->viewDirectory) ?>"></input>
		<span class="help-block">The default directory for the views. This is relative to the source code directory</span>
	</div>
</div>

<div class="control-group">
	<div class="controls">
		<button name="action" value="generate" type="submit" class="btn btn-danger">Install Splash</button>
	</div>
</div>
</form>