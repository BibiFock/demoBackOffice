<?php

namespace DemoBackOffice\Controller{

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Silex\ControllerCollection;
	use Symfony\Component\Validator\Constraints as Assert;
	use Symfony\Component\HttpFoundation\Request;
	use Exception;

	/**
	 * Manage all connection to section user
	 */
	class ManageController implements ControllerProviderInterface{

		//get acces of the current section for identified user
		protected function checkAccess(Application $app, $section){
			$user = $app['security']->getToken()->getUser();
			return $user->getAccessBySectionName($section);
		}

		//define routing
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

		//load divers section
		public function section(Application $app, $page, $forbidden = false, $ajax = false){
			//get section
			$section = $app['manager.section']->getSectionByName($page);
			$jsonUrlEdit = "";
			if(!$forbidden){
				//if section isn't forbidden
				if($section->id != '' ){
					//if we find a section
					$access = $this->checkAccess( $app, $section->name);
					$forbidden = !$access->canRead();	
					//check access
					if($access->canEdit()){
						//check is user can edit it
						$jsonUrlEdit = json_encode(array('url' => $app['url_generator']->generate('manage.sections.edit', array('id' => $section->id)) ));
					}
				}else $app->abort(404);
			}

			return $app['twig']->render('manage/section.html.twig', array(
				'ajax' => $ajax,
				'forbidden' => $forbidden,
				'pageId' => $section->id,
				'jsonUrlEdit' => $jsonUrlEdit,
				'page' => $section->name,
				'content' => $section->content,
			)); 
		}

	}

}
