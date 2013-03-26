<?php

namespace DemoBackOffice\Controller{

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Silex\ControllerCollection;
	use Symfony\Component\HttpFoundation\Request;
	use Exception;

	class InstallController implements ControllerProviderInterface{

		private function testDb(Application $app){
			$options = $app['db.options'];
			$dbname = $options['dbname'];
			$options['dbname'] = '';
			$app['db.options'] = $options;
			$db = $app['db'];
			$db->executeQuery('CREATE DATABASE IF NOT EXISTS '.$dbname);
			$db->executeQuery('USE '.$dbname);
			$options['dbname'] = $dbname;
			$sql = file_get_contents($app['db.options.schema']);
			$db->executeQuery($sql);
			return true;
		}
	

		public function connect(Application $app){
			// créer un nouveau controller basé sur la route par défaut
			$index = $app['controllers_factory'];
			$index->match("/",'DemoBackOffice\Controller\InstallController::index')->bind("install.index");
			return $index;
		}

		
		public function index(Application $app, Request $request){
			$isErrorForm = false;
			$form = $app['form.factory']->createBuilder('form')
				->add('driver', 'text', array('data' => $app['db.options']['driver']))
				->add('host', 'text', array('data' => $app['db.options']['host']))
				->add('dbname', 'text', array('data' => $app['db.options']['dbname']))
				->add('user', 'text', array('data' => $app['db.options']['user']))
				->add('password', 'text', array('data' => $app['db.options']['password'], 'required' => false))
				->getForm();
			$installDone = false;
			if('POST' == $request->getMethod()){
				$form->bind($request);
				try{
					if($form->isValid()){
						$datas = $form->getData();
						$json = json_encode($datas);
						if($json !== false){
							if(@file_put_contents($app['db.options.src'], $json)){
								$app['db.options'] = $datas;
							}else throw new Exception("Failed to write connection content in file: ".$app['db.options.src']."( please check the permission for this conf:".exec('id', $r).")");
						}
					}else $isErrorForm = true;
					$installDone = $this->testDb($app);
					$app['session']->getFlashBag()->add('info', 'Databse create');
				}catch(Exception $e){
					$app['session']->getFlashBag()->add('error', 'Database Error'.$e->getMessage());
				}

			}	
			return $app['twig']->render('install.html.twig', array(
				'form'  => $form->createView(),
				'isErrorForm' => $isErrorForm,
				'installDone' => $installDone,
			)); 
		}
	}

}
