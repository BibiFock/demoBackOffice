<?php

$app->mount("/", new DemoBackOffice\Controller\IndexController());
$app->mount("/manage/", new DemoBackOffice\Controller\ManageController());
$app->mount("/manage/section/", new DemoBackOffice\Controller\ManageSectionController());
$app->mount("/manage/right/", new DemoBackOffice\Controller\ManageUserTypeController());

$app->error(function (\Exception $e, $code) use ($app) { 
	if($app['debug']) return;
	if (404 == $code) { 
		return $app->redirect($app['url_generator']->generate('index.index'));
	} 
}); 

//$app->get('/logout', function() use ($app) {
    //return $app['twig']->render('layout.html.twig', array('name' => 'test'));
//})->bind('logout');

//$app->get('/login', function(Request $request) use($app) {
    //$form = $app['form.factory']->createBuilder('form')
        //->add('username', 'text', array('label' => 'Username', 'data' => $app['session']->get('_security.last_username')))
        //->add('password', 'password', array('label' => 'Password'))
        //->getForm();

    //return $app['twig']->render('login.html.twig', array(
        //'form'  => $form->createView(),
        //'error' => $app['security.last_error']($request),
    //));
    ////return $app['twig']->render('login.html.twig', array('name' => 'test'));
//})
//->bind('login');

