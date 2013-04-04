<?php

namespace DemoBackOffice\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Exception;

class IndexController implements ControllerProviderInterface
{

    //define routing
    public function connect(Application $app)
    {
        // créer un nouveau controller basé sur la route par défaut
        $index = $app['controllers_factory'];
        $index->match(
            "/",
            'DemoBackOffice\Controller\IndexController::login'
        )->bind("index.index");

        $index->match(
            "/login",
            'DemoBackOffice\Controller\IndexController::login'
        )->bind("index.login");

        return $index;
    }

    public function login(Application $app, Request $request)
    {
        $form = $app['form.factory']->createBuilder('form')
            ->add('username', 'text')
            ->add('password', 'password')
            ->getForm();

        return $app['twig']->render('login.html.twig', array(
            'form'  => $form->createView(),
            'error' => $app['security.last_error']($request),
        ));
    }

}

