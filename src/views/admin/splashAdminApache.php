<h1>Splash Apache Configuration</h1>

<p>This page helps you create the <em>.htaccess</em> file that is used by Splash to manage the redirection of web pages.</p>
<br/>
<p>
By default, some file extetions are excluded by the rewrite rule : "js", "ico", "gif", "jpg", "png", "css". The "mouf" and "plugins" folders are also excluded. However, you are free to configure those exclusions as you want: just write each extention or folder name that you want to exclude in the textareas below (one line for each extention/folder):
</p>
<br/>
<div class="warn"><u>WARNING</u> : .HTACESS file is handling all the redirections on your site, so please be ver carefull when editing this file!</div>
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
<input type="hidden" name="selfedit" value="<?php echo plainstring_to_htmlprotected(isset($_REQUEST["selfedit"])?$_REQUEST["selfedit"]:"false") ?>" />
<button>Write file</button>
</form>