<?php
header("Content-Type: application/json");

require 'config.php';
require $_SERVER["DOCUMENT_ROOT"].'/php_includes/connection.php';
require $_SERVER["DOCUMENT_ROOT"].'/php_includes/api_class.php';
require 'vendor/slim/slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array(
  'mode' => APP_MODE
));

$app->get("/users", function ()  use ($app, $mysqli) {
  // Instantiate DRR api class
  $api = new DRR_API($mysqli);
  // params
  $offset = $app->request->params("off");
  $limit = $app->request->params("limit");
  
  $api->get_users($app, $offset, $limit);
});

$app->run();