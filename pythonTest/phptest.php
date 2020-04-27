<?php
// This is the data you want to pass to Python
$data = array('as', 'df', 'gh');
//http://www.cashierbook.com/retailer/pythonTest/phptest.php
// Execute the python script with the JSON data
$result = shell_exec('http://www.cashierbook.com/retailer/pythonTest/myScript.py ' . escapeshellarg(json_encode($data)));

// Decode the result
$resultData = json_decode($result, true);

// This will contain: array('status' => 'Yes!')
var_dump($resultData);
echo $resultData;
echo 'hello world';
?>