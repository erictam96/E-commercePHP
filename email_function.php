<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Created by PhpStorm.
 * User: erict
 * Date: 5/17/2018
 * Time: 3:59 PM
 */

date_default_timezone_set('Asia/Kuala_Lumpur');
class email_function
{
    const from =  'erictam96@hotmail.com';
    const reply = 'erictam96@hotmail.com';
    const name = 'E-commerce FYP';

    // constructor
    function __construct()
    {

    }

    // destructor
    function __destruct()
    {

    }


    public function sendReceiptMail($to,$orderID,$total,$transactionchrg,$itemList){

        $subject = "Order Confirmation";
        $message = file_get_contents(__DIR__ ."/email_template/Receipt.html");

        $message = str_replace('{{orderid}}', $orderID, $message);
        $message = str_replace('{{total}}', $total, $message);
        $message = str_replace('{{shipping}}', $transactionchrg, $message);

        $itemNameString="";
        $itemPriceString="";
        foreach($itemList as $key =>$value) {
            $itemName = $value['itemName'];
            $itemNameString .=$itemName.'<br>';
            $itemPrice = $value['itemPrice'];
            $itemPriceString .='RM'.$itemPrice.'<br>';
        }
        $message = str_replace('{{item}}', $itemNameString, $message);
        $message = str_replace('{{price}}', $itemPriceString, $message);

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        $headers .=
            'From :'. email_function::name ." <" . email_function::from . ">"."\r\n".
            'Reply-To :'. email_function::reply."\r\n".
            'X-Mailer: PHP/' . phpversion();

        // Sending email
        if(mail($to, $subject, $message, $headers)){
            echo 'Your mail has been sent successfully.';
        } else{
            echo 'Unable to send email. Please try again.';
        }
    }

    public function sendCheckStockMail($to,$pendinglist){
        $subject ="Check Stock Availability";
        $message=file_get_contents(__DIR__ ."/email_template/CheckStock.html");

        //$to='arixlee96@gmail.com , erictam96@hotmail.com';

        $itemNameString="";
        $itemVariantString="";
        foreach($pendinglist as $key =>$value) {
            $itemName = $value['prodName'];
            $itemNameString .=$itemName.'<br>';
            $itemVariant = $value['prodVariant'];
            $itemVariantString .=$itemVariant.'<br>';
        }
        $message = str_replace('{{item}}', $itemNameString, $message);
        $message = str_replace('{{variant}}', $itemVariantString, $message);

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        $headers .=
            'From :'. email_function::name ." <" . email_function::from . ">"."\r\n".
            'Reply-To :'. email_function::reply."\r\n".
            'X-Mailer: PHP/' . phpversion();

        // Sending email
        if(mail($to, $subject, $message, $headers)){
            echo 'Your mail has been sent successfully.';
        } else{
            echo 'Unable to send email. Please try again.';
        }
    }

    public function sendDeliveredMail($to){
        $subject ="Item arrived";
        $message=file_get_contents(__DIR__ ."/email_template/ItemArrive.html");

       // $to='arixlee96@gmail.com';

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        $headers .=
            'From :'. email_function::name ." <" . email_function::from . ">"."\r\n".
            'Reply-To :'. email_function::reply."\r\n".
            'X-Mailer: PHP/' . phpversion();

        // Sending email
        if(mail($to, $subject, $message, $headers)){
            echo 'Your mail has been sent successfully.';
        } else{
            echo 'Unable to send email. Please try again.';
        }
    }

    public function sendCancelCustomer($to){
        $subject ="Order has been cancelled";
        $message=file_get_contents(__DIR__ ."/email_template/cancelCustomer.html");

        //$to='arixlee96@gmail.com , erictam96@hotmail.com';

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        $headers .=
            'From :'. email_function::name ." <" . email_function::from . ">"."\r\n".
            'Reply-To :'. email_function::reply."\r\n".
            'X-Mailer: PHP/' . phpversion();

        // Sending email
        if(mail($to, $subject, $message, $headers)){
            echo 'Your mail has been sent successfully.';
        } else{
            echo 'Unable to send email. Please try again.';
        }
    }

    public function sendCancelMerchant($to){
        $subject ="Stock confirmation has been cancelled";
        $message=file_get_contents(__DIR__ ."/public_html/email_template/cancelMerchant.html");

        //$to='arixlee96@gmail.com , erictam96@hotmail.com';

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        $headers .=
            'From :'. email_function::name ." <" . email_function::from . ">"."\r\n".
            'Reply-To :'. email_function::reply."\r\n".
            'X-Mailer: PHP/' . phpversion();

        // Sending email
        if(mail($to, $subject, $message, $headers)){
            echo 'Your mail has been sent successfully.';
        } else{
            echo 'Unable to send email. Please try again.';
        }
    }
}