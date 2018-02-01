<?php

	/**
	 * Require and costants
     * NOTA: if utile per percorso assoluto usato dal crontab per l'IMPORTER Esito Lead
	 */
     $contextapp="MailRestService";
     $this_path = dirname(__FILE__); 
     $server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/";
        require_once $server_root.'env/env_utils.php';
	    require_once $server_root."services/eloqua/eloqua_contact_utils.php";
		require_once $server_root."services/importer/importer_notifications.php";
        require_once $server_root."services/logger/logger.php";

	/**
	 * Importer class: to allow import W3B Csv to Eloqua and handles the csv flow for the Importer
	 * 
	 * @author francescoimperato
	 *
	 */
	class importer {
		
		/**
		 * Properties 
		 *
		 */
		public $header_to_upload="First Name;Last Name;Business Phone;Email Address;Provincia;Note;Marker;Lead Score;Esito;Causale;Data Esito;Consenso Privacy";
        
        public $POS_EMAIL_CSV=3;

		/**
		 * Constructor
		 * 
		 */
		public function __construct() {
			
            $this->utils = new utils();
            $this->eloqua_contact_utils = new eloqua_contact_utils();
			$this->importer_notification=new importer_notification();
            
            $this->isDegug=$this->utils->getEnvValue("is_debug");
            $this->w3bReportPath=$this->utils->getEnvValue("w3b_report_path"); // rilasciati qui da W3B
            $this->w3bReportMergedPath=$this->utils->getEnvValue("w3b_report_merged_path");
            $this->w3bReportUploadingName=$this->utils->getEnvValue("w3b_report_uploading_name");
            $this->w3bReportBkpPath=$this->utils->getEnvValue("w3b_report_bkp_path"); // backup folder after process
            $this->w3bReportOldPath=$this->utils->getEnvValue("w3b_report_old_path"); // old single-w3b-file after process
            $this->logger = new logger();
            if(isset($this->isDegug) && $this->isDegug) {
                // debug                
                echo 'Starting importer ...<br/>';
                $this->logger->log("DEBUG IMPORTER: " .
                        " , w3bReportPath is " . $this->w3bReportPath);
            } 

            if( ! ini_get('date.timezone') ) {
                date_default_timezone_set('Europe/Rome');
            }
		}
		
		/**
		 * Imports new CSV file info to Eloqua
		 * 
		 * @return a boolean indicating the result of the import operation
		 */
		public function importNewCsvToEloqua() {
			
            $date = new DateTime();
            $dateStr = $date->format('Ymd');
            $dateStrMinSec = $date->format('YmdHis');
            $dateStrMinSecToEmail = $date->format('d-m-Y H:i:s');
            echo $dateStr;

            // Caso di upload attraverso il Data Import di Eloqua:
            $uploading_file_exists=file_exists( $this->w3bReportMergedPath.$this->w3bReportUploadingName );
            if($uploading_file_exists) {
                rename( $this->w3bReportMergedPath.$this->w3bReportUploadingName, str_replace("_uploading","_bkp_".$dateStrMinSec,$this->w3bReportBkpPath.$this->w3bReportUploadingName) );
            }

            // Scan directory dei file report arrivati da W3B
            $msg=" Lista file:";
            echo $msg;
            $this->logger->log($msg);
            $fileReportArr=array();
            $nFile=0;
            $files_merged_ok=true; // TBD da inserire una gestione degli errori
            if (is_dir($this->w3bReportPath)) {
                if ($dir_handler = opendir($this->w3bReportPath)) {
                    while (($fileName = readdir($dir_handler)) !== false) {
                        if($fileName != '.' && $fileName != '..' && is_file($this->w3bReportPath.$fileName)) {
                            $msg= "filename: ".$fileName." ";
                            echo $msg;
                            $this->logger->log($msg);
                            array_push($fileReportArr,$fileName);
                            $nFile++;
                        }
                    }
                    closedir($dir_handler);
                }
            }
            // Array in ordine inverso (i più recenti prima): per facilitare il reject delle info meno recenti se con email già esistente:
            arsort($fileReportArr);

            // Operazione di merge dell' array dei report in unico report pronto per l'upload verso eloqua
            $msg= "Merge File - in upload file ";
            echo $msg;
            $this->logger->log($msg);
            if($nFile>0) {
                $nameFileToUpload=str_replace("_uploading","_uploading_".$dateStrMinSec,$this->w3bReportMergedPath.$this->w3bReportUploadingName);
                $fBackup = fopen($nameFileToUpload, 'w'); 
                // loop over the rows, outputting them
                $msg=" HEADER file (to upload): "
                    ." ".$this->header_to_upload." ";
                echo $msg;
                $this->logger->log($msg);
                fwrite($fBackup, $this->header_to_upload."\n");
            } else {
                $msg= " -> NESSUN file da mergiare...";
                echo $msg;
                $this->logger->log($msg);
            }
            
            
            $nFile=0;
            if(!empty($fileReportArr)) {
                $emailSet=array();
                foreach ($fileReportArr as &$fileReportName) {
                    $msg= "fileReportArr FILE ".$nFile." (".$fileReportName."):";
                    echo $msg;
                    $this->logger->log($msg);
                    $file1 = fopen($this->w3bReportPath.$fileReportName,'r') or die("can not open file");
                    $nRow=0;
                    while($row = fgetcsv($file1, 1024, ';')) { 
                        if($nRow>0 /*|| ($nRow==0 && $nFile==0)*/) {
                            // for ($i = 0, $j = count($row); $i < $j; $i++) { echo '<div>'.$i.' is '.$row[$i].' @@ '.'</div>'; }
                            $email_i=$row[$this->POS_EMAIL_CSV];
                            // assicura che solo l'ultima riga viene inserita su eloqua
                            if(!in_array($email_i,$emailSet)) {
                                array_push($emailSet,$email_i);
                                fputcsv($fBackup, $row, ";");
                                $msg= ' '.count($row).' elementi caricati ';
                                echo $msg;
                                $this->logger->log($msg);
                            } else {
                                $msg= 'Già presente riga con email: '.$email_i.'! ';
                                echo $msg;
                                $this->logger->log($msg);
                            }
                        }
                        $nRow++;
                    }
                    $nFile++;
                    fclose($file1);
                }
                //close the handler
                fclose($fBackup); 
                // FINE Operazione di merge
            
                // Backup dei singoli file
                if($files_merged_ok) {
                    foreach ($fileReportArr as &$fileReportName) {
                        $msg= " SPOSTO IL FILE: ".$this->w3bReportPath.$fileReportName;
                        echo $msg;
                        $this->logger->log($msg);
                        rename( $this->w3bReportPath.$fileReportName, str_replace(".csv","_bkp_".$dateStr.".csv",$this->w3bReportOldPath.$fileReportName) );
                    }
                }
            }

            $msg= "Scan directory merged file. E lettura file completo-merged, pronto per upload: ";
            echo $msg;
            $this->logger->log($msg);
            // Costruisco array file merged to upload:
            $fileReportMergedArr=array();
            $nFileToUpload=0;
            if (is_dir($this->w3bReportMergedPath)) {
                $msg= "Lettura cartella: ".$this->w3bReportMergedPath."";
                echo $msg;
                $this->logger->log($msg);
                if ($dir_handler = opendir($this->w3bReportMergedPath)) {
                    while (($fileMergedName = readdir($dir_handler)) !== false) {
                        if($fileMergedName != '.' && $fileMergedName != '..') {
                            $msg= " #### INVIO AD ELOQUA DATI DA FILE: ".$fileMergedName."";
                            echo $msg;
                            $this->logger->log($msg);
                            array_push($fileReportMergedArr,$fileMergedName);
                            $nFileToUpload++;
                        }

                    }
                    closedir($dir_handler);
                }
            }

            // Task di upload verso eloqua per ognuno dei file:
            $msg= "nFile Elaborati nel merging: ".$nFile;
            echo $msg;
            $this->logger->log($msg);
            foreach ($fileReportMergedArr as &$fileReportMergedName) {
                $bkpPathAfterProcess=str_replace("_uploading_","_bkp_",$this->w3bReportBkpPath.$fileReportMergedName);	
                $msg= "UPLOAD TO ELOQUA CALL ...".$this->w3bReportMergedPath.$fileReportMergedName
                    ." (to backup to: ".$bkpPathAfterProcess.")";
                echo $msg;
                $this->logger->log($msg);
                $upload_ok = $this->eloqua_contact_utils->contactsImportByCsv(
                    $this->w3bReportMergedPath.$fileReportMergedName, $bkpPathAfterProcess);
                //if($upload_ok) {
                //    rename( $this->w3bReportMergedPath.$fileReportMergedName, $bkpPathAfterProcess );
                //}
            }

            if($nFileToUpload==0) {           
                $this->importer_notification->importerNotification( 'INFO', 
                    'Nessun nuovo file rilasciato: elaborazione terminata in data: '
                    .$dateStrMinSecToEmail.', '
                    .'senza nessun nuovo contatto aggiornato / inserito.');
            }
            
            return true;
		}
		
	}
	
?>