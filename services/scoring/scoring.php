<?php

	/**
	 * Require and costants
     * NOTA: if utile per percorso assoluto usato dai test di riga di comando
     */
     $contextapp="MailRestService";
     $this_path = dirname(__FILE__); 
     $server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/"; 
    require_once $server_root.'env/env_utils.php';
	require_once $server_root."services/eloqua/eloqua_score_utils.php";
	require_once $server_root."services/csvcreator/csvcreator.php";
    require_once $server_root."services/logger/logger.php";

	/**
	 * Scoring management class
	 * 
	 * @author francescoimperato
	 *
	 */
	class scoring {
		
		/**
		 * Properties 
		 *
		 */
        public $scoring_report_folder;
        public $high_score_csv_path;
        public $high_score_csv_name;
        public $high_score_csv_path_and_name;
        public $POS_EMAIL_CSV=3;
        public $POS_LEADSCORE_CSV=7;

		/**
		 * Constructor
		 * 
		 */
		public function __construct() {
			
            $this->utils = new utils();
            $this->eloqua_score_utils = new eloqua_score_utils();
            $this->isDegug=$this->utils->getEnvValue("is_debug");
            $this->scoring_report_folder=$this->utils->getEnvValue("scoring_report_folder");
            $this->high_score_csv_path=$this->utils->getEnvValue("high_score_csv_path");
            $this->high_score_csv_name=$this->utils->getEnvValue("high_score_csv_name");
            $this->high_score_csv_path_and_name=$this->high_score_csv_path.$this->high_score_csv_name;

            $this->logger = new logger();
            if(isset($this->isDegug) && $this->isDegug) {
                // debug
                echo 'Starting iScoring ...<br/>';
                $this->logger->log("DEBUG Scoring " );
            } 

            if( ! ini_get('date.timezone') ) {
                date_default_timezone_set('Europe/Rome');
            }
		}
		
		/**
		 * Monitoring on contacts Lead Score and export CSV file info
		 * 
		 * @return a boolean indicating the result of the operation
		 */
		public function scoringMonitorAndExport($leadScoreModelName) {
			
            $date = new DateTime();
            $dateStrMinSec = $date->format('YmdHis');

            // Leggo il file di tutti i contatti già dentro la soglia di scoring:
            $contacts_high_score_arr = array();
            $history_model_suffix = "-".$leadScoreModelName;
            $high_score_csv_complete_name=$this->high_score_csv_path_and_name.$history_model_suffix.".csv";
            if(file_exists($high_score_csv_complete_name)) {
                $file1 = fopen($high_score_csv_complete_name,'r') or die("can not open file");
                $nRow=0;
                while($row = fgetcsv($file1, 1024, ';')) { 
                    if($nRow>0) {
                        $email_address=$row[$this->POS_EMAIL_CSV];
                        $lead_score=$row[$this->POS_LEADSCORE_CSV];
                        $contacts_high_score_arr[$email_address] = $lead_score;
                        echo "contatto already high score is: ".$email_address;
                    }
                    $nRow++;
                }
                fclose($file1);
            } // 
            
            $items = $this->eloqua_score_utils->exportContactsByScoreModel($leadScoreModelName);
            $csv_body_arr = array(
				array('Nome', 'Cognome', 'Telefono', 'Email', 'Provincia', 'Note', 'Marker', 'Livello Lead', 'Esito', 'Causale', 'Data Esito' , 'Consenso Privacy'),
            );
            $high_score_csv_body_arr = array(
				array('Nome', 'Cognome', 'Telefono', 'Email', 'Provincia', 'Note', 'Marker', 'Livello Lead', 'Esito', 'Causale', 'Data Esito' , 'Consenso Privacy'),
			); // Preparo anche l'highscore csv per la fase successiva
            $numItem=0;
            $scoring_limit=$this->utils->getEnvValue("scoring_limit");
            if(!empty($items)) {
                foreach ($items as $item){	
                    $eloqua_firstname=$item["first_name"];
                    $eloqua_lastname=$item["last_name"];
                    $eloqua_businessphone=$item["business_phone"];
                    $eloqua_emailaddress=$item["email_address"];
                    $eloqua_provincia=$item["provincia"];
                    $eloqua_note=$item["note"];
                    $eloqua_marker=$item["marker"];
                    $eloqua_livello_lead=$item["ls_rating"];
                    $w3b_esito=$item["esito"];
                    $w3b_causale=$item["causale"];
                    $w3b_data_esito=$item["data_esito"];
                    $eloqua_consenso_privacy=$item["consenso_privacy"];
                    
                    // SE nel csv dei contatti già nella soglia, non considero l'item:
                    // $ls_previous_rating = $contacts_high_score_arr[$eloqua_emailaddress])
                    if(!array_key_exists($eloqua_emailaddress, $contacts_high_score_arr))
                    {
                        array_push($csv_body_arr,
                            array($eloqua_firstname, $eloqua_lastname, $eloqua_businessphone, $eloqua_emailaddress, $eloqua_provincia, $eloqua_note, $eloqua_marker, $eloqua_livello_lead, $w3b_esito, $w3b_causale, $w3b_data_esito, $eloqua_consenso_privacy)
                        );
                    } else {
                        echo "IS ALREADY in high score map: ".$eloqua_emailaddress;
                    }
                    // la riga, sull'high_score_csv_body_arr, viene salvata sempre (storico di tutti gli high score aggiornato ad oggi) 
                    array_push($high_score_csv_body_arr,
                        array($eloqua_firstname, $eloqua_lastname, $eloqua_businessphone, $eloqua_emailaddress, $eloqua_provincia, $eloqua_note, $eloqua_marker, $eloqua_livello_lead, $w3b_esito, $w3b_causale, $w3b_data_esito, $eloqua_consenso_privacy)
                    );
                    $numItem++;
                    echo 'num item: '.$numItem;
                    
                    // Limite solo per la fase di sviluppo/collaudo: parametro scoring_limit_active=true
                    if(isset($scoring_limit) && !empty($scoring_limit) && $scoring_limit>=0 && $numItem>=$scoring_limit) {
                        $this->logger->log('IS SET $scoring_limit '.$scoring_limit.': break scoring procedure.');
                        break;
                    }
                }
            } else {
                $this->logger->log('Attenzione. Nessun lead trovato nelle fasce minime di score per il modello: '.$leadScoreModelName); 
            }
            $csvCreator = new csvcreator();
            // Nota: $model_suffix differenzia la campaign (hp. un lead score model per campaign)
            $model_suffix = $leadScoreModelName."-";
			$delta_csv_filename = $csvCreator->createCSVWithPathAndSuffix(
                $csv_body_arr,$this->scoring_report_folder,$model_suffix);
            echo 'delta-scoring csv filename is: '.$delta_csv_filename;
            
            // INVIO il file per email ai sistemi W3B (simulando il flusso del CM). 
            // Invio email solo se il delta nuovi lead non è vuoto
            if($numItem>0) {
                $subject = $this->utils->getEnvValue("mail_subject");
                $mailer = new mailer($delta_csv_filename, $subject);
                $this->logger ->log('Delta csv:'.$delta_csv_filename.' . Mailer initialized...');
                $sending_result = $mailer->sendMail();
                $this->logger ->log('Delta csv:'.$delta_csv_filename.' . Report sending_result: '.$sending_result);
            }

            // La mappa completa dei contatti già high score viene aggiornata, 
            // ma solo se è andato tutto ok nella creazione del file dei delta:
            if(file_exists($delta_csv_filename)) {
                // Se esiste il file high score precedente ne creo una versione di backup:
                if(file_exists($high_score_csv_complete_name)) {
                    rename($high_score_csv_complete_name, $high_score_csv_complete_name.'.BKP');
                }
                // Ricalcolo gli utenti high score e ricreo il file di high_score_csv_name:
                $withDate=0; // false
                $highscore_history_csv_filename = $csvCreator->createCSVWithPathAndNameAndSuffixAndDate(
                    $high_score_csv_body_arr,
                    $this->high_score_csv_path,
                    $this->high_score_csv_name,
                    $history_model_suffix,
                    $withDate);
                echo 'high score history-scoring csv filename is: '.$highscore_history_csv_filename;
            }
            
            return true;
		}
		
	}
	
?>