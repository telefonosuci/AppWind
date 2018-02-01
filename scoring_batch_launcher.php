<?php

    /**
	 * Require and costants
     */
     $contextapp="MailRestService";
     $this_path = dirname(__FILE__); 
     $server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/";
    require $server_root.'vendor/autoload.php';
	require_once $server_root."env/env_utils.php";
	require_once $server_root."services/eloqua/eloqua_score_utils.php";
	require_once $server_root."services/scoring/scoring.php";
    require_once $server_root."services/logger/logger.php";
    require_once $server_root."services/mailer/mailer.php";

	/**
	 * Eloqua API test call (UNIT TEST per i metodi eloqua_score_utils)
	 * 
	 * @author francescoimperato
	 *
	 */	

	use GuzzleHttp\Client;
    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Promise;
    
    $logger = new logger();
    $date = new DateTime();
    $logger->batch_log( "SCORING BATCH: Lancio batch in data: ".$date->format('Y-m-d H:i:s') );

    $utils = new utils();
    $api_key=$utils->getEnvValue("api_key");
    //$lead_score_model_name="test";
    $ls_models_to_monitor_str=$utils->getEnvValue("ls_models_to_monitor");
    $ls_models_to_monitor_arr = explode(",", $ls_models_to_monitor_str);
    $scoring = new scoring();
    
    $num_ls_model=0;
    foreach($ls_models_to_monitor_arr as $lead_score_model_name) {
        $num_ls_model++;
        $msg='ITERAZIONE modello LS: '.$num_ls_model.' NOME modello: '.$lead_score_model_name;
        echo '<br />'.$msg;
        $logger->batch_log( $msg );
        
        $eloqua_score_utils = new eloqua_score_utils();
        
        // TEST 1. findLeadScoreModelIdByName: $eloqua_score_utils->findLeadScoreModelIdByName($lead_score_model_name);

        // Check in Eloqua and Export Contacts By ScoreModel: '<br />Export contacts by model'.$lead_score_model_name;
        $res = $scoring->scoringMonitorAndExport($lead_score_model_name);
        $logger->batch_log( 'Check in Eloqua and Export Contacts By ScoreModel. RESULT '.$res );
    }

    $date = new DateTime();
    $logger->batch_log( "SCORING BATCH: Fine batch in data: ".$date->format('Y-m-d H:i:s') );

?>