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
        $users[$key]["thumbnail"] = CDN_DOMAIN . $val["thumbnail"];
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
      $media[$key]['user']['thumbnail'] = CDN_DOMAIN. $val['user_thumbnail'];
      $media[$key]['user']['slug'] = $val['user_slug'];
      $media[$key]['media']['title'] = $val['title'];
      $media[$key]['media']['thumbnail'] = CDN_DOMAIN . $val['media_thumbnail'];
      $media[$key]['media']['type'] = $val['type'];
      $media[$key]['media']['created_at'] = $val['created'];
    }
    
    return $this->toJSON($media);
  }
  
  public function get_current_poll($date) {
    $results = $this->query_current_voting_polls($date);
    
    $poll = [];
    foreach($results as $key => $val) {
      $poll[$key]['poll']['question'] = $val['question'];
      $poll[$key]['poll']['question'] = $val['question'];
      $poll[$key]['poll']['date_start'] = $val['date_start'];
      $poll[$key]['poll']['date_end'] = $val['date_end'];
      $poll[$key]['poll']['voting_period'] = $val['voting_period'];
      $poll[$key]['poll']['created'] = $val['created'];
    }
    
    return $this->toJSON($poll);
  }

  public function get_voting_answers($poll_id) {
    $results = $this->query_poll_answers($poll_id);

    $answers = [];
    foreach($results as $key => $val) {
      $answers[] = $val;
    }

    return $this->toJSON($answers);
  }

  public function get_dirty_girls() {
    $results = $this->find_all_dirty_girls();

    $girls = [];
    foreach($results as $key => $val) {
      $girls[$key]['id'] = $val['id'];
      $girls[$key]['campaign_month'] = $val['campaign_month'];
      $girls[$key]['campaign_year'] = $val['campaign_year'];
      $girls[$key]['name'] = $val['name'];
      $girls[$key]['biography'] = $val['bio'];
      $girls[$key]['type'] = $val['type'];
      $girls[$key]['order'] = $val['ordering'];
      $girls[$key]['media']['thumbnail'] = CDN_DOMAIN . "/administrator/components/com_dirtygirlpages/uploads/" .$val['thumbnail'];
      $girls[$key]['media']['image_1'] = ($val['image_1'] != "") ? CDN_DOMAIN . "/administrator/components/com_dirtygirlpages/uploads/" . $val['image_1'] : "";
      $girls[$key]['media']['image_2'] = ($val['image_2'] != "") ? CDN_DOMAIN . "/administrator/components/com_dirtygirlpages/uploads/" . $val['image_2'] : "";
      $girls[$key]['media']['image_3'] = ($val['image_3'] != "") ? CDN_DOMAIN . "/administrator/components/com_dirtygirlpages/uploads/" . $val['image_3'] : "";
      $girls[$key]['media']['image_4'] = ($val['image_4'] != "") ? CDN_DOMAIN . "/administrator/components/com_dirtygirlpages/uploads/" . $val['image_4'] : "";
      $girls[$key]['media']['image_5'] = ($val['image_5'] != "") ? CDN_DOMAIN . "/administrator/components/com_dirtygirlpages/uploads/" . $val['image_5'] : "";
    }

    return $this->toJSON($girls);
  }

  public function get_user_profile($slug) {
    $profile = $this->generate_user_profile_data_from_slug($slug);

    return $this->toJSON($profile);
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
  
  protected function query_current_voting_polls($date) {
    $results = $this->db->query("select id, question, date_start, date_end, number_answers, voting_period, created from ".TABLE_PREFIX."_sexy_polls where date_start <= NOW() and date_end >= NOW() and published = 1");
    
    return $results;
    $results->close();
  }

  protected function query_poll_answers($poll) {
    $results = $this->db->query("select name, thumbnail, username, caption, ordering from ".TABLE_PREFIX."_sexy_answers where id_poll = $poll and published = 1 order by name");

    return $results;
    $results->close();
  }

  protected function find_poll_answers_by_id($poll) {
    $results = $this->db->query("select name, thumbnail, username, caption, ordering from ".TABLE_PREFIX."_sexy_answers where id_poll = $poll and published = 1 order by name");

    return $results;
    $results->close();
  }
  
  protected function find_all_dirty_girls() {
    $results = $this->db->query("select
      id,
      campaign_month,
      campaign_year,
      dirty_girl_name as name,
      dirty_girl_bio as bio,
      dirty_type as type,
      thumbnail_image as thumbnail,
      image_1,
      image_2,
      image_3,
      image_4,
      image_5,
      ordering
      from ".TABLE_PREFIX."_dirtygirlpages_ order by ordering ASC");

    return $results;
    $results->close();
  }

  // Output slim app with json content type
  public function toJSON($data) {
    $response = $this->app->response;
    $response['Content-Type'] = 'application/json';
    //$data = iconv('UTF-8', 'UTF-8//TRANSLIT', $data);
    $response->body( json_encode($data) );
  }
}