<?php

require $_SERVER["DOCUMENT_ROOT"].'/php_includes/auth_helpers.php';

class Authentication extends DRR_API {
  // Login
  public function user_login($user) {
    $username = makeSQLSafe($this->db, $user['username']);
    $password = makeSQLSafe($this->db, $user['password']);

    $user = $this->find_user_by_username($username);
    if($user->num_rows == 1) {
      $user = $user->fetch_assoc();

      if($this->validate_user_password($password, $user['password'])) {
        $result = ['status' => true,
          'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'slug' => $user['slug'],
            'thumbnail' => $user['thumb'],
            'hash' => $user['user_hash']
          ]
        ];
      } else {
        $result = ['status' => false];
      }
    } else {
      $result = ['status' => false];
    }

    return $this->toJSON($result);
  }

  // Registration
  public function process_registration($user) {
    // Check if username is taken
    if(!$this->check_username_uniqueness($user['username'])) {
      // Check if user email adrress has been used
      if(!$this->check_email_uniqueness($user['email'])) {
        // Save user data
        $results = $this->save_new_user($user);
      } else {
        $results = ['status' => false, 'code' => '101', 'message' => 'email address has already been used'];
      }
    } else {
      $results = ['status' => false, 'code' => '100', 'message' => 'username is already taken'];
    }

    return $this->toJSON($results);
  }

  public function check_username_uniqueness($username) {
    $username = makeSQLSafe($this->db, $username);

    $results = $this->db->query("select username from ".TABLE_PREFIX."_users where username = '$username' LIMIT 1");

    return $results->num_rows;
    $results->close();
  }

  protected function check_email_uniqueness($email) {
    $email = makeSQLSafe($this->db, $email);

    $results = $this->db->query("select email from ".TABLE_PREFIX."_users where email = '$email' LIMIT 1");

    return $results->num_rows;
    $results->close();
  }

  // Check to make sure generated user hash is unique
  protected function check_user_hash_uniqueness($hash) {
    $results = $this->db->query("select user_hash from ".TABLE_PREFIX."_users WHERE user_hash = '$hash' LIMIT 1");

    return $results->num_rows;
  }

  protected function generate_user_hash($name, $username) {
    $name = makeSQLSafe($this->db, $name);
    $username = makeSQLSafe($this->db, $username);
    $saltLength = 9;
    $salt = substr(md5(uniqid(rand(), true)), 0, $saltLength);
    
    // Check if hash is unique, if not, generate new hash till a unique hash is found
    if($this->check_user_hash_uniqueness($salt)) {
      return $this->generate_user_hash($name . $username);
    } else {
      return $salt . sha1($salt . rand(5, 20) . date("Y-m-d"));
    }
  }

  public function generate_user_password($password) {
    $salt = AuthHelper::genRandomPassword(32);
    $crypt = AuthHelper::getCryptedPassword($password, $salt);
    return $crypt . ':' . $salt;
  }

  protected function save_new_user($user) {
    $name = makeSQLSafe($this->db, $user['name']);
    $username = makeSQLSafe($this->db, $user['username']);
    $email = makeSQLSafe($this->db, $user['email']);
    $password = $this->generate_user_password($user['password']);
    $dob = makeSQLSafe($this->db, $user['dob']);
    $user_hash = $this->generate_user_hash($name, $username);

    // Save default user table
    $save_user = $this->db->query("insert into ".TABLE_PREFIX."_users 
      (name, username, email, password, usertype, registerDate, lastVisitDate, user_hash) values 
      ('$name', '$username', '$email', '$password', 'Registered', NOW(), NOW(), '$user_hash')");

    if($save_user) {
      // If successful save, fetch last insert for user id
      $user_id = $this->db->insert_id;
      $user_slug = $user_id . ':' . str_replace(' ', '-', strtolower($name));

      // Save community user relationship data
      $save_comm_relation = $this->db->query("insert into ".TABLE_PREFIX."_community_users (userid, alias) values ($user_id, '$user_slug')");
      $save_user_group_map = $this->db->query("insert into ".TABLE_PREFIX."_user_usergroup_map (user_id, group_id) values ($user_id, 2)");

      if($save_comm_relation && $save_user_group_map) {
        return ['status' => true, 'name' => $name, 'username' => $username, 'slug' => $user_slug, 'user_hash' => $this->find_user_hash_by_id($user_id)];
      }
    }
    
    return ['status' => false, 'code' => '1001', 'message' => 'something went wrong during registration. Please report this bug. Sorry for the inconvenience'];
  }

  protected function find_user_by_username($username) {
    $results = $this->db->query("select
      user.id,
      user.name,
      user.username,
      user.password,
      comm_user.alias as slug,
      comm_user.thumb,
      user.user_hash
      from drr_users as user,
      drr_community_users as comm_user
      where user.username = '$username'  
      and user.id = comm_user.userid LIMIT 1
    ");

    return $results;
    $results->close();
  }

  public function validate_user_password($userPass, $systemPass) {
    $salt = substr($systemPass, strpos($systemPass, ":") + 1);
    $userPass = md5($userPass . $salt) . ":" . $salt;
    // Compare passwords
    if($userPass === $systemPass) {
      return true;
    }

    return false;
  }
}

?>