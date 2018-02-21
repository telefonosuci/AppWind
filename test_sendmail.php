<?php

echo "ciao";

error_reporting(E_ALL);
ini_set('display_errors', 1);

require "lib/phpmailer/class.phpmailer.php";


/*
$to = "telefonosuci@gmail.com";
$subject = "My subject";
$txt = "Hello world! from redhat";
$headers = "From: webmaster@example.com" . "\r\n" .
"CC: enrico.succhielli@thinkopen.it";

mail($to,$subject,$txt,$headers);
*/




$bodytext = "Test mail";
$email = new PHPMailer();
$email->From      = 'myid@something.com';
$email->FromName  = 'my name';
$email->Subject   = 'Message Subject';
$email->Body      = $bodytext;
$email->AddAddress( 'telefonosuci@gmail.com' );

//$file_to_attach = SYSTEM_PATH.'pdffiles/test.pdf';
//$email->AddAttachment( $file_to_attach , 'test.pdf' );

$result = $email->Send();

echo "result = " . $result;

echo "ok ciao from redhat";

?>