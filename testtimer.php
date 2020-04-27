<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

$my_date_time = date("Y-m-d H:i:s", strtotime("+1 days"));
$current=date("Y-m-d H:i:s");
echo $current,"\n";
echo $my_date_time;
?>
