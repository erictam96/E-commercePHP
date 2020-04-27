<?php
require_once "retailer_info.php";
$db = new retailer_info();

if (isset($_POST['email']) &&isset($_POST['password2']) &&isset($_POST['companyno'])&&isset($_POST['shopname'])&&
    isset($_POST['fullname'])&&isset($_POST['shopaddr'])&&isset($_POST['phoneno'])&&isset($_POST['uid'])&&isset($_POST['agreeChk'])) {

    $email = $_POST['email'];
    $password2 = $_POST['password2'];
    $companyno = $_POST['companyno'];
    $shopname = $_POST['shopname'];
    $fullname = $_POST['fullname'];
    $shopaddr = $_POST['shopaddr'];
    $phone = $_POST['phoneno'];
    $uid = $_POST['uid'];
    $agree=1;

    

    $user = $db->registerRetailerAccount($uid,$email,$password2,$companyno,$shopname,$fullname,$shopaddr,$phone,$agree);
    if($user!=false){
        echo "<h1><span style='text-align: center;'\">Retailer ".$shopname." successfully registered!</h1>";
    }else{
        echo "<h1 align=\"center\"><a style='color: crimson'>FAILED</a>  REGISTRATION
</h1></br> <img src='failedd.gif' style='display: block;margin-left: auto;margin-right: auto'><br>";    }
}else if (isset($_POST['email']) &&isset($_POST['password2']) &&isset($_POST['companyno'])&&isset($_POST['shopname'])&&
    isset($_POST['fullname'])&&isset($_POST['shopaddr'])&&isset($_POST['phoneno'])&&isset($_POST['uid'])) {

    $email = $_POST['email'];
    $password2 = $_POST['password2'];
    $companyno = $_POST['companyno'];
    $shopname = $_POST['shopname'];
    $fullname = $_POST['fullname'];
    $shopaddr = $_POST['shopaddr'];
    $phone = $_POST['phoneno'];
    $uid = $_POST['uid'];
    $agree=0;

    $user = $db->registerRetailerAccount($uid,$email,$password2,$companyno,$shopname,$fullname,$shopaddr,$phone,$agree);
    if($user!=false){
        echo "<h1><span style='text-align: center;'\">Retailer ".$shopname." successfully registered!</h1>";
    }else{
        echo "<h1 align=\"center\"><a style='color: crimson'>FAILED</a>  REGISTRATION
</h1></br> <img src='failedd.gif' style='display: block;margin-left: auto;margin-right: auto'><br>";    }
}
else{
    echo "<h1 align=\"center\"><a style='color: crimson'>FAILED</a>  REGISTRATION
</h1></br> <img src='failedd.gif' style='display: block;margin-left: auto;margin-right: auto'><br>";
}
?>

