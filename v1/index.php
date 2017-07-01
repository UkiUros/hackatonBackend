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

    //----------------------------------------------------//

    // User register
    $app->post('/user/register', function() use ($app) {
               // reading post params
               $name = $app->request->post('name');
               $email = $app->request->post('email');
               $password = $app->request->post('password');
               $about = $app->request->post('about');
               $country = $app->request->post('country');
               $gcmID = $app->request->post('push_id');

               $db = new DbHandler();
               $response = $db->createUser($name, $email, $password, $about, $country, $gcmID);

               // echo json response
               echoRespnse(200, $response);
    });

    // User update
    $app->post('/user/updateProfile', function() use ($app) {
               // check for required params
               verifyRequiredParams(array('full_name', 'email', 'id', 'about'));

              // reading post params
              $uuid = $app->request->post('id');
              $email = $app->request->post('email');
              $about = $app->request->post('about');
              $name = $app->request->post('full_name');

              $db = new DbHandler();
              $response = $db->updateProfile($uuid, $name, $email, $about);

              // echo json response
              echoRespnse(200, $response);
    });

              // upload user profile photo
    $app->post('/user/uploadProfilePhoto', function() use ($app) {
              $uuid = $app->request->post('user_id');
              $email = $app->request->post('email');
              $image = $app->request->post('image_url');

              $db = new DbHandler();
              $response = $db->uploadProfilePhoto($uuid, $email, $image);

              // echo json response
              echoRespnse(200, $response);
    });

    // User login
    $app->post('/user/login', function() use ($app) {

              // reading post params
              $email = $app->request->post('email');
              $password = $app->request->post('password');
              $push_id = $app->request->post('push_id');

              $db = new DbHandler();
              $response = $db->login($email, $password, $push_id);

              // echo json response
              echoRespnse(200, $response);
    });

    // User logout
    $app->post('/user/logout', function() use ($app) {
              // check for required params
              // reading post params
              $uuid = $app->request->post('user_id');
              $email = $app->request->post('email');

              $db = new DbHandler();
              $response = $db->logout($uuid, $email);

              // echo json response
              echoRespnse(200, $response);
    });

    $app->post('/user/searchUser', function() use ($app) {
              $response = array();

              $uuid = $app->request->post('user_id');
              $name = $app->request->post('name');

              $db = new DbHandler();
              $result = $db->searchUser($name, $uuid);

              if(is_null($result)){
                $response["error"] = 40418;
                $response["error_message"] = "no user found";
              }else{
                $response["error"] = 0;
                $response["error_message"] = "success";
                $response["users"] = array();

                // pushing single user into array
                while ($row = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp["user"]["user_id"] = $row["uuid"];
                  $tmp["user"]["name"] = $row["name"];
                  $tmp["user"]["email"] = "";
                  $tmp["user"]["profile_image"] = $row["profile_image"];
                  $tmp["user"]["about"] = $row["about"];
                  $tmp["user"]["country"] = $row["country"];
                  $tmp["user"]["pets"] = $row["pets"];
                  $tmp["user"]["followers"] = $row["followers"];
                  $tmp["user"]["following"] = $row["following"];
                  $tmp["user"]["is_following"] = $row["is_following"];
                  array_push($response["users"], $tmp);
                }
              }

              echoRespnse(200, $response);
    });

    // follow/unfollow
    $app->post('/user/follow', function() use ($app) {
              // reading post params
              $following_id = $app->request->post('following_id');
              $follower_id = $app->request->post('user_id');

              $db = new DbHandler();
              $response = $db->follow($follower_id, $following_id);

              // echo json response
              echoRespnse(200, $response);
    });

    $app->post('/user/getFollowers', function() use ($app) {
              $response = array();

              $uuid = $app->request->post('user_id');

              $db = new DbHandler();
              $result = $db->getFollowers($uuid);

              if(is_null($result)){
                $response["error"] = 40416;
                $response["error_message"] = "no followers";
              }else{
                $response["error"] = 0;
                $response["error_message"] = "success";
                $response["users"] = array();

                // pushing single user into array
                while ($row = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp["user_id"] = $row["uuid"];
                  $tmp["name"] = $row["name"];
                  $tmp["email"] = "";
                  $tmp["profile_image"] = $row["profile_image"];
                  $tmp["about"] = $row["about"];
                  $tmp["country"] = $row["country"];
                  $tmp["pets"] = $row["pets"];
                  $tmp["followers"] = $row["followers"];
                  $tmp["following"] = $row["following"];
                  $tmp["is_following"] = $row["is_following"];

                  array_push($response["users"], $tmp);
                }
              }

              echoRespnse(200, $response);
    });

    $app->post('/user/getFollowing', function() use ($app) {
              $response = array();

              $uuid = $app->request->post('user_id');

              $db = new DbHandler();
              $result = $db->getFollowing($uuid);

              if(is_null($result)){
                $response["error"] = 40417;
                $response["error_message"] = "no following";
              }else{
                $response["error"] = 0;
                $response["error_message"] = "no following";
                $response["users"] = array();

                // pushing single user into array
                while ($row = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp["user_id"] = $row["uuid"];
                  $tmp["name"] = $row["name"];
                  $tmp["email"] = "";
                  $tmp["profile_image"] = $row["profile_image"];
                  $tmp["about"] = $row["about"];
                  $tmp["country"] = $row["country"];
                  $tmp["pets"] = $row["pets"];
                  $tmp["followers"] = $row["followers"];
                  $tmp["following"] = $row["following"];
                  $tmp["is_following"] = $row["is_following"];
                  array_push($response["users"], $tmp);
                }
              }

              echoRespnse(200, $response);
    });


    /* * *
     * Updating user gcm
     *  we use this url to update user's gcm registration id
     */
    $app->put('/user/:id', function($user_id) use ($app) {
              global $app;

              verifyRequiredParams(array('push_id'));

              $gcm_registration_id = $app->request->put('push_id');

              $db = new DbHandler();
              $response = $db->updateGcmID($user_id, $gcm_registration_id);

              echoRespnse(200, $response);
    });

    $app->post('/user/getPets', function() use ($app) {
              $response = array();

              $uuid = $app->request->post('user_id');

              $db = new DbHandler();
              $result = $db->getUsersPets($uuid);

              if(is_null($result)){
                $response["error"] = 4444;
                $response["error_message"] = "no pets";
              }else{
                $response["error"] = 0;
                $response["error_message"] = "success";
                $response["pets"] = array();

                // pushing single user into array
                while ($row = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp["user_id"] = $row["uuid"];
                  $tmp["pet_id"] = $row["upid"];
                  $tmp["owner_name"] = $row["owner_name"];
                  $tmp["owner_profile_image"] = $row["owner_profile_image"];
                  $tmp["pet_name"] = $row["name"];
                  $tmp["profile_image"] = $row["profile_image"];
                  $tmp["about"] = $row["about"];
                  $tmp["gender"] = $row["gender"];
                  $tmp["category"] = $row["category"];
                  $tmp["year"] = $row["year"];
                  $tmp["month"] = $row["month"];
                  $tmp["cuteness"] = $row["cuteness"];
                  $tmp["cuteness_count"] = $row["cuteness_count"];
                  $tmp["alive"] = $row["alive"];
                  $tmp["lost"] = $row["lost"];

                  array_push($response["pets"], $tmp);
                }
              }

              echoRespnse(200, $response);
    });

    $app->post('/pet/addPet', function() use ($app) {
              $uuid = $app->request->post('user_id');
              $email = $app->request->post('email');
              $pet_name = $app->request->post('name');
              $image = $app->request->post('image_url');
              $about = $app->request->post('about');
              $category = $app->request->post('category');
              $gender = $app->request->post('gender');
              $month = $app->request->post('month');
              $year = $app->request->post('year');

              $db = new DbHandler();
              $response = $db->addPet($uuid, $email, $pet_name, $about, $category, $gender, $month, $year, $image);

              // echo json response
              echoRespnse(200, $response);
    });

    $app->post('/pet/updatePet', function() use ($app) {
              $uuid = $app->request->post('user_id');
              $email = $app->request->post('email');
              $upid = $app->request->post('pet_id');
              $pet_name = $app->request->post('name');
              $about = $app->request->post('about');
              $category = $app->request->post('category');
              $gender = $app->request->post('gender');
              $month = $app->request->post('month');
              $year = $app->request->post('year');

              $db = new DbHandler();
              $response = $db->updatePet($uuid, $email, $upid, $pet_name, $about, $category, $gender, $month, $year);

              // echo json response
              echoRespnse(200, $response);
    });

    $app->post('/pet/removePet', function() use ($app) {
              $uuid = $app->request->post('user_id');
              $email = $app->request->post('email');
              $upid = $app->request->post('pet_id');

              $db = new DbHandler();
              $response = $db->removePet($uuid, $email, $upid);

              // echo json response
              echoRespnse(200, $response);
    });

    $app->post('/pet/uploadPetProfilePhoto', function() use ($app) {
              $uuid = $app->request->post('user_id');
              $upid = $app->request->post('pet_id');
              $email = $app->request->post('email');
              $image = $app->request->post('image_url');

              $db = new DbHandler();
              $response = $db->uploadPetProfilePhoto($uuid, $email, $upid, $image);

              // echo json response
              echoRespnse(200, $response);
    });

    $app->post('/pet/petLost', function() use ($app) {
              $uuid = $app->request->post('user_id');
              $upid = $app->request->post('pet_id');
              $email = $app->request->post('email');
              $lat = $app->request->post('lat');
              $lng = $app->request->post('lng');

              $db = new DbHandler();
              $response = $db->petLost($uuid, $email, $upid, $lat, $lng);

              // echo json response
              echoRespnse(200, $response);
    });

    $app->post('/pet/petFound', function() use ($app) {
              $uuid = $app->request->post('user_id');
              $upid = $app->request->post('pet_id');
              $lpid = $app->request->post('lost_pet_id');
              $email = $app->request->post('email');

              $db = new DbHandler();
              $response = $db->petFound($uuid, $email, $lpid, $upid);

              // echo json response
              echoRespnse(200, $response);
    });

    $app->post('/pet/petPassedAway', function() use ($app) {
              $uuid = $app->request->post('user_id');
              $upid = $app->request->post('pet_id');
              $email = $app->request->post('email');

              $db = new DbHandler();
              $response = $db->petPassedAway($uuid, $email, $upid);

              // echo json response
              echoRespnse(200, $response);
    });

    /*
    * CHAT
    */

    /* * *
     * fetching all chat rooms
     */
    $app->get('/inbox', function() {
              $response = array();
              $db = new DbHandler();

              // fetching all user tasks
              $result = $db->getInbox();

              $response["error"] = 0;
              $response["chat_rooms"] = array();

              // pushing single chat room into array
              while ($chat_room = $result->fetch_assoc()) {
              $tmp = array();
              $tmp["chat_room_id"] = $chat_room["chat_room_id"];
              $tmp["name"] = $chat_room["name"];
              $tmp["created_at"] = $chat_room["created_at"];
              array_push($response["chat_rooms"], $tmp);
              }

              echoRespnse(200, $response);
    });

    /**
     * Sending push notification to a single user
     * We use user's gcm registration id to send the message
     * * */
    $app->post('/users/:id/message', function($to_user_id) {
               global $app;
               $db = new DbHandler();

               verifyRequiredParams(array('message'));

               $from_user_id = $app->request->post('user_id');
               $message = $app->request->post('message');

               $response = $db->addMessage($from_user_id, $to_user_id, $message);

               if ($response["error"] == 0) {
                require_once __DIR__ . '/../libs/gcm/gcm.php';
                require_once __DIR__ . '/../libs/gcm/push.php';
                $gcm = new GCM();
                $push = new Push();

                $user = $db->getUser($to_user_id);

                $data = array();
                $data["user"] = $user;
                $data["message"] = $response['message'];
                $data["image"] = "";
                $data["action"] = "private_message";

                $push->setTitle("Google Cloud Messaging");
                $push->setIsBackground(FALSE);
                $push->setFlag(PUSH_FLAG_USER);
                $push->setData($data);

                // sending push message to single user
                $gcm->send($user["gcm_registration_id"], $push->getPush());

                $response["user"] = $user;
                $response["error"] = 0;
                $response["error_message"] = "success";
               }

               echoRespnse(200, $response);
    });


    /**
     * Sending push notification to multiple users
     * We use gcm registration ids to send notification message
     * At max you can send message to 1000 recipients
     * * */
    $app->post('/users/message', function() use ($app) {

               $response = array();
               verifyRequiredParams(array('user_id', 'to', 'message'));

               require_once __DIR__ . '/../libs/gcm/gcm.php';
               require_once __DIR__ . '/../libs/gcm/push.php';

               $db = new DbHandler();

               $user_id = $app->request->post('user_id');
               $to_user_ids = array_filter(explode(',', $app->request->post('to')));
               $message = $app->request->post('message');

               $user = $db->getUser($user_id);
               $users = $db->getUsers($to_user_ids);

               $registration_ids = array();

               // preparing gcm registration ids array
               foreach ($users as $u) {
               array_push($registration_ids, $u['gcm_registration_id']);
               }

               // insert messages in db
               // send push to multiple users
               $gcm = new GCM();
               $push = new Push();

               // creating tmp message, skipping database insertion
               $msg = array();
               $msg['message'] = $message;
               $msg['message_id'] = '';
               $msg['chat_room_id'] = '';
               $msg['created_at'] = date('Y-m-d G:i:s');

               $data = array();
               $data['user'] = $user;
               $data['message'] = $msg;
               $data['image'] = '';

               $push->setTitle("Google Cloud Messaging");
               $push->setIsBackground(FALSE);
               $push->setFlag(PUSH_FLAG_USER);
               $push->setData($data);

               // sending push message to multiple users
               $gcm->sendMultiple($registration_ids, $push->getPush());

               $response['error'] = false;

               echoRespnse(200, $response);
               });

    $app->post('/users/send_to_all', function() use ($app) {

               $response = array();
               verifyRequiredParams(array('user_id', 'message'));

               require_once __DIR__ . '/../libs/gcm/gcm.php';
               require_once __DIR__ . '/../libs/gcm/push.php';

               $db = new DbHandler();

               $user_id = $app->request->post('user_id');
               $message = $app->request->post('message');

               require_once __DIR__ . '/../libs/gcm/gcm.php';
               require_once __DIR__ . '/../libs/gcm/push.php';
               $gcm = new GCM();
               $push = new Push();

               // get the user using userid
               $user = $db->getUser($user_id);

               // creating tmp message, skipping database insertion
               $msg = array();
               $msg['message'] = $message;
               $msg['message_id'] = '';
               $msg['chat_room_id'] = '';
               $msg['created_at'] = date('Y-m-d G:i:s');

               $data = array();
               $data['user'] = $user;
               $data['message'] = $msg;
               $data['image'] = 'http://www.androidhive.info/wp-content/uploads/2016/01/Air-1.png';

               $push->setTitle("Google Cloud Messaging");
               $push->setIsBackground(FALSE);
               $push->setFlag(PUSH_FLAG_USER);
               $push->setData($data);

               // sending message to topic `global`
               // On the device every user should subscribe to `global` topic
               $gcm->sendToTopic('global', $push->getPush());

               $response['user'] = $user;
               $response['error'] = false;

               echoRespnse(200, $response);
               });

    /**
     * Fetching single chat room including all the chat messages
     *  */
    $app->get('/chat_rooms/:id', function($chat_room_id) {
              global $app;
              $db = new DbHandler();

              $result = $db->getChatRoom($chat_room_id);

              $response["error"] = false;
              $response["messages"] = array();
              $response['chat_room'] = array();

              $i = 0;
              // looping through result and preparing tasks array
              while ($chat_room = $result->fetch_assoc()) {
              // adding chat room node
              if ($i == 0) {
              $tmp = array();
              $tmp["chat_room_id"] = $chat_room["chat_room_id"];
              $tmp["name"] = $chat_room["name"];
              $tmp["created_at"] = $chat_room["chat_room_created_at"];
              $response['chat_room'] = $tmp;
              }

              if ($chat_room['user_id'] != NULL) {
              // message node
              $cmt = array();
              $cmt["message"] = $chat_room["message"];
              $cmt["message_id"] = $chat_room["message_id"];
              $cmt["created_at"] = $chat_room["created_at"];

              // user node
              $user = array();
              $user['user_id'] = $chat_room['user_id'];
              $user['username'] = $chat_room['username'];
              $cmt['user'] = $user;

              array_push($response["messages"], $cmt);
              }
              }

              echoRespnse(200, $response);
              });

    /**
     * Verifying required params posted or not
     */
    function verifyRequiredParams($required_fields) {
        $error = false;
        $error_fields = "";
        $request_params = array();
        $request_params = $_REQUEST;
        // Handling PUT request params
        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            $app = \Slim\Slim::getInstance();
            parse_str($app->request()->getBody(), $request_params);
        }
        foreach ($required_fields as $field) {
            if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
                $error = true;
                $error_fields .= $field . ', ';
            }
        }

        if ($error) {
            // Required field(s) are missing or empty
            // echo error json and stop the app
            $response = array();
            $app = \Slim\Slim::getInstance();
            $response["error"] = 4444;
            $response["error_message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
            echoRespnse(400, $response);
            $app->stop();
        }
    }

    /**
     * Validating email address
     */
    function validateEmail($email) {
        $app = \Slim\Slim::getInstance();
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response["error"] = 4042;
            $response["error_message"] = 'Email address is not valid';
            echoRespnse(400, $response);
            $app->stop();
        }
    }

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
