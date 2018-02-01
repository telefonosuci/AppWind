<?php 

	


	/**
	 * This file handles the Eloqua "FormProfilo"
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
	$eloqua_firstname = $_POST['firstName'];
	$eloqua_lastname = $_POST['lastName'];
	$eloqua_emailaddress = $_POST['email'];
	$eloqua_businessphone = $_POST['phone'];
	$eloqua_company = $_POST['companyName'];
	$eloqua_companysize = $_POST['companySize'];
	
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
			array('FirstName', 'LastName', 'Email Address', 'Business Phone', 'Company', 'Company Size'),
			array($eloqua_firstname, $eloqua_lastname, $eloqua_emailaddress, $eloqua_businessphone, $eloqua_company, $eloqua_companysize)
	);
	
	/**
	 * Reproduce the validation and execution phase of MailRestService
	 */
	include_once "compose_and_send.php";

?>