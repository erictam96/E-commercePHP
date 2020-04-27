<?php
class retailer_product
{
    private $conn;

    // constructor
    function __construct() {
        require_once 'connect_db.php';
        // connecting to database
        $db = new connect_db();
        $this->conn = $db->connect();
        // Create connection
    }

    // destructor
    function __destruct() {

    }

    /**
     * Retailer and new product
     * @param $detailsArray
     * @param $ImageArray
     */
    public function AddProduct($detailsArray,$ImageArray){
        $name = $detailsArray['Details:1'];
        $price = $detailsArray['Details:2'];
        $category = $detailsArray['Details:3'];
        $desc = $detailsArray['Details:4'];
        $discount = $detailsArray['Details:5'];
        $retailer = $detailsArray['Details:6'];
        $quantity = $detailsArray['Details:7'];
        $size = "small";
        $status = "Available";
        $date = date("Y-m-d H:i:s");


        //GET Latest Code from database
        $sth = $this->conn->query("SELECT MAX(ProdCode) FROM product");
        $result = $sth->fetch_row();
        $code = $result[0]+1;
        $sth->close();

        $stmt = $this->conn->prepare("INSERT INTO product(ProdID,ProdCode,ProdName,ProdPrice,
 ProdDate,ProdCategory,ProdDesc,ProdSize,ProdDiscount,ProdStatus,RID) VALUES(NULL,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("isdssssdss",$code,$name,$price,$date,$category,
            $desc,$size,$discount,$status,$retailer);


        if($quantity<=10000 && $quantity>0){
            for($a=0;$a<$quantity;$a++){
                $stmt->execute();
            }
        }

        $stmt->close();
        print_r($ImageArray);
        $num = 0;
        foreach($ImageArray as $key => $value){
            $ImagePath = "product_image/$code$date$retailer$num.jpg";
            $ImageURL = "http://www.cashierbook.com/retailer/$ImagePath";

            file_put_contents($ImagePath,base64_decode($value));
            $query = $this->conn->prepare("INSERT INTO prodimage(ImageID,ImageURL,ProdCode) VALUES (NULL,?,?)");
            $query->bind_param("ss",$ImageURL,$code);
            $query->execute();
            $query->close();
            $num++;
        }
    }
}