<?php
require 'config/config.php';
require 'config/Database.php';
require 'model.php';
require '../Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->setName('oauther');

//user define variable
$app->db = new Database();
$app->ErrorMsg = $ErrorMsg;
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

$app->post('/userLogin', function () use ($app) {
	$user = new user($app->db);
	if(!$user->verifyPasswd($_POST)){
		echo $app->ErrorMsg['ERROR_USERNAME_OR_PASSWORD'];
	}else{
		$client = new client($app->db);
		$getArray = json_decode($_POST['getArray'],true);
		if(!$client->verifyAppKey($getArray)){
			echo $app->ErrorMsg['ERROR_APP_KEY'];
		}else{
			$random_code = $client->authorizeCode($getArray,$_POST['userName']);
			$app->redirect($client->getRedirectUrl($getArray,$random_code));
		}
	}
});

/**
 * https://www.example.com/oauther/authorize?client_id=YOUR_CLIENT_ID&response_type=code&redirect_uri=YOUR_REGISTERED_REDIRECT_URI
 * https://www.example.com/oauther/access_token?client_id=YOUR_CLIENT_ID&client_secret=YOUR_CLIENT_SECRET&grant_type=authorization_code&redirect_uri=YOUR_REGISTERED_REDIRECT_URI&code=CODE
 *
 */
$app->post('/access_token', function () use ($app) {
	$client = new client($app->db);
	if(!$client->verifyAccessTokenRequest($_POST)){
		echo "Illegel AccessToken Request";
	}else{
		echo $client->authorizeToken($_POST['code']);
	}

});

$app->get('/authorize', function () use ($app) {
	$client = new client($app->db);
	if(!$client->verifyGet($_GET)){
		echo $app->ErrorMsg['ERROR_URL_FORMAT'];
	}else{
		if(!$client->verifyAppKey($_GET)){
			echo $app->ErrorMsg['ERROR_APP_KEY'];
		}else{
			$privilege = $client->getPrivilege($_GET);
			$app->render("authLogin.php",array(
							'getArray' => json_encode($_GET),
							'authPage' => '/oauther/userLogin',
							'privilege' => $privilege,
						  ));
		}
	}
});

$app->run();
?>
