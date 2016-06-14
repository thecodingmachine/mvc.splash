<h1>Splash Apache Configuration</h1>

<p>This page helps you create the <code>.htaccess</code> file that is used by Splash to manage the redirection of web pages.</p>
<br/>
<p>
By default, some file extensions are excluded by the rewrite rule : "js", "ico", "gif", "jpg", "png", "css". The "vendor" folder is also excluded. However, you are free to configure those exclusions as you want: just write each extension or folder name that you want to exclude in the textareas below (one line for each extension/folder):
</p>
<br/>
<div class="warn"><strong>WARNING</strong> : the <code>.htaccess</code> file is handling all the redirects on your site, so please be very careful when editing this file!</div>
<br/>
<form action="write" method="post">
<div>
<label>
Extention rewrite exclusions
</label>
<textarea rows="10" name="textExtentions"><?php echo plainstring_to_htmlprotected(implode("\r\n", $this->exludeExtentions)) ?></textarea>

</div>
<div>
<label>
Folders rewrite exclusions
</label>
<textarea rows="5" name="textFolders"><?php echo plainstring_to_htmlprotected(implode("\r\n", $this->exludeFolders)) ?></textarea>

</div>
<input type="hidden" name="selfedit" value="<?php echo plainstring_to_htmlprotected(isset($_REQUEST['selfedit']) ? $_REQUEST['selfedit'] : 'false') ?>" />
<button class="btn btn-danger">Write file</button>
</form>
