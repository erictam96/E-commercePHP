
<?php
// $to = "erictam96@hotmail.com";
// $subject = "My subject";
// $txt = "Hello world!";
// $headers = "From: webmaster@example.com" . "\r\n" .
//     "CC: somebodyelse@example.com";

// mail($to,$subject,$txt,$headers);

        require_once 'connect_db.php';
        require_once __DIR__ . '/firebase_notification.php';
        require_once __DIR__ . '/push_notification.php';
        require_once  __DIR__ .'/email_function.php';
        require_once('phpCrypto.php5');
        require_once 'NEMApiLibrary.php';

        // connecting to database
        $db = new connect_db();
        $db->connect();
        // Create connection
    
    

$runnerid="asdadsad";
$custID="asdasdsad";
$date = date("YmdHis");
        $dropItemStmt = $db->connect()->prepare( "update orderdetail o,custorder c set o.DeliveredTime=?,o.ItemTracking='delivered' where o.itemtracking='Delivering' 
and o.runnerid=? and o.orderid=c.orderid and c.custid=?");
        $dropItemStmt->bind_param("sss",$date,$runnerid,$custID);
        $zzz=$dropItemStmt->execute();
        $dropItemStmt->close();
        
        if($zzz){
            echo "success";
        }else{
            echo "fail";
        }

?>