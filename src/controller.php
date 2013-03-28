<?php

//define routing
$app->mount("/", new DemoBackOffice\Controller\IndexController());
$app->mount("/install", new DemoBackOffice\Controller\InstallController());
$app->mount("/manage/", new DemoBackOffice\Controller\ManageController());
$app->mount("/manage/sections/", new DemoBackOffice\Controller\ManageSectionController());
$app->mount("/manage/rights/", new DemoBackOffice\Controller\ManageUserTypeController());
$app->mount("/manage/users/", new DemoBackOffice\Controller\ManageUserController());

//define 404
$app->error(function (\Exception $e, $code) use ($app) { 
	if($app['debug']) return;
	if (404 == $code) { 

		return $app['twig']->render('404.html.twig'); 
		//return $app->redirect($app['url_generator']->generate('index.index'));
	} 
}); 

