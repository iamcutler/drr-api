<?php

class DRR_API {
  function __construct($connect, $app) {
    $this->db = $connect;
    $this->app = $app;
  }
  
  public function get_users($offset = 0, $max = 10) {
    return ($offset != NULL && $max != NULL) ? $this->query_drr_users($offset, $max) : $this->query_drr_users();
  }
  
  // Get application users with pagination
  protected function query_drr_users($offset = 0, $max = 10) {
    $results = $this->db->query("select 
      users.name,
      comm_users.thumb as thumbnail,
      comm_users.alias as slug,
      comm_users.status
      from ".TABLE_PREFIX."_users as users, ".TABLE_PREFIX."_community_users as comm_users
      where users.id = comm_users.userid
      limit $offset, $max");
    
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