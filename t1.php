<?php
require_once('phpCrypto.php5'); // require the phpFreaksCrypto class


if(isset($_POST['SubmitButton'])){
$crypto = new phpFreaksCrypto();
$the_string_to_be_encrypted = $_POST["name"];
$the_string_that_is_encrypted = $crypto->encrypt($the_string_to_be_encrypted);
$the_encrypted_string_decrypted = $crypto->decrypt($the_string_to_be_encrypted);

$Message = 
    'Encrypted: ' . $the_string_that_is_encrypted. "<br>".
'Decrypted: ' . $the_encrypted_string_decrypted. "<br>"  ;
}

?>


 <!DOCTYPE HTML>
<html>
<body>

<form action="" method="post">

    <h1>Mycrpt: Encryption and Decryption</h1>

    <div>
        Input: <input type="text" name="name"> <br><br>
        <input type="submit" name="SubmitButton"/><br><br><br><br>
    </div>

    <div>
        <?php echo $Message; ?>
    </div>

</form>
</body>
</html>