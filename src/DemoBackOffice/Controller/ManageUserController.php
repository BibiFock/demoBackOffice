<?php

namespace DemoBackOffice\Controller{

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Silex\ControllerCollection;
	use Symfony\Component\Validator\Constraints as Assert;
	use Symfony\Component\HttpFoundation\Request;
	use DemoBackOffice\Model\Entity\User;
	use Exception;

	class ManageUserController implements ControllerProviderInterface{

		public function connect(Application $app){
			// créer un nouveau controller basé sur la route par défaut
			$index = $app['controllers_factory'];
			$index->match("/",'DemoBackOffice\Controller\ManageUserController::user')->bind("manage.users");
			$index->match("/edit/{name}",'DemoBackOffice\Controller\ManageUserController::userEdit')->value('name', '')->bind("manage.users.edit");
			$index->match("/save/{name}",'DemoBackOffice\Controller\ManageUserController::userSave')->value('name', '')->bind("manage.users.save");
			$index->match("/del/{id}",'DemoBackOffice\Controller\ManageUserController::userDel')->assert('id', '[0-9]+')->bind("manage.users.delete");

			return $index;
		}

		public function userSave(Application $app, Request $request, $name){
			return $this->userEdit($app, $request, $name, true);
		}

		public function userDel(Application $app, Request $request, $id){
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

		public function userEdit(Application $app, Request $request, $name, $ajax = false){
			$error = false;
			$isErrorForm = false;
			$isNew = ($name == "");
			$user = $app['manager.user']->getUserByName($name);
			$rights = $app['manager.rights']->loadUserTypes();
			for ($i = 0, $tmp = array(); $i < count($rights); $i++) {
				$tmp[$rights[$i]->id] = $rights[$i]->name;
			}
			$rights = $tmp;
			$form = $app['form.factory']->createBuilder('form')
				->add('username', 'text', array(
					'data' => $user->username,
					'disabled' => $user->isSuperAdmin(),
					'constraints'  => array(
						new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '50')),
						new Assert\Regex(array(
							'pattern' => '#^[^\/]+$#',
							'match'   => true,
							'message' => "Your user name can only contains this chars [0-9_-A-Za-z]",
						))
					)
				))
				->add('password', 'text',array(
					'data' => $user->password,
					'constraints'  => array(new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '100')))
				))
				->add('right', 'choice', array(
					'choices' => $rights,
					'data' => ($user->type != null ? $user->type->id: ''),
					'disabled' => $user->isSuperAdmin()
				))
				->getForm();
			$jsonSaveUser = array('url' => $app['url_generator']->generate('manage.users.save', array('name' => $user->username)));
			if($request->isMethod('POST') && !$error){
				$form->bind($request);
				try{
					$datas = $form->getData();
					if($form->isValid()){
						$datas = $form->getData();
						$user = $app['manager.user']->saveUser($datas['username'], $datas['password'], $datas['right'], $isNew);
						$app['session']->getFlashBag()->add('info', 'User '.$user->username.' '.($isNew ? 'created' : 'updated'));
						if($isNew) return $app->redirect($app['url_generator']->generate('manage.users.edit', array('name' => $user->username)));
					}else $isErrorForm = true;
				}catch(Exception $e){
					$app['session']->getFlashBag()->add('warning', $e->getMessage());
				}
			}
			
			return $app['twig']->render('manage/user-edit.html.twig', 
				array(
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

		public function user(Application $app, $ajax = false){
			$userManager = $app['manager.user'];
			$users = $userManager->loadUsers();
			return $app['twig']->render('manage/user-list.html.twig', array(
				'users' => $users,
				'jsonNewUser' => json_encode(array('url'=> $app['url_generator']->generate('manage.users.edit'))),
				'ajax' => $ajax,
			)); 
		}

	}

}
