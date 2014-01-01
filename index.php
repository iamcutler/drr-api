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

// Instantiate DRR api class
$api = new DRR_API($mysqli, $app);

// ----------------------- Users ------------------------
//Get active users
$app->get("/users", function ()  use ($mysqli, $app, $api) {
  // params
  $offset = $app->request->params("off");
  $limit = $app->request->params("limit");
  
  $api->get_users($offset, $limit);
});

// Latest user media
$app->get("/latest-media/:offset/:limit", function($offset, $limit) use($mysqli, $api) {
  $api->get_user_media($offset, $limit);
});

$app->run();