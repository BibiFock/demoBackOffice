<?php

$app['debug'] = true;
if($app['debug']){ 
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
}

$app['db.options.src'] = __DIR__."/../resources/db.json";
$app['db.options.schema'] = __DIR__."/../resources/sql/demoBackOffice.sql";
if(file_exists($app['db.options.src'])) $app['db.options'] = json_decode( file_get_contents($app['db.options.src']), true);
if($app['db.options'] == NULL){
	$app['db.options'] = array(
		'driver' => 'pdo_mysql', 
		'host' => '127.0.0.1', 
		'dbname' => 'demoBackOffice', 
		'user' => 'root', 
		'password' => '', 
	);
	file_put_contents($app['db.options.src'], json_encode($app['db.options']));
}
// Local
//$app['session.default_locale'] = $app['locale'];
//$app['translator.messages'] = array(
    //'fr' => __DIR__.'/../resources/locales/fr.yml',
//);

//// Cache
//$app['cache.path'] = __DIR__ . '/../cache';

//// Http cache
//$app['http_cache.cache_dir'] = $app['cache.path'] . '/http';

//// Twig cache
//$app['twig.options.cache'] = $app['cache.path'] . '/twig';

//// Assetic
//$app['assetic.enabled']              = true;
//$app['assetic.path_to_cache']        = $app['cache.path'] . '/assetic' ;
//$app['assetic.path_to_web']          = __DIR__ . '/../../web/assets';
//$app['assetic.input.path_to_assets'] = __DIR__ . '/../assets';

//$app['assetic.input.path_to_css']       = $app['assetic.input.path_to_assets'] . '/less/style.less';
//$app['assetic.output.path_to_css']      = 'css/styles.css';
//$app['assetic.input.path_to_js']        = array(
    //__DIR__.'/../../vendor/twitter/bootstrap/js/*.js',
    //$app['assetic.input.path_to_assets'] . '/js/script.js',
//);
//$app['assetic.output.path_to_js']       = 'js/scripts.js';

// Doctrine (db)
//$app['db.options'] = array(
	//'driver'   => 'pdo_mysql',
	//'host'     => 'localhost',
	//'dbname'   => 'demoBackOffice',
	//'user'     => 'root',
	//'password' => '',
//);

//// User
//$app['security.users'] = array('username' => array('ROLE_USER', 'password'));
