<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

use Silex\Provider\TwigServiceProvider;
$app->register(new TwigServiceProvider(), array(
    'twig.options'        => array(
        'strict_variables' => true
    ),
    'twig.form.templates' => array('form_div_layout.html.twig'),
    'twig.path'           => array(__DIR__ . '/../src/views')
));
// mode debug
$app['debug'] = true;

$app->get('/test', function() use ($app) {
    return $app['twig']->render('layout.html.twig', array('name' => 'test'));
});

$app->get('/{pageName}', function($pageName) {
    return $app['twig']->render('layout.html.twig');
	return 'Hello you are here:'.$pageName;
})
->value('pageName' , 'index');

$app->run();
