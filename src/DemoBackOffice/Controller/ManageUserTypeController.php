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

	class ManageUserTypeController implements ControllerProviderInterface{
		public function connect(Application $app){
			// créer un nouveau controller basé sur la route par défaut
			$index = $app['controllers_factory'];
			$index->match("/",'DemoBackOffice\Controller\ManageUserTypeController::userType')->bind("manage.rights");
			$index->match("/edit/{id}",'DemoBackOffice\Controller\ManageUserTypeController::userTypeEdit')->value('id', '')->assert('id', '[0-9]*')->bind("manage.rights.edit");
			$index->match("/save/{id}",'DemoBackOffice\Controller\ManageUserTypeController::userTypeSave')->value('id', '')->assert('id', '[0-9]*')->bind("manage.rights.save");
			$index->match("/del/{id}",'DemoBackOffice\Controller\ManageUserTypeController::userTypeDel')->assert('id', '[0-9]+')->bind("manage.rights.delete");

			return $index;
		}

		public function userType(Application $app, $ajax = false){
			$typeUserManager = $app['manager.rights'];
			$typeUsers = $typeUserManager->loadUserTypes();
			return $app['twig']->render('manage/rights-list.html.twig', array(
				'rights' => $typeUsers,
				'jsonNewRights' => json_encode(array('url'=> $app['url_generator']->generate('manage.rights.edit'))),
				'ajax' => $ajax,
			)); 
		}

		public function userTypeEdit(Application $app, Request $request, $id, $ajax = false){
			$error = false;
			$isErrorForm = false;
			$isNew = ($id == "");
			$userType = $app['manager.rights']->getUserTypeById($id);
			$sections = $app['manager.section']->loadSections();
			$form = $app['form.factory']->createBuilder('form')
				->add('name', 'text', array(
					'data' => $userType->name,
					'disabled' => $userType->isSuperAdmin(),
					'constraints'  => array(
						new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '50')),
					)
				))
				->add('description', 'textarea',array(
					'data' => $userType->description,
					'disabled' => $userType->isSuperAdmin(),
					'constraints'  => array(new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '100')))
				))
				->getForm();

			for ($i = 0; $i < count($sections); $i++) {
				$form->add('section_'.$sections[$i]->id, 'choice', 
					array(
						'choices' => array(AccessType::$FORBIDDEN => 'Forbidden', AccessType::$READONLY => 'Readonly', AccessType::$EDIT => 'Manage'),
						'data' => $userType->getAccessToSection($sections[$i]->id)->type,
						'disabled' => $userType->isSuperAdmin(),
					)
				);
			}
			$jsonSaveUserType = array('url' => $app['url_generator']->generate('manage.rights.save', array('id' => $userType->id)));
			if($request->isMethod('POST') && !$error){
				$form->bind($request);
				try{
					$datas = $form->getData();
					if($form->isValid()){
						$datas = $form->getData();
						$access = array();
						foreach($datas as $k=>$v){
							if(preg_match('#^section_(\d+)#i', $k, $match) > 0){
								$access[$match[1]] = new AccessType($v);
							}
						}
						$userType = $app['manager.rights']->saveUserType($userType->id, $datas['name'], $datas['description'], $access, $isNew);
						$app['session']->getFlashBag()->add('info', 'Rights '.$userType->name.' '.($isNew ? 'created' : 'updated'));
						if($isNew) return $app->redirect($app['url_generator']->generate('manage.rights.edit', array('id' => $userType->id)));
					}else $isErrorForm = true;
				}catch(Exception $e){
					$app['session']->getFlashBag()->add('warning', $e->getMessage());
				}
			}
			
			return $app['twig']->render('manage/rights-edit.html.twig', 
				array(
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

		public function userTypeDel(Application $app, Request $request, $id){
			if($id != "" && $request->isMethod('POST')){ 
				try{
					$userType = $app['manager.rights']->getUserTypeById($id);
					if($userType->id != '') $app['manager.rights']->deleteUserType($userType);
					$app['session']->getFlashBag()->add('info', 'Section '.$userType->name.' deleted');
				}catch(Exception $e){
					$app['session']->getFlashBag()->add('error', 'delete error:'.$e->getMessage());
				}
			}
			return $this->userType($app, true);
		}

		public function userTypeSave(Application $app, Request $request, $id){
			return $this->userTypeEdit($app, $request, $id, true);
		}

	}
}
