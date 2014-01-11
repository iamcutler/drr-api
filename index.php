<?php
header('Content-Type: application/json; charset=utf-8');

require 'config.php';
require $_SERVER["DOCUMENT_ROOT"].'/php_includes/connection.php';
require $_SERVER["DOCUMENT_ROOT"].'/php_includes/api_helpers.php';
require $_SERVER["DOCUMENT_ROOT"].'/php_includes/classes/api.php';
require $_SERVER["DOCUMENT_ROOT"].'/php_includes/classes/auth.php';
require $_SERVER["DOCUMENT_ROOT"].'/php_includes/classes/user.php';
require 'vendor/slim/slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array(
  'mode' => APP_MODE
));

// Instantiate DRR api class
$api = new DRR_API($mysqli, $app);
$auth = new Authentication($mysqli, $app);
$user = new User($mysqli, $app);

// -------------------- Registration --------------------
$app->post("/user/registration", function () use ($mysqli, $auth) {
  //$data = ['name' => 'John Westfield', 'username' => 'test2342', 'email' => 'test@test.com', 'password' => 'testingAPI123', 'dob' => '03/08/1988'];
  $auth->process_registration($_POST);
});

// ------------------- Authenication --------------------
// Login
$app->post("/user/login", function () use ($mysqli, $auth) {
  $auth->user_login($_POST);
});

// Check username uniqueness
$app->post("/check/username/:username", function($username) use ($mysqli, $auth) {
  $check = $auth->check_username_uniqueness($username);
  $result = ['unique' => ($check) ? false : true];
  $auth->toJSON($result);
});

// ----------------------- Users ------------------------
// Get active users
$app->get("/users/:offset/:limit", function ($offset, $limit) use ($mysqli, $api) {
  $api->get_users($offset, $limit);
});

// User profile
$app->post("/profile/:slug", function($slug) use ($mysqli, $user) {
  $user->get_user_profile($slug);
});

// Latest user media
$app->get("/latest-media/:offset/:limit", function($offset, $limit) use($mysqli, $api) {
  $api->get_user_media($offset, $limit);
});

// ----------------------- Voting -----------------------
$app->get("/current-voting", function() use($mysqli, $api) {
  $api->get_current_poll("NOW()");
});

$app->get("/voting-answers/:id", function($id) use ($mysqli, $api) {
  $api->get_voting_answers($id);
});

// --------------------- Dirty Girls ---------------------
$app->get("/dirty-girls", function() use ($mysqli, $api) {
  $api->get_dirty_girls();
});

$app->run();