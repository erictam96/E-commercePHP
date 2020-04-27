<?php
/**
 * Created by PhpStorm.
 * User: leeyipfung
 * Date: 3/1/2018
 * Time: 3:51 PM
 */

require_once 'retailer_info.php';
$db = new retailer_info();

$oldPassword=$_POST["oldPassword"];
$newPassword=$_POST["newPassword"];
$retailerEmail=$_POST["retailerEmail"];


$result = $db->updateUserPassword($oldPassword,$newPassword,$retailerEmail);

echo $result;

?>