<?php

use Silex\Provider\SessionServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

$app->register(new TranslationServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new UrlGeneratorServiceProvider());

$app->register(new TwigServiceProvider(), array(
    'twig.options'        => array(
        'strict_variables' => true
    ),
    'twig.path'           => array(__DIR__ . '/../resources/views'),
));

$app->register(new DoctrineServiceProvider());

$app['manager.user'] = $app->share(function () use ($app) {
	return new DemoBackOffice\Model\UserProvider($app);
});

$app->register(new SecurityServiceProvider(), array(
	'security.firewalls' => array(
		'manage' => array(
			'pattern' => '^/manage/',
			'form'    => array(
				'login_path'         => '/login',
				'check_path'         => '/manage/login_check',
				'default_target_path' => '/manage/',
				'always_use_default_target_path' => true,
				'username_parameter' => 'form[username]',
				'password_parameter' => 'form[password]',
			),
			'logout' => array(
				'logout_path' => "/manage/logout",
		        'target' => '/',
			),
			'anonymous' => false,
			'users' => $app['manager.user'],
		),
		'web' => array(
			'pattern' => '^/',
			'anonymous' => true,
		),
	),
	'security.access_rules' => array(
		array('^/manage/', 'ROLE_ADMIN'),
	),
));

$app['security.encoder.digest'] = $app->share(function ($app) {
    return new PlaintextPasswordEncoder();
});


$app['manager.section'] = $app->share(function () use ($app) {
	return new DemoBackOffice\Model\SectionManager($app['db']);
});

$app['manager.rights'] = $app->share(function () use ($app) {
	return new DemoBackOffice\Model\UserTypeManager($app['db']);
});

