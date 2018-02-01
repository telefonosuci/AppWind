<?php 

	/**
	 * Require and costants
     * NOTA: if utile per percorso assoluto usato dal crontab per l'IMPORTER Esito Lead
	 */
	 $contextapp="MailRestService";
	 $this_path = dirname(__FILE__); 
	 $server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/";
	require_once $server_root."env/env_utils.php";
	require_once $server_root."services/mailer/generic_mailer.php";
    require_once $server_root."services/logger/logger.php";
	

	/**
	 * Importer class: to allow import W3B Csv to Eloqua and handles the csv flow for the Importer
	 * 
	 * @author francescoimperato
	 *
	 */
	class importer_notification {
		
		/**
		 * Properties 
		 *
		 */


		/**
		 * Constructor
		 * 
		 */
		public function __construct() {
            $this->utils = new utils();
			$this->isDegug=$this->utils->getEnvValue("is_debug");
			$this->logger = new logger();
		}
	
		// INVIO EMAIL notifica
		public function importerNotification($statusInfo,$message) {
			$msg=" INVIO EMAIL PER NOTIFICA IMPORTER con msg: ".$message;
            echo $msg;
            $this->logger->log($msg);
			if($statusInfo=="OK") {
				$messageTitle1=" I report sono stati caricati correttamente su Eloqua. ";
			} else if($statusInfo=="INFO") {
				$messageTitle1=" Info - Nessun Report da Elaborare. ";
			} else if($statusInfo=="ERROR") {
				$messageTitle1=" Attenzione, i report NON sono stati caricati: elaborazione andata in ERRORE! ";
			} 
			$body="<html>" 
				."  <head>" 
				."    <title></title>" 
				."    <meta content=\"\">" 
				."    <style>" 
				."    body {font-family:Calibri, serif;}" 
				."    </style>" 
				."  </head>" 
				."  <body style=\"font-family:Calibri, serif;\">" 
				."<div style=\"margin:0;padding:0;background-color:#fdfdfd;min-width:100%!important\">" 
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
				."<span style=\"color:#ff6a00;font-size:28px;font-weight:normal\">Eloqua Report Esito</span>" 
				."</td>" 
				."</tr>" 
				."<tr>" 
				."<td height=\"20\">&nbsp;</td>" 
				."</tr>" 
				."<tr>" 
				."<td style=\"font-family:'Fira Sans',sans-serif;text-align:left\" colspan=\"2\" valign=\"top\">" 
				."<span style=\"color:#08080d;font-size:18px;font-weight:300\">Ciao,</span>" 
				."</td>" 
				."</tr>" 
				."<tr>" 
				."<td height=\"10\">&nbsp;</td>" 
				."</tr>" 
				."<tr>" 
				."<td style=\"font-family:'Fira Sans',sans-serif;text-align:left\" colspan=\"2\" valign=\"top\">" 
				."<span style=\"color:#08080d;font-size:16px;font-weight:300\">"
				.$messageTitle1
				."<br/> Report pervenuti da <a href=\"http://www.windtrebusiness.it\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=it&amp;q=http://www.windtrebusiness.it&amp;source=gmail&amp;ust=1501832723906000&amp;usg=AFQjCNHLm9JrXHgzQJwXiarC9_0wEnZHOg\">www.windtrebusiness.it</a>" 
				."</span>" 
				."<br/><span style=\"color:#08080d;font-size:11px;\">(tramite Sysdata-Mailer Rest Service app)</span>"
				."</td>"
				."</tr>" 
				."<tr>" 
				."<td height=\"1\">&nbsp;</td>" 
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
				."<td valign=\"top\">" 
				."<table class=\"m_3065328796200063752table600\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">" 
				."<tbody>" 
				."<tr>" 
				."<td style=\"font-family:'Fira Sans',sans-serif;text-align:left\"><span style=\"color:#08080d;font-size:12px;font-weight:normal\">Info sui file caricati: " 
				." ".$message  
				."<br/><br/></span></td>" 
				."</tr>" 
				."<tr>" 
				."<td colspan=\"2\" style=\"font-family:'Fira Sans',sans-serif;text-align:left\"><span style=\"color:#ff6a00;font-size:21px;font-weight:normal\">Sysdata per Wind Tre S.p.A." 
				."</span></td>" 
				."</tr>" 
				."<tr>" 
				."<td colspan=\"2\" height=\"1\">&nbsp;</td>" 
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
				."<td colspan=\"2\" height=\"1\">&nbsp;</td>" 
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
				."</table>" 
				."</div>" 
				."</body>" 
				."</html>";
			$generic_mailer = new generic_mailer(null,null);
			$mail_from = 'sysdata_eloqua@sysdata.it';
			$generic_mailer->mail_fromName = 'Sysdata-Eloqua For W3B';
			
			// $generic_mailer->mail_to = "francesco.imperato@sysdata.it"; 
			$mail_to_str=$this->utils->getEnvValue("importer_notification_mail_to");
			$mail_to_arr_temp = explode(",", $mail_to_str);			
			foreach ($mail_to_arr_temp as $single_mail_to) {
				$mail_to_associative_temp = explode('/', $single_mail_to);
				$generic_mailer->mail_to[$mail_to_associative_temp[0]] = $mail_to_associative_temp[1];
			}	

			$generic_mailer->mail_subject = 'Caricamento Report Esito W3B';
			//$generic_mailer->mail_ccs;
			$generic_mailer->mail_body = $body;
			//echo "<br/>body is: ".$generic_mailer->mail_body;
			//$generic_mailer->mail_attachment;
			$generic_mailer->isHTML(true);
			$sending_result = $generic_mailer->sendMail();
			$msg=" Sending result: ".$sending_result;
            echo $msg;
            $this->logger->log($msg);
		}

	}

?>