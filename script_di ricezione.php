<?php

$name=$_POST['firstName'];
$surname=$_POST['lastName'];
$email=$_POST['emailAddress'];

error_log("Buongiornooo!");


error_log("Chiamata allo script di ricezione avvenuta con successo!");

error_log("name = ". $name ."  surname = ". $surname." email = ".$email);

error_log("Fine");


?>