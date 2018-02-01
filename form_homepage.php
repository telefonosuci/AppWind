<?php 

	/**
	 * This file handles the Eloqua "FormHomepage"
	 * 
	 * @author lucapompei
	 * 
	 */
	
	/**
	 * Variables obtained from Eloqua form
	 * 
	 * @var string $eloqua_nome
	 * @var string $eloqua_cognome
	 * @var string $eloqua_telefono
	 * @var string $eloqua_email
	 * @var string $eloqua_provincia
	 * @var string $eloqua_note
	 */
	$eloqua_firstname = $_POST['fos_user_registration_form[firstName]'];
	$eloqua_lastname = $_POST['fos_user_registration_form[lastName]'];
	$eloqua_emailaddress = $_POST['fos_user_registration_form[email]'];
	// single checkbox button
	$eloqua_comunicazionicommerciali = empty($_POST['fos_user_registration_form[commercial]']) ? "No" : "Si";
	// single checkbox button
	$eloqua_privacy = empty($_POST['fos_user_registration_form[privacy]']) ? "No" : "Si";
	$eloqua_digitalcorner = $_POST['digitalCorner'];
	
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
			array('Firstname', 'LastName', 'EmailAddress', 'ComunicazioniCommerciali', 'Privacy', 'DigitalCorner'),
			array($eloqua_firstname, $eloqua_lastname, $eloqua_emailaddress, $eloqua_comunicazionicommerciali, $eloqua_privacy, $eloqua_digitalcorner)
	);
	
	/**
	 * Reproduce the validation and execution phase of MailRestService
	 */
	include_once "compose_and_send.php";

?>