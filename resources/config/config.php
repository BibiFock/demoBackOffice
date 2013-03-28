<?php

//report error in debug
$app['debug'] = true;
if($app['debug']){ 
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
}
// db conf file
$app['db.options.src'] = __DIR__."/db.json";
$app['db.options.schema'] = __DIR__."/../sql/demoBackOffice.sql";
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
