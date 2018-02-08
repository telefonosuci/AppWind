<?php

    /**
     * This file handles the Eloqua "FormProfilo"
     *
     * @author lucapompei
     *
     */
	 
	require_once "env/env_utils.php";
	$utils = new utils();
	$mailerPath=$utils->getEnvValue("mailer_php_path");
	$isDegug=$utils->getEnvValue("is_debug");

	if(isset($isDegug) && $isDegug) {
		// debug step
		echo 'Starting service ...<br/>';
		require_once "services/logger/logger.php";
		$logger = new logger();
		$logger->log("DEBUG PROD: " .
				" , livello_lead is " . $_POST['livelloLead'].
				" , elqCampaignId is " . $_POST['elqCampaignId'].
				" , marker is " . $_POST['marker'].
				" , consensoPrivacy is " . $_POST['consensoPrivacy'].
				" , elqSiteId is " . $_POST['elqSiteId'].
				" , mailerpath is " . $mailerPath);
	} 
	
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

	$eloqua_marker = '';
	if(isset($_POST['elqCampaignId']) && !empty($_POST['elqCampaignId'])) {
		$eloqua_marker = $_POST['elqCampaignId'];
	} else {
		$eloqua_marker = $_POST['marker'];
	}
	$eloqua_livello_lead = $_POST['livelloLead'];
	$w3b_esito = '';   		// Definiti da W3B
	$w3b_causale = '';  	// Definiti da W3B
	$w3b_data_esito = '';	// Definiti da W3B
	if(strcmp($_POST['consensoPrivacy'], "on")===0 || strcmp(strtoupper($_POST['consensoPrivacy']), "SI")===0) {
        $eloqua_consenso_privacy = "SI";
    } else {
        $eloqua_consenso_privacy = "NO";
    }
	$leadIdW3B = $_POST['leadIdW3B']; // non usato per ora
	
    /** la checkbox ritorna false o true, per quello isset non fungeva. provare la funzione empty */

    /**
    * if preferred contact is checked add a string to notes to track that check
    */
    if(strcmp($_POST['ContattoEmail'], "on")===0 || strcmp(strtoupper($_POST['ContattoEmail']), "SI")===0)  {
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
			array('Nome', 'Cognome', 'Telefono', 'Email', 'Provincia', 'Note', 'Marker', 'Livello Lead', 'Esito', 'Causale', 'Data Esito' , 'Consenso Privacy'),
			array($eloqua_firstname, $eloqua_lastname, $eloqua_businessphone, $eloqua_emailaddress, $eloqua_provincia, $eloqua_note, $eloqua_marker, $eloqua_livello_lead, $w3b_esito, $w3b_causale, $w3b_data_esito, $eloqua_consenso_privacy)
	);
	
    /* 
	GESTIONE INVIO EMAIL all'utente dopo immagazzinamento dato su eloqua, ora la gestione è in carico a Piksel, nel caso la gestione diventasse lato nostro, decommentare e testare: 
	if($_POST['marker']) {
		// MAILER task (solo se provengo da processo WindForm->BFS): 
		//$mailerPath="services/mailer/COLLAUDO_mailer.php"; // $this->utils->getEnvValue("mailer_php_path");
		require_once $mailerPath;
		$body="<div style=\"margin:0;padding:0;background-color:#fdfdfd;min-width:100%!important\">" 
			."<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" bgcolor=\"#ffffff\" align=\"center\">" 
			."<tbody>" 
			."<tr>" 
			."<td valign=\"top\" align=\"center\">" 
			."<table class=\"m_3065328796200063752table600\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">" 
			."<tbody>" 
			."<tr>" 
			."<td height=\"10\">&nbsp;</td>" 
			."</tr>" 
			."<tr>" 
			."<td height=\"91\">" 
			."<table class=\"m_3065328796200063752table130\" width=\"130\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\">" 
			."<tbody>" 
			."<tr>" 
			."<td valign=\"top\"><img src=\"https://ci4.googleusercontent.com/proxy/n1sKf1wt4umy98TM5fA8LVkLpjcpsT7okBT_7n5aGYHi39pO5ERKPla20J49MFHJX8F_Q84OZaPT3QyHZsI3F5pnS9eWxwTlFEw=s0-d-e1-ft#https://www.windtrebusiness.it/res/imgs/logo_WTB.png\" alt=\"logo_wind\" style=\"display:block;font-family:Arial,Helvetica,sans-serif;text-align:left;color:#f48135;font-size:18px;font-weight:bold\" class=\"CToWUd\" width=\"130\" height=\"81\">" 
			."</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."</td>" 
			."</tr>" 
			."<tr>" 
			."<td height=\"10\">&nbsp;</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" bgcolor=\"#F4F4F4\">" 
			."<tbody>" 
			."<tr>" 
			."<td style=\"background-color:#f4f4f4\" class=\"m_3065328796200063752background\" valign=\"top\">" 
			."<table class=\"m_3065328796200063752table600\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">" 
			."<tbody>" 
			."<tr>" 
			."<td height=\"50\">&nbsp;</td>" 
			."</tr>" 
			."<tr rowspan=\"2\" colspan=\"2\">" 
			."<td style=\"font-family:'Fira Sans',Arial,Helvetica,sans-serif;text-align:left\" colspan=\"2\">" 
			."<span style=\"color:#ff6a00;font-size:28px;font-weight:normal\">Richiesta Informazioni</span>" 
			."</td>" 
			."</tr>" 
			."<tr>" 
			."<td height=\"20\">&nbsp;</td>" 
			."</tr>" 
			."<tr>" 
			."<td style=\"font-family:'Fira Sans',sans-serif;text-align:left\" colspan=\"2\" valign=\"top\">" 
			."<span style=\"color:#08080d;font-size:18px;font-weight:300\">Gentile Cliente,</span>" 
			."</td>" 
			."</tr>" 
			."<tr>" 
			."<td height=\"10\">&nbsp;</td>" 
			."</tr>" 
			."<tr>" 
			."<td style=\"font-family:'Fira Sans',sans-serif;text-align:left\" colspan=\"2\" valign=\"top\">" 
			."<span style=\"color:#08080d;font-size:18px;font-weight:300\">la tua richiesta &egrave; stata inoltrata correttamente. Un nostro consulente ti contatter&agrave;  al pi&ugrave; presto per darti tutte le informazioni che desideri. Ti ricordiamo che puoi trovarci anche su&nbsp;<a href=\"http://www.windtrebusiness.it\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=it&amp;q=http://www.windtrebusiness.it&amp;source=gmail&amp;ust=1501832723906000&amp;usg=AFQjCNHLm9JrXHgzQJwXiarC9_0wEnZHOg\">www.windtrebusiness.it</a>" 
			."</span></td>" 
			."</tr>" 
			."<tr>" 
			."<td height=\"50\">&nbsp;</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" bgcolor=\"#ffffff\" align=\"center\">" 
			."<tbody>" 
			."<tr>" 
			."<td height=\"50\">&nbsp;</td>" 
			."</tr>" 
			."<tr>" 
			."<td valign=\"top\">" 
			."<table class=\"m_3065328796200063752table600\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">" 
			."<tbody>" 
			."<tr>" 
			."<td style=\"font-family:'Fira Sans',sans-serif;text-align:left\"><span style=\"color:#08080d;font-size:30px;font-weight:normal\">Grazie per averci contattato!" 
			."</span></td>" 
			."</tr>" 
			."<tr>" 
			."<td colspan=\"2\" style=\"font-family:'Fira Sans',sans-serif;text-align:left\"><span style=\"color:#ff6a00;font-size:21px;font-weight:normal\">Wind Tre S.p.A." 
			."</span></td>" 
			."</tr>" 
			."<tr>" 
			."<td colspan=\"2\" height=\"50\">&nbsp;</td>" 
			."</tr>" 
			."<tr>" 
			."<td style=\"font-family:'Fira Sans',sans-serif;text-align:left\">" 
			."<p style=\"font-family:'Fira Sans',sans-serif;font-size:13px;color:#08080d;font-weight:300\">" 
			."Attenzione: non rispondere a questa mail, questo &egrave; un messaggio inviato automaticamente." 
			."<br>" 
			."</p>" 
			."</td>" 
			."</tr>" 
			."<tr>" 
			."<td colspan=\"2\" height=\"50\">&nbsp;</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."<table style=\"background-color:#f4f4f4\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">" 
			."<tbody>" 
			."<tr>" 
			."<td height=\"50\">&nbsp;</td>" 
			."</tr>" 
			."<tr>" 
			."<td>" 
			."<table class=\"m_3065328796200063752table600\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">" 
			."<tbody>" 
			."<tr>" 
			."<td width=\"78\">" 
			."<table width=\"78\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">" 
			."<tbody>" 
			."<tr>" 
			."<td><img src=\"https://ci4.googleusercontent.com/proxy/ODmiqe42cOeDCcB5rBYl_SamnDU3gqtB1Hrx78WUygFW99i1_F1s9kowmmm9N_WSTB9OwDVFU7MX339YcGSP63c31ouVMm0=s0-d-e1-ft#https://www.windtrebusiness.it/res/imgs/agent.png\" alt=\"agent\" style=\"font-family:Arial,Helvetica,sans-serif;text-align:left;color:#f48135;font-size:18px;font-weight:bold\" class=\"CToWUd\" width=\"78\" height=\"74\">" 
			."</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."</td>" 
			."<td>" 
			."<table width=\"428\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"left\">" 
			."<tbody>" 
			."<tr>" 
			."<td style=\"padding-left:20px\"><span style=\"font-size:32px;color:#656d78;font-family:'Fira Sans',sans-serif\">Sei gi&agrave;  un nostro cliente ed hai bisogno di aiuto?" 
			."</span></td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."</td>" 
			."</tr>" 
			."<tr>" 
			."<td height=\"18\">&nbsp;</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."<table style=\"background-color:#f4f4f4\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">" 
			."<tbody>" 
			."<tr>" 
			."<td>" 
			."<table class=\"m_3065328796200063752table600\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">" 
			."<tbody>" 
			."<tr>" 
			."<td height=\"20\"></td>" 
			."</tr>" 
			."<tr>" 
			."<td style=\"font-size:16px;font-family:'Fira Sans',sans-serif;font-size:14px;color:#08080d;text-align:left;font-weight:300\" valign=\"top\">" 
			."•</td>" 
			."<td><a class=\"m_3065328796200063752applelinksGray\" style=\"font-size:16px;font-family:'Fira Sans',sans-serif;font-size:14px;color:#08080d;text-align:left;font-weight:300\">Consulta la sezione Assistenza all'interno del sito www.windtrebusiness.it</a>" 
			."</td>" 
			."</tr>" 
			."<tr>" 
			."<td height=\"5\"></td>" 
			."</tr>" 
			."<tr>" 
			."<td style=\"font-size:16px;font-family:'Fira Sans',sans-serif;font-size:14px;color:#08080d;text-align:left;font-weight:300\" valign=\"top\">" 
			."•</td>" 
			."<td><a class=\"m_3065328796200063752applelinksGray\" style=\"font-size:16px;font-family:'Fira Sans',sans-serif;font-size:14px;color:#08080d;text-align:left;font-weight:300\">Chiama il 1928</a>" 
			."</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."</td>" 
			."</tr>" 
			."<tr>" 
			."<td height=\"50\">&nbsp;</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."" 
			."<table style=\"background-color:#626a73\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">" 
			."<tbody>" 
			."<tr>" 
			."<td height=\"10\">&nbsp;</td>" 
			."</tr>" 
			."<tr>" 
			."<td>" 
			."<table class=\"m_3065328796200063752table600\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">" 
			."<tbody>" 
			."<tr>" 
			."<td align=\"center\">" 
			."<p style=\"margin-top:0;margin-bottom:0;font-family:'Fira Sans',sans-serif;font-size:10px;color:white\">" 
			."<span class=\"m_3065328796200063752symbolfix\">&copy;</span>Wind Tre S.p.A - P.IVA n.<span class=\"m_3065328796200063752applelinksW\"><a href=\"tel:(337)%20852-0152\" value=\"+13378520152\" target=\"_blank\">13378520152</a></span></p>" 
			."</td>" 
			."</tr>" 
			."</tbody>" 
			."</table>" 
			."</td>" 
			."</tr>" 
			."<tr>" 
			."<td height=\"10\">&nbsp;</td>" 
			."</tr>" 
			."</tbody>" 
			."</table><div class=\"yj6qo\"></div><div class=\"adL\">" 
			."</div></div>"; 

		$mailer = new mailer(null,null);
		$mail_from = 'noreply@windbusiness.it';
		$mailer->mail_fromName = 'Wind Business';
		$mailer->mail_to = $eloqua_emailaddress; // Per i test inserire email propria
		$mailer->mail_subject = 'Grazie di averci contattato';
		//$mailer->mail_ccs;
		$mailer->mail_body = $body;
		echo "body is: ".$mailer->mail_body;
		//$mailer->mail_attachment;
		$mailer->isHTML(true);
		$sending_result = $mailer->sendMail();
		echo "sending_result: ".$sending_result;
	}
	*/
    
    /**
     * Reproduce the validation and execution phase of MailRestService
     */
    include_once "compose_and_send.php";

?>