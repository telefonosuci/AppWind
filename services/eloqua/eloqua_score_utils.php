<?php

	/**
	 * Require and costants
     * NOTA: if utile per percorso assoluto usato dai test di riga di comando
	 */
	 $contextapp="AppWind";
	 $this_path = dirname(__FILE__); 
	 $server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/";
    require $server_root."vendor/autoload.php";
	require_once $server_root."env/env_utils.php";
	require_once $server_root."services/logger/logger.php";
	require_once $server_root."services/eloqua/model/contact.php";

	use GuzzleHttp\Client;
    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Promise;

	/**
	 * Importer class: to allow import W3B Csv to Eloqua and handles the csv flow for the Importer
	 * 
	 * @author francescoimperato
	 *
	 */
	class eloqua_score_utils {
		
		/**
		 * Properties 
		 */
        public $syncedInstanceUri;
        public $syncCheckUri;
		public $lsModelId;
		public $SECONDS_WAIT_FOR_SYNC_RETRIEVE;
		public $num_sync_attempt=0;
		public $min_ls_rating_arr;

		/**
		 * Constructor
		 * 
		 */
		public function __construct() {
			
            $this->logger = new logger();
            $this->utils = new utils();
   			$this->api_key=$this->utils->getEnvValue("api_key");
			$this->isDegug=$this->utils->getEnvValue("is_debug");
			$this->SECONDS_WAIT_FOR_SYNC_RETRIEVE=2;
            
            if(isset($this->isDegug) && $this->isDegug) {
                // debug
                echo ' Starting Eloqua Score Utils ...<br/>';
                $this->logger->log(" DEBUG Eloqua Score Utils: ... ");
            } 
		}

		/**
		 * Chiamata GET che estrae il LeadScore model ID dal model name
		 * 
		 * @return 
		 */
		public function findLeadScoreModelIdByName($modelName) {
			$msg='  FIND SCORING MODEL: '.$modelName;
			echo $msg;
			$this->logger->log($msg);
			$modelNameToSearch=str_replace(" ","*",$modelName);
			$client = new Client();
			$headers = array('Authorization' => $this->api_key, 'Content-Type' => 'application/json');
			$request = new Request('GET', 'https://secure.p06.eloqua.com/API/bulk/2.0/contacts/scoring/models?q="name='
				.$modelNameToSearch.'"', $headers);
			$response = $client->send($request);
			$jsonRes = json_decode($response->getBody(), true);
			$modelItems=$jsonRes["items"];
			$lsModelId=$modelItems[0]["id"];
			$msg='<br/>lsModelId: '.$lsModelId;
			echo $msg;
			$this->logger->log($msg);
			return $lsModelId;
		}
		
		/**
		 * Chiamata POST per l'export dei contact e lead score
		 * 
		 * @return 
		 */
		public function exportContactsByScoreModel($modelName) {
			$lsModelId = $this->findLeadScoreModelIdByName($modelName);
			$msg=' CONTACTS EXPORT by MODEL SCORE ID: '.$lsModelId;
			echo $msg;
			$this->logger->log($msg);
			$client = new Client();
			$headers = array('Authorization' => $this->api_key, 'Content-Type' => 'application/json'); 
			$filter = "";
			
			$min_ls_ratings_models_str=$this->utils->getEnvValue("min_ls_rating");
			$min_ls_rating_model_temp = explode(";", $min_ls_ratings_models_str);	
			foreach ($min_ls_rating_model_temp as $single_rating_model) {
				$rating_model_associative_temp = explode('/', $single_rating_model);
				if($rating_model_associative_temp[0]==$modelName) {
					$min_ls_rating_str=$rating_model_associative_temp[1];
					$this->min_ls_rating_arr = explode(",", $min_ls_rating_str);
				}
			}	
			if(!empty($this->min_ls_rating_arr)) {

				foreach($this->min_ls_rating_arr as $rating) {
					if($filter=="") {
						$filter=$filter."'{{Contact.LeadScore.Model[".$lsModelId."].Rating}}'='".$rating."'";
					} else {
						$filter=$filter."OR'{{Contact.LeadScore.Model[".$lsModelId."].Rating}}'='".$rating."'";
					}
				}
				$msg=' FILTER debug: '.$filter;
				echo $msg;
				$this->logger->log($msg);
				// TODO refactorizzare la "fields" definition, passarla come parametro
				$definition = array( 
								"name" => 'WIND - LS Info Export',
								"fields" => array(
									"first_name" => "{{Contact.Field(C_FirstName)}}",
									"last_name" => "{{Contact.Field(C_LastName)}}",
									"business_phone" => "{{Contact.Field(C_BusPhone)}}",
									"email_address" => "{{Contact.Field(C_EmailAddress)}}",
									"provincia" => "{{Contact.Field(C_Provincia1)}}",
									"note" => "{{Contact.Field(C_Note1)}}",
									"marker" => "{{Contact.Field(C_Marker1)}}",
									"esito" => "{{Contact.Field(C_Esito1)}}",
									"causale" => "{{Contact.Field(C_Causale1)}}",
									"data_esito" => "{{Contact.Field(C_Data_Esito1)}}",
									"consenso_privacy" => "{{Contact.Field(C_Consenso_Privacy1)}}",
									"ls_rating" => "{{Contact.LeadScore.Model[".$lsModelId."].Rating}}",
									"ls_profileScore" => "{{Contact.LeadScore.Model[".$lsModelId."].ProfileScore}}",
									"ls_engagementScore" => "{{Contact.LeadScore.Model[".$lsModelId."].EngagementScore}}"
									),
								"filter" => $filter
								);
				$msg=" CREATA DEFINITION di exportContactsByScoreModel: ".json_encode($definition);;
				echo $msg;
				$this->logger->log($msg);
				$request = new Request('POST', 'https://secure.p06.eloqua.com/API/bulk/2.0/contacts/exports', 
					$headers, json_encode($definition) );
				$response=$client->send($request);
				//echo ' Completed exportContactsByScoreModel! Result:<br/>'.$response->getBody();
				$msg='Completed exportContactsByScoreModel! Result:<br/>'.$response->getBody();
				echo $msg;
				$this->logger->log($msg);
				$jsonRes = json_decode($response->getBody(), true);
				$uri=$jsonRes['uri'];
				$msg='<br/>URI value: '.$uri;
				echo $msg;
				$this->logger->log($msg);
				$itemsFound = $this->eloquaSync($uri);
				return $itemsFound;
			} 
			else {
				$errMsg=" ERROR impossibile procedere: ".
					"nessuna fascia di rating minimo in input per model score: ".$modelName.
					"; ratings-models in input: ".$this->utils->getEnvValue("min_ls_rating");
				echo $errMsg;
				$this->logger->log($errMsg);
				return null;
			}
		}

		/**
		*  Step 2. eloquaSync (TODO refactor in eloqua_commons_utils)
		*
		*/
		public function eloquaSync($uri) {
			$msg=' SYNC syncToEloqua TO ELOQUA ..';
			echo $msg;
			$this->logger->log($msg);
			$client = new Client();
			$headers = array('Authorization' => $this->api_key, 'Content-Type' => 'application/json'); 			
			$definition = array( "syncedInstanceUri" => $uri);
			$request = new Request('POST', 'https://secure.p06.eloqua.com/API/bulk/2.0/syncs', 
				$headers, json_encode($definition) );
			$response=$client->send($request);
			$msg=' Completed syncToEloqua! Result:<br/>'.$response->getBody();
			$this->logger->log($msg);
			$jsonRes = json_decode($response->getBody(), true);
			$uri=$jsonRes['uri'];
			$msg=' URI value: '.$uri;
			echo $msg;
			$this->logger->log($msg);
			$this->syncCheckUri=$uri;
			$this->num_sync_attempt=0;
			$itemsFound = $this->checkStatusSync($uri);
			return $itemsFound;
		}

		public function checkStatusSync($uri) {
			$this->num_sync_attempt++;
			$msg=' CHECK STATUS SYNC ; tentativo: '.$this->num_sync_attempt;
			echo $msg;
			$this->logger->log($msg);
			$client = new Client();
			$headers = array('Authorization' => $this->api_key, 'Content-Type' => 'application/json');
			$request = new Request('GET', 'https://secure.p06.eloqua.com/API/bulk/2.0'.$uri, $headers);
			sleep($this->SECONDS_WAIT_FOR_SYNC_RETRIEVE);
			$response=$client->send($request);
			//echo ' Completed checkStatusSync! Result:<br/>'.$response->getBody()."<br/>";
			$msg=' Completed checkStatusSync! Result:<br/>'.$response->getBody()."<br/>";
			$this->logger->log($msg);
			$jsonRes = json_decode($response->getBody(), true);
			$status=$jsonRes['status'];
			$syncedInstanceUri=$jsonRes['syncedInstanceUri'];
			$msg=' STATUS value: '.$status.' syncedInstanceUri: '.$syncedInstanceUri;
			echo $msg;
			$this->logger->log($msg);
			$itemsFound=null;
			if($status=="success") {
				$itemsFound=$this->getContactsData($syncedInstanceUri);
			}
			elseif($this->num_sync_attempt<=3) {
				$msg=' Nuovo tentativo dopo secondi: '.$this->SECONDS_WAIT_FOR_SYNC_RETRIEVE;
				echo $msg;
				$this->logger->log($msg);
				sleep($this->SECONDS_WAIT_FOR_SYNC_RETRIEVE);
				$this->checkStatusSync($this->syncCheckUri);
			} 
			else {
				$errMsg=" ERROR in checkStatusSync: ".$this->syncCheckUri
					.' ; Stop dopo '.$this->num_sync_attempt. ' tentativi';
				echo $errMsg;
				$this->logger->log($errMsg);
			}
			return $itemsFound;
		}

		public function getContactsData($syncedInstanceUri) {
			$msg= '  GET CONTACTS DATA with uri '.$syncedInstanceUri;
			echo $msg;
			$this->logger->log($msg);
			$client = new Client();
			$offset=0;
			/*$limitFilter="";
			$scoring_limit=$this->utils->getEnvValue("scoring_limit");
			if(isset($scoring_limit) && !empty($scoring_limit) && $scoring_limit>=0 && $numItem>=$scoring_limit) {
				$this->logger->log('IS SET $scoring_limit '.$scoring_limit.': break scoring procedure.');
				$limitFilter="&limit=".$scoring_limit;
			}*/
			$headers = array('Authorization' => $this->api_key, 'Content-Type' => 'application/json');
			$request = new Request('GET', 'https://secure.p06.eloqua.com/API/bulk/2.0'
				.$syncedInstanceUri.'/data'.'?offset='.$offset, $headers);
			$response = $client->send($request);
			$jsonRes = json_decode($response->getBody(), true);
			$totalResults=$jsonRes['totalResults'];
			$items=$jsonRes['items'];
			$hasMore=$jsonRes['hasMore'];
			$msg=' Total items item to extract: '.$totalResults
				.' ; hasMore items ? ... '.$hasMore.' total items count: '.count($items);
			echo $msg;
			$this->logger->log($msg);
			while($hasMore) {
				$offset+=1000;
				$uriRequest='https://secure.p06.eloqua.com/API/bulk/2.0'
					.$syncedInstanceUri.'/data'.'?offset='.$offset;
				$msg=' new GET request ... '.$uriRequest;
				echo $msg;
				$this->logger->log($msg);
				$request = new Request('GET', $uriRequest, $headers);
				$response = $client->send($request);
				$jsonRes = json_decode($response->getBody(), true);
				$itemstemp=$jsonRes['items'];
				$hasMore=$jsonRes['hasMore'];
				$items=array_merge($items,$itemstemp);
				$msg=' hasMore items ? ... '.$hasMore.' new total items count: '.count($items)
				 	.' added items count: '.count($itemstemp);
				echo $msg;
				$this->logger->log($msg);
			}
			$msg=" Fine iterazione per get ContactsData: ".' new total items count: '.count($items);
			echo $msg;
			$this->logger->log($msg);
			return $items;
		}
		
		
	}
	
?>