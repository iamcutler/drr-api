<?php

class DRR_API {
  function __construct($connect, $app) {
    $this->db = $connect;
    $this->app = $app;
  }
  
  public function get_users($offset = 0, $max = 10) {
    $results = $this->query_drr_users($offset, $max);
    
    // Set empty users array to push results
    $users = [];
    
    // Format array for json output
    foreach($results as $key => $val) {
        $users[$key]["name"] = $val["name"];
        $users[$key]["thumbnail"] = $val["thumbnail"];
        $users[$key]["slug"] = $val["slug"];
        $users[$key]["status"] = $val["status"];
    }
    
    return $this->toJSON($users);
    $results->close();
  }
  
  // Output slim app with json content type
  protected function toJSON($data) {
    $response = $this->app->response;
    $response['Content-Type'] = 'application/json';
    $response->body( json_encode($data) );
  }
}