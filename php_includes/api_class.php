<?php

class DRR_API {
  function __construct($connect) {
    $this->db = $connect;
  }
  
  public function get_users($app, $offset = 0, $max = 10) {
    return ($offset != NULL && $max != NULL) ? $this->query_drr_users($app, $offset, $max) : $this->query_drr_users($app);
  }
  
  // Get application users with pagination
  protected function query_drr_users($app, $offset = 0, $max = 10) {
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
    
    return $this->toJSON($app, $users);
    $results->close();
  }
  
  // Output slim app with json content type
  protected function toJSON($app, $data) {
    $response = $app->response;
    $response['Content-Type'] = 'application/json';
    $response->body( json_encode($data) );
  }
}