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
$app->get("/users/:offset/:limit", function ($offset, $limit) use ($mysqli, $api) {
  $api->get_users($offset, $limit);
});

// Latest user media
$app->get("/latest-media/:offset/:limit", function($offset, $limit) use($mysqli, $api) {
  $api->get_user_media($offset, $limit);
});

// Voting
$app->get("/current-voting", function() use($mysqli, $api) {
  $api->get_current_poll("NOW()");
});

$app->get("/voting-answers/:id", function($id) use ($mysqli, $api) {
  $api->get_voting_answers($id);
});

$app->run();