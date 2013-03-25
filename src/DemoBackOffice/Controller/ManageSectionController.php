<?php

namespace DemoBackOffice\Controller{

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Silex\ControllerCollection;
	use Symfony\Component\Validator\Constraints as Assert;
	use Symfony\Component\HttpFoundation\Request;
	use DemoBackOffice\Model\Entity\Section;
	use Exception;

	class ManageSectionController implements ControllerProviderInterface{

		public function connect(Application $app){
			// créer un nouveau controller basé sur la route par défaut
			$index = $app['controllers_factory'];
			$index->match("/",'DemoBackOffice\Controller\ManageSectionController::section')->bind("manage.sections");
			$index->match("/edit/{name}",'DemoBackOffice\Controller\ManageSectionController::sectionEdit')->value('name', '')->bind("manage.sections.edit");
			$index->match("/save/{name}",'DemoBackOffice\Controller\ManageSectionController::sectionSave')->value('name', '')->bind("manage.sections.save");
			$index->match("/del/{id}",'DemoBackOffice\Controller\ManageSectionController::sectionDel')->assert('id', '[0-9]+')->bind("manage.sections.delete");

			return $index;
		}

		public function sectionSave(Application $app, Request $request, $name){
			return $this->sectionEdit($app, $request, $name, true);
		}

		public function sectionDel(Application $app, Request $request, $id){
			if($id != "" && $request->isMethod('POST')){ 
				try{
					$section = $app['manager.section']->getSectionById($id);
					$app['manager.section']->deleteSection($section);
					$app['session']->getFlashBag()->add('info', 'Section '.$section->name.' deleted');
				}catch(Exception $e){
					$app['session']->getFlashBag()->add('error', 'delete error:'.$e->getMessage());
				}
			}
			return $this->section($app, true);
		}

		public function sectionEdit(Application $app, Request $request, $name, $ajax = false){
			$error = false;
			$isErrorForm = false;
			$isNew = ($name == "");
			$section = $app['manager.section']->getSectionByName($name);
			$form = $app['form.factory']->createBuilder('form')
				->add('name', 'text', array(
					'data' => $section->name,
					'constraints'  => array(
						new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '50')),
						new Assert\Regex(array(
							'pattern' => '#^[^\/]+$#',
							'match'   => true,
							'message' => "Your section name can only contains this chars [0-9_-A-Za-z]",
						))
					)
				))
				->add('content', 'textarea',array(
					'data' => $section->content,
					'constraints'  => array(new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '100')))
				))
				->getForm();
			$jsonSaveSection = array('url' => $app['url_generator']->generate('manage.sections.save', array('name' => $section->name)));
			if($request->isMethod('POST') && !$error){
				$form->bind($request);
				try{
					$datas = $form->getData();
					if($form->isValid()){
						$datas = $form->getData();
						$section = $app['manager.section']->saveSection($datas['name'], $datas['content'], $isNew);
						$app['session']->getFlashBag()->add('info', 'Section '.$section->name.' '.($isNew ? 'created' : 'updated'));
						if($isNew) return $app->redirect($app['url_generator']->generate('manage.sections.edit', array('name' => $section->name)));
					}else $isErrorForm = true;
				}catch(Exception $e){
					$app['session']->getFlashBag()->add('warning', $e->getMessage());
				}
			}
			
			return $app['twig']->render('manage/section-edit.html.twig', 
				array(
					'form' => $form->createView(),
					'ajax' => $ajax,
					'error' => $error,
					'isErrorForm' => $isErrorForm,
					'section' => $section,
					'jsonSaveSection' => json_encode($jsonSaveSection),
					'isNew' => $isNew,
				) 
			); 
		}

		public function section(Application $app, $ajax = false){
			$sectionManager = $app['manager.section'];
			$sections = $sectionManager->loadSections();
			return $app['twig']->render('manage/section-list.html.twig', array(
				'sections' => $sections,
				'jsonNewSection' => json_encode(array('url'=> $app['url_generator']->generate('manage.sections.edit'))),
				'ajax' => $ajax,
			)); 
		}

	}

}
