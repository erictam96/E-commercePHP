<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$to      = 'web-xrvyk@mail-tester.com';
$subject = 'the subject';
$message = 'hello';
$headers = 'From: no-reply@cashierbook.com' . "\r\n" .
    'Reply-To: system@cashierbook.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
?>