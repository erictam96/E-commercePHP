<?php
require_once 'retailer_info.php';
$db = new retailer_info();

// json response array
$response = array("error" => FALSE);

switch (true){
    case isset($_POST['verifyRole']):
        // receiving the post params
        $uid = $_POST["verifyRole"];

        // get the user by email and password
        $user = $db->VerifyUserRole($uid);

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
    case isset($_POST['changepassword']):
        $changePassword = $_POST["changepassword"];
        $objArray = json_decode($changePassword,true);
        $uid = $objArray['Uid'];
        $password = $objArray['NewPassword'];
        $db->changePassword($uid,$password);
        break;

    case isset($_POST['getDisplayName']):
        $db->getDisplayName($_POST['getDisplayName']);
        break;
}

?>