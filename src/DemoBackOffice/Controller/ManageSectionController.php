<?php

namespace DemoBackOffice\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use DemoBackOffice\Model\Entity\Section;
use Exception;

/**
 * Controller part admin Sections
 * take care of insert/update/delete of sections
 */
class ManageSectionController extends ManageController
{
    //for the right
    private $sectionName = 'sections';

    //define routing
    public function connect(Application $app)
    {
        // créer un nouveau controller basé sur la route par défaut
        $index = $app['controllers_factory'];
        $index->match(
            "/",
            'DemoBackOffice\Controller\ManageSectionController::index'
        )->bind("manage.sections");
        //we define routing and add a verification for the id: only number
        $index->match(
            "/edit/{id}",
            'DemoBackOffice\Controller\ManageSectionController::sectionEdit'
        )->assert('id', '[0-9]+')
        ->value('id', '')
        ->bind("manage.sections.edit");

        $index->match(
            "/save/{id}",
            'DemoBackOffice\Controller\ManageSectionController::sectionSave'
        )->assert('id', '[0-9]+')
        ->value('id', '')
        ->bind("manage.sections.save");

        $index->match(
            "/del/{id}",
            'DemoBackOffice\Controller\ManageSectionController::sectionDel'
        )->assert('id', '[0-9]+')
        ->bind("manage.sections.delete");

        return $index;
    }

    /**
     * save form
     */
    public function sectionSave(Application $app, Request $request, $id)
    {
        return $this->sectionEdit($app, $request, $id, true);
    }

    /**
     * delete request
     */
    public function sectionDel(Application $app, Request $request, $id)
    {
        //check access
        $access = $this->checkAccess($app, $this->sectionName);
        if (!$access->canRead()) {
            return $this->section($app, $this->sectionName, true);
        }
        if ($id != "" && $request->isMethod('POST')) {
            try {
                $section = $app['manager.section']->getSectionById($id);
                $app['manager.section']->deleteSection($section);
                $app['session']->getFlashBag()->add(
                    'info',
                    'Section '.$section->name.' deleted'
                );
            } catch(Exception $e) {
                $app['session']->getFlashBag()->add(
                    'error',
                    'delete error:'.$e->getMessage()
);
            }
        }

        return $this->index($app, true);
    }

    /**
     * section edition
     * generate form and handle the response
     */
    public function sectionEdit(Application $app, Request $request, $id, $ajax = false)
    {
        //variable init
        $error = false;
        $isErrorForm = false;
        //if request come from a user section
        $fromUser = $request->get('from') == 'user';
        if(!$ajax) $ajax = $fromUser;
        $isNew = ($id == "");
        //get the section access
        $section = $app['manager.section']->getSectionByid($id);
        $sectionName = ($fromUser ? $section->name : $this->sectionName);
        $access = $this->checkAccess($app, $sectionName);
        //if authentified user can read this page we redirect him
        if(!$access->canRead()){
            return $this->section($app, $sectionName, true);
        }
        //check if user can edit the field
        $readonly = !$access->canEdit();
        //generate some constraint for name
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
        //if from form user, we erased the constraint because user can't rename a section
        if ($fromUser) {
            unset($nameOptions['constraints']);
        }
        //generate end of the form
        $form = $app['form.factory']->createBuilder('form')
            ->add('name', 'text', $nameOptions)
            ->add('content', 'textarea',array(
                'data' => $section->content,
                'disabled' => $readonly,
                'constraints'  => array(new Assert\NotBlank(), new Assert\Length(array('min' => 2,'max' => '100')))
            ))
            ->getForm();
        //generate section save url
        $jsonSaveSection = array(
            'url' => $app['url_generator']->generate(
                'manage.sections.save',
                array('id' => $section->id)
            )
        );
        //handle form submit
        if ($request->isMethod('POST') && !$error) {
            $form->bind($request);
            try {
                $datas = $form->getData();
                if ($form->isValid()) { // form valid
                    $datas = $form->getData();
                    if (!isset($datas['name'])) {  //from user (form with no name
                        $section = $app['manager.section']->saveSectionContent($section->id, $datas['content']);
                    } else {
                        $section = $app['manager.section']->saveSection($section->id, $datas['name'], $datas['content'], $isNew);
                    }
                    //aff message
                    $app['session']->getFlashBag()->add(
                        'info',
                        'Section '.$section->name.' '.($isNew ? 'created' : 'updated')
                    );
                    if (!isset($datas['name'])) {
                        return $this->section($app, $section->name, false, true);
                    }

                    if ($isNew) {
                        return $app->redirect(
                            $app['url_generator']->generate(
                                'manage.sections.edit',
                                array('id' => $section->id)
                            )
                        );
                    }
                } else {
                    $isErrorForm = true;
                }
            } catch(Exception $e) {
                $app['session']->getFlashBag()->add(
                    'warning',
                    $e->getMessage()
                );
            }
        }

        if ($fromUser) {
            $urlBack = $app['url_generator']->generate(
                'manage.other',
                array('page' => $section->name)
            );
        } else {
            $urlBack = $app['url_generator']->generate(
                'manage.sections',
                array('id' => $section->id)
            );
        }

        return $app['twig']->render('manage/section-edit.html.twig',
            array(
                'fromUser' => $fromUser,
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

    /**
     * handle section list aff
     */
    public function index(Application $app, $ajax = false)
    {
        $access = $this->checkAccess($app, $this->sectionName);
        if (!$access->canRead()){
            return $this->section($app, $this->sectionName, true);
        }
        $sectionManager = $app['manager.section'];
        $sections = $sectionManager->loadSections(false, true);
        for ($i = 0, $tmp = array(); $i < count($sections); $i++) {
            if ($this->checkAccess($app, $sections[$i]->name)->canRead()) {
                $tmp[] = $sections[$i];
            }
        }
        $sections = $tmp;
        return $app['twig']->render('manage/section-list.html.twig', array(
            'sections' => $sections,
            'jsonNewSection' => json_encode(
                array(
                    'url'=> $app['url_generator']->generate('manage.sections.edit')
                )
            ),
            'ajax' => $ajax,
            'readonly' => !$access->canEdit(),
        ));
    }

}

