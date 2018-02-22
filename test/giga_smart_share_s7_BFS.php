<?php 

	/**
	 * This file handles the Eloqua "FormProfilo"
	 * 
	 * @author marcobuzzoni
	 * 
	 */

    require_once "services/logger/logger.php";
	
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
    $eloqua_businessphone = $_POST['busPhone'];
	$eloqua_emailaddress = $_POST['emailAddress'];
	$eloqua_provincia = $_POST['provincia1'];
	$eloqua_note = $_POST['Note'];

    $logger = new logger();
    $logger->log("elqSiteIdBFS is " . $_POST['elqSiteIdBFS']);

    /** la checkbox ritorna false o true, per quello isset non fungeva. provare la funzione empty */

    /**
    * if preferred contact is checked add a string to notes to track that check
    */
    if(strcmp($_POST['ContattoEmail'], "on") === 0)  {
        $eloqua_note = "Contatto preferenziale via email. " . $eloqua_note;
    }
	
	/**
	 * Variables used to compose .csv report
	 * 
	 * @var string $subject, the email subject
	 * @var array $csv_body, the content used to compose .csv report,
	 * 		the first array represents the header,
	 * 		the second array represents the content body
	 */
	$subject = "Campagna_Eloqua";
	$csv_body = array(
			array('Nome', 'Cognome', 'Telefono', 'Email', 'Provincia', 'note'),
			array($eloqua_firstname, $eloqua_lastname, $eloqua_businessphone, $eloqua_emailaddress, $eloqua_provincia, $eloqua_note)
	);
	
	/**
	 * Reproduce the validation and execution phase of MailRestService
	 */
	include_once "compose_and_send.php";

?>