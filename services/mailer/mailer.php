<?php

	/**
	 * Import the library used to handle the email sending
	 */
	$contextapp="app";
	$this_path = dirname(__FILE__); 
	$server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/";
	echo '<br/>MAILER : server_root: '.$server_root;
	require_once $server_root."env/env_utils.php";
	require_once $server_root."lib/phpmailer/class.phpmailer.php";
	require_once $server_root."services/logger/logger.php";

	/**
	 * This class handles the email sending
	 * 
	 * @author lucapompei
	 *
	 */
	class mailer {
		
		/**
		 * Properties used for email
		 *
		 * @var string @mail_from, the email address from which the email is send
		 * @var string $mail_fromName, the shown name from which the email is send
		 * @var string $mail_to, the recipient's email address
		 * @var string $mail_subject, the email subject
		 * @var array $mail_ccs, the array of email cc addresses
		 * @var string $mail_body, the email body
		 * @var string $mail_attachment, the attachment file sended via email
		 * @var PHPMailer $php_mailer, the class used to send email with attachment
		 *
		 */
		public $mail_from;
		public $mail_fromName;
		public $mail_to;
		public $mail_subject;
		public $mail_ccs;
		public $mail_body;
		public $mail_attachment;
		public $mail_attachment_name;
		public $php_mailer;
		
		/**
		 * Constructor
		 * 
		 * @var string $mail_attachment, the attachment file to send via email
		 * @var string $subject, the email subject
		 */
		public function __construct($attachment_path, $subject) {
			$this->logger = new logger();		
			$attachment_name = substr($attachment_path, strrpos($attachment_path, "/") + 1);     
			// Recupero i parametri dal file di 'properties'
			$this->utils = new utils();
			$this->mail_from=$this->utils->getEnvValue("mail_from");
			$this->mail_fromName=$this->utils->getEnvValue("mail_fromName");
			
			// mail to:
			$mail_to_str=$this->utils->getEnvValue("mail_to");
			$mail_to_arr_temp = explode(",", $mail_to_str);			
			foreach ($mail_to_arr_temp as $single_mail_to) {
				$mail_to_associative_temp = explode('/', $single_mail_to);
				$this->mail_to[$mail_to_associative_temp[0]] = $mail_to_associative_temp[1];
			}	
			
			// mail ccs:
			$mail_ccs_str=$this->utils->getEnvValue("mail_ccs");
			if(!empty($mail_ccs_str)) {
				$mail_ccs_arr_temp = explode(",", $mail_ccs_str);			
				foreach ($mail_ccs_arr_temp as $single_mail_ccs) {
					$mail_ccs_associative_temp = explode('/', $single_mail_ccs);
					$this->mail_ccs[$mail_ccs_associative_temp[0]] = $mail_ccs_associative_temp[1];
				}
			} else {
				$this->mail_ccs = array();
			}
			
			$this->mail_body='Report generato da Sysdata (Test Report).';
			
			$this->mail_subject = $subject;
			$this->mail_attachment = $attachment_path;
			$this->mail_attachment_name = $attachment_name;
			$this->php_mailer = new PHPMailer();


			foreach ($this->mail_to as $key => $value) {
				echo '<br/>CHECK mail TO: key '.$key.' value '.$value;
				$this->logger->log('<br/>CHECK mail TO: key '.$key.' value '.$value);
			}
			if(!empty($this->mail_ccs)) {
				foreach ($this->mail_ccs as $key => $value) {
					echo '<br/>CHECK mail css: key '.$key.' value '.$value;
					$this->logger->log('<br/>CHECK mail css: key '.$key.' value '.$value);
				}
			}

		}
		
		/**
		 * Send an email with attachment using PHPMailer library 
		 * 
		 * @return a boolean indicating the result of the sending operation
		 */
		public function sendMail() {
			$this->php_mailer->From      = $this->mail_from;
			$this->php_mailer->FromName  = $this->mail_fromName;
			$this->php_mailer->Subject   = $this->mail_subject;
			$this->php_mailer->Body      = $this->mail_body;
			//$this->php_mailer->AddAddress($this->mail_to);
			foreach($this->mail_to as $toAddress => $toName) {
				$this->php_mailer->AddAddress($toAddress, $toName);
			}
			if(!empty($this->mail_ccs)) {
				foreach($this->mail_ccs as $ccAddress => $ccName) {
					$this->php_mailer->AddCC($ccAddress, $ccName);
				}
			}
			$this->php_mailer->AddAttachment($this->mail_attachment, $this->mail_attachment_name);
			return $this->php_mailer->Send();
		}

		public function isHTML($isHtml = true) {
			$this->php_mailer->isHTML($isHtml);
		}
		
	}
	
?>