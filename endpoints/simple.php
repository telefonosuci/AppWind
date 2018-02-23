<?php

$text = $_POST['text'];

// send email
$result = mail("telefonosuci@gmail.com", "Mail from PHP", $text);

echo "Result: ". $result;

?>