<?php

/**
 * Created by PhpStorm.
 * User: erict
 * Date: 5/28/2018
 * Time: 3:36 PM
 */

class staff_info
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
    public function VerifyUserRole($sid)
    {

        $stmt = $this->conn->prepare("SELECT SID FROM Staff WHERE SID = ?");

        $stmt->bind_param("s", $sid);
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

}