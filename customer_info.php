<?php

/**
 * Created by PhpStorm.
 * User: erict
 * Date: 3/16/2018
 * Time: 12:23 PM
 */
date_default_timezone_set('Asia/Kuala_Lumpur');
class customer_info
{
    private $conn;

    // constructor
    function __construct() {
        require_once 'connect_db.php';
        require_once('phpCrypto.php5');

        // connecting to database
        $db = new connect_db();
        $this->conn = $db->connect();


    }

    // destructor
    function __destruct() {

    }

    
public function VerifyUserRole($uid,$email,$name)
    {

        $stmt = $this->conn->prepare("SELECT RID FROM retailer WHERE RID = ?");
        $stmt->bind_param("s",$uid);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows>0){
            //this uid is retailer
            $stmt->close();
            return FALSE;
        }else{
	$stmt->close();
            $query = $this->conn->prepare("INSERT IGNORE INTO customer(CustID,CEmail,CDisplayName)
 VALUES(?,?,?)");
            $query->bind_param("sss",$uid,$email,$name);
            $query->execute();
            $query->close();
            return TRUE;
    }
}

 public function GetNotification($uid){
        $stmt = $this->conn->prepare ("SELECT NotifyID,NotifyTitle,NotifyMsg,NotifyDate,NotifyStatus,
        NotifyAction,NotifyURL FROM notification WHERE UID = ? ORDER BY NotifyDate DESC LIMIT 20");
        $stmt->bind_param("s",$uid);
        $stmt->execute();
        $stmt->bind_result($notifyID,$notifyTitle,$notifyMsg,$notifyDate,$notifyStatus,$notifyAction,$notifyURL);
        $NotificationArray=array();

        while($stmt->fetch()) {

            $temp=array();
            $temp['notifyID']=$notifyID;
            $temp['notifyTitle']=$notifyTitle;
            $temp['notifyMsg']=$notifyMsg;
            $temp['notifyDate']=$this->timeAgo($notifyDate);
            $temp['notifyStatus']=$notifyStatus;
            $temp['notifyAction']=$notifyAction;
 	    $temp['notifyURL']=$notifyURL;

            array_push($NotificationArray,$temp);
        }
        $stmt->close();
        $json = json_encode($NotificationArray);
        echo $json;
    }

    public function UpdateNotification($notifyID){
        $stmt = $this->conn->prepare ("UPDATE notification SET NotifyStatus = 'READ' WHERE NotifyID = ?" );
        $stmt->bind_param("s",$notifyID);
        $stmt->execute();
        $stmt->close();
    }

    public function timeAgo($timestamp){
        $datetime1=new DateTime("now");
        $datetime2=date_create($timestamp);
        $diff=date_diff($datetime1, $datetime2);
        $timemsg='';
        if($diff->y > 0){
            $timemsg = $diff->y .' year'. ($diff->y > 1?"s":'');
        }
        else if($diff->m > 0){
            $timemsg = $diff->m . ' month'. ($diff->m > 1?"s":'');
        }
        else if($diff->d > 0){
            $timemsg = $diff->d .' day'. ($diff->d > 1?"s":'');
        }
        else if($diff->h > 0){
            $timemsg = $diff->h .' hour'.($diff->h > 1 ? "s":'');
        }
        else if($diff->i > 0){
            $timemsg = $diff->i .' minute'. ($diff->i > 1?"s":'');
        }
        else if($diff->s > 0){
            $timemsg = $diff->s .' second'. ($diff->s > 1?"s":'');
        }

        $timemsg = $timemsg.' ago';
        return $timemsg;
    }

    public function getDisplayName($getDisplayName)
    {
        $stmt = $this->conn->prepare("SELECT CDisplayName FROM customer WHERE CustID = ?");
        $stmt->bind_param("s", $getDisplayName);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($ownername);
            $stmt->fetch();
            echo $ownername;
        }
        $stmt->close();
    }
}