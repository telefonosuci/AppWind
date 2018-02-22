<?php

    /**
	 * Require and costants
     * NOTA: if utile per percorso assoluto usato dal crontab per l'IMPORTER Esito Lead
	 */
     $contextapp="AppWind";
     $this_path = dirname(__FILE__); 
     $server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/";
    require $server_root.'vendor/autoload.php';
	require_once $server_root."/env/env_utils.php";
	require_once $server_root."/services/eloqua/eloqua_contact_utils.php";
    require_once $server_root."/services/eloqua/eloqua_php_request_utils/eloquaRequest.php";
	require_once $server_root."services/importer/importer_notifications.php";

    // ERROR HANDLER
    // function customError($errno, $errstr) { echo "<br/><b>Error:</b><br/> [$errno] $errstr"; }
    // set_error_handler("customError");

	/**
	 * Eloqua API test call (UNIT TEST per i metodi eloqua_contact_utils)
	 * 
	 * @author francescoimperato
	 *
	 */	

	use GuzzleHttp\Client;
    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Promise;
    
    $utils = new utils();
    $api_key=$utils->getEnvValue("api_key");

    // TEST 1. contact import:
    echo '<br />TEST 1. contact import';
    $eloqua_contact_utils = new eloqua_contact_utils();
    $eloqua_contact_utils->contactsImportByCsv();

    // TEST 2.
    echo '<br /><br />TEST 2. test POST';
    //$eloqua_contact_utils->testPostContactsImport();

    // TEST 3.
    echo '<br /><br />TEST 3. test GET findContactsFields';
    //$eloqua_contact_utils->findContactsFields();

    echo '<br />TEST 4. importer notification';
    $importer_notification = new importer_notification();
    $importer_notification->importerNotification("TEST-4");

    /*  echo '<br /><br />TEST POST server file';
	$client = new Client([
		// Base URI is used with relative requests
		'base_uri' => 'http://127.0.0.1/MailRestService/',
		// You can set any number of default request options.
		'timeout'  => 2.0,
	]);
    $response = $client->request('POST', 'testFiles/test_cron.php');
	echo "RES";
    $body = $response->getBody();
    echo "<br/>Body:<br /> ".$body."<br/>";
    echo "<br/>STATUS CODE: ".$response->getStatusCode()."<br/>";
    */

    
?>