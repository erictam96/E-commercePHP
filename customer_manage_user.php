<?php
/**
 * Created by PhpStorm.
 * User: erict
 * Date: 3/16/2018
 * Time: 12:24 PM
 */
require_once 'customer_info.php';
$db = new customer_info();

switch(true) {
    case isset($_POST['registerCust']):
        $registerCust = $_POST["registerCust"];
        $custArray = json_decode($registerCust, true);
        $CustDetails = $custArray['register'];

            //create new user
            $user = $db->RegisterCustInfo($CustDetails);
            if ($user) {
                // user stored successfully
                $response["error"] = FALSE;
                $response["user"]["name"] = $user["name"];
                echo json_encode($response);

            } else {
                // user failed to store
                $response["error"] = TRUE;
                $response["error_msg"] = "Unknown error occurred in registration!";
                echo json_encode($response);
            }
        break;
    case isset($_POST['verifyRole']):
        // receiving the post params
        $verifyRole = $_POST["verifyRole"];
        $CustObj = json_decode($verifyRole, true);

        $uid = $CustObj["uid"];
        $email=$CustObj["email"];
        $name = $CustObj["name"];

        //get displayname, email and password
        $user = $db->VerifyUserRole($uid,$email,$name);

        if ($user != false) {
            //user is found
            $response["error"] = FALSE;
           //$response["user"]["uid"] = $user["uid"];
            echo json_encode($response);
        } else {
            //user is not found with the credentials
            $response["error"] = TRUE;
            $response["error_msg"] = "Login credentials are wrong. Please try again!";
            echo json_encode($response);
        }
        break;
    case isset($_POST['getNotification']):
        $db->GetNotification($_POST['getNotification']);
        break;
    case isset($_POST['notifyID']):
        $db->UpdateNotification($_POST['notifyID']);
        break;

    case isset($_POST['getDisplayName']):
        $db->getDisplayName($_POST['getDisplayName']);
        break;
    default:
        break;
}