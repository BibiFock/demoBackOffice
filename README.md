#DemoBackOffice

#### WHY ? 
+ help learn silex symfony 
  + Symfony is the #1 php MVC framework, 
  + Silex is a framework that allow direct integration with Symfony components but with a fastest learning curve.
+ help learn MongoDB
  + MongoDB allows easy scaling of the database.
+ create a wordpress like CMS 
  + Most of clients are used to manage their content with a Wordpress like interface
  + this app will try to reproduce the best features of the wordpress CMS , like easy content management , easy template management , and easy plugin extension

## Installation :

+ php /path/to/composer/composer.phar install

+ Go to <your host>/demoBackOffice/web/install fill the form and install the database

+ Then go to <your host>/demoBackOffice/web/ for try it

+ SuperUser is admin / admin by default

#### Why ?
+ I have to create a small demonstration on based on PHP / MySQL of rights management in a backoffice.
	+ I choose Silex because I didn't know this framework. 
	+ So I took advantage of this exercise to discover it.
+ I also take advantage of this exercise for try bootstrap.

#### Rules
+ You have to be logged for see the list of sections
+ Admin Parts
	+ You have 3 admins sections
		+ Sections: create/edit/delete section and edit their content
		+ User: create/edit/delete user and define their right
		+ Right or type user: 
			+ create/edit/delete a right
			+ you can choose what kind of access (forbidden/readonly/edition) the user will have to the section
	+ You have a super user avaiblable: admin/admin 
		+ him and his right can't be deleted

### Silex based frameworks
+ http://silex.sensiolabs.org


