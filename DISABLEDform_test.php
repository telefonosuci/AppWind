<?php 

	/**
	 * This file handles a debug execution with a test form
	 * 
	 * @author lucapompei
	 * 
	 */

	// debug step
	$isDebug = true;
	echo 'Starting service...<br/>';
	
	/**
	 * Variables used for test purpose,
	 * obtained from a Eloqua test form
	 * 
	 * @var string $eloqua_nome
	 * @var string $eloqua_checkbox
	 */
	$eloqua_nome = $_POST['firstName'];
	// single checkbox button
	$eloqua_checkbox = empty($_POST['singleCheckbox']) ? "No" : "Si";
	
	// debug step
	echo 'Initializing csv_body...<br/>';
	
	/**
	 * Variables used to compose .csv report
	 * 
	 * @var string $subject, the email subject
	 * @var array $csv_body, the content used to compose .csv report,
	 * 		the first array represents the header,
	 * 		the second array represents the content body
	 */
	$subject = "Campagna Eloqua";
	$csv_body = array(
			array('Nome', 'Checkbox'),
			array($eloqua_nome, $eloqua_checkbox)
	);
	
	// debug step
	echo 'Including componse_and_send page...<br/>';
	
	/**
	 * Reproduce the validation and execution phase of MailRestService
	 */
	include_once "compose_and_send.php";

?>