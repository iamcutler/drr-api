<?php

class DRR_API {
  function __construct($connect) {
    $this->db = $connect;
  }
  
  // Output slim app with json content type
  protected function toJSON($app, $data) {
    $response = $app->response;
    $response['Content-Type'] = 'application/json';
    $response->body( json_encode($data) );
  }
}