<?php

    
	/**
	 * @author francescoimperato
	 *
	 */	
    
	echo 'PATH: '.dirname(__FILE__);
	
	$contextapp="AppWind";
	$this_path = dirname(__FILE__); 
	$server_root=substr($this_path, 0, strpos($this_path, $contextapp)).$contextapp."/";
	$server_root=dirname(__FILE__)."/";
	
	echo '<br/> server_root-0: '.$server_root;
	
	/**
	 * Require and costants
     *
	require $server_root.'vendor/autoload.php';
	require_once $server_root."services/mailer/mailer.php";


	echo '<br/>INIZIO call mailer test';
    $mailer = new mailer();
    echo '<br/>FINE call mailer test';
    
    */
?>