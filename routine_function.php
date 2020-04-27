<?php
require_once 'connect_db.php';
require_once __DIR__ . '/firebase_notification.php';
require_once __DIR__ . '/push_notification.php';
require_once "email_function.php";
error_reporting(E_ALL);
date_default_timezone_set('Asia/Hong_Kong');
ini_set('display_errors', 1);
// connecting to database
$db = new connect_db();
$conn = $db->connect();



$currentTime = date("H:i:s");
$currentDay=date('D');

if(strtotime($currentTime) >= strtotime('9:56:00')&&strtotime($currentTime) < strtotime('16:41:00')&&$currentDay!='Sun'){ //server delay 19min
    $sql = "SELECT p.rid as 'rid',r.remail as 'mail' from pendingitem d,product p,retailer r where DATE_ADD(d.replytime, INTERVAL 15 minute)<NOW() and isnull(d.canceltime) and isnull(d.cancelReason) and d.status='available' and d.prodid=p.prodid and p.rid=r.rid";
    $result = $conn->query($sql);
    $arrayRID=array();
    $arrayCustID=array();




    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            // echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
            if(in_array($row["rid"],$arrayRID)){
                //ignore
            }else {

                array_push($arrayRID, $row["rid"]);
                $msgTitle = "Order cancelled";
                $msgBody = "No response from customer";
                $msgStatus = "UNREAD";
                $msgAction = "";
                $date = date("YmdHis");
                $custid = $row["rid"];

                $firebase = new firebase_notification();
                $push = new push_notification();
                $payload = array();
                $payload['goto'] = $msgAction;

                $push->setTopic($row["rid"]); //RID
                $push->setTitle($msgTitle);
                $push->setMessage($msgBody);
                $push->setImage('http://www.cashierbook.com/retailer/images/MikoWong~profile2018-03-13-14-22-43.jpg');

                $push->setIsBackground(FALSE);
                $push->setPayload($payload);

                $json = $push->getPush();
                $firebase->sendToTopic($push->getTopic(), $json);

                $url = "http://www.cashierbook.com/retailer/images/ic_cancel.png";

                $insertString = "INSERT INTO notification (NotifyTitle,NotifyMsg,NotifyDate,NotifyStatus,NotifyAction,NotifyURL,UID) VALUES(" . "'" . $msgTitle . "','" .
                    $msgBody . "','" . $date . "','" . $msgStatus . "','" . $msgAction . "','" . $url . "','" . $custid . "')";

                $conn->query($insertString);

                $mail = new email_function();
                $mail->sendCancelCustomer($row["mail"]);
            }
        }
    }


    $sql = "SELECT d.custid as 'custid', c.cemail as 'mail' from pendingitem d,product p,customer c where isnull(d.replytime) and isnull(d.cancelReason) and d.ExpiredDate<NOW() and d.prodid=p.prodid and d.custid=c.custid";
    $result2 = $conn->query($sql);

    if ($result2->num_rows > 0) {
        // output data of each row
        while($row = $result2->fetch_assoc()) {
            // echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
            if(in_array($row["custid"],$arrayCustID)){
                //ignore
            }else {
                array_push($arrayCustID, $row["custid"]);
                $msgTitle = "Order cancelled";
                $msgBody = "No response from retailer";
                $msgStatus = "UNREAD";
                $msgAction = "checkoutorder";
                $date = date("YmdHis");
                $custid = $row["custid"];

                $firebase = new firebase_notification();
                $push = new push_notification();
                $payload = array();
                $payload['goto'] = $msgAction;

                $push->setTopic($row["custid"]); //custID
                $push->setTitle($msgTitle);
                $push->setMessage($msgBody);
                $push->setImage('http://www.cashierbook.com/retailer/images/MikoWong~profile2018-03-13-14-22-43.jpg');

                $push->setIsBackground(FALSE);
                $push->setPayload($payload);

                $json = $push->getPush();
                $firebase->sendToTopic($push->getTopic(), $json);

                $url = "http://www.cashierbook.com/retailer/images/ic_cancel.png";
                $insertString = "INSERT INTO notification (NotifyTitle,NotifyMsg,NotifyDate,NotifyStatus,NotifyAction,NotifyURL,UID) VALUES(" . "'" . $msgTitle . "','" .
                    $msgBody . "','" . $date . "','" . $msgStatus . "','" . $msgAction . "','" . $url . "','" . $custid . "')";

                $conn->query($insertString);

                $mail = new email_function();
                $mail->sendCancelMerchant($row["mail"]);
            }
        }
    }







//GET Latest Code from database
    $conn->query("update pendingitem d,product p set d.status='cancel no response', d.canceltime=NOW(), d.cancelreason='no response from customer',p.prodstatus='Available'  where DATE_ADD(d.replytime, INTERVAL 15 minute)<NOW() and isnull(d.canceltime) and isnull(d.cancelReason) and d.status='available' and d.prodid=p.prodid");
    $conn->query("update pendingitem d,product p set d.status='cancel no response',d.cancelTime=NOW(), d.cancelReason='no response from retailer',p.prodstatus='Available' where isnull(d.replytime) and isnull(d.cancelReason) and d.ExpiredDate<NOW() and d.prodid=p.prodid");



    $conn->close();
}



