<?php

	 $contextapp="MailRestService";
     $this_path = dirname(__FILE__); 
	 $server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/"; 
	 
	require_once $server_root."services/importer/importer.php";
    require_once $server_root."env/env_utils.php";
    require_once $server_root."services/logger/logger.php";
    	
	/**
	 * Importer call - Launcher
	 * 
	 * @author francescoimperato
	 *
	 */
  	
	$logger = new logger();
	$date = new DateTime();
	$logger->batch_log( "IMPORTER BATCH: Lancio batch in data: ".$date->format('Y-m-d H:i:s') );
	
	/*$date = new DateTime();
	$dateStrMinSec = $date->format('Y-m-d H:i:s');
	$text="IMPORTER: Tentativo lancio importer - rilascio csv dati in data: ".$dateStrMinSec;
	$filename = "/var/www/html/MailRestService/logs/MailRestService-log-".date('Ymd').".txt";
	$fp = fopen($filename, 'a+');
	fputs($fp, $text."\n");
	fclose($fp);*/

    // impostare uno scheduler per questo launcher php tramite "crontab -e" 
	// (ad es. ogni 2 ore, dal lunedì al venerdì, 0 è sunday): 
	// * */2 * * 1-5 php /var/www/html/MailRestService/importer/importer_launcher.php
	
	$importer = new importer();
	$importer->importNewCsvToEloqua();
	
	$date = new DateTime();
    $logger->batch_log( "IMPORTER BATCH: Fine batch in data: ".$date->format('Y-m-d H:i:s') );
	/*$text="IMPORTER: Lancio importer per rilascio csv dati completato in data: ".$dateStrMinSec;
	$filename = "/var/www/html/MailRestService/logs/MailRestService-log-".date('Ymd').".txt";
	$fp = fopen($filename, 'a+');
	fputs($fp, $text."\n");
	fclose($fp);*/
?>