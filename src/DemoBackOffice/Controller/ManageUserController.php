<?php

namespace DemoBackOffice\Controller{

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Silex\ControllerCollection;
	use Symfony\Component\Validator\Constraints as Assert;
	use Symfony\Component\HttpFoundation\Request;
	use DemoBackOffice\Model\Entity\User;
	use Exception;

	/**
	 * user management controller
	 */
	class ManageUserController extends ManageController{
		//user section name
		private $sectionName = 'users';

		//define routing
		public function connect(Application $app){
			// créer un nouveau controller basé sur la route par défaut
			$index = $app['controllers_factory'];
			$index->match("/",'DemoBackOffice\Controller\ManageUserController::user')->bind("manage.users");
			$index->match("/edit/{id}",'DemoBackOffice\Controller\ManageUserController::userEdit')->assert('id', '[0-9]+')->value('id', '')->bind("manage.users.edit");
			$index->match("/save/{id}",'DemoBackOffice\Controller\ManageUserController::userSave')->assert('id', '[0-9]+')->value('id', '')->bind("manage.users.save");
			$index->match("/del/{id}",'DemoBackOffice\Controller\ManageUserController::userDel')->assert('id', '[0-9]+')->bind("manage.users.delete");

			return $index;
		}

		/**
		 * user save form action
		 */
		public function userSave(Application $app, Request $request, $id){
			return $this->userEdit($app, $request, $id, true);
		}

		/**
		 * user delete action
		 */
		public function userDel(Application $app, Request $request, $id){
			$access = $this->checkAccess($app, $this->sectionName);
			if(!$access->canRead()) return $this->section($app, $this->sectionName, true);
			if($id != "" && $request->isMethod('POST')){ 
				try{
					$user = $app['manager.user']->getUserById($id);
					$app['manager.user']->deleteUser($user);
					$app['session']->getFlashBag()->add('info', 'User '.$user->username.' deleted');
				}catch(Exception $e){
					$app['session']->getFlashBag()->add('error', 'delete error:'.$e->getMessage());
				}
			}
			return $this->user($app, true);
		}

		/**
		 * edition user
		 */
		public function userEdit(Application $app, Request $request, $id, $ajax = false){
			//check access
			$access = $this->checkAccess($app, $this->sectionName);
			if(!$access->canRead()) return $this->section($app, $this->sectionName, true);
			//define default var
			$error = false;
			$isErrorForm = false;
			$isNew = ($id == "");
			//get informations
			$user = $app['manager.user']->getUserById($id);
			$rights = $app['manager.rights']->loadUserTypes();
			//convert information for show
			for ($i = 0, $tmp = array(); $i < count($rights); $i++) {
				$tmp[$rights[$i]->id] = $rights[$i]->name;
			}
			$rights = $tmp;
			//check if user can edit
			$readonly = !$access->canEdit();
			$disabled = $user->isSuperAdmin() || $readonly;
			//create the form
			$form = $app['form.factory']->createBuilder('form')
				->add('username', 'text', array(
					'data' => $user->username,
					'disabled' => $disabled,
					'constraints'  => array(
						new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '50')),
						new Assert\Regex(array(
							'pattern' => '#^[a-z0-9_-]+$#i',
							'match'   => true,
							'message' => "Your user name can only contains this chars [0-9_-A-Za-z]",
						))
					)
				))
				->add('password', 'text',array(
					'data' => $user->password,
					'constraints'  => array(new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '100'))),
					'disabled' => $readonly,
				))
				->add('right', 'choice', array(
					'choices' => $rights,
					'data' => ($user->type != null ? $user->type->id: ''),
					'disabled' => $disabled
				))
				->getForm();
			//generate url save
			$jsonSaveUser = array('url' => $app['url_generator']->generate('manage.users.save', array('id' => $user->id)));
			//handle form response
			if($request->isMethod('POST') && !$error){
				$form->bind($request);
				try{
					$datas = $form->getData();
					if($form->isValid()){
						$datas = $form->getData();
						if($user->isSuperAdmin()){ // super admin can only change is password
							$user = $app['manager.user']->changePassword($user->id, $datas['password']);
						}else{
							$user = $app['manager.user']->saveUser($user->id, $datas['username'], $datas['password'], $datas['right'], $isNew);
						}
						$app['session']->getFlashBag()->add('info', 'User '.$user->username.' '.($isNew ? 'created' : 'updated'));
						if($isNew) return $app->redirect($app['url_generator']->generate('manage.users.edit', array('id' => $user->id)));
					}else $isErrorForm = true;
				}catch(Exception $e){
					$app['session']->getFlashBag()->add('warning', $e->getMessage());
				}
			}
			
			return $app['twig']->render('manage/user-edit.html.twig', 
				array(
					'readonly' => !$access->canEdit(),
					'form' => $form->createView(),
					'ajax' => $ajax,
					'error' => $error,
					'isErrorForm' => $isErrorForm,
					'user' => $user,
					'jsonSaveUser' => json_encode($jsonSaveUser),
					'isNew' => $isNew,
				) 
			); 
		}

		/**
		 * user list
		 */
		public function user(Application $app, $ajax = false){
			$access = $this->checkAccess($app, $this->sectionName);
			$userManager = $app['manager.user'];
			$users = $userManager->loadUsers();
			return $app['twig']->render('manage/user-list.html.twig', array(
				'readonly' => !$access->canEdit(),
				'users' => $users,
				'jsonNewUser' => json_encode(array('url'=> $app['url_generator']->generate('manage.users.edit'))),
				'ajax' => $ajax,
			)); 
		}

	}

}
