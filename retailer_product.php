<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kuala_Lumpur');
class retailer_product
{
    private $conn;

    // constructor
    function __construct()
    {
        require_once 'connect_db.php';
        require_once __DIR__ . '/firebase_notification.php';
        require_once __DIR__ . '/push_notification.php';

        // connecting to database
        $db = new connect_db();
        $this->conn = $db->connect();
        // Create connection

        function generateRandomString()
        {
            $length = 10;
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

    }

    // destructor
    function __destruct()
    {

    }

    /**
     * Retailer and new product
     * @param $detailsArray
     * @param $ImageArray
     */
    public function addProduct($detailsArray, $ImageArray, $variantAll)
    {
        //print_r($variantAll);
        $name = $detailsArray['Details:1'];
        $category = $detailsArray['Details:2'];
        $desc = $detailsArray['Details:3'];
        $size = $detailsArray['Details:4'];
        $retailer = $detailsArray['Details:5'];
        $status = "Available";
        $date = date("YmdHis");

        //GET Latest Code from database
        $this->conn->query("INSERT INTO codeseq(code) VALUES(NULL)");

        $sth = $this->conn->query("SELECT MAX(code) FROM codeseq");
        $result = $sth->fetch_row();
        $code = $result[0];
        $sth->close();
        foreach ($variantAll as $key => $value) {
            $price = $value['Price:'];
            $variant = $value['Title:'];
            $discount = $value['Discount:'];
            $finalPrice = round($price - ($price * $discount / 100), 2);

            $this->conn->autocommit(false);
            $stmt = $this->conn->prepare("INSERT INTO product(ProdID,ProdCode,ProdName,ProdVariant,ProdPrice,
 ProdDate,ProdCategory,ProdDesc,ProdSize,ProdDiscount,discountPrice,ProdStatus,RID) VALUES(NULL,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("issdssssddss", $code, $name, $variant, $price, $date, $category,
                $desc, $size, $discount, $finalPrice, $status, $retailer);
            for ($i = 0; $i < (int)$value['Quantity:']; $i++) {
                $stmt->execute();
            }
            $stmt->close();
        }

        //print_r($ImageArray);
        $num = 0;
        foreach ($ImageArray as $key => $Imagevalue) {
            $ImagePath = "product_image/$code$date$retailer$num.jpg";
            $ImageURL = "http://ecommercefyp.000webhostapp.com/retailer/$ImagePath";

            file_put_contents($ImagePath, base64_decode($Imagevalue));
            $queryURL = $this->conn->prepare("INSERT INTO prodimage(ImageID,ImageURL,ProdCode) VALUES (NULL,?,?)");
            $queryURL->bind_param("ss", $ImageURL, $code);
            $queryURL->execute();
            $queryURL->close();
            $num++;
        }
        for ($num; $num < 5; $num++) {
            $empty = $this->conn->prepare("INSERT INTO prodimage(ImageID,ImageURL,ProdCode) VALUES (NULL,NULL,?)");
            $empty->bind_param("s", $code);
            $empty->execute();
            $empty->close();
        }
        $this->conn->commit();
        echo $code;
    }

    public function fetchRetailerProduct($retailerID,$index,$searchKey){

        $searchKey= "%" . $searchKey . "%";
        $stmt = $this->conn->prepare( "SELECT p.code, retailer.RShopName, 
p.discount, p.price, p.name, img.url,coalesce(ranking.rankprice,'NA'),coalesce(ranking.endDate,'NA') FROM retailer, (select prodcode as 'code',proddiscount as 'discount',prodprice as 'price',prodname as 'name',rid as 'rid' ,prodshow as 'show' from product where prodshow =1 and prodstatus='Available' and (prodname like ? or prodcategory like ? or proddesc like ?) group by prodcode) p left join (select prodcode as 'code',price as 'rankprice',enddate as 'endDate' from rank where NOW()<enddate) ranking on ranking.code=p.code,(select imageurl as 'url',prodcode as 'code' from prodimage group by prodcode) img
 WHERE p.rid =? AND p.rid = retailer.RID AND p.code = img.code limit ?,6");
        $stmt->bind_param("sssss", $searchKey, $searchKey, $searchKey, $retailerID,$index);
//executing the query
        $stmt->execute();
//binding results to the query
        $stmt->bind_result($prodCode, $shopName, $discount, $price, $prodName, $imageURL,$boostPrice,$endBoostDate);

        $products = array();

        //traversing through all the result
        while($stmt->fetch()){
            $temp = array();

            $temp['prodcode'] = $prodCode;
            $temp['shopname'] = $shopName;
            $temp['discount'] = $discount;
            $temp['price'] = $price;
            $temp['prodname'] = $prodName;
            $temp['imageurl'] = $imageURL;
            $temp['boostprice']=$boostPrice;
            $temp['endboostdate']=$endBoostDate;
            array_push($products, $temp);
        }
        $json = json_encode($products);

        //displaying the result in json format
        echo $json;
        $stmt->close();
    }

    public function fetchProductDetails($prodCode)
    {
        //Declare new array
        $products = array();
        $productArray = array();
        $variantArray = array();
        $imageArray = array();
        $temp = array();
        $temp1 = array();

        //Select product details
        $qry = $this->conn->prepare("SELECT prodcode,prodname,proddesc,prodcategory,prodsize FROM product WHERE
prodcode = ? AND prodstatus = 'Available' LIMIT 1");
        $qry->bind_param("s", $prodCode);
        $qry->execute();
        $qry->store_result();
        if ($qry->num_rows > 0) {
            $qry->bind_result($prodcode, $prodname, $proddesc, $prodcategory, $prodsize);
            $qry->fetch();
            $temp1['prodcode'] = $prodcode;
            $temp1['prodname'] = $prodname;
            $temp1['proddesc'] = $proddesc;
            $temp1['prodcategory'] = $prodcategory;
            $temp1['prodsize'] = $prodsize;
            array_push($productArray, $temp1);
            $qry->close();
        } else {
            $qry->close();
            $qry2 = $this->conn->prepare("SELECT prodcode,prodname,proddesc,prodcategory,prodsize FROM product WHERE
prodcode = ? LIMIT 1");
            $qry2->bind_param("s", $prodCode);
            $qry2->execute();
            $qry2->bind_result($prodcode, $prodname, $proddesc, $prodcategory, $prodsize);
            $qry2->fetch();
            $temp1['prodcode'] = $prodcode;
            $temp1['prodname'] = $prodname;
            $temp1['proddesc'] = $proddesc;
            $temp1['prodcategory'] = $prodcategory;
            $temp1['prodsize'] = $prodsize;
            array_push($productArray, $temp1);
            $qry2->close();
        }

        //Select product quantity,price,variant and discount
        $stmt = $this->conn->prepare("select src.variant,src.price,src.discount,coalesce(tar.qty,'0') as 'quantity' from (SELECT ProdCode as 'code',ProdName as 'name',
ProdVariant as 'variant', ProdPrice as 'price' ,ProdCategory as 'cat',ProdDesc as 'description',
ProdSize  as 'size',ProdDiscount as 'discount' FROM product  WHERE ProdCode = ? AND ProdShow= 1 and prodstatus='Available'
group by prodcode,prodvariant ) src left join (SELECT prodcode as 'code',p.prodvariant as 'variant',
count(p.prodid) as 'qty' from product p WHERE p.ProdCode = ? and p.prodstatus='Available' 
group by p.prodcode,p.prodvariant) tar on tar.code=src.code and tar.variant=src.variant");
        $stmt->bind_param("ss", $prodCode, $prodCode);
        $stmt->execute();
        $stmt->bind_result($prodvariant, $prodprice, $prodDiscount, $prodQuantity);

        //Fetch all product details
        while ($stmt->fetch()) {
            $temp['prodvariant'] = $prodvariant;
            $temp['prodprice'] = $prodprice;
            $temp['proddiscount'] = $prodDiscount;
            $temp['prodquantity'] = $prodQuantity;
            array_push($variantArray, $temp);
        }
        $stmt->close();

        //Select product all imageUrl and ID
        $query = $this->conn->prepare("SELECT ImageID ,ImageURL FROM prodimage WHERE ProdCode= ?");
        $query->bind_param("s", $prodCode);
        $query->execute();
        $query->bind_result($imageid, $imageurl);
        //Fetch all imageURL and ID
        while ($query->fetch()) {
            $temp1['imageid'] = $imageid;
            $temp1['imageurl'] = $imageurl;
            array_push($imageArray, $temp1);
        }
        $query->close();

        //Push 2 array into 1
        array_push($products, $productArray);
        array_push($products, $variantArray);
        array_push($products, $imageArray);
        $json = json_encode($products);

        //displaying the result in json format
        echo $json;
    }

    public function updateProductDetails($detailsArray, $VariantArray, $ImageArray, $ImageIDArray, $RemoveIDArray)
    {
        print_r($VariantArray);
        //Initiative Value
        $name = $detailsArray['Details:1'];
        $category = $detailsArray['Details:2'];
        $desc = $detailsArray['Details:3'];
        $code = $detailsArray['Details:4'];
        $size = $detailsArray['Details:5'];
        $retailer = $detailsArray['Details:6'];
        $date = date("YmdHis");
        $status = "Available";

        //Clear row and re-insert again
        $clear = $this->conn->prepare("UPDATE product SET prodstatus = 'Deleted' WHERE ProdCode=? 
AND ProdStatus ='Available'");
        $clear->bind_param("i", $code);
        $clear->execute();
        $clear->close();

        foreach ($VariantArray as $key => $value) {
            $price = $value['Price:'];
            $variant = $value['Title:'];
            $discount = $value['Discount:'];
            $finalPrice = round($price - ($price * $discount / 100), 2);

            $this->conn->autocommit(false);
            $stmt = $this->conn->prepare("INSERT INTO product(ProdID,ProdCode,ProdName,ProdVariant,ProdPrice,
 ProdDate,ProdCategory,ProdDesc,ProdSize,ProdDiscount,discountPrice,ProdStatus,RID) VALUES(NULL,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("issdssssddss", $code, $name, $variant, $price, $date, $category,
                $desc, $size, $discount, $finalPrice, $status, $retailer);
            for ($i = 0; $i < (int)$value['Quantity:']; $i++) {
                $stmt->execute();
            }
            $stmt->close();
        }

        //Image
        $num = 0;
        $idArray = array();
        foreach ($ImageIDArray as $key => $id) {
            array_push($idArray, $id);
        }
        foreach ($ImageArray as $key => $value) {
            $ImagePath = "product_image/$code$date$retailer$num.jpg";
            $ImageURL = "http://ecommercefyp.000webhostapp.com/retailer/$ImagePath";
            file_put_contents($ImagePath, base64_decode($value));
            $query = $this->conn->prepare("UPDATE prodimage SET ImageURL = ? WHERE ImageID = ?");
            $query->bind_param("ss", $ImageURL, $idArray[$num]);
            $query->execute();
            $query->close();
            $num++;
        }

        foreach ($RemoveIDArray as $key => $removeid) {
            $removeqry = $this->conn->prepare("UPDATE prodimage SET ImageURL = NULL WHERE ImageID = ?");
            $removeqry->bind_param("s", $removeid);
            $removeqry->execute();
            $removeqry->close();
        }
        $this->conn->commit();
    }

    public function deleteProduct($deleteProd)
    {
        //Delete product details
        $query = $this->conn->prepare("UPDATE product SET prodstatus ='Deleted' WHERE prodCode = ? 
AND ProdStatus='Available'");
        $query->bind_param("i", $deleteProd);
        $query->execute();
        $query->close();

//Hide product from retailer
        $query2 = $this->conn->prepare("UPDATE product SET prodshow = 0 WHERE prodcode = ?");
        $query2->bind_param("i", $deleteProd);
        $query2->execute();
        $query2->close();
    }

    public function fetchOrderProduct($RID)
    {

        $fetchOrderQuery = $this->conn->prepare("select o.orderid,p.prodname,p.ProdVariant,c.orderdate , count(p.ProdVariant),p.prodcode from orderdetail o,product p, custorder c where o.orderid=c.orderid and o.prodid=p.prodid and p.rid=? and 	o.ItemTracking='packing' group by p.ProdVariant,o.orderid order by o.orderid ");
        $fetchOrderQuery->bind_param("s", $RID);
        $fetchOrderQuery->execute();
        $fetchOrderQuery->bind_result($orderid, $itemname, $variant, $date, $qty, $prodcode);

        $orderProductArray = array();
        while ($fetchOrderQuery->fetch()) {

            $temp = array();
            $temp['orderid'] = $orderid;
            $temp['itemname'] = $itemname;
            $temp['variant'] = $variant;
            $temp['qty'] = $qty;
            $temp['date'] = $date;
            $temp['prodcode'] = $prodcode;


            array_push($orderProductArray, $temp);

        }
        $fetchOrderQuery->close();
        $json = json_encode($orderProductArray);
        echo $json;

    }

    public function donePackItem($orderid, $prodvariant, $prodcode, $rid)
    {
        
        //update orderdetail ord, (select o.prodid from orderdetail o,product p where o.orderid=? and p.prodvariant=? and o.prodid=p.prodid and p.prodcode=? and p.rid=? and o.ItemTracking='packing' ) src set ord.ItemTracking='ready to deliver' where ord.prodid=src.prodid;
        $updateOrderItemStmt = $this->conn->prepare("update orderdetail ord, (select o.prodid from orderdetail o,product p where o.orderid=? and p.prodvariant=? and o.prodid=p.prodid and p.prodcode=? and p.rid=? and o.ItemTracking='packing' ) src set ord.ItemTracking='ready to deliver' where ord.prodid=src.prodid");
        $updateOrderItemStmt->bind_param("ssss", $orderid, $prodvariant, $prodcode, $rid);
        $updateOrderItemStmt->execute();
        $updateOrderItemStmt->close();
       
    }

    public function cancelAllSameProductOrder($prodvar, $prodcode, $rid)
    {
        $date = date("YmdHis");
        $cancelOrderItemStmt = $this->conn->prepare( "update orderdetail ord,product prod, (select o.prodid as 'prodid' from orderdetail o,product p where  p.prodvariant=? and o.prodid=p.prodid and p.prodcode=? and p.rid=? and (o.ItemTracking='packing' or o.ItemTracking='ready to deliver' )) src set ord.ItemTracking='short selling!!!!', prod.ProdStatus='short selling!!!!',prod.ShortSellTime=? where prod.prodid=src.prodid and ord.prodid=src.prodid ");
        $cancelOrderItemStmt->bind_param("ssss", $prodvar,$prodcode,$rid,$date);
        $cancelOrderItemStmt->execute();
        $cancelOrderItemStmt->close();

        $cancelOrderItemStmt2 = $this->conn->prepare( "update product  set ProdStatus='short selling!!!!', ShortSellTime=? where prodcode=? and prodstatus!='Sold' ");
        $cancelOrderItemStmt2->bind_param("ss",$date, $prodcode);
        $cancelOrderItemStmt2->execute();
        $cancelOrderItemStmt2->close();
    }

    public function fetchOrderConfirmation($rid)
    {
        $fetchOrderQuery = $this->conn->prepare("select c.CDisplayName, p.ProdName,d.expireddate,count(d.PendingID) as 'qty' ,p.prodvariant,src.url,d.custid,p.prodcode from pendingitem d, product p,customer c, (select ImageURL as 'url',prodcode as 'code' from prodimage group by prodcode) src where p.prodid=d.prodid and p.rid=? and c.custid=d.custid  and src.code=p.prodcode and d.status='pending' group by d.CustID, d.expireddate,p.prodcode,p.prodvariant order by d.expireddate");
        $fetchOrderQuery->bind_param("s", $rid);
        $fetchOrderQuery->execute();
        $fetchOrderQuery->bind_result($custname, $itemname, $orderdate, $qty, $variant, $imgurl, $custid, $prodcode);
        $orderProductArray = array();
        while ($fetchOrderQuery->fetch()) {

            $temp = array();
            $temp['customername'] = $custname;
            $temp['itemname'] = $itemname;
            $temp['orderdate'] = $orderdate;
            $temp['qty'] = $qty;
            $temp['variant'] = $variant;
            $temp['imgurl'] = $imgurl;
            $temp['custid'] = $custid;
            $temp['prodcode'] = $prodcode;

            array_push($orderProductArray, $temp);
        }
        $fetchOrderQuery->close();
        $json = json_encode($orderProductArray);
        echo $json;
    }

    public function confirmOrderImage($detailsArray, $ImageArray, $prodcode, $prodvar)
    {
        $msgTitle = "Your item is available";
        $msgStatus = "UNREAD";
        $msgAction = "checkoutorder";

        $arrayID = array();
        $date = date("YmdHis");
        $getRandomString = generateRandomString();
        $num = 0;
        $custID = $detailsArray['custID'];
        $expiredDate = $detailsArray['expiredDate'];
        $Imagevalue = $ImageArray['Image:1'];
        $ImagePath = "confirm_order_image/$date$getRandomString$num.jpg";
        $ImageURL = "http://ecommercefyp.000webhostapp.com/retailer/$ImagePath";

        if (sizeof($ImageArray) > 1) {
            $Imagevalue2 = $ImageArray['Image:2'];
            $num = 1;
            $ImagePath2 = "confirm_order_image/$date$getRandomString$num.jpg";
            $ImageURL2 = "http://ecommercefyp.000webhostapp.com/retailer/$ImagePath2";
            file_put_contents($ImagePath2, base64_decode($Imagevalue2));
        } else {
            $ImageURL2 = NULL;
        }

        file_put_contents($ImagePath, base64_decode($Imagevalue));
        $queryURL = $this->conn->prepare("update pendingitem d,product p set d.picurl=?,d.picurl2=?,d.status='available',	d.replytime=?
 where d.custid=? and d.expireddate=? and d.prodid=p.prodid and p.prodcode=? and p.ProdVariant=?");
        $queryURL->bind_param("sssssss", $ImageURL, $ImageURL2, $date, $custID, $expiredDate, $prodcode, $prodvar);
        $queryURL->execute();
        $queryURL->close();

        echo " image 1: ";
        echo $Imagevalue;
        echo " image 2:";
        echo $Imagevalue2;
        echo "  product code:  ";
        echo $prodcode;
        echo "  product variant:  ";
        echo $prodvar;

        if (in_array($custID, $arrayID)) {
            //ignore
        } else {
            $qry3 = $this->conn->prepare("SELECT prodname FROM product WHERE prodcode = ? AND prodstatus = 'Available' LIMIT 1");
            $qry3->bind_param("s", $prodcode);
            $qry3->execute();
            $qry3->bind_result($prodname);
            $qry3->fetch();
            $qry3->close();

            $msgBody = "Your item " . $prodname . " is available. Please make payment within 15 minutes ";
            $firebase = new firebase_notification();
            $push = new push_notification();
            $payload = array();
            $payload['goto'] = $msgAction;

            $push->setTopic($custID); //RID
            $push->setTitle($msgTitle);
            $push->setMessage($msgBody);
            $push->setImage('http://ecommercefyp.000webhostapp.com/retailer/images/MikoWong~profile2018-03-13-14-22-43.jpg');

            $push->setIsBackground(FALSE);
            $push->setPayload($payload);

            $json = $push->getPush();
            $firebase->sendToTopic($push->getTopic(), $json);

            $qry = $this->conn->prepare("INSERT INTO notification (NotifyTitle,NotifyMsg,NotifyDate,
NotifyStatus,NotifyAction,NotifyURL,UID) VALUES(?,?,?,?,?,?,?)");
            $qry->bind_param("sssssss", $msgTitle, $msgBody, $date, $msgStatus, $msgAction, $ImageURL, $custID);
            $qry->execute();
            $qry->close();
        }
    }

    public function orderOutOfStock($expdate, $custid)
    {
        $delPendingquery = $this->conn->prepare("update pendingitem d,product p set d.status='out of stock',p.prodstatus='out of stock' where d.custid=? and d.expireddate=? and d.prodid=p.prodid ");
        $delPendingquery->bind_param("ss", $custid, $expdate);
        $delPendingquery->execute();
        $delPendingquery->close();

        echo "order out of stock updated";
    }

    public function fetchNotfCount($RID)
    {
        $fetchChk = $this->conn->prepare("select count(*) from (select c.CDisplayName, p.ProdName,d.expireddate,count(d.PendingID) as 'qty' ,p.prodvariant,src.url,d.custid,p.prodcode from pendingitem d, product p,customer c, (select ImageURL as 'url',prodcode as 'code' from prodimage group by prodcode) src where p.prodid=d.prodid and p.rid=? and c.custid=d.custid  and src.code=p.prodcode and d.status='pending' group by d.CustID, d.expireddate,p.prodcode,p.prodvariant order by d.expireddate) confirm");
        $fetchChk->bind_param("s",$RID);
        $fetchChk->execute();
        $fetchChk->bind_result($confirmnum);
        $fetchChk->fetch();

        $orderConfirmCounter=$confirmnum;
        $fetchChk->close();

        $fetchOrd = $this->conn->prepare("select count(*) from (select o.orderid,p.prodname,p.ProdVariant,c.orderdate , count(p.ProdVariant),p.prodcode from orderdetail o,product p, custorder c where o.orderid=c.orderid and o.prodid=p.prodid and p.rid=? and 	o.ItemTracking='packing' group by p.ProdVariant,o.orderid order by o.orderid ) pack");
        $fetchOrd->bind_param("s",$RID);
        $fetchOrd->execute();
        $fetchOrd->bind_result($packnum);
        $fetchOrd->fetch();

        $ordPackCounter=$packnum;
        $fetchOrd->close();

        $fetchCart = $this->conn->prepare("select count(*)from (select r.retailercartid,r.prodcode,r.price,r.period,i.url,p.name from (select prodcode as 'code',prodname as 'name'
 from product group by prodcode) p,retailerCart r,(select imageurl as 'url', prodcode as 'code' from prodimage group by prodcode) i where r.rid=? 
 and r.prodcode=i.code and p.code=r.prodcode order by r.retailercartid desc) z");
        $fetchCart->bind_param("s",$RID);
        $fetchCart->execute();
        $fetchCart->bind_result($cartnum);
        $fetchCart->fetch();

        $cartCounter=$cartnum;
        $fetchCart->close();


        $countArray=array();
        $temp=array();
        $temp['ordconfirmnum']=$orderConfirmCounter;
        $temp['ordpacknum']=$ordPackCounter;
        $temp['cartnum']=$cartCounter;

        array_push($countArray,$temp);

        $json = json_encode($countArray);
        echo  $json;
    }

    public function fetchOverallSalesReport($parameter)
    {
        $JsonObj = json_decode($parameter, true);

        $reportStartDate = $JsonObj[0]['reportStartDate'];
        $reportEndDate = $JsonObj[0]['reportEndDate'];
        $RID = $JsonObj[0]['RID'];

        $fetchReportQuery = $this->conn->prepare("SELECT sum(p.discountprice), d.soldtime FROM pendingitem d, product p WHERE d.STATUS =  'sold' AND ( d.soldtime >  ? AND d.soldtime <  ? ) AND p.prodid = d.prodid  AND p.RID=? GROUP BY DAY(d.soldtime)");
        $fetchReportQuery->bind_param("sss", $reportStartDate, $reportEndDate, $RID);
        $fetchReportQuery->execute();
        $fetchReportQuery->bind_result($salesFigure, $figureDate);
        $orderProductArray = array();
        while ($fetchReportQuery->fetch()) {

            $temp = array();
            $temp['salesFigure'] = $salesFigure;
            $temp['figureDate'] = $figureDate;


            array_push($orderProductArray, $temp);
        }
        $fetchReportQuery->close();
        $json = json_encode($orderProductArray);
        echo $json;


    }

    public function fetchItemSoldCount($parameter)
    {
        $JsonObj = json_decode($parameter, true);

        $reportStartDate = $JsonObj[0]['reportStartDate'];
        $reportEndDate = $JsonObj[0]['reportEndDate'];
        $RID = $JsonObj[0]['RID'];

        $fetchCountQuery = $this->conn->prepare("SELECT  count(p.prodid) FROM pendingitem d, product p WHERE d.STATUS =  'sold' AND ( d.soldtime >  ? AND d.soldtime <  ? ) AND p.prodid = d.prodid AND p.RID=? ");
        $fetchCountQuery->bind_param("sss", $reportStartDate, $reportEndDate, $RID);
        $fetchCountQuery->execute();
        $fetchCountQuery->bind_result($transcount);
        $orderProductArray = array();
        while ($fetchCountQuery->fetch()) {

            $temp = array();
            $temp['itemSoldCount'] = $transcount;
            array_push($orderProductArray, $temp);
        }
        $fetchCountQuery->close();
        $json = json_encode($orderProductArray);
        echo $json;


    }

    public function fetchTransactionCount($parameter)
    {
        $JsonObj = json_decode($parameter, true);

        $reportStartDate = $JsonObj[0]['reportStartDate'];
        $reportEndDate = $JsonObj[0]['reportEndDate'];
        $RID = $JsonObj[0]['RID'];

        $fetchCountQuery = $this->conn->prepare("select count(*) from (SELECT p.prodcode,p.prodname,p.prodvariant, d.soldtime FROM pendingitem d, product p WHERE d.STATUS =  'sold' AND ( d.soldtime >  ? AND d.soldtime <  ? ) AND p.prodid = d.prodid AND p.RID=? group by soldtime) z");
        $fetchCountQuery->bind_param("sss", $reportStartDate, $reportEndDate, $RID);
        $fetchCountQuery->execute();
        $fetchCountQuery->bind_result($transcount);
        $orderProductArray = array();
        while ($fetchCountQuery->fetch()) {

            $temp = array();
            $temp['transactionCount'] = $transcount;
            array_push($orderProductArray, $temp);
        }
        $fetchCountQuery->close();
        $json = json_encode($orderProductArray);
        echo $json;


    }

    public function fetchSalesDetailReport($parameter)
    {
        $JsonObj = json_decode($parameter, true);

        $reportStartDate = $JsonObj[0]['salesStartDate'];
        $reportEndDate = $JsonObj[0]['salesEndDate'];
        $RID = $JsonObj[0]['RID'];

        $fetchReportQuery = $this->conn->prepare("select p.prodname,p.prodvariant,p.discountprice,count(p.prodid) as 'soldqty' from pendingitem d,product p where d.status='sold' and p.prodid=d.prodid and (d.soldtime >? and d.soldtime <?)  and  p.rid=? group by p.prodcode,p.prodvariant,DAY(d.soldtime) order by soldqty desc");
        $fetchReportQuery->bind_param("sss", $reportStartDate, $reportEndDate, $RID);
        $fetchReportQuery->execute();
        $fetchReportQuery->bind_result($prodName, $prodVar, $prodPrice, $prodQty);
        $orderProductArray = array();
        while ($fetchReportQuery->fetch()) {

            $temp = array();
            $temp['prodName'] = $prodName;
            $temp['prodVar'] = $prodVar;
            $temp['prodQty'] = $prodQty;
            $temp['prodPrice'] = $prodPrice;


            array_push($orderProductArray, $temp);
        }
        $fetchReportQuery->close();
        $json = json_encode($orderProductArray);
        echo $json;


    }

    public function updateProfile($parameter)
    {
        $JsonObj = json_decode($parameter, true);

        $ownerName = $JsonObj[0]["ROwnerName"];
        $ownerContact = $JsonObj[0]["RContact"];
        $shopDescription = $JsonObj[0]["RDesc"];
        $profilePhotoData = $JsonObj[0]["ProfilePhotoData"];
        $coverPhotoData = $JsonObj[0]["CoverPhotoData"];
        $retailerEmail = $JsonObj[0]["retailerEmail"];
        $cover = "cover";
        $profile = "profile";
        $sdate = date('Y-m-d-H-i-s');

        $coverImagePath = "images/$ownerName$cover$sdate.jpg";
        $profileImagePath = "images/$ownerName$profile$sdate.jpg";

        $coverImagePath = str_replace(' ', '', $coverImagePath);
        $profileImagePath = str_replace(' ', '', $profileImagePath);

        $coverServerURL = "http://ecommercefyp.000webhostapp.com/retailer/$coverImagePath";
        $profileServerURL = "http://ecommercefyp.000webhostapp.com/retailer/$profileImagePath";


        $SQL_Update = "";

        $value1 = strcmp($profilePhotoData, "");
        $value2 = strcmp($coverPhotoData, "");


        if ((int)$value1 == 0) {
//no profile image
            $fetchOldURL = $this->conn->prepare("select profilePicURL from retailer where REmail=?");
            $fetchOldURL->bind_param("s", $retailerEmail);
            $fetchOldURL->execute();
            $fetchOldURL->bind_result($oldURL);
            $fetchOldURL->fetch();


            $profileServerURL = $oldURL;
            $fetchOldURL->close();


        } else {
            file_put_contents($profileImagePath, base64_decode($profilePhotoData));
        }

        if ((int)$value2 == 0) {
//no cover image
            $fetchOldCoverURL = $this->conn->prepare("select coverPicURL from retailer where REmail=?");
            $fetchOldCoverURL->bind_param("s", $retailerEmail);
            $fetchOldCoverURL->execute();
            $fetchOldCoverURL->bind_result($oldCoverURL);
            $fetchOldCoverURL->fetch();


            $coverServerURL = $oldCoverURL;
            $fetchOldCoverURL->close();
        } else {
            file_put_contents($coverImagePath, base64_decode($coverPhotoData));
        }

        $updateProfileQuery = $this->conn->prepare("UPDATE retailer SET ROwnerName = ?, RContact = ?, RDesc = ?,coverPicURL = ?,profilePicURL = ? WHERE REmail =?");
        $updateProfileQuery->bind_param("ssssss", $ownerName, $ownerContact, $shopDescription, $coverServerURL, $profileServerURL, $retailerEmail);
        $updateProfileQuery->execute();
        $updateProfileQuery->close();

        echo "Profile Updated Successfully";
    }

    public function deleteVariant($deleteVariant, $variantProdCode)
    {
        $stmt = $this->conn->prepare("UPDATE product SET ProdStatus = 'Deleted', ProdShow = 0 
WHERE ProdCode = ? AND ProdVariant = ?");
        $stmt->bind_param("is", $variantProdCode, $deleteVariant);
        $stmt->execute();
        $stmt->close();
    }

    
    public function deleteSerial($serialID){
        $stmt = $this->conn->prepare("DELETE FROM serial  WHERE serialID=?");
        $stmt->bind_param("i",$serialID);
        $stmt->execute();
        $stmt->close();
    }

    public function selectSerial($prodcode){
        $fetchSerial=$this->conn->prepare("select serialID,serialNumber from serial where prodcode=? and  status='Available' ");
        $fetchSerial->bind_param("s",$prodcode);
        $fetchSerial->execute();
        $fetchSerial->bind_result($serialID,$serialNumber);
        $serialArray=array();
        while($fetchSerial->fetch()) {

            $temp=array();
            $temp['serial']=$serialNumber;
            $temp['serialID']=$serialID;


//kj
            array_push($serialArray,$temp);
        }
        $fetchSerial->close();
        $json = json_encode($serialArray);
        echo $json;
    }

    public function insertSerial($prodcode,$serial){
        $status='Available';
        $stmt = $this->conn->prepare("INSERT INTO serial(prodCode,status,serialNumber) VALUES(?,?,?)");
        $stmt->bind_param("sss",$prodcode,$status,$serial);
        $stmt->execute();
        $stmt->close();

        echo 'Successfully insert serial code';
    }

    public function assignSerial($serialid,$orderid,$prodvariant){
        $stmt = $this->conn->prepare("UPDATE serial SET status = 'Assigned', orderid = ? ,prodVariant=?
WHERE serialid=?");
        $stmt->bind_param("sss",$orderid,$prodvariant,$serialid);
        $stmt->execute();
        $stmt->close();
    }

    public function selectAssignedSerial($prodcode,$prodvariant,$orderid){
        $fetchSerial=$this->conn->prepare("SELECT serialID,serialnumber from `serial` where prodcode=? and prodvariant=? and orderid=?");
        $fetchSerial->bind_param("sss",$prodcode,$prodvariant,$orderid);
        $fetchSerial->execute();
        $fetchSerial->bind_result($serialID,$serialNumber);
        $serialArray=array();
        while($fetchSerial->fetch()) {

            $temp=array();
            $temp['serial']=$serialNumber;
            $temp['serialID']=$serialID;


//kj
            array_push($serialArray,$temp);
        }
        $fetchSerial->close();
        $json = json_encode($serialArray);
        echo $json;
    }

    public function removeSerial($serialid){
        $stmt = $this->conn->prepare("UPDATE serial SET status = 'Available', orderid = null ,prodVariant=null
WHERE serialid=?");
        $stmt->bind_param("s",$serialid);
        $stmt->execute();
        $stmt->close();
    }

    public function retailerAddCart($cartArray){
        $rid = $cartArray['rid'];
        $prodcode = $cartArray['prodcode'];
        $price = $cartArray['price'];
        $period = $cartArray['period'];


        //Add customer cart to database
        $stmt = $this->conn->prepare("INSERT INTO retailerCart (rid,prodcode,price,period) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE price=?, period=?");
        $stmt->bind_param("ssssss",$rid,$prodcode,$price,$period,$price,$period);
        $stmt->execute();
        $stmt->close();
    }

    public function retailerFetchCart($rid){
        $fetchCartQuery=$this->conn->prepare("select r.retailercartid,r.prodcode,r.price,r.period,i.url,p.name from (select prodcode as 'code',prodname as 'name'
 from product group by prodcode) p,retailerCart r,(select imageurl as 'url', prodcode as 'code' from prodimage group by prodcode) i where r.rid=? 
 and r.prodcode=i.code and p.code=r.prodcode order by r.retailercartid desc");
        $fetchCartQuery->bind_param("s",$rid);
        $fetchCartQuery->execute();
        $fetchCartQuery->bind_result($cartID,$prodcode,$price,$period,$url,$name);

        $orderProductArray=array();
        while($fetchCartQuery->fetch()) {

            $temp=array();
            $temp['cartid']=$cartID;
            $temp['prodcode']=$prodcode;
            $temp['price']=$price;
            $temp['period']=$period;
            $temp['url']=$url;
            $temp['prodname']=$name;
            array_push($orderProductArray,$temp);

        }
        $fetchCartQuery->close();
        $json = json_encode($orderProductArray);
        echo $json;
    }

    public function removeCart($cartID){
        $stmt = $this->conn->prepare("delete from retailerCart where retailercartid=?");
        $stmt->bind_param("s",$cartID);
        $stmt->execute();
        $stmt->close();
    }

    public function createOrder($orderDetailObj){
        $arrayRID=array();

        $JsonObj = json_decode($orderDetailObj,true);

        //echo $JsonObj;
        $addressid=$JsonObj[0]['addressid'];
        $delivery=$JsonObj[0]['delivery'];
        $orderDate=date("YmdHis");
        $paymentMethod=$JsonObj[0]['paymentmethod'];
        $total=0;
        $transactionchrg=0;
        $trackingstatus="preparing";
        $rid=$JsonObj[0]['rid'];

        $querycancelfailorder = $this->conn->prepare("update retailerOrder set trackingstatus='cancelled' where trackingstatus='preparing' and rid=?");
        $querycancelfailorder->bind_param("s",$rid);
        $querycancelfailorder->execute();
        $querycancelfailorder->close();


        $querycreateorder = $this->conn->prepare("INSERT INTO retailerOrder (orderDate,rid,TrackingStatus) VALUES (?,?,?)");
        $querycreateorder->bind_param("sss",$orderDate,$rid,$trackingstatus);
        $querycreateorder->execute();
        $querycreateorder->close();

        $selectorder = $this->conn->prepare("SELECT OrderID FROM retailerOrder  WHERE TrackingStatus='preparing' AND totalAmount=0  AND rid=?");
        $selectorder->bind_param("s",$rid);
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



        //echo $orderDate.$orderID;
    }

    public function cancelOrder($orderid){
        $cancelOrder=$this->conn->prepare("update retailerOrder set trackingstatus='cancelled' where orderid=?");
        $cancelOrder->bind_param("s",$orderid);
        $cancelOrder->execute();
        $cancelOrder->close();

        echo "transaction cancelled";
    }

     public function placeOrder($orderList,$rid,$orderID,$ipayTransid){
        $msgTitle = "new order";
        $date = date("YmdHis");

        $msgStatus = "UNREAD";
        $msgAction = "packorder";

        $arrayRID=array();
        $paymentDate=date("YmdHis");
        $cartListArray=array();
        $cartListArray=json_decode($orderList,true);
        $total=0;



        for($a=0;$a<sizeof($cartListArray);$a++) {

            $cartID = $cartListArray[$a]['cartID'];
            $price = $cartListArray[$a]['price'];
            $period = $cartListArray[$a]['period'];
            $prodcode=$cartListArray[$a]['prodcode'];

//            INSERT INTO product(ProdID,ProdCode,ProdName,ProdVariant,ProdPrice,
//                ProdDate,ProdCategory,ProdDesc,ProdSize,ProdDiscount,discountPrice,ProdStatus,RID) VALUES(NULL,?,?,?,?,?,?,?,?,?,?,?,?)
//
            $insertOrder=$this->conn->prepare("insert into retailerOrderDetail (orderID,prodCode,price,period) values (?,?,?,?) ");
            $insertOrder->bind_param("ssss",$orderID,$prodcode,$price,$period);
            $insertOrder->execute();
            $insertOrder->close();

            $end_date = date("Y-m-d H:i:s", strtotime("+".$period."day"));
            $inserRank=$this->conn->prepare("insert into rank (prodcode,startDate,endDate,price) values (?,?,?,?) ");
            $inserRank->bind_param("ssss",$prodcode,$date,$end_date,$price);
            $inserRank->execute();
            $inserRank->close();


            $stmt = $this->conn->prepare("delete from retailerCart where retailercartid=?");
            $stmt->bind_param("s",$cartID);
            $stmt->execute();
            $stmt->close();



            $total=$total+($price*$period);
            //do string compare from "" to null, if($prodVar), diuleiloumouhai
        }


        $orderquery = $this->conn->prepare("UPDATE retailerOrder SET ipayID=?,totalAmount=?,TrackingStatus='paid' WHERE orderID=? AND  
totalAmount=0 AND TrackingStatus='preparing' AND rid=?");
        $orderquery->bind_param("ssss",$ipayTransid,$total,$orderID,$rid);
        $orderquery->execute();
        $orderquery->close();

    }

    public  function fetchDeliveryStatus($parameter){
        $JsonObj = json_decode($parameter,true);

        $reportStartDate=$JsonObj[0]['startDate'];
        $reportEndDate=$JsonObj[0]['endDate'];
        $searchKey=$JsonObj[0]['searchKeyWord'];
        $dateExist=$JsonObj[0]['date'];


        $RID=$JsonObj[0]['RID'];

        $searchKey= "%" . $searchKey . "%";

        if($dateExist=='1'){
            $fetchReportQuery=$this->conn->prepare("select o.orderid as 'orderid',p.prodname as 'prodname',p.ProdVariant as 'prodvariant',
c.orderdate  as 'orderdate', count(p.ProdVariant) as 'qty',o.ItemTracking as 'status',cust.CDisplayName as 'custname',i.url as 'picURL' from orderdetail o,product p, custorder c ,customer cust,(select prodcode as 'code',imageurl as 'url' from prodimage group by prodcode) i
where o.orderid=c.orderid and o.prodid=p.prodid and p.rid=? and 	o.ItemTracking!='packing' and c.custid=cust.custid and i.code=p.prodcode and c.orderdate>? and c.orderdate<? and (o.orderid like ? or p.prodname like ? or cust.CDisplayName like ?)
 group by p.ProdVariant,o.orderid order by o.orderid ");
            $fetchReportQuery->bind_param("ssssss",$RID,$reportStartDate,$reportEndDate,$searchKey,$searchKey,$searchKey);
        }else{
            $fetchReportQuery=$this->conn->prepare("select o.orderid as 'orderid',p.prodname as 'prodname',p.ProdVariant as 'prodvariant',
c.orderdate  as 'orderdate', count(p.ProdVariant) as 'qty',o.ItemTracking as 'status',cust.CDisplayName as 'custname',i.url as 'picURL' from orderdetail o,product p, custorder c ,customer cust,(select prodcode as 'code',imageurl as 'url' from prodimage group by prodcode) i
where o.orderid=c.orderid and o.prodid=p.prodid and p.rid=? and 	o.ItemTracking!='packing' and c.custid=cust.custid and i.code=p.prodcode and (o.orderid like ? or p.prodname like ? or cust.CDisplayName like ?)
 group by p.ProdVariant,o.orderid order by o.orderid ");
            $fetchReportQuery->bind_param("ssss",$RID,$searchKey,$searchKey,$searchKey);
        }
        $fetchReportQuery->execute();
        $fetchReportQuery->bind_result($orderid,$prodname,$prodvariant,$orderdate,$qty,$status,$custname,$picurl);
        $orderProductArray=array();
        while($fetchReportQuery->fetch()) {

            $temp=array();
            $temp['orderID']=$orderid;
            $temp['prodName']=$prodname;
            $temp['prodVar']=$prodvariant;
            $temp['orderDate']=$orderdate;
            $temp['prodQty']=$qty;
            $temp['itemStatus']=$status;
            $temp['custName']=$custname;
            $temp['picURL']=$picurl;

//kj
            array_push($orderProductArray,$temp);
        }
        $fetchReportQuery->close();
        $json = json_encode($orderProductArray);
        echo $json;


    }


     public function fetchCancelReport($parameter){
        $JsonObj = json_decode($parameter,true);

        $reportStartDate=$JsonObj[0]['salesStartDate'];
        $reportEndDate=$JsonObj[0]['salesEndDate'];
        $RID=$JsonObj[0]['RID'];

        $fetchReportQuery=$this->conn->prepare("SELECT COUNT( z.status ) AS  'qty', z.status, z.reason AS  'description'
FROM (

SELECT d.status AS  'status', d.cancelreason AS  'reason'
FROM pendingitem d, product p
WHERE ! ISNULL( d.canceltime ) 
AND d.canceltime >  ?
AND d.canceltime <  ?
AND p.prodid = d.prodid
AND p.rid =  ?
GROUP BY d.canceltime, d.custid
)z
GROUP BY z.status, z.reason
order by qty desc");
        $fetchReportQuery->bind_param("sss",$reportStartDate,$reportEndDate,$RID);
        $fetchReportQuery->execute();
        $fetchReportQuery->bind_result($qty,$status,$desc);
        $orderProductArray=array();
        while($fetchReportQuery->fetch()) {

            $temp=array();
            $temp['qty']=$qty;
            $temp['status']=$status;
            $temp['desc']=$desc;


//kj
            array_push($orderProductArray,$temp);
        }
        $fetchReportQuery->close();
        $json = json_encode($orderProductArray);
        echo $json;
    }

}
?>