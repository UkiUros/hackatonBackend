<?php

/**
 *
 */
class DbHandler {
    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/db_connect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function getLocationData($user_id) {
      $stmt = $this->conn->prepare("SELECT *
        FROM location
        WHERE user_id = ?
        ORDER BY created_at DESC");

      $stmt->bind_param("s", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $stmt->close();
      return $result;
    }

    public function getDeviceData($user_id) {
      $stmt = $this->conn->prepare("SELECT *
        FROM device_data
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 10");

      $stmt->bind_param("s", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $stmt->close();
      return $result;
    }

    public function setLocation($user_id, $latitude, $longitude, $address, $city, $state, $country, $postal_code, $known_address, $time_stamp) {
        $response = array();
        //subscribe to itself
        $stmt = $this->conn->prepare("INSERT INTO location
        (user_id, latitude, longitude, address, city, state, country, postal_code, known_address, time_stamp)
        VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $user_id, $latitude, $longitude, $address, $city, $state, $country, $postal_code, $known_address, $time_stamp);

        $result = $stmt->execute();
        $stmt->close();

        if($result) {
          $response["error"] = 0;
          $response["error_message"] = "success";
        } else {
          // Failed to create user
          $response["error"] = 4444;
          $response["error_message"] = "Failed to insert new location data";
        }
        return $response;
    }

    public function setDevice($user_id, $imei, $phone_number, $phone_model, $os) {
        $response = array();
        //subscribe to itself
        $stmt = $this->conn->prepare("INSERT INTO device_data
        (user_id, imei, phone_number, phone_model, os)
        VALUES(?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $user_id, $imei, $phone_number, $phone_model, $os);

        $result = $stmt->execute();
        $stmt->close();

        if($result) {
          $response["error"] = 0;
          $response["error_message"] = "success";
        } else {
          // Failed to create user
          $response["error"] = 4444;
          $response["error_message"] = "Failed to insert new location data";
        }
        return $response;
    }
// -----------------------------------------------//

    public function getStatus($app_key) {
        $stmt = $this->conn->prepare("SELECT app_version, status FROM status ORDER BY created_at DESC LIMIT 1");
        $result = $stmt->execute();
        $stmt->bind_result($app_version, $status);
        $stmt->fetch();
        $response = array();
        $response["app_version"] = $app_version;
        $response["status"] = $status;
        $response["error"] = 0;
        $response["error_message"] = "success";
        $stmt->close();
        return $response;
    }

    // creating new user if not existed
    public function createUser($name, $email, $password, $about, $country, $gcmID) {
        $response = array();

        // First check if user already existed in db
        if (!$this->doesUserExists($email)) {
            $uuid = uniqid('', true);
            $hash = $this->hashSSHA($password);
            $encrypted_password = $hash["encrypted"]; // encrypted password
            $salt = $hash["salt"]; // salt
            $profile_image = "";
            $pets = 0;
            $followers = 0;
            $following = 0;

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO users(uuid, name, email, password, salt, profile_image, about, country, pets, followers, following, gcm_id) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssiiis", $uuid, $name, $email, $encrypted_password, $salt, $profile_image, $about, $country, $pets, $followers, $following, $gcmID);

            $result = $stmt->execute();
            $stmt->close();

            // Check for successful insertion
            if ($result) {
                //subscribe to itself
                $stmt = $this->conn->prepare("INSERT INTO subscriber (follower_id, user_id) VALUES(?, ?)");
                $stmt->bind_param("ss", $uuid, $uuid);

                $result_subscribe = $stmt->execute();
                $stmt->close();

                if($result_subscribe){

                  $Subject = "Petwork registration";
                  $Message = $name.", welcome to Petwork! Email: ".$email." \r\n Password: ".$password;
                  $Headers = "From: registration@petwork.com \r\n" .
                  "Reply-To: no-reply@petwork.com \r\n" .
                  "Content-type: text/html; charset=UTF-8 \r\n";
                  mail($email, $Subject, $Message, $Headers);

                  $response["user"] = $this->getUserByEmail($email);
                  $response["error"] = 0;
                  $response["error_message"] = "success";
                }else{
                  // Failed to create user
                  $response["error"] = 4444;
                  $response["error_message"] = "Error occurred while subscribing and registering";
                }

            } else {
                // Failed to create user
                $response["error"] = 4444;
                $response["error_message"] = "Oops! An error occurred while registering";
            }
        } else {
            // User with same email already existed in the db
            $response["error"] = 4041;
            $response["error_message"] = "User with same email already exists";
        }

        return $response;
    }

    // updating user
    public function updateProfile($uuid, $name, $email, $about) {
      $response = array();
      $stmt = $this->conn->prepare("UPDATE users SET name = ?, about = ? WHERE email = ? AND uuid = ?");
      $stmt->bind_param("ssss", $uuid, $name, $email, $about);

      $result = $stmt->execute();
      $stmt->close();
      if ($result) {
          $response["error"] = 0;
          $response["error_message"] = 'Profile updated successfully';
        } else {
          // Failed to update user
          $response["error"] = 4444;
          $response["error_message"] = "Failed to update profile";
        }

        return $response;
      }

    // upload profile photo
    public function uploadProfilePhoto($uuid, $email, $image_url) {
        $response = array();
        $stmt = $this->conn->prepare("UPDATE users SET profile_image = ?, uuid = ? WHERE email = ?");
        $stmt->bind_param("sss", $image_url, $uuid, $email);

        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            $response["error"] = 0;
            $response["error_message"] = 'Profile photo uploaded successfully';
        } else {
            // Failed to update user
            $response["error"] = 4444;
            $response["error_message"] = "Failed to update profile photo";
        }

        return $response;
    }

    public function login($email, $password, $gcmID) {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");

        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // verifying user password
            $salt = $user['salt'];
            $encrypted_password = $user['password'];
            $hash = $this->checkhashSSHA($salt, $password);
            // check for password equality
            if ($encrypted_password == $hash) {
                // user authentication details are correct

                $this->updateGcmID($email, $gcmID);
                $response["user"] = $this->getUserByEmail($email);

                $response["error"] = 0;
                $response["error_message"] = "success";
            }else{
              $response["error"] = 4043;
              $response["error_message"] = "bad credentials";
            }
        } else {
            $response["error"] = 4444;
            $response["error_message"] = "OOooops something went wrong.";
        }

        return $response;
    }

    public function logout($uuid, $email){
      $stmt = $this->conn->prepare("UPDATE users SET gcm_id = '' WHERE email = ? AND uuid = ?");
      $stmt->bind_param("ss", $email, $uuid);

      $result = $stmt->execute();
      $stmt->close();

      $response = array();
      if($result){
        $response["error"] = 0;
        $response["error_message"] = "success";
      }else{
        $response["error"] = 4045;
        $response["error_message"] = "failed to loggout";
      }

      return $response;
    }

    // updating user GCM registration ID
    public function updateGcmID($email, $gcm_registration_id) {
      $response = array();
      $stmt = $this->conn->prepare("UPDATE users SET gcm_id = ? WHERE email = ?");
      $stmt->bind_param("ss", $gcm_registration_id, $email);

      if ($stmt->execute()) {
          $response["error"] = 0;
          $response["error_message"] = 'GCM registration ID updated successfully';
        } else {
          // Failed to update user
          $response["error"] = 4044;
          $response["error_message"] = "Failed to update GCM registration ID";
          $stmt->error;
        }
        $stmt->close();

        return $response;
      }

    /**
     * Fetching user by email
     */
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT uuid, name, email, profile_image, about, country, pets, followers, following FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($uuid, $name, $email, $profile_image, $about, $country, $pets, $followers, $following);
            $stmt->fetch();
            $user = array();
            $user["user_id"] = $uuid;
            $user["name"] = $name;
            $user["email"] = $email;
            $user["profile_image"] = $profile_image;
            $user["about"] = $about;
            $user["country"] = $country;
            $user["pets"] = $pets;
            $user["followers"] = $followers;
            $user["following"] = $following;
            $user["is_following"] = 0;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user by unique user id
     * @param String $uuid User id
     */
    public function getUserById($uuid) {
        $stmt = $this->conn->prepare("SELECT uuid, name, profile_image, about, country, pets, followers, following FROM users WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($uuid, $name, $profile_image, $about, $country, $pets, $followers, $following);
            $stmt->fetch();
            $user = array();
            $user["user_id"] = $uuid;
            $user["name"] = $name;
            $user["email"] = "";
            $user["profile_image"] = $profile_image;
            $user["about"] = $about;
            $user["country"] = $country;
            $user["pets"] = $pets;
            $user["followers"] = $followers;
            $user["following"] = $following;
            $user["is_following"] = 0;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    public function searchUser($name, $uuid) {
	      $likeString = $name . '%';
        $stmt = $this->conn->prepare("SELECT (SELECT COUNT(*) FROM subscriber, users WHERE subscriber.user_id = users.uuid AND subscriber.follower_id = ? LIMIT 1) as is_following, users.uuid, users.name, users.profile_image, users.about, users.country, users.pets, users.followers, users.following FROM users WHERE name LIKE ?");
        $stmt->bind_param("ss", $uuid, $likeString);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    /**
     * follow/unfollow to user
     */
    public function follow($follower_id, $following_id) {
      $response = array();
      // (id, user_id, follower_id, created_at)
      $stmt = $this->conn->prepare("SELECT * from subscriber WHERE user_id = ? AND follower_id = ? LIMIT 1");
      $stmt->bind_param("ss", $following_id, $follower_id);
      $stmt->execute();
      $stmt->store_result();
      $num_rows = $stmt->num_rows;
      $stmt->close();

      if($num_rows > 0){
        // unfollow
        $stmt = $this->conn->prepare("DELETE from subscriber WHERE follower_id = ? AND user_id = ?");
        $stmt->bind_param("ss", $follower_id, $following_id);
        $result_unfollow = $stmt->execute();
        $stmt->close();

        if ($result_unfollow) {
          $stmt = $this->conn->prepare("UPDATE users SET followers = followers - 1 WHERE uuid = ?");
          $stmt->bind_param("s", $following_id);
          $stmt->execute();
          $stmt->close();

          $stmt = $this->conn->prepare("UPDATE users SET following = following - 1 WHERE uuid = ?");
          $stmt->bind_param("s", $follower_id);
          $result_unfollow = $stmt->execute();
          $stmt->close();

          if($result_unfollow){
            $response["error"] = 0;
            $response["error_message"] = "follow";
          }else{
            $response["error"] = 4444;
            $response["error_message"] = "error while unfollowing and updating followers/following";
          }

        } else {
          $response["error"] = 4444;
          $response["error_message"] = "error while unfollowing";

        }
      }else{
        // follow

        $stmt = $this->conn->prepare("INSERT INTO subscriber (follower_id, user_id) VALUES(?, ?)");
        $stmt->bind_param("ss", $follower_id, $following_id);

        $result_follow = $stmt->execute();
        $stmt->close();

        $response = array();
        if($result_follow){
          $stmt = $this->conn->prepare("UPDATE user SET followers = followers + 1 WHERE uuid = ?");
          $stmt->bind_param("s", $following_id);
          $stmt->execute();
          $stmt->close();

          $stmt = $this->conn->prepare("UPDATE user SET following = following + 1 WHERE uuid = ?");
          $stmt->bind_param("s", $follower_id);
          $result_follow = $stmt->execute();
          $stmt->close();

          if($result_follow){
            $response["error"] = 0;
            $response["error_message"] = "following";
          }else{
            $response["error"] = 4444;
            $response["error_message"] = "error while following and updating followers/following";
          }
        }else{
          $response["error"] = 4444;
          $response["error_message"] = "Error occurred while following";
        }
      }

      return $response;
    }

    public function getFollowers($uuid) {
        $stmt = $this->conn->prepare("SELECT (SELECT COUNT(*) FROM * subscriber WHERE subscriber.user_id = users.uuid AND subscriber.follower_id = ? LIMIT 1) as is_following, users.uuid, users.name, users.profile_image, users.about, users.country, users.pets, users.followers, users.following FROM users, subscriber WHERE subscriber.user_id = ? AND users.uuid = subscriber.follower_id AND subscriber.follower_id <> ?");
        $stmt->bind_param("sss", $uuid, $uuid, $uuid);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function getFollowing($uuid) {
        $stmt = $this->conn->prepare("SELECT (SELECT COUNT(*) FROM * subscriber WHERE subscriber.user_id = users.uuid AND subscriber.follower_id = ? LIMIT 1) as is_following, users.uuid, users.name, users.profile_image, users.about, users.country, users.pets, users.followers, users.following FROM users, subscriber WHERE subscriber.follower_id = ? AND users.uuid = subscriber.user_id AND subscriber.user_id <> ?");
        $stmt->bind_param("sss", $uuid, $uuid, $uuid);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function getUsersPets($uuid) {
        $stmt = $this->conn->prepare("SELECT pets.*, users.uuid as uuid, users.name as owner_name, users.profile_image as owner_profile_image FROM pets, users WHERE pets.uuid = ? AND pets.uuid = users.uuid");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    private function doesUserExists($email) {
        $stmt = $this->conn->prepare("SELECT uuid from users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    public function hashSSHA($password) {

        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }

    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) {

        $hash = base64_encode(sha1($password . $salt, true) . $salt);

        return $hash;
    }

    /**
    * END OF USER METHODS
    **/

    /**
    * PET METHODS
    **/

    public function addPet($uuid, $email, $pet_name, $about, $category, $gender, $month, $year, $image){
      $response = array();

      $upid = uniqid('', true);
      $cuteness = 0;
      $cuteness_count = 0;
      $alive = 1;
      $lost = 0;
      // insert query
      $stmt = $this->conn->prepare("INSERT INTO pets(upid, uuid, name, profile_image, about, category, gender, month, year, cuteness, cuteness_count, alive, lost) VALUES(?, (SELECT uuid from users WHERE uuid = ? AND email = ? ), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("sssssiiiiiiiii", $upid, $uuid, $email, $pet_name, $image, $about, $category, $gender, $month, $year, $cuteness, $cuteness_count, $alive, $lost);

      $result = $stmt->execute();
      $stmt->close();

      // Check for successful insertion
      if ($result) {
        $stmt = $this->conn->prepare("UPDATE users SET pets = pets + 1 WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);

        $result_update = $stmt->execute();
        $stmt->close();

        if($result_update){

          $response["error"] = 0;
          $response["error_message"] = "success";
        }else{
          $response["error"] = 4444;
          $response["error_message"] = "failed to update pet number";
        }

      }else{
        $response["error"] = 4046;
        $response["error_message"] = "Failed to add pet";
      }

      return $response;
    }

    public function updatePet($uuid, $email, $upid, $pet_name, $about, $category, $gender, $month, $year){
      $response = array();

      $stmt = $this->conn->prepare("UPDATE pets SET name = ?, about = ?, category = ?, gender = ?, month = ?, year = ? WHERE upid = ? AND uuid = (SELECT uuid from users WHERE uuid = ? AND email = ?");
      $stmt->bind_param("ssiiiisss", $pet_name, $about, $category, $gender, $month, $year, $upid, $uuid, $email);

      $result = $stmt->execute();
      $stmt->close();

      if($result){
          $response["error"] = 0;
          $response["error_message"] = "success";
      }else{
          $response["error"] = 4047;
          $response["error_message"] = "Failed to update pet";
      }

      return $response;
    }

    public function removePet($uuid, $email, $upid){
      $response = array();

      $stmt = $this->conn->prepare("DELETE FROM pets WHERE upid = ? AND uuid = (SELECT uuid from users WHERE uuid = ? AND email = ?)");
      $stmt->bind_param("sss", $upid, $uuid, $email);

      $result = $stmt->execute();
      $stmt->close();

      if($result){
        $stmt = $this->conn->prepare("UPDATE users SET pets = pets - 1 WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);

        $result_update = $stmt->execute();
        $stmt->close();

        if($result_update){
          $response["error"] = 0;
          $response["error_message"] = "success";
        }else{
          $response["error"] = 4444;
          $response["error_message"] = "failed to update pet number";
        }

      }else{
        $response["error"] = 4048;
        $response["error_message"] = "Failed to remove pet";
      }

      return $response;
    }

    public function uploadPetProfilePhoto($uuid, $email, $upid, $image_url) {
      $response = array();
      $stmt = $this->conn->prepare("UPDATE pets SET profile_image = ? WHERE upid = ? AND uuid = (SELECT uuid from users WHERE uuid = ? AND email = ?)");
      $stmt->bind_param("ssss", $image_url, $upid, $uuid, $email);

      $result = $stmt->execute();
      $stmt->close();
      if ($result) {
          $response["error"] = 0;
          $response["error_message"] = 'Profile photo uploaded successfully';
      } else {
          // Failed to update user
          $response["error"] = 4444;
          $response["error_message"] = "Failed to update profile photo";
      }

      return $response;
    }

    public function petLost($uuid, $email, $upid, $lat, $lng){
      $response = array();
      $lost = 1;

      $stmt = $this->conn->prepare("UPDATE pets SET lost = ? WHERE upid = ? AND uuid = (SELECT uuid from userss WHERE uuid = ? AND email = ?)");
      $stmt->bind_param("isss", $lost, $upid, $uuid, $email);

      $result = $stmt->execute();
      $stmt->close();

      if($result){
        $found = 0;
        $lpid = uniqid('', true);
        $stmt = $this->conn->prepare("INSERT INTO lost_pets (lpid, upid, found, lat, lng) VALUES((SELECT uuid from pets WHERE upid = ?), ?, ?, ?, ?)");
        $stmt->bind_param("ssidd", $lpid, $upid, $found, $lat, $lng);

        $result_second = $stmt->execute();
        $stmt->close();

        if($result_second){
          $ufid = uniqid('', true);
          $type_lost = 3;

          $stmt = $this->conn->prepare("INSERT INTO feeds (ufid, uuid, description, image_url, likes_no, visibility, type) VALUES(?, (SELECT uuid from userss WHERE uuid = ? AND email = ?), (SELECT upid from petss WHERE upid = ?), '', '0', '1', ?)");
          $stmt->bind_param("ssssi", $ufid, $uuid, $email, $upid, $type_lost);

          $result_create_feed = $stmt->execute();
          $stmt->close();

          if($result_create_feed){
            $response["error"] = 0;
            $response["error_message"] = "success";
          }else{
            $response["error"] = 40411;
            $response["error_message"] = "failed to create feed for lost pet";
          }

        }else{
          $response["error"] = 40410;
          $response["error_message"] = "failed to add pet to lost pets list";
        }

      }else{
        $response["error"] = 4049;
        $response["error_message"] = "Failed mark pet as lost";
      }

      return $response;
    }

    public function petFound($uuid, $email, $lpid, $upid){
      $response = array();
      $lost = 0;

      $stmt = $this->conn->prepare("UPDATE pets SET lost = ? WHERE upid = ? AND uuid = (SELECT uuid from userss WHERE uuid = ? AND email = ?)");
      $stmt->bind_param("isss", $lost, $upid, $uuid, $email);

      $result = $stmt->execute();
      $stmt->close();

      if($result){
        $found = 1;
        $stmt = $this->conn->prepare("UPDATE lost_pets SET found = ? WHERE upid = ? AND lpid = ?");
        $stmt->bind_param("isss", $found, $upid, $lpid);

        $result_second = $stmt->execute();
        $stmt->close();

        if($result_second){
          $ufid = uniqid('', true);
          $type_found = 4; // found pet

          $stmt = $this->conn->prepare("INSERT INTO feeds (ufid, uuid, description, image_url, likes_no, visibility, type) VALUES(?, (SELECT uuid from userss WHERE uuid = ? AND email = ?), (SELECT upid from petss WHERE upid = ?), '', '0', '1', ?)");
          $stmt->bind_param("ssssi", $ufid, $uuid, $email, $upid, $type_found);

          $result_create_feed = $stmt->execute();
          $stmt->close();

          if($result_create_feed){
            $response["error"] = 0;
            $response["error_message"] = "success";
          }else{
            $response["error"] = 40414;
            $response["error_message"] = "failed to create feed";
          }

        }else{
          $response["error"] = 40413;
          $response["error_message"] = "failed to add pet to lost pets list";
        }

      }else{
        $response["error"] = 40412;
        $response["error_message"] = "Failed mark pet as found";
      }

      return $response;
    }

    public function petPassedAway($uuid, $email, $upid){
      $response = array();
      $alive = 0;

      $stmt = $this->conn->prepare("UPDATE pets SET alive = '?' WHERE upid = ? AND uuid = (SELECT uuid from users WHERE uuid = ? AND email = ?)");
      $stmt->bind_param("isss", $alive, $upid, $uuid, $email);

      $result = $stmt->execute();
      $stmt->close();

      if($result){
        $ufid = uniqid('', true);
        $type = 5;
        $stmt = $this->conn->prepare("INSERT INTO feeds (ufid, uuid, description, image_url, likes_no, visibility, type) VALUES(?, (SELECT uuid from users WHERE uuid = ? AND email = ?), (SELECT upid from pets WHERE upid = ?), '', '0', '1', ?)");
        $stmt->bind_param("ssssi", $ufid, $uuid, $email, $upid,  $type);

        $create_feed = $stmt->execute();
        $stmt->close();

        if($create_feed){
          $response["error"] = 0;
          $response["error_message"] = "success";
        }else{
          $response["error"] = 40414;
          $response["error_message"] = "Failed to create feed";
        }

      }else{
        $response["error"] = 40415;
        $response["error_message"] = "Failed to mark pet as passed away";
      }

      return $response;
    }
}

?>
