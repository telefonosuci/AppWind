<?php 

	/**
	 * This file represents the execution phase of MailRestService.
	 * It imports all required utils, validates the page preventing
	 * fail or not authorized executions, composes a .csv report 
	 * and finally send it as attachment using a customized email
	 * 
	 * @author francescoimperato
	 * 
	 */
	 
	 $contextapp="MailRestService";
     $this_path = dirname(__FILE__); 
     $server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/"; 
	require_once $server_root."env/env_utils.php";
	$utils = new utils();
	$mailerPath=$utils->getEnvValue("mailer_php_path");

	/**
	 * Import the required service used to create customized .csv reports
	 */
	require_once $server_root."services/csvcreator/csvcreator.php";
	/**
	 * Import the required service used to send customized emails
	 */
	require_once $server_root.$mailerPath;

	require_once $server_root."services/logger/logger.php";
	
	/**
	 * Validation controls used to prevent a fail or not authorized executions
	 * 
	 * Check if the request is coming from Eloqua and if the
	 * correct values are setted, otherwise block the execution
	 */
	if ((empty($_POST['elqSiteId']) && empty($_POST['elqSiteIdBFS'])) || is_null($subject) || is_null($csv_body)) {
		$logger = new logger();
        /*$eloquaSiteId = "";
        if (isset($_POST['elqSiteID']))  {
            if(empty($_POST['elqSiteID']))   {
                $logger->log("elqSiteID parameter set, maybe is a blind form submit");
                $eloquaSiteId = $_POST['elqSiteID'];
                $logger->log("elqSiteID is " . $eloquaSiteId);
            }
        }
        if (isset($_POST['elqSiteId']))  {
            if(empty($_POST['elqSiteId']))   {
                $logger->log("elqSiteId parameter set, maybe is a blind form submit");
                $eloquaSiteId = $_POST['elqSiteId'];
                $logger->log("elqSiteId is " . $eloquaSiteId);
            }
        }*/
		$logger->log("Error: the service is not called from Eloqua or subject or csv body are empty!
			The IP is: " . $_SERVER['REMOTE_ADDR'] . ".
			ElqSiteId is " . $_POST['elqSiteId'] . ",
            elqSiteIdBFS hidden field is " . $_POST['elqSiteIdBFS'] . ",
			Subject mail is " . $subject . ",
			CsvBody presence is " . (is_null($csv_body) ? "false" : "true"));
		echo 'Wrong or not authorized operation!';
		return;
	}
	
	// debug step
	if ($isDebug) {
		echo 'Initializing csv creator...<br/>';
	}
	
	/**
	 * Snipped used to create a .csv report
	 *
	 * @var csvcreator $csvCreator, the csvcreator service used to 
	 * 		create customized .csv report
	 */
	$csvCreator = new csvcreator();
	$csv_filename = $csvCreator->createCSV($csv_body);
	
	// debug step
	if ($isDebug) {
		echo 'Report name is: ' . $csv_filename . '<br/>';
		echo 'Initializing mailer...<br/>';
	}
	
	/**
	 * Snipped used to send customized email and backup the sended report
	 * 
	 * @var mailer $email, the mailer service used to send customized 
	 * 		email with customized report as attachment
	 */
	$mailer = new mailer($csv_filename, $subject);
	$sending_result = $mailer->sendMail();
	if ($sending_result) {
		// backup only if the report is correctly sended
		$csvCreator->backup($csv_filename);
	}
	
	if ($isDebug) {
		echo 'Email sending result: ' . $sending_result . '<br/>';
	}

?>