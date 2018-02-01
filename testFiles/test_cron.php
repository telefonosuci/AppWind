<?php   
# test del seguente cron scheduler tramite "crontab -e": 
# */1 * * * * php /var/www/html/MailRestService/testFiles/test_cron.php

require_once __DIR__."/../env/env_utils.php";
$utils = new utils();
$environment=$utils->getEnvValue("environment");

# registrazione della data corrente in una variabile   
$oggi = date('Y-m-d'); 


echo "fine test cron: "."Eseguito il ".date('Y-m-d H:i:s');

#chiusura del file      
fclose($log);

?>