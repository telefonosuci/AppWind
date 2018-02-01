<?php

	/**
	 * Require and costants
     * NOTA: if utile per percorso assoluto usato dal crontab per l'IMPORTER Esito Lead
	 */
	 $contextapp="MailRestService";
	 $this_path = dirname(__FILE__); 
	 $server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/";
		require_once $server_root."services/logger/logger.php";
		require_once $server_root."env/env_utils.php";
		require_once $server_root."services/eloqua/model/contact.php";
		require_once $server_root."services/importer/importer_notifications.php";
        require $server_root.'vendor/autoload.php';


	use GuzzleHttp\Client;
    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Promise;

	/**
	 * Importer class: to allow import W3B Csv to Eloqua and handles the csv flow for the Importer
	 * 
	 * @author francescoimperato
	 *
	 */
	class eloqua_contact_utils {
		
		/**
		 * Properties 
		 */
        public $syncedInstanceUri;
        public $syncCheckUri;
		public $numUpload;
		public $fileReportToUploadName;
		public $bkpPathAfterProcess;

        public $POS_FIRSTNAME_CSV=0;
        public $POS_LASTNAME_CSV=1;
        public $POS_BUSINESSPHONE_CSV=2;
        public $POS_EMAIL_CSV=3;
        public $POS_PROVINCIA_CSV=4;
        public $POS_NOTE_CSV=5;
        public $POS_MARKER_CSV=6;
        public $POS_LEADSCORE_CSV=7;
        public $POS_ESITO_CSV=8;
        public $POS_CAUSALE_CSV=9;
        public $POS_DATAESITO_CSV=10;
        public $POS_CONSENSOPRIVACY_CSV=11;

		/**
		 * Constructor
		 * 
		 */
		public function __construct() {
			
            $this->logger = new logger();
            $this->utils = new utils();
			$this->importer_notification=new importer_notification();
   			$this->api_key=$this->utils->getEnvValue("api_key");
            
            $this->isDegug=$this->utils->getEnvValue("is_debug");
            $this->w3bReportMergedPath=$this->utils->getEnvValue("w3b_report_merged_path");
            
            if(isset($this->isDegug) && $this->isDegug) {
                // debug
                echo 'Starting Eloqua Utils ...<br/>';
                $this->logger->log("DEBUG Eloqua Utils: " .
                        " , w3bReportMergedPath is " . $this->w3bReportMergedPath);
            } 

			// ERROR HANDLER
			function customError($errno, $errstr) { 
				$errMsg=" <b>Error:</b><br/> [$errno] $errstr"; 
				echo $errMsg;
				//$this->logger->log($errMsg);
			}
			set_error_handler("customError");
		}

		/**
		 * Chiamata GET, asincrona, che estrae i contact fields
		 * 
		 * @return 
		 */
		public function findContactsFields() {
			echo '<br /><br />TEST GET ELQ';
			$client = new Client();
			$headers = array('Authorization' => $this->api_key, 'Content-Type' => 'application/json');
			$request = new Request('GET', 'https://secure.p06.eloqua.com/API/bulk/2.0/contacts/fields', $headers);
			//$response3 = $client3->send($request, ['timeout' => 2]);
			echo '<br />It is there: '.$request->hasHeader('Authorization');

			$promise = $client->sendAsync($request)->then(
				function ($response) {
					echo '<br />Completed findContactsFields! Result:<br/>'.$response->getBody()."<br/>";
				},
				function ($e) {
					$errMsg= "<br />ERROR in findContactsFields"
						."ERR-MSG IS:<br /> ".$e->getMessage()."<br/>"
						."ERR-METHOD IS: ".$e->getRequest()->getMethod()."<br/>";
					echo $errMsg;
					$this->logger->log($errMsg);
				}
			);
		}
		
		/**
		 * Chiamata POST per l'imports contacts
		 * rif. http://docs.oracle.com/cloud/latest/marketingcs_gs/OMCAB/Developers/BulkAPI/Tutorials/Import.htm
		 * 
		 * @return 
		 */
		public function contactsImportByCsv($fileReportToUploadName,$bkpPathAfterProcess) {
			$msg=' CONTACTS IMPORT by: '.$fileReportToUploadName.' (next bkp file:'.$bkpPathAfterProcess.')';
			echo $msg;
			$this->logger->log($msg);
			$this->fileReportToUploadName=$fileReportToUploadName;
			$this->bkpPathAfterProcess=$bkpPathAfterProcess;
			$client = new Client();
			$headers = array('Authorization' => $this->api_key, 'Content-Type' => 'application/json'); 
			$definition = array( "identifierFieldName" => 'email_address',
							"name" => 'WIND Contact Import API - W3B_Report_Esito',
							"fields" => array(
								"first_name" => "{{Contact.Field(C_FirstName)}}",
								"last_name" => "{{Contact.Field(C_LastName)}}",
								"business_phone" => "{{Contact.Field(C_BusPhone)}}",
								"email_address" => "{{Contact.Field(C_EmailAddress)}}",
								"provincia" => "{{Contact.Field(C_Provincia1)}}",
								"note" => "{{Contact.Field(C_Note1)}}",
								"marker" => "{{Contact.Field(C_Marker1)}}",
								// "lead_score" => "{{Contact.Field(C_LastName)}}",
								"esito" => "{{Contact.Field(C_Esito1)}}",
								"causale" => "{{Contact.Field(C_Causale1)}}",
								"data_esito" => "{{Contact.Field(C_Data_Esito1)}}",
								"consenso_privacy" => "{{Contact.Field(C_Consenso_Privacy1)}}"
								)
							);
			$msg="<br/>CREATA DEFINITION di import: ".json_encode($definition);
			echo $msg;
			$this->logger->log($msg);
			$request = new Request('POST', 'https://secure.p06.eloqua.com/API/bulk/2.0/contacts/imports/', 
				$headers, json_encode($definition) );
			$promise = $client->sendAsync($request)->then(
				function ($response) {
					$msg='<br />Completed contactsImport! Result:<br/>'.$response->getBody();
					echo $msg;
					$this->logger->log($msg);
					$jsonRes = json_decode($response->getBody(), true);
					$uri=$jsonRes['uri'];
					$msg='<br/>URI value: '.$uri;
					echo $msg;
					$this->logger->log($msg);
					$this->uploadContactsStagingArea($uri,$this->fileReportToUploadName);
				},
				function ($e) {
					$errMsg="<br />ERROR in contactsImport"
						."<br/>ERR-MSG IS:<br /> ".$e->getMessage()
						."<br/>ERR-METHOD IS: ".$e->getRequest()->getMethod();
					echo $errMsg;
					$this->logger->log($errMsg);
					$this->importer_notification->importerNotification( 'ERROR', 
						'File elaborato disponibile al percorso: <br/>'.$this->bkpPathAfterProcess
						.'<br/>Elaborazione in errore: '.$errMsg);
				}
			);
			//$promise->wait();
			return true;
		}

		/**
		*  Step 2. Caricamento delle informazioni per Contact in staging area:
		*  Nota: $fileReportToUploadName comprende anche il path del file csv da caricare.
		*
		*/
		public function uploadContactsStagingArea($uri,$fileReportToUploadName) {
			$msg='<br />UPLOAD CONTACT IN STAGING AREA..';
			echo $msg;
			$this->logger->log($msg);
			$this->syncedInstanceUri=$uri;
			$client = new Client();
			$headers = array('Authorization' => $this->api_key, 'Content-Type' => 'application/json'); 
			
			$contactsArr=array();
			// Estraggo I VALORI DAL CSV (FOR PER N-RIGHE):
			//$body = fopen('/var/wind/w3b/report/merged/reportEsitoLead_uploading.csv', 'r');
			$msg="FILE ".$fileReportToUploadName." in upload:";
			echo $msg;
			$this->logger->log($msg);
			$file1 = fopen($fileReportToUploadName,'r') or die("can not open file");
			$nRow=0;
			while($row = fgetcsv($file1, 1024, ';')) { 
				if($nRow>0) {
					// for ($i = 0, $j = count($row); $i < $j; $i++) { echo '<div>'.$i.' is '.$row[$i].' @@ '.'</div>'; }
					// Per ogni riga del csv (array push):
					$first_name=$row[$this->POS_FIRSTNAME_CSV];
					$last_name=$row[$this->POS_LASTNAME_CSV];
					$business_phone=$row[$this->POS_BUSINESSPHONE_CSV];
					$email_address=$row[$this->POS_EMAIL_CSV];
					$provincia=$row[$this->POS_PROVINCIA_CSV];
					$note=$row[$this->POS_NOTE_CSV];
					$marker=$row[$this->POS_MARKER_CSV];
					//$lead_score=$row[$this->POS_LEADSCORE_CSV];
					$esito=$row[$this->POS_ESITO_CSV];
					$causale=$row[$this->POS_CAUSALE_CSV];
					$data_esito=$row[$this->POS_DATAESITO_CSV];
					$consenso_privacy=$row[$this->POS_CONSENSOPRIVACY_CSV];
					$contact=new contact();
					$contact->populateEsitoCsvContact($first_name,$last_name,$business_phone,
								$email_address,$provincia,$note,$marker,/*$lead_score,*/
								$esito,$causale,$data_esito,$consenso_privacy);
					//$cars = array($myCar, $yourCar);
					array_push($contactsArr,$contact);
				}
				$nRow++;
			}
			fclose($file1);
			$this->numUpload=$nRow - 1;

			$msg="<br/>CREATA DEFINITION di upload array contacts (SIZE:'.$this->numUpload.'): ".json_encode($contactsArr);
			echo $msg;
			$this->logger->log($msg);
			if($this->numUpload > 0) {
				$request = new Request('POST', 'https://secure.p06.eloqua.com/API/bulk/2.0'.$uri.'/data', 
					$headers, json_encode($contactsArr) );
				$promise = $client->sendAsync($request)->then(
					function ($response) {
						$msg=' Completed uploadContactsStagingArea! Result:<br/>'.$response->getBody()
							.' STATUS CODE: '.$response->getStatusCode();
						echo $msg;
						$this->logger->log($msg);
						$this->syncContactsToEloqua($this->syncedInstanceUri);
					},
					function ($e) {
						$errMsg="<br />ERROR in uploadContactsStagingArea"
							."<br/>ERR-MSG IS:<br /> ".$e->getMessage()
							."<br/>ERR-METHOD IS: ".$e->getRequest()->getMethod();
						echo $errMsg;
						$this->logger->log($errMsg);
						$this->importer_notification->importerNotification( 'ERROR', 
							'File elaborato disponibile al percorso: <br/>'.$this->bkpPathAfterProcess
							.'<br/>Elaborazione in errore: '.$errMsg);
					}
				);
			} else {
				$this->importer_notification->importerNotification( 'OK', 
						'File elaborato disponibile al percorso: <br/>'.$this->bkpPathAfterProcess
						.'<br/>Numero degli utenti caricati/aggiornati: '.$this->numUpload
						.'<br/>(Nessun contatto elaborato/caricato).');
					$msg='<br/>...(Nessun contatto elaborato/caricato) Notifica email inviata.';
					echo $msg;
					$this->logger->log($msg);
			}
		}

		public function syncContactsToEloqua($uri) {
			echo '<br />SYNC CONTACT TO ELOQUA (to complete the upload)..';
			$client = new Client();
			$headers = array('Authorization' => $this->api_key, 'Content-Type' => 'application/json'); 			
			$definition = array( "syncedInstanceUri" => $uri);
			$request = new Request('POST', 'https://secure.p06.eloqua.com/API/bulk/2.0/syncs', 
				$headers, json_encode($definition) );
			$promise = $client->sendAsync($request)->then(
				function ($response) {
					$msg='<br />Completed syncContactsToEloqua! Result:<br/>'.$response->getBody();
					echo $msg;
					$this->logger->log($msg);
					$jsonRes = json_decode($response->getBody(), true);
					$uri=$jsonRes['uri'];
					$msg='<br/>URI value: '.$uri;
					echo $msg;
					$this->logger->log($msg);
					$this->syncCheckUri=$uri;
					$msg='<br/>FILE DA UPLOADING FOLDER A BACKUP FOLDER...';
					echo $msg;
					$this->logger->log($msg);
					rename( $this->fileReportToUploadName, $this->bkpPathAfterProcess );
					$this->checkStatusContactUpload($uri);
				},
				function ($e) {
					$errMsg="<br />ERROR in syncContactsToEloqua"
							."<br/>ERR-MSG IS:<br /> ".$e->getMessage()
							."<br/>ERR-METHOD IS: ".$e->getRequest()->getMethod();
					echo $errMsg;
					$this->logger->log($errMsg);
					$this->importer_notification->importerNotification( 'ERROR', 
						'File elaborato disponibile al percorso: <br/>'.$this->bkpPathAfterProcess
						.'<br/>Elaborazione in errore: '.$errMsg);
				}
			);
			//$promise->wait();
		}

		public function checkStatusContactUpload($uri) {
			$msg=' CHECK STATUS CONTATC UPLOAD GET ELQ';
			echo $msg;
			$this->logger->log($msg);
			$client = new Client();
			$headers = array('Authorization' => $this->api_key, 'Content-Type' => 'application/json');
			$request = new Request('GET', 'https://secure.p06.eloqua.com/API/bulk/2.0'.$uri, $headers);
			$promise = $client->sendAsync($request)->then(
				function ($response) {
					$msg='<br />Completed checkStatusContactUpload! Result:<br/>'.$response->getBody()."<br/>";
					echo $msg;
					$this->logger->log($msg);
					$jsonRes = json_decode($response->getBody(), true);
					$status=$jsonRes['status'];
					$msg='<br/>STATUS value: '.$status;
					echo $msg;
					$this->logger->log($msg);
					$this->importer_notification->importerNotification( 'OK',
						'File caricato disponibile al percorso: <br/>'.$this->bkpPathAfterProcess
						.'<br/>Numero degli utenti caricati/aggiornati: '.$this->numUpload
						.'<br/>Controlla lo stato con la seguente URI: <br/>'
						.'https://secure.p06.eloqua.com/API/bulk/2.0'.$this->syncCheckUri
						.' <br/>(status value is now: '.$status.')');
					$msg='<br/>...Notifica email inviata.';
					echo $msg;
					$this->logger->log($msg);
				},
				function ($e) {
					$errMsg="<br />ERROR in checkStatusContactUpload"
							."<br/>ERR-MSG IS:<br /> ".$e->getMessage()
							."<br/>ERR-METHOD IS: ".$e->getRequest()->getMethod();
					echo $errMsg;
					$this->logger->log($errMsg);
					$this->importer_notification->importerNotification( 'ERROR', 
						'File elaborato disponibile al percorso: <br/>'.$this->bkpPathAfterProcess
						.'<br/>Elaborazione in errore: '.$errMsg);
				}
			);
		}

		/**
		 * TEST: chiamata POST di test verso Eloqua per l'imports contacts
		 * Da completare la definitions e gli step di sync/check (POST/GET) e ottenimento dei dati (GET)
		 * 
		 * @return 
		 */
		public function testPostContactsImport() {
			echo '<br />TEST POST CONTACTS ELQ';
			// https://secure.p06.eloqua.com/API/   bulk/2.0/contacts/imports/
			$client = new Client();
			$headers = array('Authorization' => $this->api_key, 'Content-Type' => 'application/json'); // 'textv/csv'
			$body = fopen('/var/wind/w3b/report/merged/reportEsitoLead_uploading.csv', 'r');
			$definition = array( "identifierFieldName" => 'LastName',
							"name" => 'name - import01',
							"fields" => array(
								"LastName" => "{{Contact.Field(C_LastName)}}"
								)
							);
			echo "<br/>CREATA DEFINITION di import: ".json_encode($definition);
			$request = new Request('POST', 'https://secure.p06.eloqua.com/API/bulk/2.0/contacts/imports/', 
				$headers, json_encode($definition) );
			fclose($body);
			$promise = $client->sendAsync($request)->then(
				function ($response) {
					echo '<br />Completed testPostContactsImport! Result:<br/>'.$response->getBody();
					$jsonRes = json_decode($response->getBody(), true);
					echo '<br/>URI value: '.$jsonRes['uri'];;
				},
				function ($e) {
					echo "<br />ERROR in testPostContactsImport";
					echo "ERR-MSG IS:<br /> ".$e->getMessage() . "<br/>";
					echo "ERR-METHOD IS: ".$e->getRequest()->getMethod();
				}
			);
			// $response = $client->send( $request, ['timeout' => 2] );
		}
		
	}
	
?>