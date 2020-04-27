<?php
/**
 * Created by PhpStorm.
 * User: leeyipfung
 * Date: 2/28/2018
 * Time: 11:09 AM
 */

//date_default_timezone_set('Asia/Kuala_Lumpur');

include "db_init.php";

date_default_timezone_set('Asia/Kuala_Lumpur');



$sdate =  date('Y-m-d-H-i-s');

$ownerName = $_POST["ROwnerName"];
$ownerContact = $_POST["RContact"];
$shopDescription = $_POST["RDesc"];
$profilePhotoData = $_POST["ProfilePhotoData"];
$coverPhotoData = $_POST["CoverPhotoData"];
$retailerEmail=$_POST["retailerEmail"];
$cover="cover";
$profile="profile";
//$coverImagePath = "Retailer/Images/$sdate$ownerName$ownerContact$cover.jpg";
//$profileImagePath = "http://www.cashierbook.com/Retailer/Images/$sdate$ownerName$ownerContact$profile.jpg";

//$sdate$ownerName$ownerContact$cover


$coverImagePath = "images/$ownerName$cover$sdate.jpg";
$profileImagePath = "images/$ownerName$profile$sdate.jpg";

$coverImagePath =str_replace(' ', '', $coverImagePath);
$profileImagePath=str_replace(' ', '', $profileImagePath);

$coverServerURL = "http://www.cashierbook.com/retailer/$coverImagePath";
$profileServerURL = "http://www.cashierbook.com/retailer/$profileImagePath";



$SQL_Update = "UPDATE retailer SET ROwnerName = '$ownerName', RContact = '$ownerContact', RDesc = '$shopDescription',coverPicURL = '$coverServerURL',profilePicURL = '$profileServerURL' WHERE REmail ='$retailerEmail'";

if(mysqli_query($conn,$SQL_Update))
{
    file_put_contents($coverImagePath,base64_decode($coverPhotoData));
    file_put_contents($profileImagePath,base64_decode($profilePhotoData));
 
    echo "Profile Updated Successfully";
}
else
{
    echo "Something went wrong, please contact server admin.".mysqli_error($conn);
}

mysqli_close($conn);
?>