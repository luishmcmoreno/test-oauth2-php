<?php

require __DIR__ . '/../vendor/autoload.php';
//require_once __DIR__ . '/../class-loader.php';
use CampuseroOAuth2\AppContainer as AppContainer;

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

session_start();

// Instantiate the app
//$app = new \Slim\App($settings);
$app = AppContainer::getInstance();
//$app->add(new \CorsSlim\CorsSlim());
$app->get('/', function ($request, $response, $args) {
    return $response->getBody()->write("Hello, teste do php oauth2");
});

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
//require __DIR__ . '/../src/routes.php';
$app->group('/api', function () {
    //Include endpoints
    foreach (glob("endpoints/*.php") as $filename) {
        require_once __DIR__ . '/'.$filename;
    }
});

// Run app
$app->run();
