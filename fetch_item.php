<?php
require_once 'retailer_info.php';
$db = new retailer_info();

$retailerID =  $_POST["retailerID"];


$result = $db->fetchRetailerProduct($retailerID);

echo json_encode($result);




?>