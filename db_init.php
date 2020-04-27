<?php
$host = "localhost";
$user = "id6667176_admin";
$password = "0173873588";
$db = "id6667176_ecommercefyp";

$conn = mysqli_connect($host,$user,$password,$db);
$mysqli = new mysqli($host,$user,$password,$db);

if(!$conn)
{
	die("Error in connection" . mysqli_connect_error());
}

?>

