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
        ORDER BY created_at DESC
        LIMIT 20");

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
          $response["error"] = 4444;
          $response["error_message"] = "Failed to insert new location data";
        }
        return $response;
    }

    public function setSafeLocation($user_id, $latitude, $longitude) {
        $response = array();
        $stmt = $this->conn->prepare("INSERT INTO safe_location
        (user_id, latitude, longitude)
        VALUES(?, ?, ?)");
        $stmt->bind_param("sss", $user_id, $latitude, $longitude);

        $result = $stmt->execute();
        $stmt->close();

        if($result) {
          $response["error"] = 0;
          $response["error_message"] = "success";
        } else {
          // Failed to create user
          $response["error"] = 4444;
          $response["error_message"] = "Failed to insert new safe location data";
        }
        return $response;
    }

    public function getSafeLocation($user_id) {
      $stmt = $this->conn->prepare("SELECT *
        FROM safe_location
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 3");

      $stmt->bind_param("s", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $stmt->close();
      return $result;
    }
// -----------------------------------------------//
}

?>
