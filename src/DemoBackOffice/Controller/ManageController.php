<?php

namespace DemoBackOffice\Controller{

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Silex\ControllerCollection;
	use Symfony\Component\Validator\Constraints as Assert;
	use Symfony\Component\HttpFoundation\Request;
	use DemoBackOffice\Model as Model;
	use Exception;

	class ManageController implements ControllerProviderInterface{

		public function index(Application $app){
			return $app['twig']->render('manage/index.html.twig'); 
		}

		public function connect(Application $app){
			// créer un nouveau controller basé sur la route par défaut
			$index = $app['controllers_factory'];
			$index->match("/",'DemoBackOffice\Controller\ManageController::index')->bind("manage.index");
			$index->match("/users",'DemoBackOffice\Controller\ManageController::index')->bind("manage.users");
			$index->match("/access",'DemoBackOffice\Controller\ManageController::index')->bind("manage.access");
			return $index;
		}
	}

}
