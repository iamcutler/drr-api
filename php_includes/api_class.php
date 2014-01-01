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
  }
  
  public function get_user_media($offset = 0, $max = 10) {
    $results = $this->query_latest_media($offset, $max);
    
    // Empty array to push result data
    $media = [];
    
    foreach($results as $key => $val) {
      // User object
      $media[$key]['user']['name'] = $val['name'];
      $media[$key]['user']['thumbnail'] = CDN_HOST. $val['user_thumbnail'];
      $media[$key]['user']['slug'] = $val['user_slug'];
      $media[$key]['media']['title'] = $val['title'];
      $media[$key]['media']['thumbnail'] = CDN_HOST . $val['media_thumbnail'];
      $media[$key]['media']['type'] = $val['type'];
      $media[$key]['media']['created_at'] = $val['created'];
    }
    
    return $this->toJSON($media);
  }
  
  protected function query_drr_users($offset = 0, $max = 10) {
    $results = $this->db->query("select 
      users.name,
      comm_users.thumb as thumbnail,
      comm_users.alias as slug,
      comm_users.status
      from ".TABLE_PREFIX."_users as users, ".TABLE_PREFIX."_community_users as comm_users
      where users.id = comm_users.userid
      limit $offset, $max");

    return $results;
    $results->close();
  }
  
   protected function query_latest_media($offset = 0, $max = 10) {
    $results = $this->db->query("select
      users.name,
      comm_users.thumb as user_thumbnail,
      comm_users.alias as user_slug,
      media.title,
      media.thumbnail as media_thumbnail,
      media.published,
      media.type,
      media.created
      from
      (
      	select
      	photos.creator as userid,
      	'photo'  as type,
      	photos.caption as title,
      	photos.image as thumbnail,
      	photos.published,
      	photos.created as created
          from ".TABLE_PREFIX."_community_photos as photos
          union all
          select
          videos.creator as userid,
          'video' as type,
          videos.title as title,
          videos.thumb,
          videos.published,
          videos.created as created
          from ".TABLE_PREFIX."_community_videos as videos
      ) as media, ".TABLE_PREFIX."_users as users,
      ".TABLE_PREFIX."_community_users as comm_users
      where media.userid = users.id and comm_users.userid = media.userid and media.published = 1
      order by media.created desc
      limit $offset, $max");
    
    return $results;
    $results->close();
  }
  
  // Output slim app with json content type
  protected function toJSON($data) {
    $response = $this->app->response;
    $response['Content-Type'] = 'application/json';
    $response->body( json_encode($data) );
  }
}