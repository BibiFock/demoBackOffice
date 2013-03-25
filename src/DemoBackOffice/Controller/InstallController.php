<?php

namespace DemoBackOffice\Controller{

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Silex\ControllerCollection;
	use Symfony\Component\HttpFoundation\Request;
	use Exception;

	class InstallController implements ControllerProviderInterface{
		public function connect(Application $app){
			// créer un nouveau controller basé sur la route par défaut
			$index = $app['controllers_factory'];
			$index->match("/",'DemoBackOffice\Controller\InstallController::index')->bind("install.index");
			return $index;
		}

		public function index(Application $app, Request $request){
			$isErrorForm = false;
			$form = $app['form.factory']->createBuilder('form')
				->add('driver', 'text', array('data' => 'pdo_mysql'))
				->add('host', 'text', array('data' => '127.0.0.1'))
				->add('dbname', 'text', array('data' => 'demoBackOffice'))
				->add('user', 'text', array('data' => 'root'))
				->add('password', 'text', array('data' => ''))
				->getForm();
		
			if('POST' == $request->getMethod()){
				$form->bind($request);
				if($form->isValid()){
					$datas = $form->getData();
					//todo something
				}else $isErrorForm = true;
			}
			return $app['twig']->render('install.html.twig', array(
				'form'  => $form->createView(),
				'isErrorForm' => $isErrorForm,
			)); 
		}
	}

}
