<?php /* @var $this TdbmInstallController */ ?>
<h1>Setting up Splash</h1>

<p>This wizard will help you setting up the Splash MVC framework.</p>
<p>The Splash install procedure will create a "splash" instance. It will then write a ".htaccess" file to redirect relevant calls to Splash.
Finally, it will create a "RootController" controller class that will be in charge of the root of your website.</p>

<p>You can bypass this install procedure and do those step yourself by clicking the "Skip" button.</p>

<form action="configure">
	<input type="hidden" name="selfedit" value="<?php echo $this->selfedit ?>" />
	<button>Configure Splash</button>
</form>
<form action="skip">
	<input type="hidden" name="selfedit" value="<?php echo $this->selfedit ?>" />
	<button>Skip</button>
</form>