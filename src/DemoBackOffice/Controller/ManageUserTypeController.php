<?php

namespace DemoBackOffice\Controller{

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Silex\ControllerCollection;
	use Symfony\Component\Validator\Constraints as Assert;
	use Symfony\Component\HttpFoundation\Request;
	use DemoBackOffice\Model\Entity\UserType;
	use DemoBackOffice\Model\Entity\AccessType;
	use Exception;

	/**
	 * handle right management
	 */
	class ManageUserTypeController extends ManageController{
		//section name
		private $sectionName = 'rights';

		/**
		 * define routing
		 */
		public function connect(Application $app){
			// créer un nouveau controller basé sur la route par défaut
			$index = $app['controllers_factory'];
			$index->match("/",'DemoBackOffice\Controller\ManageUserTypeController::userType')->bind("manage.rights");
			$index->match("/edit/{id}",'DemoBackOffice\Controller\ManageUserTypeController::userTypeEdit')->value('id', '')->assert('id', '[0-9]*')->bind("manage.rights.edit");
			$index->match("/save/{id}",'DemoBackOffice\Controller\ManageUserTypeController::userTypeSave')->value('id', '')->assert('id', '[0-9]*')->bind("manage.rights.save");
			$index->match("/del/{id}",'DemoBackOffice\Controller\ManageUserTypeController::userTypeDel')->assert('id', '[0-9]+')->bind("manage.rights.delete");

			return $index;
		}

		/**
		 * home of the section aff the list of right
		 */
		public function userType(Application $app, $ajax = false){
			$access = $this->checkAccess($app, $this->sectionName);
			if(!$access->canRead()) return $this->section($app, $this->sectionName, true);

			$typeUserManager = $app['manager.rights'];
			$typeUsers = $typeUserManager->loadUserTypes();
			return $app['twig']->render('manage/rights-list.html.twig', array(
				'rights' => $typeUsers,
				'jsonNewRights' => json_encode(array('url'=> $app['url_generator']->generate('manage.rights.edit'))),
				'ajax' => $ajax,
				'readonly' => !$access->canEdit(),
			)); 
		}

		/**
		 * form edit right
		 *
		 */
		public function userTypeEdit(Application $app, Request $request, $id, $ajax = false){
			//check access
			$access = $this->checkAccess($app, $this->sectionName);
			if(!$access->canRead()) return $this->section($app, $this->sectionName, true);
			//init var
			$error = false;
			$isErrorForm = false;
			$isNew = ($id == "");
			//get infos
			$userType = $app['manager.rights']->getUserTypeById($id);
			$sections = $app['manager.section']->loadSections();
			//check if the current user can edit
			$readonly = $userType->isSuperAdmin() || !$access->canEdit();
			//create form
			$form = $app['form.factory']->createBuilder('form')
				->add('name', 'text', array(
					'data' => $userType->name,
					'disabled' => $readonly,
					'constraints'  => array(
						new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '50')),
						new Assert\Regex(array(
							'pattern' => '#^[a-z0-9_-]+$#i',
							'match'   => true,
							'message' => "Your right name can only contains this chars [0-9_-A-Za-z]",
						))
					)
				))
				->add('description', 'textarea',array(
					'data' => $userType->description,
					'disabled' => $readonly,
					'constraints'  => array(new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '100')))
				))
				->getForm();
			//create section form list
			for ($i = 0; $i < count($sections); $i++) {
				$form->add('section_'.$sections[$i]->id, 'choice', 
					array(
						'choices' => array(AccessType::$FORBIDDEN => 'Forbidden', AccessType::$READONLY => 'Readonly', AccessType::$EDIT => 'Manage'),
						'data' => $userType->getAccessToSection($sections[$i]->id)->type,
						'disabled' => $readonly,
					)
				);
			}
			//json url request for the ajax request
			$jsonSaveUserType = array('url' => $app['url_generator']->generate('manage.rights.save', array('id' => $userType->id)));
			//handle form request
			if($request->isMethod('POST') && !$error){
				$form->bind($request);
				try{
					$datas = $form->getData();
					if($form->isValid()){
						$datas = $form->getData();
						$access = array();
						//generate access
						foreach($datas as $k=>$v){
							if(preg_match('#^section_(\d+)#i', $k, $match) > 0){
								$access[$match[1]] = new AccessType($v);
							}
						}
						//save right infos
						$userType = $app['manager.rights']->saveUserType($userType->id, $datas['name'], $datas['description'], $access, $isNew);
						//aff result message
						$app['session']->getFlashBag()->add('info', 'Rights '.$userType->name.' '.($isNew ? 'created' : 'updated'));
						if($isNew) return $app->redirect($app['url_generator']->generate('manage.rights.edit', array('id' => $userType->id)));
					}else $isErrorForm = true;
				}catch(Exception $e){
					$app['session']->getFlashBag()->add('warning', $e->getMessage());
				}
			}
			
			return $app['twig']->render('manage/rights-edit.html.twig', 
				array(
					'readonly' => $readonly,
					'form' => $form->createView(),
					'ajax' => $ajax,
					'error' => $error,
					'isErrorForm' => $isErrorForm,
					'right' => $userType,
					'sections' => $sections,
					'jsonSaveRight' => json_encode($jsonSaveUserType),
					'isNew' => $isNew,
				) 
			); 
		}

		/**
		 * Right delete
		 *
		 */
		public function userTypeDel(Application $app, Request $request, $id){
			//check access
			$access = $this->checkAccess($app, $this->sectionName);
			if(!$access->canRead()) return $this->section($app, $this->sectionName, true);
			//handle form request
			if($id != "" && $request->isMethod('POST')){ 
				try{
					//check is there is no user with this type
					$userType = $app['manager.rights']->getUserTypeById($id);
					if($userType->id != '') $app['manager.rights']->deleteUserType($userType);
					$app['session']->getFlashBag()->add('info', 'Section '.$userType->name.' deleted');
				}catch(Exception $e){
					$app['session']->getFlashBag()->add('error', 'delete error:'.$e->getMessage());
				}
			}
			return $this->userType($app, true);
		}
		
		/**
		 * save right infos request
		 */
		public function userTypeSave(Application $app, Request $request, $id){
			return $this->userTypeEdit($app, $request, $id, true);
		}

	}
}
