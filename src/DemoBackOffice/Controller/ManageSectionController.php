<?php

namespace DemoBackOffice\Controller{

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Silex\ControllerCollection;
	use Symfony\Component\Validator\Constraints as Assert;
	use Symfony\Component\HttpFoundation\Request;
	use DemoBackOffice\Model\Entity\Section;
	use Exception;

	class ManageSectionController extends ManageController{
		private $sectionName = 'sections';

		public function connect(Application $app){
			// créer un nouveau controller basé sur la route par défaut
			$index = $app['controllers_factory'];
			$index->match("/",'DemoBackOffice\Controller\ManageSectionController::index')->bind("manage.sections");
			$index->match("/edit/{id}",'DemoBackOffice\Controller\ManageSectionController::sectionEdit')->assert('id', '[0-9]+')->value('id', '')->bind("manage.sections.edit");
			$index->match("/save/{id}",'DemoBackOffice\Controller\ManageSectionController::sectionSave')->assert('id', '[0-9]+')->value('id', '')->bind("manage.sections.save");
			$index->match("/del/{id}",'DemoBackOffice\Controller\ManageSectionController::sectionDel')->assert('id', '[0-9]+')->bind("manage.sections.delete");

			return $index;
		}

		public function sectionSave(Application $app, Request $request, $id){
			return $this->sectionEdit($app, $request, $id, true);
		}

		public function sectionDel(Application $app, Request $request, $id){
			$access = $this->checkAccess($app, $this->sectionName);
			if(!$access->canRead()) return $this->section($app, $this->sectionName, true);
			if($id != "" && $request->isMethod('POST')){ 
				try{
					$section = $app['manager.section']->getSectionById($id);
					$app['manager.section']->deleteSection($section);
					$app['session']->getFlashBag()->add('info', 'Section '.$section->name.' deleted');
				}catch(Exception $e){
					$app['session']->getFlashBag()->add('error', 'delete error:'.$e->getMessage());
				}
			}
			return $this->index($app, true);
		}

		public function sectionEdit(Application $app, Request $request, $id, $ajax = false){
			$error = false;
			$isErrorForm = false;
			$fromUser = $request->get('from') == 'user';
			if(!$ajax) $ajax = $fromUser;
			$isNew = ($id == "");
			$section = $app['manager.section']->getSectionByid($id);
			$sectionName = (!$isNew ? $section->name : $this->sectionName);
			$access = $this->checkAccess($app, $sectionName);
			if(!$access->canRead()) return $this->section($app, $sectionName, true);
			$readonly = !$access->canEdit();
			$nameOptions = array(
					'data' => $section->name,
					'disabled' => ($readonly || $fromUser),
					'constraints'  => array(
						new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '50')),
						new Assert\Regex(array(
							'pattern' => '#^[a-z0-9_-]+$#i',
							'match'   => true,
							'message' => "Your section name can only contains this chars [0-9_-A-Za-z]",
						))
					)
				);
			if($fromUser) unset($nameOptions['constraints']);
			//TODO finish this part
			$form = $app['form.factory']->createBuilder('form')
				->add('name', 'text', $nameOptions)
				->add('content', 'textarea',array(
					'data' => $section->content,
					'disabled' => $readonly,
					'constraints'  => array(new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '100')))
				))
				->getForm();

			if($fromUser) $form->add('from', 'hidden', array('data' => 'user'));

			$jsonSaveSection = array('url' => $app['url_generator']->generate('manage.sections.save', array('id' => $section->id)));
			if($request->isMethod('POST') && !$error){
				$form->bind($request);
				try{
					$datas = $form->getData();
					if($form->isValid()){
						$datas = $form->getData();
						if(!isset($datas['name'])) $section = $app['manager.section']->saveSectionContent($section->id, $datas['content']);
						else $section = $app['manager.section']->saveSection($section->id, $datas['name'], $datas['content'], $isNew);
						$app['session']->getFlashBag()->add('info', 'Section '.$section->name.' '.($isNew ? 'created' : 'updated'));
						if($isNew) return $this->section($app, $section->name, false, true);
					}else $isErrorForm = true;
				}catch(Exception $e){
					$app['session']->getFlashBag()->add('warning', $e->getMessage());
				}
			}
			if($fromUser) $urlBack = $app['url_generator']->generate('manage.other', array('page' => $section->name));	
			else $urlBack = $app['url_generator']->generate('manage.sections', array('id' => $section->id));
			return $app['twig']->render('manage/section-edit.html.twig', 
				array(
					'readonly' => $readonly,
					'form' => $form->createView(),
					'ajax' => $ajax,
					'error' => $error,
					'isErrorForm' => $isErrorForm,
					'section' => $section,
					'jsonSaveSection' => json_encode($jsonSaveSection),
					'isNew' => $isNew,
					'urlBack' =>$urlBack ,
				) 
			); 
		}

		public function index(Application $app, $ajax = false){
			$access = $this->checkAccess($app, $this->sectionName);
			if(!$access->canRead()) return $this->section($app, $this->sectionName, true);
			$sectionManager = $app['manager.section'];
			$sections = $sectionManager->loadSections(false, true);
			for ($i = 0, $tmp = array(); $i < count($sections); $i++) {
				if($this->checkAccess($app, $sections[$i]->name)->canRead()){
					$tmp[] = $sections[$i];
				}
			}
			$sections = $tmp;
			return $app['twig']->render('manage/section-list.html.twig', array(
				'sections' => $sections,
				'jsonNewSection' => json_encode(array('url'=> $app['url_generator']->generate('manage.sections.edit'))),
				'ajax' => $ajax,
				'readonly' => !$access->canEdit(),
			)); 
		}

	}

}
