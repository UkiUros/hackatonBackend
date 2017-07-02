<?php

    error_reporting(-1);
    @ini_set('display_errors', 'On');

    require_once '../include/db_handler.php';
    require '.././libs/Slim/Slim.php';

    \Slim\Slim::registerAutoloader();

    $app = new \Slim\Slim();

    $app->get('/getLocationData',  function() use ($app) {
              $user_id = $app->request->get('user_id');

              $db = new DbHandler();
              $result = $db->getLocationData($user_id);

              if(is_null($result)){
                $response["error"] = "no results";
              }else{
                $response["locations"] = array();
                // pushing single location into array
                while ($row = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp["location_id"] = $row["id"];
                  $tmp["user_id"] = $row["user_id"];
                  $tmp["latitude"] = $row["latitude"];
                  $tmp["longitude"] = $row["longitude"];
                  $tmp["address"] = $row["address"];
                  $tmp["city"] = $row["city"];
                  $tmp["state"] = $row["state"];
                  $tmp["country"] = $row["country"];
                  $tmp["postal_code"] = $row["postal_code"];
                  $tmp["known_address"] = $row["known_address"];
                  $tmp["time_stamp"] = $row["time_stamp"];
                  $tmp["created_at"] = $row["created_at"];

                  array_push($response["locations"], $tmp);
                }
              }

              echoRespnse(200, $response);
    });

    $app->get('/getDeviceData',  function() use ($app) {
              $user_id = $app->request->get('user_id');

              $db = new DbHandler();
              $result = $db->getDeviceData($user_id);

              if(is_null($result)){
                $response["error"] = "no results";
              }else{
                $response["devices"] = array();
                // pushing single location into array
                while ($row = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp["device_data_id"] = $row["id"];
                  $tmp["user_id"] = $row["user_id"];
                  $tmp["imei"] = $row["imei"];
                  $tmp["phone_number"] = $row["phone_number"];
                  $tmp["phone_model"] = $row["phone_model"];
                  $tmp["os"] = $row["os"];
                  $tmp["created_at"] = $row["created_at"];

                  array_push($response["devices"], $tmp);
                }
              }
              echoRespnse(200, $response);
    });

    $app->post('/location', function() use ($app) {
               // reading post params
               $user_id = $app->request->post('user_id');
               $latitude = $app->request->post('latitude');
               $longitude = $app->request->post('longitude');
               $address = $app->request->post('address');
               $city = $app->request->post('city');
               $state = $app->request->post('state');
               $country = $app->request->post('country');
               $postal_code = $app->request->post('postal_code');
               $known_address = $app->request->post('known_address');
               $time_stamp = $app->request->post('time_stamp');

               $db = new DbHandler();
               $response = $db->setLocation($user_id, $latitude, $longitude, $address, $city, $state, $country, $postal_code, $known_address, $time_stamp);

               // echo json response
               echoRespnse(200, $response);
    });

    $app->post('/device', function() use ($app) {
               // reading post params
               $user_id = $app->request->post('user_id');
               $imei = $app->request->post('imei');
               $phone_number = $app->request->post('phone_number');
               $phone_model = $app->request->post('phone_model');
               $os = $app->request->post('os');

               $db = new DbHandler();
               $response = $db->setDevice($user_id, $imei, $phone_number, $phone_model, $os);

               // echo json response
               echoRespnse(200, $response);
    });

    $app->post('/safe_location', function() use ($app) {
               // reading post params
               $user_id = $app->request->post('user_id');
               $latitude = $app->request->post('latitude');
               $longitude = $app->request->post('longitude');

               $db = new DbHandler();
               $response = $db->setSafeLocation($user_id, $latitude, $longitude);

               // echo json response
               echoRespnse(200, $response);
    });

    $app->get('/getSafeLocation',  function() use ($app) {
              $user_id = $app->request->get('user_id');

              $db = new DbHandler();
              $result = $db->getSafeLocation($user_id);

              if(is_null($result)){
                $response["error"] = "no results";
              }else{
                $response["safe_locations"] = array();
                // pushing single location into array
                while ($row = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp["user_id"] = $row["user_id"];
                  $tmp["latitude"] = $row["latitude"];
                  $tmp["longitude"] = $row["longitude"];

                  array_push($response["safe_locations"], $tmp);
                }
              }
              echoRespnse(200, $response);
    });

    //----------------------------------------------------//


    function IsNullOrEmptyString($str) {
        return (!isset($str) || trim($str) === '');
    }

    /**
     * Echoing json response to client
     * @param String $status_code Http response code
     * @param Int $response Json response
     */
    function echoRespnse($status_code, $response) {
        $app = \Slim\Slim::getInstance();
        // Http response code
        $app->status($status_code);

        // setting response content type to json
        $app->contentType('application/json');

        echo json_encode($response);
    }

    $app->run();
    ?>
