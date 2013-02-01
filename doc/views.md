Managing Views
==============

The ugly truth is that Splash MVC does not handle the view mechanism at all.
It is completely _view agnostic_.

This is actually a good thing. It means you can use your favorite templating mechanism to manage the view.
For instance, you could use Splash along Smarty, or Twig.

But of course, since you are using Mouf, a view and template mechanism acting in a "Moufesque" way might be interesting.
Have a look at Mouf's the [TemplateInterface](/mouf/html.template.templateinterface/) and 
its [the BootstrapTemplate implementation](/mouf/html.template.bootstrap/) to see how 
the view layer can be managed using Mouf. 