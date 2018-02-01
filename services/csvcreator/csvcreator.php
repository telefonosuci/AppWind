<?php
	
	/**
	 * This class handles the creation of .csv reports
	 * 
	 * @author lucapompei
	 *
	 */
	class csvcreator {
		
		/**
		 * Properties used for .csv creation
		 * 
		 * @var string $filename_template, the template used to 
		 * 		compose the report filename
		 * @var string $report_folder, the folder used to store
		 * 		all created reports
		 * @var string $backup_folder, the folder used to store
		 * 		all already sended reports
		 */
		public $filename_template;
		public $report_folder;
		public $backup_folder;
		
		/**
		 * Constructor
		 */
		public function __construct() {
			$this->filename_template = "Sysdata2WindReport-";
			$this->report_folder = "reports";
			$this->backup_folder = "backup";
		}
		
		/**
		 * Create a new .csv report
		 * 
		 * @param array $body, the content used to compose the .csv
		 * @return the report filename
		 */
		public function createCSV($body) {
			// format the filename template 
			// using the current time in milliseconds
			$filename = $this->report_folder . "/" . 
						$this->filename_template .
						date('YmdHis') . 
						substr(explode(" ", microtime())[0], 2) .
						".csv";
			$fp = fopen($filename, 'w+');
			foreach ($body as $fields) {
				fputcsv($fp, $fields, ';');
			}
			fclose($fp);
			return $filename;
		}

		public function createCSVWithPath($body,$reportPath) {
			$filename = $this->createCSVWithPathAndNameAndSuffixAndDate($body,$reportPath,null,null,true);
			return $filename;
		}

		public function createCSVWithPathAndSuffix($body,$reportPath,$suffix) {
			$filename = $this->createCSVWithPathAndNameAndSuffixAndDate($body,$reportPath,null,$suffix,true);
			return $filename;
		}

		public function createCSVWithPathAndNameAndSuffixAndDate($body,$reportPath,$csvname,$suffix,$withDate) {
			/*if(is_dir($reportPath)) {
				echo "Percorso valido: ".$reportPath;
			}*/
			if($csvname==null) {
				$csvname=$this->filename_template;
			}
			if($suffix==null) {
				$suffix="";
			}		
			$nowDateStr="";
			if($withDate!==0) {
				$nowDateStr=date('YmdHis'); // substr(explode(" ", microtime())[0], 2).
			}
			$filename = $reportPath. // "/". 
				$csvname.$suffix.
				$nowDateStr.
				".csv";
			$fp = fopen($filename, 'w+');
			foreach ($body as $fields) {
				fputcsv($fp, $fields, ';');
			}
			fclose($fp);
			return $filename;
		}
		
		/**
		 * Move the already sended report to the backup folder
		 * 
		 * @param string $filename, the name of the report to 
		 * 		store in the backup folder
		 * @return a boolean indicating whether the operation is correctly done
		 */
		public function backup($filename) {
			if (file_exists($filename)) {
				rename($filename, str_replace( 
						$this->report_folder, $this->report_folder . "/" . $this->backup_folder, $filename));
				return true;
			}
			return false;
		}
		
	}
	
?>