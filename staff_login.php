<?php
/**
 * Created by PhpStorm.
 * User: erict
 * Date: 5/28/2018
 * Time: 3:36 PM
 */
require_once 'staff_info.php';
$db = new staff_info();


// json response array
$response = array("error" => FALSE);

switch (true) {
    case isset($_POST['verifyRole']):

        // receiving the post params
        $sid = $_POST["verifyRole"];

        //get the user by email and password
        $user = $db->VerifyUserRole($sid);

        if ($user != false) {
            //user is found
            $response["error"] = FALSE;
            echo json_encode($response);
        } else {
            //user is not found with the credentials
            $response["error"] = TRUE;
            $response["error_msg"] = "Login credentials are wrong. Please try again!";
            echo json_encode($response);
        }
        break;

}