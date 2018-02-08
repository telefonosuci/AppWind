// Declare Eloqua object
var _elqQ = _elqQ || [];

// CONSTRUCTOR
function ElqLib (params) {

	/* TODO
		- Use email address in the URL
	*/

	// DEFAULTS
	var defaults = {
		elq_site_id: '',
		elq_visitor_lookup_key: '',
		elq_recipient_id_lookup_key: '',
		elq_contact_lookup_key: '',
		elq_field_email_rec_id: 'V_EmailRecipientID',
		elq_field_contact_email: 'C_EmailAddress',
		elq_field_visitor_email: 'V_Email_Address',
		elq_field_visitor_firstname: 'V_First_Name',
		elq_field_visitor_lastname: 'V_Last_Name',
		notme_link_id: null,
		notme_fields_class: null,
		notme_message: 'Not {name}? Click here.',
		notme_message_noname: 'Not your details below? Click here.'
	};
    
	// PUBLIC PROPERTIES
	this.user_elq_email = '';
	this.user_elq_firstname = '';
	this.user_elq_lastname = '';

    // PRIVATE PROPERTIES
    var url_vars;
    var visitor_elq_id = null;
	var field_mappings = [];
	var callback_queue = [];
	var controller = this;
	var fields_populated = false;
	
	
    // INIT
	
	// Merge default with params
	params = merge(defaults, params);
	
    // Set Eloqua site ID and load Eloqua scripts
    _elqQ.push(['elqSetSiteId', params.elq_site_id]);
    (function () {
        function async_load() {
            var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true;
            s.src = 'http://img.en25.com/i/elqCfg.min.js';
            var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x);
        }
        if (window.addEventListener) window.addEventListener('DOMContentLoaded', async_load, false);
        else if (window.attachEvent) window.attachEvent('onload', async_load);
    })();
	
    // Store URL vars
    store_url_vars ();
	
    // Store visitor's Eloqua ID
    store_visitor_elq_id ();
	
    // Set window function to handle Eloqua callbacks
    window.SetElqContent = eloqua_callback;

    
    // PUBLIC METHODS
	
    // Fire an Eloqua pageview
    this.fire_pageview = function(url) {
		// If a URL was set fire with this, otherwise use default call which will use current page URL
        try {
            if (typeof url === 'undefined') {
                _elqQ.push(['elqTrackPageView']);   
            }
            else {
                _elqQ.push(['elqTrackPageView', url]); 
            }
            log("TRACKING: Eloqua pageview fired");
        } catch (e) {
            log("TRACKING: Could not fire Eloqua pageview: " + e);
        }
    }
	
	// Fires a series of lookups to identify the contact and retrieve their data from the web lookup
	this.find_eloqua_contact = function () {
		// Queue callback action for return of lookup
		callback_queue.push(handle_visitor_lookup);
		// Start with an recipient lookup if ID was supplied in URL
		if (visitor_elq_id !== null) {
			lookup_visitor_by_recipient_id();
		}
		// If no visitor ID, attempt to do a visitor lookup with an Eloqua cookie
		else {
			lookup_visitor_by_cookie();
		}
	}
	
	// Adds an element to field map
	this.add_field_mapping = function(mapping) {
		field_mappings.push(mapping);
	}
	
	// Adds a queued item to be run after the next Eloqua callback is received
	this.add_queue_action = function(action) {
		callback_queue.push(action);
	}
    
	
    // PRIVATE METHODS
	
    // Safe logging function - checks for console and if present writes out message - otherwise does nothing
    function log (message) {
        if (window.console) {
            console.log(message);
        }
    }
	
    // Gets URL parameters and stores them in object for later use 
    function store_url_vars () {
        url_vars = {};
    	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        	url_vars[key] = value;
		});
    }
	
    // Gets the user's Eloqua ID from the URL and stores it in the object, or null if no ID is present
    function store_visitor_elq_id () {
		log ("INIT: Looking for visitor's Eloqua ID in URL...");
        if (typeof url_vars['elq'] === 'undefined') {
            visitor_elq_id = null;
            log ("INIT: ...no visitor Eloqua ID found in URL");
        }
        else {
            var elq_id = url_vars['elq'];
            elq_id = elq_id.toUpperCase();
            elq_id = elq_id.slice(0,8)+"-"+elq_id.slice(8,12)+"-"+elq_id.slice(12,16)+"-"+elq_id.slice(16,20)+"-"+elq_id.slice(20,32);
            visitor_elq_id = elq_id;
            log ("INIT: ...visitor Eloqua ID found in URL: " + elq_id);
        }
    }
	
	// Merge objects together
	function merge(root){
		for (var i = 1; i < arguments.length; i++) {
			for (var key in arguments[i]) {
				root[key] = arguments[i][key];
			}
		}
		return root;
	}
	
    // Callback for Eloqua lookups
    function eloqua_callback () {
        log("LOOKUP: Eloqua lookup finished");
		log("QUEUE: Processing callback queue");
		var queued_actions = callback_queue;
		callback_queue = [];
		
		// Loop through queued actions, execute; add back onto the queue if an action failed
		for (action_index in queued_actions) {
			var action_num = parseInt(action_index) + 1;
			log("QUEUE: Executing queued action " + action_num);
			if(queued_actions[action_index]()) {
				log("QUEUE: Queued action " + action_num + " executed successfully, removed from queue");
			}
			else {
				log("QUEUE: Queued action " + action_num + " failed, leaving in queue");
				callback_queue.push(queued_actions[action_index])
			}
		}
		
		log("QUEUE: All callback actions processed");
		
		// Update user details
		update_user_details ();
		
		// Process field mappings
		populate_mapped_fields();
    }
	
	// Eloqua lookups, only run if they find a relevant lookup key
    function lookup_visitor_by_cookie () {
		if (params.elq_visitor_lookup_key != '') {
			_elqQ.push(['elqDataLookup', escape(params.elq_visitor_lookup_key),'']); 
			log("LOOKUP: Visitor lookup sent using Eloqua cookie");
		}
		else {
			log("LOOKUP: A visitor lookup cannot be performed as no lookup key was defined");
		}
    }
    function lookup_visitor_by_recipient_id () {
		if (params.elq_recipient_id_lookup_key != '') {
			_elqQ.push(['elqDataLookup', escape(params.elq_recipient_id_lookup_key),'<' + params.elq_field_email_rec_id + '>' + visitor_elq_id + '</' + params.elq_field_email_rec_id + '>']);
			log("LOOKUP: Visitor lookup sent using email recipient id: " + visitor_elq_id);
		}
		else {
			log("LOOKUP: A visitor lookup cannot be performed as no lookup key was defined");
		}
    }
    function lookup_contact_by_email () {
		if (params.elq_contact_lookup_key != '') {
			_elqQ.push(['elqDataLookup', escape(params.elq_contact_lookup_key),'<' + params.elq_field_contact_email + '>' + controller.user_elq_email + '</' + params.elq_field_contact_email + '>']);
			log("LOOKUP: Contact lookup sent using email address: " + controller.user_elq_email);
		}
		else {
			log("LOOKUP: A visitor lookup cannot be performed as no lookup key was defined");
		}
    }
	
	// Receives Eloqua visitor lookup and attempts to lookup contact
	function handle_visitor_lookup () {
		if (GetElqContentPersonalizationValue(params.elq_field_visitor_email) != '') {
			log("LOOKUP: Found visitor email address: " + GetElqContentPersonalizationValue(params.elq_field_visitor_email));
			controller.user_elq_email = GetElqContentPersonalizationValue(params.elq_field_visitor_email).trim();
			lookup_contact_by_email();
			callback_queue.push(handle_contact_lookup);
			return true;
		}
		else {
			return false;
		}
	}
	
	// Receives Eloqua contact lookup
	function handle_contact_lookup () {
		if (GetElqContentPersonalizationValue(params.elq_field_contact_email) != '') {
			log("LOOKUP: Found contact email address: " + GetElqContentPersonalizationValue(params.elq_field_contact_email));
			return true;
		}
		else {
			return false;
		}
	}
	
	// Runs through mapping and maps any available fields
	function populate_mapped_fields () {
		log("MAPPING: Starting field mapping");
		// Loop through all mappings
		for (mapping_set in field_mappings) {
			for (mapping_element in field_mappings[mapping_set]) {
				var mapping_elq_field = field_mappings[mapping_set][mapping_element];
				var element = document.getElementById(mapping_element);
				var field_value = GetElqContentPersonalizationValue(mapping_elq_field);
				
				// Check that field exists
				if (element == null) {
					log("MAPPING: Could not populate element '" + mapping_element + "' as it does not exist");
				}
				else if (element.value != '') {
					log("MAPPING: Could not populate element '" + mapping_element + "' as it already contained a value");
				}
				else if (field_value == '') {
					log("MAPPING: Could not populate element '" + mapping_element + "' as the Eloqua field '" + mapping_elq_field + "' contained no data");
				}
				else {
					fields_populated = true;
					element.value = field_value;
					log("MAPPING: Mapped element '" + mapping_element + "' with Eloqua field '" + mapping_elq_field + "'");
				}
			}
		}
		log("MAPPING: Finished field mapping");
		
		// Update notme link
		update_notme_link ();
	}
	
	// Update locally stored user details
	function update_user_details () {
		if (controller.user_elq_email == '' && GetElqContentPersonalizationValue(params.elq_field_visitor_email) != '') {
			controller.user_elq_email = GetElqContentPersonalizationValue(params.elq_field_visitor_email);
		}
		if (controller.user_elq_firstname == '' && GetElqContentPersonalizationValue(params.elq_field_visitor_firstname) != '') {
			controller.user_elq_firstname = GetElqContentPersonalizationValue(params.elq_field_visitor_firstname);
		}
		if (controller.user_elq_lastname == '' && GetElqContentPersonalizationValue(params.elq_field_visitor_lastname) != '') {
			controller.user_elq_lastname = GetElqContentPersonalizationValue(params.elq_field_visitor_lastname);
		}
	}
	
	// Add not me link
	function update_notme_link () {
		
		// Get link element
		var notme_link_element = $('#' + params.notme_link_id);
		
		// Get Eloqua vars
		var first_name = controller.user_elq_firstname;
		var last_name = controller.user_elq_lastname;
		var email = controller.user_elq_email;
		
		// Check if we have both first name and last name
		if (first_name != '' && last_name !='') {
			name = first_name + ' ' + last_name;
			display_link = true;
		}
		// Check if we have first name
		else if (first_name != '') {
			name = first_name;
		}
		// Other use a generic message
		else {
			name = '';
		}
		// Check if some fields have been populated
		if (fields_populated && params.notme_link_id != null && params.notme_fields_class != null) {
			if (name != '') {
				var message = params.notme_message.replace("{name}", name);
			}
			else {
				var message = params.notme_message_noname;
			}
		
			log("MAPPING: Updating not me link");
			notme_link_element.find('*').remove();
			var link = $('<a href="">' + message + '</a>').appendTo(notme_link_element).click(remove_user_details);
		}
	}
	
	// Remove user details from form
	function remove_user_details () {
		controller.user_elq_email = '';
		controller.user_elq_firstname = '';
		controller.user_elq_lastname = '';
	
		$('.' + params.notme_fields_class).val('');
		$('#' + params.notme_link_id).find('a').remove();
    
    //Flush out old user cookie
    _elqQ.push(['elqVisitorGuid'," "]);
    _elqQ.push(['elqTrackPageView']);

    
		return false;
	}
}