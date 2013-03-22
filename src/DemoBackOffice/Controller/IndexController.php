<?php

namespace DemoBackOffice\Controller{

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Silex\ControllerCollection;
	use Symfony\Component\HttpFoundation\Request;

	class IndexController implements ControllerProviderInterface{
		public $form = "this is a form";

		public function login(Application $app, Request $request){
			$form = $app['form.factory']->createBuilder('form')
				->add('username', 'text', array('label' => 'Username', 'data' => $app['session']->get('_security.last_username')))
				->add('password', 'password', array('label' => 'Password'))
				->getForm();

			return $app['twig']->render('login.html.twig', array(
					'form'  => $form->createView(),
					'error' => $app['security.last_error']($request),
				));
		}

		//public function logout(Application $app){
		//}

		public function connect(Application $app){
			// créer un nouveau controller basé sur la route par défaut
			$index = $app['controllers_factory'];
			$index->match("/",'DemoBackOffice\Controller\IndexController::login')->bind("index.index");
			$index->match("/login",'DemoBackOffice\Controller\IndexController::login')->bind("index.login");
			//$index->match("/logout",'DemoBackOffice\Controller\IndexController::logout')->bind("index.login");
			return $index;
		}
	}

}
