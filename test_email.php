<?php
require_once "email_function.php";

$mail = new email_function();
$email= "erictam96@hotmail.com";
$itemList=array();
$temp=array();
$temp['itemName']="item1";
$temp['itemPrice']="1111.00";
array_push($itemList,$temp);
$temp=array();
$temp['itemName']="item2";
$temp['itemPrice']="2222.00";
array_push($itemList,$temp);
$temp=array();
$temp['itemName']="item3";
$temp['itemPrice']="3333.00";
array_push($itemList,$temp);

$mail = new email_function();
$mail->sendReceiptMail($email,"1234","4567.00","15.00",$itemList);
echo "DONE";