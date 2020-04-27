<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Created by PhpStorm.
 * User: leeyipfung
 * Date: 3/9/2018
 * Time: 11:37 AM
 */
date_default_timezone_set('Asia/Hong_Kong');
class customer_search_info
{
    private $conn;

    // constructor
    function __construct() {
        require_once 'connect_db.php';
        require_once __DIR__ . '/firebase_notification.php';
        require_once __DIR__ . '/push_notification.php';
        require_once  __DIR__ .'/email_function.php';
        require_once('phpCrypto.php5');
        require_once 'NEMApiLibrary.php';

        // connecting to database
        $db = new connect_db();
        $this->conn = $db->connect();
        // Create connection
    }

    // destructor
    function __destruct() {

    }

    /**
     * @return mysqli
     */
    public function customer_search($searchKey,$index,$userid)

    {
         $x=(int)$index;
        if($x==-1){
            $stmt = $this->conn->prepare("select yyy.code,yyy.shopname,yyy.discount,yyy.price,yyy.prodname,yyy.url,yyy.disprice
from (select c.prefid as 'id' from (SELECT tag as 'tag' FROM preference where prefid=?) src,preference c where c.tag=src.tag ) zzz right join 
(SELECT prod.code as 'code', retailer.RShopName as 'shopname', prod.discount as 'discount', 
prod.price as 'price', prod.name as 'prodname',img.url as 'url',prod.disprice as 'disprice'  
FROM retailer, (select prodcode as 'code', proddiscount as 'discount',prodprice as 'price',prodname as 'name',
discountprice as 'disprice' ,rid as 'rid' from product where (prodname like ? or prodcategory like ? or proddesc like ?) 
and prodstatus='Available' group by prodcode) prod, (select imageurl as 'url', prodcode as 'code' from prodimage group by prodcode) img
  WHERE prod.rid = retailer.RID and prod.code=img.code  ) yyy  on zzz.id=yyy.code left join (select prodcode as 'code',price as 'rankprice' from rank where NOW()<enddate) ranking on ranking.code=yyy.code order by ranking.rankprice desc, id desc,code desc limit ?,6");
            $index=0;
        }else{
            $stmt = $this->conn->prepare("select yyy.code,yyy.shopname,yyy.discount,yyy.price,yyy.prodname,yyy.url,yyy.disprice
from (select c.prefid as 'id' from (SELECT tag as 'tag' FROM preference where prefid=?) src,preference c where c.tag=src.tag ) zzz right join 
(SELECT prod.code as 'code', retailer.RShopName as 'shopname', prod.discount as 'discount', 
prod.price as 'price', prod.name as 'prodname',img.url as 'url',prod.disprice as 'disprice'  
FROM retailer, (select prodcode as 'code', proddiscount as 'discount',prodprice as 'price',prodname as 'name',
discountprice as 'disprice' ,rid as 'rid' from product where (prodname like ? or prodcategory like ? or proddesc like ?) 
and prodstatus='Available' group by prodcode) prod, (select imageurl as 'url', prodcode as 'code' from prodimage group by prodcode) img
  WHERE prod.rid = retailer.RID and prod.code=img.code  ) yyy  on zzz.id=yyy.code left join (select prodcode as 'code',price as 'rankprice' from rank where NOW()<enddate) ranking on ranking.code=yyy.code order by ranking.rankprice desc, id desc,code limit ?,6");
        }

        $searchKey= "%" . $searchKey . "%";
//echo $searchKey;

        $stmt->bind_param("sssss",$userid, $searchKey, $searchKey, $searchKey,$index);
        //executing the query
        $stmt->execute();
        //binding results to the query
        $stmt->bind_result($prodCode, $shopName, $discount, $price, $prodName, $imageURL,$discountPrice);

        $products = array();

        //traversing through all the result
        while ($stmt->fetch()) {
            $temp = array();

            $temp['prodcode'] = $prodCode;
            $temp['shopname'] = $shopName;
            $temp['discount'] = $discount;
            $temp['price'] = $price;
            $temp['prodname'] = $prodName;
            $temp['imageurl'] = $imageURL;
            $temp['discountPrice']=$discountPrice;
            array_push($products, $temp);
        }
        $json = json_encode($products);

        //displaying the result in json format
        echo $json;
        $stmt->close();
        
    }

     public function customer_search_shop($searchKey,$index,$userid,$rid)

    {
        //Select product details with count
        $stmt = $this->conn->prepare("select yyy.code,yyy.shopname,yyy.discount,yyy.price,yyy.prodname,yyy.url,yyy.disprice 
from (select c.prefid as 'id' from (SELECT tag as 'tag' FROM preference where prefid=?) src,preference c where c.tag=src.tag ) zzz right join 
(SELECT prod.code as 'code', retailer.RShopName as 'shopname', prod.discount as 'discount', 
prod.price as 'price', prod.name as 'prodname',img.url as 'url',prod.disprice as 'disprice'  
FROM retailer, (select prodcode as 'code', proddiscount as 'discount',prodprice as 'price',prodname as 'name',
discountprice as 'disprice' ,rid as 'rid' from product where (prodname like ? or prodcategory like ? or proddesc like ?) 
and prodstatus='Available' and rid=? group by prodcode) prod, (select imageurl as 'url', prodcode as 'code' from prodimage group by prodcode) img
  WHERE prod.rid = retailer.RID and prod.code=img.code  ) yyy  on zzz.id=yyy.code order by  id desc,code limit ?,6");
        //$symbol="%";
        $searchKey= "%" . $searchKey . "%";
//echo $searchKey;

        $stmt->bind_param("ssssss",$userid, $searchKey, $searchKey, $searchKey,$rid,$index);
        //executing the query
        $stmt->execute();
        //binding results to the query
        $stmt->bind_result($prodCode, $shopName, $discount, $price, $prodName, $imageURL,$discountPrice);

        $products = array();

        //traversing through all the result
        while ($stmt->fetch()) {
            $temp = array();

            $temp['prodcode'] = $prodCode;
            $temp['shopname'] = $shopName;
            $temp['discount'] = $discount;
            $temp['price'] = $price;
            $temp['prodname'] = $prodName;
            $temp['imageurl'] = $imageURL;
            $temp['discountPrice']=$discountPrice;
            array_push($products, $temp);
        }
        $json = json_encode($products);

        //displaying the result in json format
        echo $json;
        $stmt->close();
    }

    /**
     * @return mysqli
     */
    public function customer_view_item($prodCode)
    {

         //Select product details with count
        $stmt = $this->conn->prepare( "SELECT product.ProdCode,product.ProdName, product.ProdPrice ,product.ProdCategory,product.ProdDesc ,product.ProdDiscount, product.ProdSize ,retailer.RShopName, retailer.ROwnerName,retailer.profilePicURL, retailer.RDesc,retailer.rid , product.ProdVariant, COUNT(product.ProdVariant)   ,COUNT(product.ProdCode) ,product.discountPrice
 FROM product, retailer WHERE product.ProdCode = ? AND product.RID= retailer.RID AND product.ProdStatus='Available' GROUP BY product.ProdCode, product.ProdVariant");
        $stmt->bind_param("s", $prodCode);
        $stmt->execute();
        $stmt->bind_result($prodcode,$prodname, $prodprice, $prodcategory, $proddesc, $prodDiscount,$prodSize,$shopName,$shopOwner,$shopProfilePicURL,$shopDesc,$rid,$prodVariant,$prodVarQty,$prodTotalQty,$discountPrice);

        $products = array();
        $prodDetail=array();
        $imageDetail=array();
        $soldArray=array();
        $temp = array();

        while ($stmt->fetch()) {
            $temp['prodcode'] = $prodcode;
            $temp['prodname'] = $prodname;
            $temp['prodprice'] = $prodprice;
            $temp['prodcategory'] = $prodcategory;
            $temp['proddesc'] = $proddesc;
            $temp['proddiscount'] = $prodDiscount;
            $temp['prodSize']=$prodSize;
            $temp['shopName']=$shopName;
            $temp['shopOwner']=$shopOwner;
            $temp['shopProfilePicURL']=$shopProfilePicURL;
            $temp['shopDesc']=$shopDesc;
            $temp['prodvariant']=$prodVariant;
            $temp['prodvarqty']=$prodVarQty;
            $temp['prodTotalQty']=$prodTotalQty;
            $temp['discountedPRice']=$discountPrice;
            $temp['rid']=$rid;

            array_push($prodDetail, $temp);
        }

        $stmt->close();

        //Select product all image url
        $query = $this->conn->prepare("SELECT ImageID ,ImageURL FROM prodimage WHERE ProdCode= ?");
        $query->bind_param("s",$prodCode);
        $query->execute();
        $query->bind_result($imageid,$imageurl);

        //Fetch record into array

        while($query->fetch()) {
            $image = array();
            $image['imageid'] = $imageid;
            $image['imageurl'] = $imageurl;
            array_push($imageDetail, $image);
        }
        $query->close();


        $soldQTYquery = $this->conn->prepare("select count(prodid) from product where prodcode=? and prodstatus='Sold'");
        $soldQTYquery->bind_param("s",$prodCode);
        $soldQTYquery->execute();
        $soldQTYquery->bind_result($soldQty);

        //Fetch record into array

        while($soldQTYquery->fetch()) {
            $qty = array();
            $qty['soldQty'] = $soldQty;

            array_push($soldArray, $qty);
        }
        $soldQTYquery->close();


        array_push($products,$prodDetail);
        array_push($products,$imageDetail);
        array_push($products,$soldArray);
        $json = json_encode($products);

        //displaying the result in json format
        echo $json;
    }

    public function addCart($cartArray){
        $email = $cartArray['CartDetails:1'];
        $code = $cartArray['CartDetails:2'];
        $variation = $cartArray['CartDetails:3'];
        $qty = $cartArray['CartDetails:4'];
        $date = date("YmdHis");

        //Add customer cart to database
        $stmt = $this->conn->prepare("INSERT INTO cart (CustID,ProdCode,ProdVariant,CartItemQuantity,CartDate) 
    VALUES(?,?,?,?,?)");
        $stmt->bind_param("sisis",$email,$code,$variation,$qty,$date);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @return mysqli
     */
    public function fetchCart($custID){
        //Select product all image url
        $query = $this->conn->prepare("SELECT cart.ProdVariant,retailer.profilePicURL, retailer.RShopName, z.url, p.name, p.price, cart.CartItemQuantity,cart.cartID, 
p.qty ,p.disprice,p.dis ,p.code
FROM cart,(select prodname as 'name',prodprice as 'price',prodid as 'id',discountprice as 'disprice',proddiscount as 'dis',prodcode as 'code',rid as 'rid',prodvariant as 'var',count(prodid) as 'qty' from product where prodstatus='Available' group by prodcode,prodvariant) p, retailer, (select  ImageURL as 'url',prodcode as 'code' from prodimage group by prodcode) z WHERE cart.ProdCode=p.code and cart.prodvariant=p.var AND  p.rid=retailer.RID AND p.code=z.code AND cart.CustID=?  group by cart.cartid");
        $query->bind_param("s",$custID);
        $query->execute();
        $query->bind_result($prodVariant,$retailerProfPicURL,$retailerShopName,$prodImgURL,$prodName,$prodPrice,$cartQty,$cartID,$limitQty,$discountPrice,$discount,$prodcode);

        $cartItemArray=array();

        while($query->fetch()) {
            $temp=array();
            $temp['prodVariant']=$prodVariant;
            $temp['prodImgURL']=$prodImgURL;
            $temp['prodName']=$prodName;
            $temp['prodPrice']=$prodPrice;
            $temp['cartQty']=$cartQty;

            $temp['retailerProfPicURL']=$retailerProfPicURL;
            $temp['retailerShopName']=$retailerShopName;
            $temp['cartID']=$cartID;
            $temp['limitqty']=$limitQty;
            $temp['discountPrice']=$discountPrice;
            $temp['discount']=$discount;
            $temp['prodcode']=$prodcode;

            array_push($cartItemArray,$temp);

        }

        $query->close();
        $json = json_encode($cartItemArray);
        echo $json;
    }



     public function deleteCartItem($cartID){
        $query = $this->conn->prepare("DELETE FROM cart  WHERE cart.CartID=?");
        $query->bind_param("s",$cartID);
        $query->execute();

        echo "done delete";
    }

    public function createOrder($orderDetailObj){
        $arrayRID=array();

        $JsonObj = json_decode($orderDetailObj,true);

        //echo $JsonObj;
        $addressid=$JsonObj[0]['addressid'];
        $delivery=$JsonObj[0]['delivery'];
        $orderDate=date("YmdHis");;
        $paymentMethod=$JsonObj[0]['paymentmethod'];
        $total=0;
        $transactionchrg=0;
        $trackingstatus="preparing";
        $custid=$JsonObj[0]['custid'];

        $paying= $this->conn->prepare("update pendingitem  set status='paying' where status='available' and custid=?");
        $paying->bind_param("s",$custid);
        $paying->execute();
        $paying->close();

        $querycancelfailorder = $this->conn->prepare("update custorder set trackingstatus='cancelled' where trackingstatus='preparing' and custid=?");
        $querycancelfailorder->bind_param("s",$custid);
        $querycancelfailorder->execute();
        $querycancelfailorder->close();

        $querycreateorder = $this->conn->prepare("INSERT INTO custorder (AddressID,DeliveryCharge,OrderDate,PaymentMethod,TotalAmount,TransactionCharge,TrackingStatus,CustID) VALUES (?,?,?,?,?,?,?,?)");
        $querycreateorder->bind_param("sdssddss",$addressid,$delivery,$orderDate,$paymentMethod,$total,$transactionchrg,$trackingstatus,$custid);
        $querycreateorder->execute();
        $querycreateorder->close();

        $selectorder = $this->conn->prepare("SELECT OrderID FROM custorder  WHERE TrackingStatus='preparing' AND TotalAmount=0 AND TransactionCharge=0 AND CustID=?");
        $selectorder->bind_param("s",$custid);
        $selectorder->execute();
        $selectorder->bind_result($resultID);
        $selectorder->fetch();
        $orderID=$resultID;
        

        $orderArray=array();

        $temp=array();
        $temp['orderdate']=$orderDate;
        $temp['orderid']=$orderID;
        array_push($orderArray,$temp);
        $selectorder->close();
        $json = json_encode($orderArray);
        echo $json;
    }


    public function cancelOrder($orderid,$custid){
        $cancelOrder=$this->conn->prepare("update custorder set trackingstatus='cancelled' where orderid=?");
        $cancelOrder->bind_param("s",$orderid);
        $cancelOrder->execute();
        $cancelOrder->close();

        $paying= $this->conn->prepare("update pendingitem  set status='available' where status='paying' and custid=?");
        $paying->bind_param("s",$custid);
        $paying->execute();
        $paying->close();

        echo "transaction cancelled";
    }


    public function placeOrder($orderList,$custid,$orderID,$ipayTransid){
        $msgTitle = "new order";
        $date = date("YmdHis");
        $msgStatus = "UNREAD";
        $msgAction = "packorder";

        $arrayRID=array();
        $paymentDate=date("YmdHis");
        $cartListArray=array();
        $cartListArray=json_decode($orderList,true);
        $total=0;
        $qry3= $this->conn->prepare("SELECT CDisplayName FROM customer WHERE CustID = ?");
        $qry3->bind_param("s",$custid);
        $qry3->execute();
        $qry3->bind_result($custName);
        $qry3->fetch();
        $qry3->close();


        for($a=0;$a<sizeof($cartListArray);$a++){

            $expdate=$cartListArray[$a]['expdate'];
            $prodVariant=$cartListArray[$a]['prodVariant'];
            $prodname=$cartListArray[$a]['prodname'];

            //do string compare from "" to null, if($prodVar), diuleiloumouhai

            $updateProductQuery=$this->conn->prepare("update product p,pendingitem d set p.ProdStatus='Sold', d.Status='Sold',d.soldTime=?  where d.custid=? and d.expireddate=? and p.prodid=d.prodid and p.ProdVariant=? and p.ProdName=?");
            $updateProductQuery->bind_param("sssss",$paymentDate,$custid,$expdate,$prodVariant,$prodname);
            $updateProductQuery->execute();
            $updateProductQuery->close();

            $status="packing";

            $query2 = $this->conn->prepare("SELECT d.ProdID,p.prodprice,p.proddiscount,p.discountprice FROM pendingitem d,product p  where d.custid=? and d.expireddate=? and d.prodid=p.prodid and p.prodvariant=?");
            $query2->bind_param("sss",$custid,$expdate,$prodVariant);
            $query2->execute();
            $query2->bind_result($prodid,$price,$dis,$disprice);
            //$products=$prodid;

            // $prodidArray=null;
            $prodidArray=array();
            while($query2->fetch()) {
                $temp=array();
                $temp['prodidlist']=$prodid;
                $temp['pricelist']=$price;
                $temp['dislist']=$dis;
                $temp['dispricelist']=$disprice;
                array_push($prodidArray,$temp);
                // echo $prodid;
                // print_r($products);
            }

            $query2->close();

            $json = json_encode($prodidArray);
            $jsonObj=json_decode($json,true);

            // print_r($jsonObj[0]['prodidlist']);

            for($index=0;$index<sizeof($jsonObj);$index++){
                $productid=$jsonObj[$index]['prodidlist'];
                $oriprice=$jsonObj[$index]['pricelist'];
                $soldprice=$jsonObj[$index]['dispricelist'];
                $discount=$jsonObj[$index]['dislist'];
                $insertOrder = $this->conn->prepare("INSERT INTO orderdetail (ProdID,OrderID,ItemTracking) VALUES (?,?,?)");
                $insertOrder->bind_param("sss",$productid,$orderID,$status);
                $insertOrder->execute();
                $insertOrder->close();
            }


            $query = $this->conn->prepare("select p.discountprice,p.rid from pendingitem d,product p WHERE d.prodid=p.prodid and d.custid=? and d.expireddate=? and p.prodvariant=?");
            $query->bind_param("sss",$custid,$expdate,$prodVariant);
            $query->execute();
            $query->bind_result($price,$RID);

            $cartItemArray=array();

            while($query->fetch()) {
                //$floatprice=floatval($price);
                echo $price;
                $total = $total + $price;

            }

            $query->close();

            if(in_array($RID,$arrayRID)){
                //ignore
            }else{
                $msgBody = "New order from customer , please pack item";

                $qry4=$this->conn->prepare("SELECT imageurl FROM prodimage,product WHERE prodid = ? 
AND product.prodcode = prodimage.prodcode LIMIT 1");
                $qry4->bind_param("s",$productid);
                $qry4->execute();
                $qry4->bind_result($prodURL);
                $qry4->fetch();
                $qry4->close();

                $firebase = new firebase_notification();
                $push = new push_notification();
                $payload = array();
                $payload['goto'] = $msgAction;
                array_push($arrayRID,$RID);
                $push->setTopic($RID); //RID
                $push->setTitle($msgTitle);
                $push->setMessage($msgBody);
                $push->setImage('');
                $push->setIsBackground(FALSE);
                $push->setPayload($payload);
                $json = $push->getPush();
                $firebase->sendToTopic($push->getTopic(), $json);

                $qry = $this->conn->prepare("INSERT INTO notification (NotifyTitle,NotifyMsg,NotifyDate,
NotifyStatus,NotifyAction,NotifyURL,UID) VALUES(?,?,?,?,?,?,?)");
                $qry->bind_param("sssssss",$msgTitle,$msgBody,$date,$msgStatus,$msgAction,$prodURL,$RID);
                $qry->execute();
                $qry->close();
            }
        }


        $transactionchrg=round($total*.01,2);
        echo "total:";
        echo $total;
        echo "  transactioncharge: ";
        echo $transactionchrg;

        //add trigger total amount here , verify ipay88 amount with here, if not match cancel order and ban the account.
        $orderquery = $this->conn->prepare("UPDATE custorder SET TotalAmount=? , TransactionCharge=?, TrackingStatus='packing item',IpayID=? WHERE CustID=? AND  TotalAmount=0 AND TransactionCharge=0 AND TrackingStatus='preparing' AND OrderID=?");
        $orderquery->bind_param("ddsss",$total,$transactionchrg,$ipayTransid,$custid,$orderID);
        $orderquery->execute();
        $orderquery->close();

        //For send email
        $getEmail = $this->conn->prepare("SELECT CEmail FROM customer WHERE CustID=?");
        $getEmail->bind_param("s",$custid);
        $getEmail->execute();
        $getEmail->bind_result($getCustEmail);
        $getEmail->fetch();
        $getEmail->close();

        $getItem= $this->conn->prepare("SELECT ProdName,ProdPrice FROM orderdetail,product 
WHERE OrderID = ? AND product.prodID = orderdetail.prodID");
        $getItem->bind_param("i",$orderID);
        $getItem->execute();
        $getItem->bind_result($itemName,$itemPrice);
        $itemList=array();

        while($getItem->fetch()) {
            $temp=array();
            $temp['itemName']=$itemName;
            $temp['itemPrice']=$itemPrice;
            array_push($itemList,$temp);
         
        }
        $getItem->close();
        echo "mail details".$getCustEmail."  ".$orderID."  ".$total."  ".$transactionchrg."  ".$itemList;
        $mail = new email_function();
        $mail->sendReceiptMail($getCustEmail,$orderID,$total,$transactionchrg,$itemList);

        $paying= $this->conn->prepare("update pendingitem  set status='available' where status='paying' and custid=?");
        $paying->bind_param("s",$custid);
        $paying->execute();
        $paying->close();
    }

    public function addAddress($addressObj){
        $newAddress=json_decode($addressObj,true);

        //$cartListArray=json_decode($orderList,true);
        $default=$newAddress[0]['defaultAddress'];
        $address=$newAddress[0]['address'];
        $state=$newAddress[0]['state'];
        $city=$newAddress[0]['city'];
        $postcode=$newAddress[0]['postcode'];
        $fullname=$newAddress[0]['fullname'];
        $contact=$newAddress[0]['contact'];
        $email=$newAddress[0]['email'];
        $userid=$newAddress[0]['userid'];
        $type="DEFAULT";

        echo $address;
        $value=strcmp($default,"yes");
        echo $value;
        if((int)$value==0){
            $query3 = $this->conn->prepare("update DeliveryAddress set AddressType='-' where CustID=? and AddressType='DEFAULT'");
            $query3->bind_param("s",$userid);
            $query3->execute();
            $query3->close();

            $query2 = $this->conn->prepare("INSERT INTO DeliveryAddress (AddressType,Address,State,City,PostCode,CustID,RecpEmail,RecpName,RecpContact) VALUES (?,?,?,?,?,?,?,?,?)");
            $query2->bind_param("sssssssss",$type,$address,$state,$city,$postcode,$userid,$email,$fullname,$contact);
            $query2->execute();
            $query2->close();
        }else{
            $query2 = $this->conn->prepare("INSERT INTO DeliveryAddress (Address,State,City,PostCode,CustID,RecpEmail,RecpName,RecpContact) VALUES (?,?,?,?,?,?,?,?)");
            $query2->bind_param("ssssssss",$address,$state,$city,$postcode,$userid,$email,$fullname,$contact);
            $query2->execute();
            $query2->close();
        }

        echo "new address added";
    }

    public function fetchOrder($customerID){
        $fetchorder = $this->conn->prepare("select  coalesce(tol.totalcount,'0') as totalcount, coalesce(dev.rdycollect,'0') as rdycollectcount  , c.orderid, c.orderdate   from   (select count(itemtracking) as 'rdycollect' ,orderid  as 'ordid' from orderdetail where itemtracking='ready to collect' group by orderid) dev right join custorder c on dev.ordid=c.orderid left join (select count(itemtracking) as 'totalcount' ,orderid  as 'ordid' from orderdetail  group by orderid) tol on tol.ordid=c.orderid where c.custid=? and c.trackingstatus!='cancelled' group by c.orderid order by rdycollectcount desc,orderid ");
        $fetchorder->bind_param("s",$customerID);
        $fetchorder->execute();
        $fetchorder->bind_result($totalQty,$rdycollectQty,$orderId,$orderDate);

        $orderArray=array();

        while($fetchorder->fetch()) {
            $temp=array();
            $temp['orderID']=$orderId;
            $temp['rdyCollectItem']=$rdycollectQty;

            $temp['totalItem']=$totalQty;
            $temp['orderDate']=$orderDate;



            array_push($orderArray,$temp);

        }

        $fetchorder->close();

        $json = json_encode($orderArray);
        echo  $json;



    }

    public function fetchOrderDetail($custid){
            $fetchorderitem = $this->conn->prepare("select count(p.prodid),p.ProdName,p.ProdVariant,o.ItemTracking ,c.orderdate , i.url,r.rshopname,o.pickuptime ,d.address
from product p,orderdetail o,custorder c,retailer r,DeliveryAddress d,(select imageurl as 'url',prodcode as 'code' from prodimage group by prodcode) i 
where c.custid=? and o.ProdID=p.ProdID and o.OrderID=c.OrderID and i.code=p.prodcode and r.rid=p.rid and d.addressid=c.addressid and   (o.itemtracking='Delivering' or o.itemtracking='ready to collect' 
or o.itemtracking='packing' or o.itemtracking='ready to deliver'  )  group by p.prodcode,p.prodvariant,c.orderid order by itemtracking,orderdate");
            $fetchorderitem->bind_param("s",$custid);
            $fetchorderitem->execute();
            $fetchorderitem->bind_result($qty,$itemname,$variant,$stat,$orderdate,$url,$shopname,$pickTime,$address);

            $orderItemArray=array();

            while($fetchorderitem->fetch()) {
                $temp=array();
                $temp['itemName']=$itemname;
                $temp['itemQty']=$qty;
                $temp['itemVar']=$variant;
                $temp['itemStatus']=$stat;
                $temp['orderdate']=$orderdate;
                $temp['url']=$url;
                $temp['shopname']=$shopname;
                $temp['picktime']=$pickTime;
                $temp['address']=$address;



                array_push($orderItemArray,$temp);

            }

            $fetchorderitem->close();

            $json = json_encode($orderItemArray);
            echo  $json;
        }


    public function pendingRequest($orderlistitem){
        $msgTitle = "Pending order";
        $msgBody = "Please check stock and reply.";
        $msgStatus = "UNREAD";
        $msgAction = "checkorder";
        $date = date("YmdHis");

        $arrayRID=array();
        $arrayCustID=array();
        $total=0;
        $cartListArray=json_decode($orderlistitem,true);

        $orderMsg="";
        $sendNotify=0;
        for($a=0;$a<sizeof($cartListArray);$a++) {
            $qty = $cartListArray[$a]['cartQty'];
            $cartId = $cartListArray[$a]['cartID'];
            $prodVar = $cartListArray[$a]['prodVariant'];

            $status="pending";
            $volume=(int)$qty;
            $exp_date_time = date("Y-m-d H:i:s", strtotime("+15 minutes"));
            // echo $volume;
            $previousItem="";

            for($b=0;$b<$volume;$b++){
                $count=$b+1;
                $query = $this->conn->prepare("SELECT ProdID,ProdPrice,RID,ProdName FROM product where ProdStatus = 'Available' AND product.ProdVariant=
(select ProdVariant from cart where CartID=?) AND product.ProdCode=(select ProdCode from cart where CartID=?) LIMIT 1 ");
                $query->bind_param("ss",$cartId,$cartId);
                $query->execute();
                $query->bind_result($prodCode,$singlePrice,$RID,$prodname);
                $fetchResult=$query->fetch();
                //echo "pending request:".$fetchResult;


                $productID=$prodCode;
                $productPrice=(float)$singlePrice;
                $query->close();

                if(!$fetchResult &&$b==0){//1st sudah mati
                    break;
                }
                if(!$fetchResult &&$volume!=$b+1){ //half way mati

                    $orderMsg=$orderMsg . $previousItem ;
                    $orderMsg=$orderMsg ."</t>". $count. "</br>";
                    break;
                }else{
                    $previousItem=$prodname; //on the way
                }
                if($volume==$b+1){//all added
                    $orderMsg=$orderMsg . $previousItem ;
                    $orderMsg=$orderMsg ."</t>". $count . "</br>";
                }



                $query3 = $this->conn->prepare("UPDATE product SET product.ProdStatus = 'lock' WHERE product.ProdID=? ");
                $query3->bind_param("s",$productID);
                $query3->execute();
                $query3->close();

                $getRefundAgree = $this->conn->prepare("SELECT  r.refundagree FROM product p,retailer r WHERE p.rid=r.rid and p.prodid=?");
                $getRefundAgree->bind_param("s",$productID);
                $getRefundAgree->execute();
                $getRefundAgree->bind_result($agreeStatus);
                $getRefundAgree->fetch();
                $getRefundAgree->close();


                $agree=(int)$agreeStatus;
                if($agree==0){
                    $insertOrder = $this->conn->prepare("INSERT INTO pendingitem (ProdID,Status,ExpiredDate,CustID) VALUES (?,?,?,(select CustID from cart where CartID=?))");
                    $insertOrder->bind_param("ssss",$productID,$status,$exp_date_time,$cartId);
                    $insertOrder->execute();
                    $insertOrder->close();
                }else{
                    $status = 'available';
                    $msgTitle = "Your item is available";
                    $msgAction = "checkoutorder";
                    $msgBody = "Your item " . $prodname . " is available. Please make payment within 15 minutes ";

                    $insertOrder2 = $this->conn->prepare("INSERT INTO pendingitem (ProdID,Status,ExpiredDate,CustID,picurl,replytime) VALUES (?,?,?,(select CustID from cart where CartID=?),(select i.url from (SELECT imageurl as 'url',prodcode as 'code' FROM prodimage group by prodcode) i,product p where p.prodid=? and p.prodcode=i.code),?)");
                    $insertOrder2->bind_param("ssssss", $productID, $status, $exp_date_time, $cartId, $productID, $date);
                    $insertOrder2->execute();
                    $insertOrder2->close();

                    $qry3 = $this->conn->prepare("select p.prodname,i.url from (SELECT imageurl as 'url',prodcode as 'code' FROM prodimage group by prodcode) i,product p where p.prodid=? and p.prodcode=i.code");
                    $qry3->bind_param("s", $productID);
                    $qry3->execute();
                    $qry3->bind_result($prodname, $ImageURL);
                    $qry3->fetch();
                    $qry3->close();

                    $qry4 = $this->conn->prepare("select CustID from cart where CartID=?");
                    $qry4->bind_param("s", $cartId);
                    $qry4->execute();
                    $qry4->bind_result($custID);
                    $qry4->fetch();
                    $qry4->close();
                    if(in_array($custID,$arrayCustID)){
                        //ignore
                    }else {


                        
                        array_push($arrayCustID, $custID);
                        $firebase = new firebase_notification();
                        $push = new push_notification();
                        $payload = array();
                        $payload['goto'] = $msgAction;

                        $push->setTopic($custID); //RID
                        $push->setTitle($msgTitle);
                        $push->setMessage($msgBody);
                        $push->setImage('http://www.cashierbook.com/retailer/images/MikoWong~profile2018-03-13-14-22-43.jpg');

                        $push->setIsBackground(FALSE);
                        $push->setPayload($payload);

                        $json = $push->getPush();
                        $firebase->sendToTopic($push->getTopic(), $json);

                        $qryz = $this->conn->prepare("INSERT INTO notification (NotifyTitle,NotifyMsg,NotifyDate,
NotifyStatus,NotifyAction,NotifyURL,UID) VALUES(?,?,?,?,?,?,?)");
                        $qryz->bind_param("sssssss", $msgTitle, $msgBody, $date, $msgStatus, $msgAction, $ImageURL, $custID);
                        $qryz->execute();
                        $qryz->close();

                        $sendNotify = 1;
                    }
                }



                //SELECT ProdID FROM product where ProdStatus = 'Available' AND product.ProdVariant=(select ProdVariant from cart where CartID=?) AND product.ProdCode=(select ProdCode from cart where CartID=?) LIMIT 1


                $query2 = $this->conn->prepare("UPDATE product SET product.ProdStatus = 'pending' WHERE product.ProdID=? ");
                $query2->bind_param("s",$productID);
                $query2->execute();
                $query2->close();
            }
            $delquery = $this->conn->prepare("DELETE FROM cart  WHERE cart.CartID=?");
            $delquery->bind_param("s",$cartId);
            $delquery->execute();
            $delquery->close();

            $total=$total+($volume*$productPrice);
            if(in_array($RID,$arrayRID)){
                //ignore
            }else if($sendNotify==0){
                //firebase push notification
                array_push($arrayRID, $RID);
                $imageURL="http://www.cashierbook.com/retailer/images/check-list-icon-flat-design.png";
                $firebase = new firebase_notification();
                $push = new push_notification();
                $payload = array();
                $payload['goto'] = $msgAction;

                $push->setTopic($RID); //$RID
                $push->setTitle($msgTitle);
                $push->setMessage($msgBody);
                $push->setImage('');

                $push->setIsBackground(FALSE);
                $push->setPayload($payload);

                $json = $push->getPush();
                $firebase->sendToTopic($push->getTopic(), $json);

                $qry = $this->conn->prepare("INSERT INTO notification (NotifyTitle,NotifyMsg,NotifyDate,
NotifyStatus,NotifyAction,NotifyURL,UID) VALUES(?,?,?,?,?,?,?)");
                $qry->bind_param("sssssss",$msgTitle,$msgBody,$date,$msgStatus,$msgAction,$imageURL,$RID);
                $qry->execute();
                $qry->close();

                //send mail
                $qry1 = $this->conn->prepare("SELECT REmail FROM retailer WHERE RID = ?");
                $qry1->bind_param("s",$RID);
                $qry1->execute();
                $qry1->bind_result($getEmail);
                $qry1->fetch();
                $qry1->close();

                $qry2 = $this->conn->prepare("select p.ProdName,p.prodvariant from pendingitem d, product p,customer c, (select ImageURL as 'url',
prodcode as 'code' from prodimage group by prodcode) src where p.prodid=d.prodid and p.rid=? and c.custid=d.custid  and src.code=p.prodcode and d.status='pending' 
group by d.CustID, d.expireddate,p.prodcode,p.prodvariant order by d.expireddate");
                $qry2->bind_param("s",$RID);
                $qry2->execute();
                $qry2->bind_result($productName,$productVariant);
                $orderArray=array();

                while($qry2->fetch()) {
                    $temp=array();
                    $temp['prodName']=$productName;
                    $temp['prodVariant']=$productVariant;
                    array_push($orderArray,$temp);

                }


                $qry2->close();

                $mail = new email_function();
                $mail->sendCheckStockMail($getEmail,$orderArray);
            }
        }
        echo $orderMsg;
    }

      public function fetchPendingOrder($customerid){
        $fetchpendingorderitem = $this->conn->prepare("SELECT p.prodvariant,p.prodname,p.prodprice,i.url,count(p.prodid),r.profilepicurl,r.rshopname,d.status,d.picurl,d.picurl2,d.replytime,d.expireddate,p.proddiscount,p.discountprice, p.prodcode  FROM pendingitem d,product p,(select prodcode as 'code',imageurl as 'url' from prodimage group by prodcode )i,retailer r WHERE (d.Status='available'  or d.Status='out of stock' or d.Status='pending' or d.status='cancel no response') and d.custid=? and p.prodid=d.prodid and i.code=p.prodcode and p.rid=r.rid group by p.prodcode,p.prodvariant,d.expireddate order by d.status,d.expireddate");
        $fetchpendingorderitem->bind_param("s",$customerid);
        $fetchpendingorderitem->execute();
        $fetchpendingorderitem->bind_result($prodVariant,$prodName,$prodPrice,$itemPicURL,$qtyOrder,$retailerPicURL,$retailerShopName,$orderStatus,$photo1,$photo2,$photodate,$expdate,$discount,$discountPrice,$prodcode);

        $orderItemArray=array();

        while($fetchpendingorderitem->fetch()) {
            $temp=array();
            $temp['prodVariant']=$prodVariant;
            $temp['prodImgURL']=$itemPicURL;
            $temp['prodName']=$prodName;
            $temp['prodPrice']=$prodPrice;
            $temp['cartQty']=$qtyOrder;
            $temp['retailerProfPicURL']=$retailerPicURL;
            $temp['retailerShopName']=$retailerShopName;
            $temp['pendingStatus']=$orderStatus;
            $temp['photo1']=$photo1;
            $temp['photo2']=$photo2;
            $temp['photodate']=$photodate;
            $temp['expdate']=$expdate;
            $temp['discountPrice']=$discountPrice;
            $temp['discount']=$discount;
            $temp['prodcode']=$prodcode;

            array_push($orderItemArray,$temp);

        }

        $fetchpendingorderitem->close();

        $json = json_encode($orderItemArray);
        echo  $json;
    }
    public function deletePendingOrder($expdate,$custid,$reason,$canceltype,$prodname,$prodvariant){

        $cancelDate=date("YmdHis");

        if($canceltype=='cancel no response'){
    $delPendingquery = $this->conn->prepare("update pendingitem d,product p set d.status='remove no response',p.prodstatus='Available' ,d.cancelReason=?,d.cancelTime=? where d.custid=? 
and d.expireddate=? and d.prodid=p.prodid and p.prodname=? and p.prodvariant=?");
    $delPendingquery->bind_param("ssssss",$reason,$cancelDate,$custid,$expdate,$prodname,$prodvariant);
}else{
    $delPendingquery = $this->conn->prepare("update pendingitem d,product p set d.status=?,p.prodstatus='Available' ,d.cancelReason=?,d.cancelTime=? where d.custid=? 
and d.expireddate=? and d.prodid=p.prodid and p.prodname=? and p.prodvariant=?");
    $delPendingquery->bind_param("sssssss",$canceltype,$reason,$cancelDate,$custid,$expdate,$prodname,$prodvariant);
}
        $delPendingquery->execute();
        $delPendingquery->close();

        echo "pending order removed";
    }


    public function feedback($uid,$role,$name,$category,$email,$desc){
	if($role=="Retailer"){
            $qry = $this->conn->prepare("SELECT RShopName FROM retailer WHERE RID = ?");
            $qry->bind_param("s",$uid);
            $qry->execute();
            $qry->bind_result($shopname);
            $qry->fetch();
	    $qry->close();
            $name = $shopname;
        }
        $stmt = $this->conn->prepare("INSERT INTO feedback (uid,role,feedName,feedCategory,feedEmail,feedDesc) 
	VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss",$uid,$role,$name,$category,$email,$desc);
        $stmt->execute();
        $stmt->close();
    }


    public function fetchCustAddress($custid){
       $fetchAddress = $this->conn->prepare("select address,state,city,postcode,recpname,recpcontact,recpemail,AddressID from DeliveryAddress where custid=? order by  addressType desc");
        $fetchAddress->bind_param("s",$custid);
        $fetchAddress->execute();
        $fetchAddress->bind_result($address,$state,$city,$postcode,$recpname,$recpcontact,$recpemail,$addID);

        $addressArray=array();

        while($fetchAddress->fetch()) {
            $temp=array();
            $temp['address']=$address;
            $temp['state']=$state;
            $temp['city']=$city;
            $temp['postcode']=$postcode;
            $temp['name']=$recpname;
            $temp['contact']=$recpcontact;
            $temp['email']=$recpemail;
            $temp['addId']=$addID;


            array_push($addressArray,$temp);

        }

        $fetchAddress->close();

        $json = json_encode($addressArray);
        echo  $json;
    }
    public function fetchNotificationCount($custid){
       $fetchChk = $this->conn->prepare("select count(*) from(select p.prodid from pendingitem d,product p where d.status='available' and p.prodid=d.prodid and d.custid=? group by d.expiredDate,p.prodcode,p.prodvariant) chkoutcount");
        $fetchChk->bind_param("s",$custid);
        $fetchChk->execute();
        $fetchChk->bind_result($chknum);
        $fetchChk->fetch();

        $chkNotificationCounter=$chknum;
        $fetchChk->close();

        $fetchOrd = $this->conn->prepare("select count(*) from (select count(o.orderid) from orderdetail o,custorder c where o.itemtracking='ready to collect' and o.orderid=c.orderid and c.custid=? group by o.orderid) ordercount");
        $fetchOrd->bind_param("s",$custid);
        $fetchOrd->execute();
        $fetchOrd->bind_result($ordnum);
        $fetchOrd->fetch();

        $ordNotificationCounter=$ordnum;
        $fetchOrd->close();
        
        $fetchFeedback = $this->conn->prepare("select count(*) from (select o.orderid from orderdetail o,custorder c,product p where c.orderid=o.orderid and isnull(o.ratingtime) and c.custid=? and p.prodid=o.prodid and o.itemtracking='delivered' group by p.prodcode,p.prodvariant,o.orderid) feed");
        $fetchFeedback->bind_param("s",$custid);
        $fetchFeedback->execute();
        $fetchFeedback->bind_result($feedbacknum);
        $fetchFeedback->fetch();
        $feedbackNotificationCounter=$feedbacknum;
        $fetchFeedback->close();

        $countArray=array();
        $temp=array();
        $temp['chknum']=$chkNotificationCounter;
        $temp['ordnum']=$ordNotificationCounter;
        $temp['feednum']=$feedbackNotificationCounter;

        array_push($countArray,$temp);

        $json = json_encode($countArray);
        echo  $json;
    }

    public function fetchRefund($custid){
        $fetchRefund = $this->conn->prepare("select p.shortselltime,p.prodname,p.prodprice,img.url from product p,custorder c,orderdetail o,(select prodcode as 'code',imageurl as 'url' from prodimage group by prodcode) img where c.orderid=o.orderid and o.prodid=p.prodid  and p.prodcode=img.code and  p.prodstatus='short selling!!!!' and c.custid=?");
        $fetchRefund->bind_param("s",$custid);
        $fetchRefund->execute();
        $fetchRefund->bind_result($time,$prodname,$price,$imgurl);

        $refundArray=array();

        while($fetchRefund->fetch()) {
            $temp=array();
            $temp['shortselldate']=$time;
            $temp['prodname']=$prodname;
            $temp['prodprice']=$price;
            $temp['imgurl']=$imgurl;


            array_push($refundArray,$temp);

        }

        $fetchRefund->close();

        $json = json_encode($refundArray);
        echo  $json;
    }
    
    public function fetchPickList(){
          $fetchPick = $this->conn->prepare("select count(p.prodid) as 'Qty',cust.cdisplayname as 'CustName',d.orderid as 'OrderID' ,
p.prodname as 'Product',p.prodvariant as 'variant',
r.rshopname as 'Shop',r.raddr as 'Address',p.rid,p.prodcode,dadd.address,dadd.state,dadd.city,dadd.postcode from orderdetail d,
custorder c,product p,customer cust,retailer r,DeliveryAddress dadd where d.itemtracking='ready to deliver' 
and c.orderid=d.orderid and p.prodid=d.prodid and c.custid=cust.custid and p.rid=r.rid and c.addressid=dadd.addressid group by p.prodcode,p.prodvariant,c.custid,d.orderid order by d.orderid");

        $fetchPick->execute();
        $fetchPick->bind_result($qty,$custname,$orderid,$item,$variant,$shop,$address,$retailerID,$prodcode,$deliveryAddress,$deliveryState,$deliveryCity,$deliveryPostcode);

        $pickArray=array();

        while($fetchPick->fetch()) {
            $temp=array();
            $temp['qty']=$qty;
            $temp['custname']=$custname;
            $temp['orderid']=$orderid;
            $temp['item']=$item;
            $temp['var']=$variant;
            $temp['shopname']=$shop;
            $temp['address']=$address;
            $temp['rid']=$retailerID;
            $temp['prodcode']=$prodcode;
            $temp['deliveryAddress']=$deliveryAddress.",".$deliveryState.",".$deliveryCity.",".$deliveryPostcode;


            array_push($pickArray,$temp);

        }

        $fetchPick->close();

        $json = json_encode($pickArray);
        echo  $json;
    }

    public function pickItem($parameter){

        $JsonObj = json_decode($parameter,true);

        $runnerid=$JsonObj[0]['runnerid'];
        $orderid=$JsonObj[0]['orderid'];
        $prodvariant=$JsonObj[0]['variant'];
        $prodname=$JsonObj[0]['prodname'];

        $msgTitle = "Order on the way";
        $msgBody = "Your order is delivering";
        $msgStatus = "UNREAD";
        $msgAction = "donepackitem";

        //update orderdetail o,product p set o.runnerid=?,pickuptime=?,itemtracking='Delivering' where o.orderid=? and p.prodid=o.prodid and p.prodvariant=? and p.prodname=?
        $date = date("YmdHis");
        $pickItemStmt = $this->conn->prepare( "update orderdetail o,product p set o.runnerid=?,pickuptime=?,itemtracking='Delivering' where o.orderid=? and p.prodid=o.prodid and p.prodvariant=? and p.prodname=?");
        $pickItemStmt->bind_param("sssss", $runnerid,$date,$orderid,$prodvariant,$prodname);
        $pickItemStmt->execute();
        $pickItemStmt->close();

        $fetchCustId = $this->conn->prepare("select custid from custorder where orderid=?");
        $fetchCustId->bind_param("s", $orderid);
        $fetchCustId->execute();
        $fetchCustId->bind_result($custid);
        $fetchCustId->fetch();
        $fetchCustId->close();

        $firebase = new firebase_notification();
        $push = new push_notification();
        $payload = array();
        $payload['goto'] = $msgAction;

        $push->setTopic($custid); //RID
        $push->setTitle($msgTitle);
        $push->setMessage($msgBody);
        $push->setImage('http://www.cashierbook.com/retailer/images/MikoWong~profile2018-03-13-14-22-43.jpg');

        $push->setIsBackground(FALSE);
        $push->setPayload($payload);

        $json = $push->getPush();
        $firebase->sendToTopic($push->getTopic(), $json);

        $url = "http://www.cashierbook.com/retailer/images/ic_ship.png";
        $qry = $this->conn->prepare("INSERT INTO notification (NotifyTitle,NotifyMsg,NotifyDate,
NotifyStatus,NotifyAction,NotifyURL,UID) VALUES(?,?,?,?,?,?,?)");
        $qry->bind_param("sssssss",$msgTitle,$msgBody,$date,$msgStatus,$msgAction,$url,$custid);
        $qry->execute();
        $qry->close();
    }

    public function dropItem($parameter){

        $JsonObj = json_decode($parameter,true);

        $runnerid=$JsonObj[0]['runnerid'];
        $hashUserID=$JsonObj[0]['userid'];
        $crypto = new phpFreaksCrypto();

        $custID=$crypto->decrypt($hashUserID);


        $query = $this->conn->prepare("SELECT * FROM orderdetail o,custorder c  where o.itemtracking='Delivering' and o.runnerid=? and o.orderid=c.orderid and c.custid=?");
        $query->bind_param("ss",$runnerid,$custID);
        $query->execute();
        $fetchResult=$query->fetch();
        $query->close();



        //update orderdetail o,product p set o.runnerid=?,pickuptime=?,itemtracking='Delivering' where o.orderid=? and p.prodid=o.prodid and p.prodvariant=? and p.prodname=?
        $date = date("YmdHis");
        $dropItemStmt = $this->conn->prepare( "update orderdetail o,custorder c set o.DeliveredTime=?,o.ItemTracking='delivered' where o.itemtracking='Delivering' 
and o.runnerid=? and o.orderid=c.orderid and c.custid=?");
        $dropItemStmt->bind_param("sss",$date,$runnerid,$custID);
        $dropItemStmt->execute();
        $dropItemStmt->close();

        if(!$fetchResult){
            $query2 = $this->conn->prepare("SELECT * FROM DeliveryAddress  where AddressID=? and CustID='SYSTEMDEFAULT' and AddressType='DEDICATED'");
            $query2->bind_param("i",$custID);
            $query2->execute();
            $fetchResult=$query2->fetch();
            $query2->close();

            if($fetchResult){

                $fetchPick = $this->conn->prepare("select c.custid,cust.cemail from orderdetail o,custorder c,customer cust where o.itemtracking='Delivering' 
and o.runnerid=? and o.orderid=c.orderid and c.addressid=? and c.custid=cust.custid group by c.custid");
                $fetchPick->bind_param("si",$runnerid,$custID);
                $fetchPick->execute();
                $fetchPick->bind_result($notfID,$email);

                $pickArray=array();

                while($fetchPick->fetch()) {
                    $temp=array();
                    $temp['notf']=$notfID;
                    $temp['mail']=$email;
                    array_push($pickArray,$temp);

                }

                $fetchPick->close();

                $json = json_encode($pickArray);



                $dropItemStmt2 = $this->conn->prepare( "update orderdetail o,custorder c set o.DeliveredTime=?,o.ItemTracking='ready to collect' where o.itemtracking='Delivering' 
and o.runnerid=? and o.orderid=c.orderid and c.addressid=?");
                $dropItemStmt2->bind_param("sss",$date,$runnerid,$custID);
                $dropItemStmt2->execute();
                $dropItemStmt2->close();



                $jsonObj=json_decode($json,true);

                // print_r($jsonObj[0]['prodidlist']);

                for($index=0;$index<sizeof($jsonObj);$index++){
                    $sendID=$jsonObj[$index]['notf'];
                    $email=$jsonObj[$index]['mail'];
                    //send notification
                    $msgAction = "donepackitem";
                    $firebase = new firebase_notification();
                    $push = new push_notification();
                    $payload = array();
                    $payload['goto'] = $msgAction;
                    $msgStatus = "UNREAD";
                    $msgTitle = "Your item has arrived";
                    $msgBody = "Please click manage order to collect item";

                    $push->setTopic($sendID); //RID
                    $push->setTitle($msgTitle);
                    $push->setMessage($msgBody);
                    $push->setImage('http://www.cashierbook.com/retailer/images/MikoWong~profile2018-03-13-14-22-43.jpg');

                    $push->setIsBackground(FALSE);
                    $push->setPayload($payload);

                    $json = $push->getPush();
                    $firebase->sendToTopic($push->getTopic(), $json);

                    $url = "http://www.cashierbook.com/retailer/images/delivered_icon1.png";
                    $qry = $this->conn->prepare("INSERT INTO notification (NotifyTitle,NotifyMsg,NotifyDate,
NotifyStatus,NotifyAction,NotifyURL,UID) VALUES(?,?,?,?,?,?,?)");
                    $qry->bind_param("sssssss",$msgTitle,$msgBody,$date,$msgStatus,$msgAction,$url,$sendID);
                    $qry->execute();
                    $qry->close();

                    $mail = new email_function();
                    $mail->sendDeliveredMail($email);
                    
                }




            }

        }


        if($fetchResult){
            echo "Item has been successfully delivered";
        }else{
            echo "Order doesn't exist, it might be expired, please check with our customer service, TQ.";
        }


    }

    public  function fetchFeedback($custid){
//SELECT p.prodname as 'product',p.prodvariant as 'variant',count(p.prodid) as 'qty',o.deliveredtime as 'Delivered Time' ,imgsrc.url as 'Item URL',r.profilepicurl as 'retailer pic',r.rshopname as 'shopname' FROM orderdetail o,product p,(select prodcode as 'code',imageurl as 'url' from prodimage group by prodcode) imgsrc,retailer r ,custorder c WHERE isnull(ratingtime) and itemtracking='delivered' and p.prodid=o.prodid and p.prodcode=imgsrc.code and p.rid=r.rid and c.orderid=o.orderid and c.custid='Mfa0WB8JLrOZDjP9ZqUyIOs0mJm2' group by p.prodcode,p.prodvariant,o.orderid
        $fetchFeedback = $this->conn->prepare("SELECT p.prodname as 'product',p.prodvariant as 'variant',count(p.prodid) as 'qty',
c.orderdate as 'order Time' ,imgsrc.url as 'Item URL',r.profilepicurl as 'retailer pic',
r.rshopname as 'shopname' FROM orderdetail o,product p,(select prodcode as 'code',imageurl as 'url' 
from prodimage group by prodcode) imgsrc,retailer r ,custorder c WHERE isnull(ratingtime) 
and itemtracking='delivered' and p.prodid=o.prodid and p.prodcode=imgsrc.code and p.rid=r.rid 
and c.orderid=o.orderid and c.custid=? group by p.prodcode,p.prodvariant,o.orderid order by c.orderdate desc");
        $fetchFeedback->bind_param("s",$custid);
        $fetchFeedback->execute();
        $fetchFeedback->bind_result($product,$variant,$qty,$deliveredTime,$itemURL,$retailerURL,$shopname);

        $feedbackArray=array();

        while($fetchFeedback->fetch()) {
            $temp=array();
            $temp['itemName']=$product;
            $temp['itemVariant']=$variant;
            $temp['itemQty']=$qty;
            $temp['deliveredDate']=$deliveredTime;
            $temp['itemURL']=$itemURL;
            $temp['retailerURL']=$retailerURL;
            $temp['shopName']=$shopname;


            array_push($feedbackArray,$temp);

        }

        $fetchFeedback->close();

        $json = json_encode($feedbackArray);
        echo  $json;
    }

    public function feedbackComment($parameter){
        $JsonObj = json_decode($parameter,true);

        $custid=$JsonObj[0]['custid'];
        $ratingStar=$JsonObj[0]['ratingStar'];
        $ratingComment=$JsonObj[0]['ratingComment'];
        $prodname=$JsonObj[0]['prodname'];
        $prodvar=$JsonObj[0]['prodvariant'];
        $deliveredTime=$JsonObj[0]['deliveredTime'];
        $date = date("YmdHis");


        //update orderdetail o,product p set o.runnerid=?,pickuptime=?,itemtracking='Delivering' where o.orderid=? and p.prodid=o.prodid and p.prodvariant=? and p.prodname=?

        $feedbackStmt = $this->conn->prepare( "update orderdetail o,product p,custorder c set o.ratingstar=?,o.ratingtime=?,o.ratingcomment=? where c.orderid=o.orderid and c.custid=?  and p.prodid=o.prodid and p.prodname=? and p.prodvariant=? and c.orderdate=?");
        $feedbackStmt->bind_param("sssssss",$ratingStar,$date,$ratingComment,$custid,$prodname,$prodvar,$deliveredTime);
        $feedbackStmt->execute();
        $feedbackStmt->close();

        echo "Success submit feedback";
    }
    public function fetchRetailerCover($rid){
        $fetchPick = $this->conn->prepare("select coverpicurl,profilepicurl,rshopname from retailer where rid=?");
        $fetchPick->bind_param("s",$rid);
        $fetchPick->execute();
        $fetchPick->bind_result($url,$profileurl,$shopname);

        $feedbackArray=array();

        while($fetchPick->fetch()) {
            $temp=array();
            $temp['coverurl']=$url;
            $temp['profileurl']=$profileurl;
            $temp['shopname']=$shopname;
            


            array_push($feedbackArray,$temp);

        }

        $fetchPick->close();

        $json = json_encode($feedbackArray);
        echo  $json;
    }
    
    public function getQR($uid){
        $crypto = new phpFreaksCrypto();
        $encryptedKey=$crypto->encrypt($uid);

        $object=array();

        $temp['key']=$encryptedKey;
        array_push($object,$temp);


        $json = json_encode($object);
        echo  $json;
    }

    public function fetchPurchasedHistory($custid){
        $fetchorderitem = $this->conn->prepare("select count(p.prodid),p.ProdName,p.ProdVariant,o.ItemTracking ,o.deliveredtime , i.url,r.rshopname from product p,orderdetail o,custorder c,retailer r,(select imageurl as 'url',prodcode as 'code' from prodimage group by prodcode) i where c.custid=? and o.ProdID=p.ProdID and o.OrderID=c.OrderID and i.code=p.prodcode and r.rid=p.rid and   o.itemtracking='delivered' group by p.prodcode,p.prodvariant,c.orderid order by c.orderdate desc ");
        $fetchorderitem->bind_param("s",$custid);
        $fetchorderitem->execute();
        $fetchorderitem->bind_result($qty,$itemname,$variant,$stat,$orderdate,$url,$shopname);

        $orderItemArray=array();

        while($fetchorderitem->fetch()) {
            $temp=array();
            $temp['itemName']=$itemname;
            $temp['itemQty']=$qty;
            $temp['itemVar']=$variant;
            $temp['itemStatus']=$stat;
            $temp['orderdate']=$orderdate;
            $temp['url']=$url;
            $temp['shopname']=$shopname;



            array_push($orderItemArray,$temp);

        }

        $fetchorderitem->close();

        $json = json_encode($orderItemArray);
        echo  $json;
    }

    public function fetchDeliveryList($runnerID){
        $fetchPick = $this->conn->prepare("select count(p.prodid) as 'Qty',cust.cdisplayname as 'CustName',d.orderid as 'OrderID' ,
p.prodname as 'Product',p.prodvariant as 'variant',
r.rshopname as 'Shop',r.raddr as 'Address',p.rid,p.prodcode,dadd.address,dadd.state,dadd.city,dadd.postcode from orderdetail d,
custorder c,product p,customer cust,retailer r,DeliveryAddress dadd where d.itemtracking='Delivering' 
and c.orderid=d.orderid and p.prodid=d.prodid and c.custid=cust.custid and p.rid=r.rid and c.addressid=dadd.addressid and d.runnerid=? group by p.prodcode,p.prodvariant,c.custid,d.orderid order by d.orderid");
        $fetchPick->bind_param("s",$runnerID);
        $fetchPick->execute();
        $fetchPick->bind_result($qty,$custname,$orderid,$item,$variant,$shop,$address,$retailerID,$prodcode,$deliveryAddress,$deliveryState,$deliveryCity,$deliveryPostcode);

        $pickArray=array();

        while($fetchPick->fetch()) {
            $temp=array();
            $temp['qty']=$qty;
            $temp['custname']=$custname;
            $temp['orderid']=$orderid;
            $temp['item']=$item;
            $temp['var']=$variant;
            $temp['shopname']=$shop;
            $temp['address']=$address;
            $temp['rid']=$retailerID;
            $temp['prodcode']=$prodcode;
            $temp['deliveryAddress']=$deliveryAddress.",".$deliveryState.",".$deliveryCity.",".$deliveryPostcode;


            array_push($pickArray,$temp);

        }

        $fetchPick->close();

        $json = json_encode($pickArray);
        echo  $json;
    }

    public function fetchReview($prodcode){
        //Select product all image url
        $query = $this->conn->prepare("SELECT c.cdisplayname as 'custname',d.ratingstar,d.ratingtime,p.prodvariant,d.ratingcomment FROM orderdetail d,product p,
customer c,custorder o WHERE p.prodid=d.prodid and p.prodcode=? and o.orderid=d.orderid and o.custid=c.custid and !isnull(d.ratingcomment) group by d.ratingtime,
d.orderid,p.prodvariant");
        $query->bind_param("s",$prodcode);
        $query->execute();
        $query->bind_result($custName,$ratingStar,$ratingTime,$prodVariant,$comment);

        $cartItemArray=array();

        while($query->fetch()) {
            $temp=array();
            $temp['custName']=$custName;
            $temp['star']=$ratingStar;
            $temp['ratingTime']=$ratingTime;
            $temp['prodVariant']=$prodVariant;
            $temp['comment']=$comment;
            

            array_push($cartItemArray,$temp);

        }

        $query->close();
        $json = json_encode($cartItemArray);
        echo $json;
    }

    public function sendMosaic($pubkey,$priKey,$recpAdd){
        $net = 'testnet';
        $NEMpubkey = $pubkey;
        $NEMprikey = $priKey;
        $baseurl = 'http://localhost:7890';
        $address = $recpAdd; // &#36865;&#12426;&#20808; recipient

        $mosaic = new TransactionBuilder($net);
        $mosaic->setting($NEMpubkey, $NEMprikey, $baseurl);
        $mosaic->ImportAddr($address);
        $mosaic->message = 'We are the one.';
        /*
         * &#12418;&#12375;&#12289;namuyan:namu &#12434; 23.45 (divisibility = 2)
         *       godtanu:godtanu &#12434; 100 (divisibility = 0)
         * &#36865;&#12427;&#12392;&#12375;&#12383;&#12425;&#12289;
         * if Mosaics ( 23.45 namuyan:namu AND 100 godtanu:godtanu ) transfer,
         */
        $mosaic->InputMosaic('namuyan', 'namu', 2345);
        $mosaic->InputMosaic('godtanu', 'godtanu', 100);

        $fee = $mosaic->EstimateFee();
        $levy = $mosaic->EstimateLevy();
        $reslt = $mosaic->SendMosaicVer2();
        $anal = $mosaic->analysis($reslt);

        echo '<P>','Fee is ',$fee,'<BR>';
        if($anal['status']){
            echo 'TXID is ',$anal['txid'],'</P>';
            echo '<PRE>',"levy is\n";
            print_r($levy);
            echo "Send mosaic is or are\n";
            print_r($mosaic->mosaic);
            echo '<?PRE>';
        }else{
            echo "Fail to send.<BR>error message: {$anal['message']}</P>";
        }
    }

}
?>