<?php
	
	/**
	 * This class handles the writing of logs
	 * 
	 * @author lucapompei
	 *
	 */
	class logger {
		
		/**
		 * Properties used for log files
		 * 
		 * @var string $filename_template, the template used to 
		 * 		compose the log filename
		 * @var string $log_folder, the folder used to store
		 * 		all created logs
		 */
		public $filename_template;
		public $log_folder;
		public $batch_log_folder;
		
		/**
		 * Constructor
		 */
		public function __construct() {
			$contextapp="app";
			$this_path = dirname(__FILE__); 
			$server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/";
			$this->filename_template = "MailRestService-log-";
			$this->log_folder = $server_root."logs";
			//echo '<br/>LOG FOLDER is: '.$this->log_folder;
    		$this->batch_log_folder = $server_root."logs"; // prova separazione logging per batch
			
            if( ! ini_get('date.timezone') ) {
                date_default_timezone_set('Europe/Rome');
            }
		}
		
		/**
		 * Write a log
		 * 
		 * @param string $text, the text content used to write the log
		 */
		public function log($text) {
			// format the filename template using the current day
			$filename = $this->log_folder . "/" . 
						$this->filename_template . 
						date('Ymd') .
						".txt";
			$fp = fopen($filename, 'a+');
			$date = new DateTime();
			$text=$date->format('Y-m-d H:i:s').' --- '.$text;
			fputs($fp, $text . "\n");
			fclose($fp);
		}

		public function batch_log($text) {
			$filename = $this->batch_log_folder . "/" . 
						$this->filename_template . 
						date('Ymd') .
						".txt";
			$fp = fopen($filename, 'a+');
			$date = new DateTime();
			$text=$date->format('Y-m-d H:i:s').' --- '.$text;
			fputs($fp, $text . "\n");
			fclose($fp);
		}
		
	}
	
?>