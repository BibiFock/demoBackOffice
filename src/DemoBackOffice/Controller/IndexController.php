<?php

namespace DemoBackOffice\Controller{

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Silex\ControllerCollection;
	use Symfony\Component\HttpFoundation\Request;
	use Exception;

	class IndexController implements ControllerProviderInterface{

		public function connect(Application $app){
			// créer un nouveau controller basé sur la route par défaut
			$index = $app['controllers_factory'];
			$index->match("/",'DemoBackOffice\Controller\IndexController::login')->bind("index.index");
			$index->match("/login",'DemoBackOffice\Controller\IndexController::login')->bind("index.login");
			$index->match("/home",'DemoBackOffice\Controller\IndexController::index')->bind("index.home");
			//$index->match("/logout",'DemoBackOffice\Controller\IndexController::logout')->bind("index.logout");
			return $index;
		}

		public function login(Application $app, Request $request){

			$form = $app['form.factory']->createBuilder('form')
				->add('username', 'text')
				->add('password', 'password')
				->getForm();
			//$error = "";
			//if('POST' == $request->getMethod()){
				//$form->bind($request);
				//try{
					//if($form->isValid()){
						//$datas = $form->getData();
						//$user = $app['security.users']->loadUserByUsername($datas['username']);
						//if($user->getPassword() == $datas['password']){
							//$app['session']->set('isAuthenticated', true);
							//$app['session']->set('user', $user);
							//return $app->redirect($app['url_generator']->generate('index.index'));
						//}else throw new Exception('bad password');
					//}else throw new Exception('please fill the formulaire');
				//}catch(Exception $e){
					//$error = $e->getMessage();
				//}
			//}
			return $app['twig']->render('login.html.twig', array(
					'form'  => $form->createView(),
        			'error' => $app['security.last_error']($request),
					//'error' => $error,
				));
		}


		//public function logout(Application $app){
			//$app['session']->set('isAuthenticated', false);
			//return $app->redirect($app['url_generator']->generate('index.index'));
		//}

	}

}
