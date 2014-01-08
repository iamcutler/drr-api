<?php

// Import auth helpers
require 'auth_helpers.php';

class Authentication extends DRR_API {
  public function process_registration($user) {
    // Check if username is taken
    if(!$this->check_username_uniqueness($user['username'])) {
      // Check if user email adrress has been used
      if(!$this->check_email_uniqueness($user['email'])) {
        // Save user data
        if($this->save_new_user($user)) {
          $results = ['status' => true];
        } else {
          $results = ['status' => false, 'code' => '1001', 'message' => 'something went wrong during registration. Please report this bug. Sorry for the inconvenience'];
        }
      } else {
        $results = ['status' => false, 'code' => '101', 'message' => 'email address has already been used'];
      }
    } else {
      $results = ['status' => false, 'code' => '100', 'message' => 'username is already taken'];
    }

    $this->toJSON($results);
  }

  protected function check_username_uniqueness($username) {
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

  protected function generate_user_password($password) {
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
    $result = 0;

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
      if($save_comm_relation) {
        $result = 1;
      }
    }

    return $result;
  }
}

?>