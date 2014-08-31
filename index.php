<?php
require 'global.php';
require '../Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->setName('oauther');
//slim configure
$app->config(array(
    'debug' => true,
    'templates.path' => 'templates'
));

// GET route
$app->get('/', function () {
    $template = "hello";
    echo $template;
});

$app->get('/help/:section', function ($section) {
    $template = "help $section";
    echo $template;
});

/**
 * https://www.example.com/oauther/authorize?client_id=YOUR_CLIENT_ID&response_type=code&redirect_uri=YOUR_REGISTERED_REDIRECT_URI
 * 
 *
 *
 *
 */
$app->get('/authorize', function () use ($app) {
	$authorize = new authorize($_GET);
	if(!$authorize->verifyGet()){
		echo "Wrong URL format!";
	}else{
		$app->render("authLogin.php",$_GET);
	}
});

$app->run();
?>
