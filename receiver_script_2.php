<?php

/*
    Eloqua form data
    [LP] => 57
    [firstName] => nom
    [lastName] => cognome
    [emailAddress] => email
    [elqSiteId] => 2042710490
*/


    print_r($_POST);

    




/*
$name=$_POST['firstName'];
$surname=$_POST['lastName'];
$email=$_POST['emailAddress'];
echo "ciao";
error_log("Buongiornooo!");

error_log(print_r($_POST, TRUE));
*/

/* 
error_log("Chiamata allo script di ricezione avvenuta con successo!");

error_log("name = " . $name );
error_log("surname = " . $surname);
error_log("email = " . $email);

syslog( LOG_INFO, "Starting compose of csv file");

$subject = "Form Eloqua";
$csv_body = array(
	array('First Name', 'Second Name', 'Email'),
	array($name, $surname, $email)
);

syslog( LOG_INFO, "Include compose and send page used to send the csv");

include_once "compose_and_send.php"; */


?>