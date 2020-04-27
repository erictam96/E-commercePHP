<?php
class retailer_info
{
    private $conn;

    // constructor
    function __construct() {
        require_once 'connect_db.php';
        require_once('phpCrypto.php5');
        // connecting to database
        $db = new connect_db();
        $this->conn = $db->connect();
        // Create connection
    }

    // destructor
    function __destruct() {

    }

    /**
     * @param $uid
     * @return null
     */
    public function VerifyUserRole($uid)
    {

        $stmt = $this->conn->prepare("SELECT RID FROM retailer WHERE RID = ?");

        $stmt->bind_param("s", $uid);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                {
                    return true;
                }
            } else {
                return NULL;
            }
        }
    }

    public function updateUserPassword($oldPassword,$newPassword,$retailerEmail){
//$coverImagePath = "Retailer/Images/$sdate$ownerName$ownerContact$cover.jpg";
//$profileImagePath = "http://www.cashierbook.com/Retailer/Images/$sdate$ownerName$ownerContact$profile.jpg";

//$sdate$ownerName$ownerContact$cover

        $stmt = $this->conn->prepare( "SELECT REmail FROM retailer WHERE RPWD = ? AND REmail =?");
        $stmt->bind_param("ss", $oldPassword, $retailerEmail);

        if($result=$stmt->execute())
        {
            if($result>0){
                $stmt->close();
                $stmtupdate = $this->conn->prepare( "UPDATE retailer SET RPWD = ? WHERE REmail =?");

                $stmtupdate->bind_param("ss", $newPassword, $retailerEmail);
                $stmtupdate->execute();

                $stmtupdate->close();

                return "password update success~" ;
            }


//echo "Profile Updated Successfully";
        }
        else
        {
            $stmt->close();
            return "Update password fail, please contact server admin.";
        }


    }


     public function registerRetailerAccount($uid,$email,$password2,$companyno,$shopname,$fullname,$shopaddr,$phoneno,$agree)
    {
        $imageUrl= "http://www.cashierbook.com/retailer/images/cashierbook_logoSmall.jpg";
        $crypto = new phpFreaksCrypto();

        //if($agree!=1){
        //    $agree=0;
        //}
        $insert = $this->conn->prepare("INSERT INTO retailer(RID,REmail,RPWD,RRegCode,RShopName,ROwnerName,
RAddr,RContact,coverPicUrl,refundAgree) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $encrypted_passowrd = $crypto->encrypt($password2);
        $insert->bind_param("ssssssssss", $uid, $email, $encrypted_passowrd, $companyno, $shopname, $fullname, $shopaddr,
            $phoneno,$imageUrl,$agree);
        $result = $insert->execute();
        $insert->close();

        //Check for successful store
        if ($result) {
            $stmt = $this->conn->prepare("SELECT RID FROM retailer WHERE RID = ?");
            $stmt->bind_param("s", $uid);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                //this uid is retailer
                $stmt->close();
                return true;
            }
        } else {
            return NULL;
        }
    }

    public function changePassword($uid,$password){
        $crypto = new phpFreaksCrypto();
        $stmt = $this->conn->prepare("UPDATE retailer SET RPWD = ? WHERE RID = ?");
        $encrypted_password = $crypto->encrypt($password);
        $stmt->bind_param("ss",$encrypted_password,$uid);
        $stmt->execute();
        $stmt->close();
    }
    public function getDisplayName($getDisplayName)
    {
        $stmt = $this->conn->prepare("SELECT ROwnerName FROM retailer WHERE RID = ?");
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

?>