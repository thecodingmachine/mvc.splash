<h1>Designing a template: HTML and CSS</h1>

<p>In this chapter, we will mostly talk about the HTML and CSS that make a template.
The PHP part that makes the template is discussed <a href="designing_php.xaja.php">here</a>.</p>

<h2>Best practices</h2>

<p>There are a number of good practices you should use when designing your template:</p>

<h3>Use a doctype</h3>
<p>You webpage should contain a doctype. XHTML 1.0 Transitional and HTML 4.01 Transitional are commonly used.</p>

<h3>Use CSS to layout elements of the page</h3>
<p>Avoid using tables to place elements on the page. Use CSS instead. There are plenty of good tutorials on the web for CSS positioning. 
For instance, the <a href="http://www.maxdesign.com.au/presentation/page_layouts/">Maxdesign</a> site has a number of very useful CSS layouts.</p>

<h3>Never use the <em>style</em> attribute</h3>
<p>In your template, please be sure to always use CSS classes or selectors, and never use the <em>style</em> attribute in your HTML code.
Everything that is CSS related should be in a <code>.css</code> file.</p>

<h3>Use common CSS good practices</h3>

<p>You should in particulae reset the CSS stylesheets so that you have a similar experience on every browser. See: <a href="http://www.graphicrating.com/2008/11/15/css-best-practices/">Graphicrating.com CSS best practices</a>.</p>

<h3>Write accurate CSS selectors</h3>

<p>If you write a special class "error" for an error message, and if that class can only be applied to a certain element (for instance a div),
your CSS selector should be <code>div.error {...}</code> instead of <code>.error {...}</code>.</p>

<p>This way, someone reading your CSS will know that the "error" class applies to div elements.</p>

<h3>Use &lt;ul&gt; and &lt;li&gt; tags for menus</h3>

<p>Use &lt;ul&gt; and &lt;li&gt; tags inside menus. There are plenty of useful tutorials about this <a href="http://www.secondpicture.com/tutorials/web_design/css_ul_li_horizontal_css_menu.html">here</a>, 
<a href="http://www.webcredible.co.uk/user-friendly-resources/css/css-navigation-menu.shtml">here</a> or <a href="http://phoenity.com/newtedge/horizontal_nav/">here</a>.</p>

<h3>Write valid HTML code</h3>

<p>The HTML code you write should be W3C valid. Please use the <a href="http://validator.w3.org/">W3C validation service</a> to make sure your template is 100% valid.</p>

<h3>Use HTML IDs for your sections</h3>

<p>For instance, your header should be in a div with ID "header":</p>

<pre>
&lt;div id="header"&gt;
...
&lt;/div&gt;
</pre>

<p>You should have div with those IDs at least: <code>header</code>, <code>left</code>, <code>content</code>, <code>right</code>, <code>footer</code>.</p>

<h3>Use CSS selectors with childs or descendant</h3>

<p>If a paragraph should have a special color in the left menu, instead of creating a CSS class for the paragraphs in the left menu, use a CSS selector with descendants.
For instance, instead of writing:</p>

<codehighlight language="CSS">
/* Please avoid this */
p.left {
	font-color: white
}
</codehighlight>

Please write:

<codehighlight language="CSS">
/* This is the right way to do things */
#left p {
	font-color: white
}
</codehighlight>

<p>This is much cleaner since you won't have to write <code>&lt;p class="leftmenu"&gt;</code> for each paragraph in the left menu.</p>

<h2>Compulsory CSS elements</h2>

<p>This chapter will help you make sure you do not forget any kind of blocks in your CSS.
When designing web-applications, there are a number of blocks that are common and that
should be present in any template CSS. For instance, every web-application should have
a special label to display errors. The list below lists all the CSS class that should
be present in any template.</p>

<p>By standardizing the name of the classes for common use cases, changing an application
template will be an easier task.</p>

<h3>Standard typography</h3>

<p>You will provide styling for standard HTML tags: <code>&lt;h1&gt;</code>, <code>&lt;h2&gt;</code>, <code>&lt;h3&gt;</code>, 
<code>&lt;p&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;a&gt;</code>, <code>&lt;label&gt;</code>, <code>&lt;td&gt;</code>...
</p>

<p>If needed, you will provide special typography for some menus.</p>

<h3>Links</h3>

<p>Links (<code>&lt;a&gt;</code>) should have different styles for there state (<code>:link</code>, <code>:visited</code>, <code>:hover</code>, <code>:active</code>).</p>
<br/>

<h3>Forms</h3>

<p>You should ensure that the form elements have correct styles. You should therefore provide CSS styles for <code>input</code>, <code>fieldset</code>, <code>label</code>, <code>button</code>, <code>select</code> and <code>textarea</code> tags.</p>
<p>For an example of CSS-styled forms, please read <a href="http://www.webcredible.co.uk/user-friendly-resources/css/css-forms.shtml">this article on webcredible.com</a></p>

<p>A typical form with the HTML code below should display correctly:</p>

<pre>
&lt;form action="#"&gt;
	&lt;fieldset&gt;
		&lt;legend&gt;This is my form&lt;/legend&gt;
		&lt;p&gt;&lt;label for="name"&gt;Name&lt;/label&gt; &lt;input type="text" name="name" /&gt;&lt;/p&gt;
		&lt;p&gt;&lt;label for="e-mail"&gt;E-mail&lt;/label&gt; &lt;input type="text" name="e-mail" /&gt;&&lt;/p&gt;
		&lt;p class="submit"&gt;&lt;button type="submit"/&gt;Submit&lt;/button&gt;&lt;/p&gt;
	&lt;/fieldset&gt;
&lt;/form&gt; 
</pre>
<br/><br/>

<h3>Form validation</h3>

<p>Validating a form is a very common task in a webpage. You should provide special style for any form element through the "error" class.</p>
<p>For instance, if a text fieldmust be filled and has failed validation, the HTML will look like this:</p>

<pre>
&lt;p&gt;&lt;label for="name"&gt;Name&lt;/label&gt; &lt;input type="text" name="name" class="error"/&gt;&lt;/p&gt;
</pre>
<br/>
<p>The text field should be rendered in a way that the user knows he must fill the value. This can be a special icon in the background, or a special
color (light red for instance).</p> 

<h3>Warning and error message boxes</h3>

<p>In a web-application, it is very common to display global error or warning message in a box.
Any template should have its own message boxes, with those classes: "info", "success", "warning", "error".</p>
<p>You can find a nice exemple of message boxes on <a href="http://css.dzone.com/news/css-message-boxes-different-me">this article on dzone.com</a></p>

<p>The HTML for message box will be:</p>
<pre>
&lt;div class="info"&gt;Info message&lt;/div&gt;
&lt;div class="success"&gt;Successful operation message&lt;/div&gt;
&lt;div class="warning"&gt;Warning message&lt;/div&gt;
&lt;div class="error"&gt;Error message&lt;/div&gt;
</pre>

<p>Finally, you will add another class for an empty "box". Boxes are useful to put elements into.</p>
<pre>
&lt;div class="box"&gt;
	Some HTML inside the box.
&lt;/div&gt;
</pre>

<h3>Tables</h3>

<p>By default, tables should have no margin, and be transparent (invisible table).</p>

<p>A template should also provide a class "grid" for tables that should have a specific design. You should provide a default style for <code>&lt;th&gt;</code>, and 2 classes <code>odd</code> and <code>even</code> for <code>&lt;tr&gt;</code> tags.</p>
<p>The HTML for tables should be:</p>

<pre>
&lt;table class="grid"&gt;
	&lt;tr&gt;
		&lt;th&gt;Title1&lt;/th&gt;
		&lt;th&gt;Title2&lt;/th&gt;
	&lt;/tr&gt;
	&lt;tr class="odd"&gt;
		&lt;td&gt;1&lt;/td&gt;
		&lt;td&gt;2&lt;/td&gt;
	&lt;/tr&gt;
	&lt;tr class="even"&gt;
		&lt;td&gt;3&lt;/td&gt;
		&lt;td&gt;4&lt;/td&gt;
	&lt;/tr&gt;
	&lt;tr class="odd"&gt;
		&lt;td&gt;...&lt;/td&gt;
		&lt;td&gt;...&lt;/td&gt;
	&lt;/tr&gt;
&lt;/table&gt; 
</pre>
<br/>
And the CSS should look like:

<codehighlight language="CSS">
table.grid {
	...
}

table.grid th {
	...
}

table.grid tr.odd {
	...
}

table.grid tr.even {
	...
}

table.grid td {
	...
}
</codehighlight>
<br/>

<h2>Browser compatibility</h2>

<p>When developing a template, you should ensure compatibility with those browsers:</p>
<ul>
  <li>IE 7+</li>
  <li>Firefox 3+</li>
  <li>Chrome 2+</li>
  <li>Opera 10+</li>
</ul>

<p>In order to avoid cross-browser tricks, you might want to avoid using the "padding" attribute as much as possible (it is known
to cause compatibility problems with IE).</p>
