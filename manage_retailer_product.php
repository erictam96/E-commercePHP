<?php
require_once 'retailer_product.php';
$db = new retailer_product();

switch(true){
    case isset($_POST['retailerProd'])&&isset($_POST['index'])&&isset($_POST['searchKey']):
        //$retailerID = "000009";
        $retailerProd = $_POST["retailerProd"];
        $index=$_POST['index'];
        $searchKey=$_POST['searchKey'];
        $db->fetchRetailerProduct($retailerProd,$index,$searchKey);
        break;
    case isset($_POST['prodCode']):
        //$prodCode = "1";
        $prodCode = $_POST["prodCode"];
        $db->fetchProductDetails($prodCode);
        break;
    case isset($_POST['updateProd']):
        $updateProd = $_POST["updateProd"];
        $JsonArray = json_decode($updateProd,true);
        $detailsArray = $JsonArray['details'];
	$VariantArray = $JsonArray['variant'];
        $ImageArray = $JsonArray['image'];
        $ImageIDArray = $JsonArray['imageid'];
        $RemoveIDArray = $JsonArray['removeid'];
        $db->updateProductDetails($detailsArray,$VariantArray,$ImageArray,$ImageIDArray,$RemoveIDArray);
        break;
    case isset($_POST['addProd']):
        $addProd = $_POST["addProd"];
        $objArray = json_decode($addProd,true);
        $detailsArray = $objArray['details'];
        $ImageArray = $objArray['image'];
        $VariantAll = $objArray['variant'];
        $db->addProduct($detailsArray,$ImageArray,$VariantAll);
        break;
    case isset($_POST['deleteProd']):
        $deleteProd = $_POST["deleteProd"];
        $db->deleteProduct($deleteProd);
        break;

    case isset($_POST['fetchOrder']):
        $RID=$_POST['fetchOrder'];
        $db->fetchOrderProduct($RID);
        break;

    case isset($_POST['orderid']) && isset($_POST['prodvar']) && isset($_POST['productcode']) && isset($_POST['rid']) :
        $orderid=$_POST['orderid'];
        $prodvar=$_POST['prodvar'];
        $prodcode=$_POST['productcode'];
        $retailerId=$_POST['rid'];

        $db->donePackItem($orderid,$prodvar,$prodcode,$retailerId);
        break;

    case isset($_POST['prodvariant'])&& isset($_POST['prodcode'])&& isset($_POST['retailerid']):
        $prodvar=$_POST['prodvariant'];
        $prodcode=$_POST['prodcode'];
        $rid=$_POST['retailerid'];

        $db->cancelAllSameProductOrder($prodvar,$prodcode,$rid);
        break;
    case isset($_POST['orderConfirmation']):
        $rid=$_POST['orderConfirmation'];
        $db->fetchOrderConfirmation($rid);
        break;
     case isset($_POST['confirmOrderImage']):
        $confirmOrder = $_POST["confirmOrderImage"];
        $objArray = json_decode($confirmOrder,true);
        $detailsArray = $objArray['details'];
        $ImageArray = $objArray['image'];
        $prodcode=$objArray['prodcode'];
        $prodvar=$objArray['prodvariant'];
         $db->confirmOrderImage($detailsArray,$ImageArray,$prodcode,$prodvar);
        break;

    case isset($_POST['outofstock'])&&isset($_POST['custid']):
        $orderdate=$_POST['outofstock'];
        $custid=$_POST['custid'];
        $db->orderOutOfStock($orderdate,$custid);
        break;

    case isset($_POST['getNotfCounter']):
        $RID=$_POST['getNotfCounter'];
        $db->fetchNotfCount($RID);
        break;

    case isset($_POST['overallSummary']):

        $parameter=$_POST['overallSummary'];
        $db->fetchOverallSalesReport($parameter);
        break;

    case isset($_POST['fetchTranscount']):
        $parameter=$_POST['fetchTranscount'];
        $db->fetchTransactionCount($parameter);
        break;

    case isset($_POST['fetchSoldcount']):
        $parameter=$_POST['fetchSoldcount'];
        $db->fetchItemSoldCount($parameter);
        break;

    case isset($_POST['salesDetail']):
        $parameter=$_POST['salesDetail'];
        $db->fetchSalesDetailReport($parameter);
        break;
    case isset($_POST['updateRetailerProfile']):
        $parameter=$_POST['updateRetailerProfile'];
        $db->updateProfile($parameter);
        break;

    case isset($_POST['DeleteVariant'])&&isset($_POST['VariantProdCode']):
        $deleteVariant =$_POST['DeleteVariant'];
        $variantProdCode = $_POST['VariantProdCode'];
        $db->deleteVariant($deleteVariant,$variantProdCode);
        break;

    case isset($_POST['removeSerial']):
        $serialID=$_POST['removeSerial'];
        $db->deleteSerial($serialID);
        break;

    case isset($_POST['fetchSerial']):
        $prodcode=$_POST['fetchSerial'];
        $db->selectSerial($prodcode);
        break;


    case isset($_POST['insertSerial'])&&isset($_POST['insertSerialProdcode']):
        $prodcode=$_POST['insertSerialProdcode'];
        $serial=$_POST['insertSerial'];
        $db->insertSerial($prodcode,$serial);
        break;
    case isset($_POST['assignSerial'])&&isset($_POST['assignSerialOrderID'])&&isset($_POST['assignSerialVariant']):
        $serialID=$_POST['assignSerial'];
        $orderid=$_POST['assignSerialOrderID'];
        $prodVariant=$_POST['assignSerialVariant'];
        $db->assignSerial($serialID,$orderid,$prodVariant);
        break;
    case isset($_POST['assignedProdcode'])&&isset($_POST['assignedProdVariant'])&&isset($_POST['assignedOrderID']):
        $prodcode=$_POST['assignedProdcode'];
        $prodvar=$_POST['assignedProdVariant'];
        $orderid=$_POST['assignedOrderID'];
        $db->selectAssignedSerial($prodcode,$prodvar,$orderid);
        break;

    case isset($_POST['removeAssignedSerial']):
        $serialid=$_POST['removeAssignedSerial'];
        $db->removeSerial($serialid);
        break;


    case isset($_POST['retailerCart']):
        $retailerCart=$_POST['retailerCart'];
        $objArray = json_decode($retailerCart,true);
        
        $db->retailerAddCart($objArray);
        break;

    case isset($_POST['fetchCart']):
        $rid=$_POST['fetchCart'];
        $db->retailerFetchCart($rid);
        break;

    case isset($_POST['removeCart']):
        $cartID=$_POST['removeCart'];
        $db->removeCart($cartID);
        break;

    case isset($_POST['createOrder']):
        $orderDetail=$_POST['createOrder'];
        $db->createOrder($orderDetail);
        break;

    case isset($_POST['cancelOrder']):
        $orderid=$_POST['cancelOrder'];
        $db->cancelOrder($orderid);
        break;

    case isset($_POST['cartList']) &&isset($_POST['orderid'])&&isset($_POST['rid'])&&isset($_POST['ipay_transid']):
        $cartListArray=$_POST['cartList'];
        $orderid=$_POST['orderid'];
        $rid=$_POST['rid'];
        $ipay_transid=$_POST['ipay_transid'];
        // $db->createOrder($orderobj);

        $db->placeOrder($cartListArray,$rid,$orderid,$ipay_transid);

        break;

    case isset($_POST['deliveryStatusTracking']):
        $parameter=$_POST['deliveryStatusTracking'];
        $db->fetchDeliveryStatus($parameter);
        break;

    case isset($_POST['cancelReport']):
        $parameter=$_POST['cancelReport'];
        $db->fetchCancelReport($parameter);
        break;

    default:
        break;
}
?>