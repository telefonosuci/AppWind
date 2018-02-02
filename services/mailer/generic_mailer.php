<?php

	/**
	 * Require and costants
	 * Import the library used to handle the email sending
     * NOTA: if utile per percorso assoluto usato dal crontab per l'IMPORTER Esito Lead
	 */
	 $contextapp="AppWind";
	 $this_path = dirname(__FILE__); 
	 $server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/";
		require_once $server_root."lib/phpmailer/class.phpmailer.php";


	/**
	 * This class handles the generic email sending
	 * 
	 * @author francescoimperato
	 *
	 */
	class generic_mailer {
		
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
		public $php_mailer;
		
		/**
		 * Constructor
		 * 
		 * @var string $mail_attachment, the attachment file to send via email
		 * @var string $subject, the email subject
		 */
		public function __construct() {
			$this->mail_from = 'report@sysdata.it';
			$this->mail_fromName = 'Sysdata';
			$this->mail_ccs = array();
			$this->php_mailer = new PHPMailer();
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
			foreach($this->mail_ccs as $ccAddress => $ccName) {
				$this->php_mailer->AddCC($ccAddress, $ccName);
			}
			$this->php_mailer->AddAttachment($this->mail_attachment, $this->mail_attachment);
			return $this->php_mailer->Send();
		}

		public function isHTML($isHtml = true) {
			$this->php_mailer->isHTML($isHtml);
		}
		
	}
	
?>