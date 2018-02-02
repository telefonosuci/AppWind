<?php

	/**
	 * Require and costants
     * NOTA: if utile per percorso assoluto usato dal crontab per l'IMPORTER Esito Lead
	 */
	 $contextapp="AppWind";
	 $this_path = dirname(__FILE__); 
	 $server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/";
	require_once $server_root."services/logger/logger.php";
	
	/**
	 * This class handles utils functions
	 * 	 
	 */
	class utils {

		public $logger;

		/**
		 * Constructor
		 * 
		 */
		public function __construct() {
			
			$this->logger = new logger();

		}
		
		/**
		 * Get environment properties value by row-key 
		 * 
		 * @return a string with value
		 */
		 function getEnvValue($key)
		 {
			 $this_path = dirname(__FILE__); 
			 /*if(file_exists($this_path."/"."environment.ini")) 
			 { echo '<br/>environment properties exists '.$this_path."/"."environment.ini"; } 
			 else {	echo '<br/>not exists: '.$this_path."/"."environment.ini"; }
			 $handle = fopen($this_path."/"."environment.ini", "r");
			 if ($handle) {
				 while (($line = fgets($handle)) !== false) {
					echo '<br/>line: '.$line;
				 }
				 fclose($handle);
			 } else { 
				 // error opening the file.
			 }*/
			 $ini_array = parse_ini_file($this_path."/"."environment.ini");
			 $value =  $ini_array[$key];

			 /*$msg="DEBUG ENV: " . //" ini_array is " . $ini_array.
			 " key is " . $key.
			 " , value is " . $value;
			 echo '<br/>get env msg: '.$msg;
			 $this->logger->log($msg);	*/		
			 return $value;
		}
		// getEnvValue("mailer_php_path");
		
	}
	
?>