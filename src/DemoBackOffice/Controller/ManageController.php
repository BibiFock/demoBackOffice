<?php

namespace DemoBackOffice\Controller{

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Silex\ControllerCollection;
	use Symfony\Component\Validator\Constraints as Assert;
	use Symfony\Component\HttpFoundation\Request;
	use Exception;

	class ManageController implements ControllerProviderInterface{

		protected function checkAccess(Application $app, $section){
			$user = $app['security']->getToken()->getUser();
			return $user->getAccessBySectionName($section);
		}

		public function connect(Application $app){
			// créer un nouveau controller basé sur la route par défaut
			$index = $app['controllers_factory'];
			$index->match("/",'DemoBackOffice\Controller\ManageController::index')->bind("manage.index");
			$index->match("/{page}",'DemoBackOffice\Controller\ManageController::section')->bind("manage.other");
			return $index;
		}

		public function index(Application $app){
			return $app['twig']->render('manage/index.html.twig'); 
		}

		public function section(Application $app, $page, $forbidden = false){
			$section = $app['manager.section']->getSectionByName($page);
			$jsonUrlEdit = "";
			if(!$forbidden){
				if($section->id != '' ){
					$access = $this->checkAccess( $app, $section->name);
					$forbidden = !$access->canRead();	
					if($access->canEdit()){
						$jsonUrlEdit = json_encode(array('url' => $app['url_generator']->generate('manage.sections.edit', array('id' => $section->id)) ));
					}
				}
			}
			return $app['twig']->render('manage/section.html.twig', array(
				'forbidden' => $forbidden,
				'pageId' => $section->id,
				'jsonUrlEdit' => $jsonUrlEdit,
				'page' => $section->name,
				'content' => $section->content,
			)); 
		}

	}

}
