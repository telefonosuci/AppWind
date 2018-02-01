<?php

    /**
	 * Require and costants
     * NOTA: if utile per percorso assoluto usato dal crontab per l'IMPORTER Esito Lead
	 */
    $server_root="/var/www/html/MailRestService/";
    if(file_exists($server_root."vendor/autoload.php")) {
        require '/var/www/html/MailRestService/vendor/autoload.php';
		require_once "/var/www/html/MailRestService/env/env_utils.php";
		require_once "/var/www/html/MailRestService/services/eloqua/eloqua_contact_utils.php";
        require_once "/var/www/html/MailRestService/services/eloqua/eloqua_php_request_utils/eloquaRequest.php";
    
    } else {
	    require 'vendor/autoload.php';
        require_once "env/env_utils.php";
        require_once "services/eloqua/eloqua_contact_utils.php";
        require_once "services/eloqua/eloqua_php_request_utils/eloquaRequest.php";
    }

    // ERROR HANDLER
    // function customError($errno, $errstr) { echo "<br/><b>Error:</b><br/> [$errno] $errstr"; }
    // set_error_handler("customError");

	/**
	 * Eloqua API test call 
	 * UNIT TEST per i metodi della classe di utilty esterna: 
	 * services/eloqua/eloqua_php_request_utils/eloquaRequest.php
	 * UNUSED 
	 * 
	 * @author francescoimperato
	 *
	 */	

    $utils = new utils();
    $api_key=$utils->getEnvValue("api_key");

    // Config: 
    $site = "WIND";
    $username = "Test.Sysdata";
    $password = "";

    $elq = new EloquaRequest($site,$username,$password,"https://secure.eloqua.com/API/Bulk/2.0");
    $fieldset = $elq->get("/contacts/fields?q='name=Email*Address'");
    $field = $fieldset->items[0]; //let's assume this is the Email Address field we need
    $shared_list_set = $elq->get("/contacts/lists?q='name=Import'");
    $shared_list = $shared_list_set->items[0]; //let's assume this is the Shared List we need
    //build definition
    $definition = array("isSyncTriggeredOnImport" => "true",
                        "name" => "Email Address Import ".date("Y-m-d H:i"),
                        "updateRule" => "always",
                        "identifierFieldName" => $field->internalName,
                        "secondsToRetainData" => "3600",
                        "fields" => array($field->internalName => $field->statement),
                        "syncActions" => array("destination" => $shared_list->statement,
                                            "action" => "add"));
                        
    //create import definition
    $import = $elq->post("/contacts/imports",$definition);
    /* The Eloqua PHP library doesn't support sending csv out-of-the-box (unless you adapt it), even though the API does accept this content type.
    Therefore we convert the CSV data to an array below. */
    $csv_to_import="/var/wind/w3b/report/merged/reportEsitoLead_uploading.csv";
    $csv = array_map("str_getcsv",file( $csv_to_import ));
    foreach ($csv as $n => $line) {
        if ($n == 0) {
            $headers = $line;
            continue;
        }
        
        foreach ($line as $key => $value) {
            $arr[$headers[$key]] = $value;
        }
        
        $data[] = $arr;
    }
    
    $import = $elq->post($import->uri."/data",$data);

?>