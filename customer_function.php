<?php
/**
 * Created by PhpStorm.
 * User: proflim
 * Date: 3/9/2018
 * Time: 11:01 AM
 */

require_once 'customer_search_info.php';
$db = new customer_search_info();

switch(true){
    case isset($_POST['searchKey'])&&isset($_POST['loadIndex'])&&isset($_POST['uid']):
        $searchKey = $_POST['searchKey'];
        $index=$_POST['loadIndex'];
        $custid=$_POST['uid'];
        $db->customer_search($searchKey,$index,$custid);
        break;
     case isset($_POST['searchingKey'])&&isset($_POST['loadingIndex'])&&isset($_POST['userid'])&&isset($_POST['retailerid']):
        $searchKey = $_POST['searchingKey'];
        $index=$_POST['loadingIndex'];
        $custid=$_POST['userid'];
        $rid=$_POST['retailerid'];
        $db->customer_search_shop($searchKey,$index,$custid,$rid);
        break;
    case isset($_POST['viewProdKey']):
        $viewProdKey=$_POST['viewProdKey'];
        $db->customer_view_item($viewProdKey);
        break;
    case isset($_POST['addCart']):
        $addCart = $_POST["addCart"];
        $objArray = json_decode($addCart,true);
        $cartArray = $objArray['cart'];
        $db->addCart($cartArray);
        break;

    case isset($_POST['custID']):
        $custID=$_POST['custID'];
        $db->fetchCart($custID);


        break;

    case isset($_POST['cartID']):
        $cartID=$_POST['cartID'];
        $db->deleteCartItem($cartID);

        break;

    case isset($_POST['cartList']) &&isset($_POST['orderid'])&&isset($_POST['custid'])&&isset($_POST['ipay_transid']):
        $cartListArray=$_POST['cartList'];
        $orderid=$_POST['orderid'];
        $custid=$_POST['custid'];
        $ipay_transid=$_POST['ipay_transid'];
       // $db->createOrder($orderobj);

        $db->placeOrder($cartListArray,$custid,$orderid,$ipay_transid);

        break;

    case isset($_POST['newAddress']):
        
        $addressObj=$_POST['newAddress'];
        echo $addressObj;       
        $db->addAddress($addressObj);
        break;

    case isset($_POST['FetchOrder']):
        $custID=$_POST['FetchOrder'];
        $db->fetchOrder($custID);
        break;

    case isset ($_POST['fetchorderdetail']):
        $orderid=$_POST['fetchorderdetail'];
        $db->fetchOrderDetail($orderid);
        break;

    case isset($_POST['placeorderitem']):
        $orderlist=$_POST['placeorderitem'];
        $db->pendingRequest($orderlist);
        break;

    case isset($_POST['chkoutCustID']):
        $custid=$_POST['chkoutCustID'];
        $db->fetchPendingOrder($custid);
        break;
     case isset($_POST['delChkOutItem'])&&isset($_POST['custid'])&&isset($_POST['reason'])&&isset($_POST['canceltype'])&&isset($_POST['cancelProdName'])&&isset($_POST['cancelProdVariant']):
        $expdate=$_POST['delChkOutItem'];
        $custID=$_POST['custid'];
        $reason=$_POST['reason'];
        $canceltype=$_POST['canceltype'];
        $prodname=$_POST['cancelProdName'];
        $prodvariant=$_POST['cancelProdVariant'];
        $db->deletePendingOrder($expdate,$custID,$reason,$canceltype,$prodname,$prodvariant);
        break;
 case isset($_POST['feedback']):
        $feedback = $_POST["feedback"];
        $objArray = json_decode($feedback,true);
        $name = $objArray['Name'];
	$role = $objArray['Role'];
        $category = $objArray['Category'];
        $email = $objArray['Email'];
        $uid = $objArray['Uid'];
        $desc = $objArray['Desc'];
        $db->feedback($uid,$role,$name,$category,$email,$desc);
        break;

case isset($_POST['custaddress']):
        $custid=$_POST['custaddress'];
        $db->fetchCustAddress($custid);
        break;
case isset($_POST['getNotificationCounter']):
        $custid=$_POST['getNotificationCounter'];
        $db->fetchNotificationCount($custid);
        break;

case isset($_POST['createOrder']):
        $orderdetail=$_POST['createOrder'];
        $db->createOrder($orderdetail);
        break;


    case isset($_POST['cancelOrder'])&&isset($_POST['cancelUID']):
        $orderid=$_POST['cancelOrder'];
        $uid=$_POST['cancelUID'];
        $db->cancelOrder($orderid,$uid);
        break;

    case isset($_POST['fetchRefund']):
        $custid=$_POST['fetchRefund'];
        $db->fetchRefund($custid);
        break;

    case isset($_POST['getPickList']):
        $db->fetchPickList();
        break;

    case isset($_POST['pickItem']):
        $parameter=$_POST['pickItem'];
        $db->pickItem($parameter);
        break;

    case isset($_POST['dropItem']):
        $parameter=$_POST['dropItem'];
        $db->dropItem($parameter);
        break;

    case isset($_POST['FetchFeedback']):
        $parameter=$_POST['FetchFeedback'];
        $db->fetchFeedback($parameter);
        break;

     case isset($_POST['feedbackComment']):
        $parameter=$_POST['feedbackComment'];
        $db->feedbackComment($parameter);
        break;
    case isset($_POST['getCoverPic']):
        $rid=$_POST['getCoverPic'];
        $db->fetchRetailerCover($rid);
        break;

    case isset($_POST['getQR']):
        $uid=$_POST['getQR'];
        $db->getQR($uid);
        break;
    case isset($_POST['getPurchaseHistory']):
        $uid=$_POST['getPurchaseHistory'];
        $db->fetchPurchasedHistory($uid);
        break;
    case isset($_POST['getDeliveryList']):
        $runnerID=$_POST['getDeliveryList'];
        $db->fetchDeliveryList($runnerID);
        break;

    case isset($_POST['getReview']):
        $prodcode=$_POST['getReview'];
        $db->fetchReview($prodcode);
        break;
    default:
        break;

}
?>