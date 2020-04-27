<?php
include "db_init.php";

$retailerID = $_POST['retailerID'];



$sql = "SELECT * FROM retailer r WHERE r.RID ='$retailerID' ";


$result = $conn->query($sql);

if ($result->num_rows >0) {
 // output data of each row
 while($row[] = $result->fetch_assoc()) {
 
 $tem = $row;
 
 $json = json_encode($tem);

 }
 
} else {
 echo "0 results";
}
 echo $json;
 
$conn->close();



?>
