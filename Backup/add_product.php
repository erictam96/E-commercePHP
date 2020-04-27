<?php
require_once 'retailer_product.php';
$db = new retailer_product();

//Receive JSON Object
$JSON_Received = $_POST["Product"];

$objArray = json_decode($JSON_Received,true);
$detailsArray = $objArray['details'];
$ImageArray = $objArray['image'];

$db->AddProduct($detailsArray,$ImageArray);

?>