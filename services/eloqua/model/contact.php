<?php

	/**
	 * Contact model class
	 * 
	 * @author francescoimperato
	 *
	 */
	class contact {

		public $first_name;
		public $last_name;
		public $business_phone;
		public $email_address;
		public $provincia;
		public $note;
		public $marker;
		//public $lead_score;
		public $esito;
		public $causale;
		public $data_esito;
		public $consenso_privacy;
		
		/**
		 * Constructor
		 * 
		 */
		public function __construct() {
			
		}

		public function populateEsitoCsvContact($first_name,$last_name,$business_phone,
						$email_address,$provincia,$note,$marker,/*$lead_score,*/
						$esito,$causale,$data_esito,$consenso_privacy) {

				$this->first_name=$first_name;
				$this->last_name=$last_name;
				$this->business_phone=$business_phone;
				$this->email_address=$email_address;
				$this->provincia=$provincia;
				$this->note=$note;
				$this->marker=$marker;
				//$this->lead_score=$lead_score;
				$this->esito=$esito;
				$this->causale=$causale;
				$this->data_esito=$data_esito;
				$this->consenso_privacy=$consenso_privacy;

		}
		
		
	}
	
?>