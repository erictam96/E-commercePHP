<?PHP
$merchantcode = $_REQUEST["MerchantCode"];
$paymentid = $_REQUEST["PaymentId"];
$refno = $_REQUEST["RefNo"];
$amount = $_REQUEST["Amount"];
$ecurrency = $_REQUEST["Currency"];
$remark = $_REQUEST["Remark"];
$transid = $_REQUEST["TransId"];
$authcode = $_REQUEST["AuthCode"];
$status = $_REQUEST["Status"];
$errdesc = $_REQUEST["ErrDesc"];
$signature = $_REQUEST["Signature"];
PHP?>
<Add your programming code here>
IF ($status=1) {
COMPARE Return Signature with Generated Response Signature
// update order to PAID
echo "RECEIVEOK";
}
ELSE {
// update order to FAIL
}