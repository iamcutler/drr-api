<?php

class User extends DRR_API {
  public function get_user_profile($slug) {
    $profile = $this->generate_user_profile_data_from_slug($slug);

    return $this->toJSON($profile);
  }
  
  // Generate complete user profile data
  protected function generate_user_profile_data_from_slug($slug) {
    $user = $this->find_user_profile_data_by_slug($slug);

    $profile = [];
    foreach($user as $key => $val) {
      // User array
      $profile[$key]['user']['id'] = $val['id'];
      $profile[$key]['user']['name'] = $val['name'];
      $profile[$key]['user']['username'] = $val['username'];
      $profile[$key]['user']['slug'] = $val['slug'];
      $profile[$key]['user']['thumbnail'] = $val['thumbnail'];
      // Profile array
      $profile[$key]['profile']['status'] = $val['status'];
      $profile[$key]['profile']['views'] = $val['views'];
      $profile[$key]['profile']['friends'] = $val['friends'];
      $profile[$key]['profile']['friend_count'] = $val['friendcount'];
      $profile[$key]['profile']['last_visit'] = $val['last_visit'];
      $profile[$key]['profile']['registered'] = $val['registered'];
      // Friends array
      $profile[$key]['friends'] = [];
      if($val['friends'] != NULL) {
        foreach($this->find_user_friends_by_id($val['friends']) as $k => $v) {
          $profile[$key]['friends'][] = $v;
        }
      }
      // Media array
      $profile[$key]['media'] = [];
      // Photos albums array
      $profile[$key]['media']['photo_albums'] = [];
      foreach($this->find_user_albums_by_id($val['id']) as $k => $v) {
        $profile[$key]['media']['photo_albums'][] = $v;
      }
      // Photos array
      $profile[$key]['media']['photos'] = [];
      foreach($this->find_user_photos_by_id($val['id']) as $k => $v) {
        $profile[$key]['media']['photos'][] = $v;
      }
      // Videos array
      $profile[$key]['media']['videos'] = [];
      foreach($this->find_user_videos_by_id($val['id']) as $k => $v) {
        $profile[$key]['media']['videos'][] = $v;
      }
      // Groups array
      $profile[$key]['groups'] = [];
      if($val['groups'] != NULL) {
        foreach($this->find_user_groups_by_id($val['groups']) as $k => $v) {
          $profile[$key]['groups'][] = $v;
        }
      }
      // Events array
      $profile[$key]['events'] = [];
      if($val['events'] != NULL) {
        foreach($this->find_user_events_by_id($val['events']) as $k => $v) {
          $profile[$key]['events'][] = $v;
        }
      }
    }

    return $profile;
  }
  
  protected function find_user_profile_data_by_slug($slug) {
    $results = $this->db->query("select
      user.id,
      user.name,
      user.username,
      user.registerDate as registered,
      user.lastvisitDate as last_visit,
      comm_user.status,
      comm_user.thumb as thumbnail,
      comm_user.view as views,
      comm_user.friends,
      comm_user.groups,
      comm_user.events,
      comm_user.friendcount,
      comm_user.alias as slug
      from ".TABLE_PREFIX."_users as user,
      ".TABLE_PREFIX."_community_users as comm_user
      where user.id = comm_user.userid
      and comm_user.alias = '$slug'
    ");

    return $results;
    $results->close();
  }
  
  protected function find_user_friends_by_id($friends) {
    $results = $this->db->query("select
      user.id,
      user.name,
      comm_user.thumb as thumbnail,
      comm_user.status,
      comm_user.alias as slug
      from ".TABLE_PREFIX."_users as user,
      ".TABLE_PREFIX."_community_users as comm_user
      where user.id = comm_user.userid
      and user.id IN ($friends)
    ");

    return $results;
    $results->close();
  }

  protected function find_user_groups_by_id($groups) {
    $results = $this->db->query("select * from ".TABLE_PREFIX."_community_groups where id IN ($groups)");

    return $results;
    $results->close();
  }

  protected function find_user_events_by_id($event) {
    $results = $this->db->query("select * from ".TABLE_PREFIX."_community_events where id IN ($event)");

    return $results;
    $results->close();
  }

  protected function find_user_albums_by_id($user) {
    $results = $this->db->query("select
      id,
      photoid,
      name,
      description,
      permissions,
      created,
      groupid,
      hits,
      location,
      `default`,
      params
      from ".TABLE_PREFIX."_community_photos_albums
      where creator = $user
      order by name DESC
    ");

    return $results;
    $results->close();
  }

  protected function find_user_photos_by_id($user) {
    $results = $this->db->query("select
      albumid,
      caption,
      image,
      thumbnail,
      ordering,
      hits,
      `status`,
      created 
      from ".TABLE_PREFIX."_community_photos 
      where creator = $user and published = 1");

    return $results;
    $results->close();
  }

  protected function find_user_videos_by_id($user) {
    $results = $this->db->query("select * from ".TABLE_PREFIX."_community_videos where creator = $user order by created DESC");

    return $results;
    $results->close();
  }

  protected function find_profile_feed_by_id($id) {
    $results = $this->db->query("");

    return $results;
    $results->close();
  }
  
  protected function find_user_hash_by_id($id) {
    $result = $this->db->query("select user_hash from ".TABLE_PREFIX."_users where id = $id LIMIT 1");

    return $result->fetch_array()['user_hash'];
    $result->close();
  }
}

?>