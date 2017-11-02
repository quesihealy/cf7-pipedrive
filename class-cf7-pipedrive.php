<?php
/**
 * CF7 Pipedrive Class
 *
 * @package   cf7_pipedrive
 * @author 		Lucas Healy <lucasbhealy@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.everythinghealy.com/cf7-pipedrive
 */

/**
 * @package cf7_pipedrive
 * @author  Lucas Healy <lucasbhealy@gmail.com>
 */
class Cf7_Pipedrive {
	
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 * 
	 * @since 1.0
	 *
	 * @var string
	 */
	const VERSION = '1.3';

	/**
	 * Instance of this class.
	 *
	 * @since 1.0
	 * 
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by loading scripts and styles for admin page
	 *
	 * @since 1.0
	 */
	private function __construct() {

		// Add Classes
		require_once plugin_dir_path( __FILE__ ) . '/class/class-cf7-pipedrive-admin-settings.php';
		require_once plugin_dir_path( __FILE__ ) . '/class/class-cf7-pipedrive-pipedrive-api.php';

		// Load Admin Settings
		$admin_settings = Cf7_Pipedrive_Admin_Settings::get_instance();

		// Load front end function
		if( $admin_settings->cf7_installed && $admin_settings->cf7_pipedrive_api_key != '' ) {
			$this->pipedrive = new Cf7_Pipedrive_Pipedrive_API($admin_settings->cf7_pipedrive_api_key);
			add_action( 'wpcf7_mail_sent', array( $this, 'send_to_pipedrive' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0
	 * 
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * Return boolean
	 *
	 * @since 1.0
	 * 
	 * @return boolean If a deal was created
	 */
	public function send_to_pipedrive($submission) {
		$cf7_sends_deal = false;
		if(in_array($submission->id(), $admin_settings->cf7_pipedrive_forms)) {
			$cf7_sends_deal = true;
		}

		if($cf7_sends_deal) {
			$this->process_submission($submission);
			return $cf7_sends_deal;
		} else {
			return $cf7_sends_deal;
		}
	}

	/**
	 * Print popup html code
	 *	 
	 * @since 1.0
	 */
	public function process_submission($submission){
		
		// Get Values from form submission
		$submission_values_added = $this->set_submission_values();

		if($submission_values_added == false) {
			if($admin_settings->cf7_pipedrive_debug_mode == 'yes') {
				trigger_error('PipeDrive Error: Could not add submission values');
			}
			return false;
		}

		// @TODO: Set it up so orgs can be added
		$org_id = false; 

		// if the organization was added successfully add the person and link it to the organization - But for now I'm leaving out organizations.
		// Sorry. Good news is if you're a developer all the code to add an org is here.
		if ($org_id || 0 == 0) {
			// $person['org_id'] = $org_id;
			// try adding a person and get back the ID
			$person_id = $this->pipedrive->add_person($this->person);

			// if the person was added successfully add the deal and link it to the organization and the person
			if ($person_id) {
			 
				// $this->deal['org_id'] = $org_id; // Not yet
				$this->deal['person_id'] = $person_id;
				// try adding a person and get back the ID
				$deal_id = $this->pipedrive->add_deal($this->deal);

				if ($deal_id) {
					// echo "Deal was added successfully!";
					return true;
				}

			} else {
				// echo "There was a problem with adding the person!";
				return false;
			}
		 
		} else {
			// echo "There was a problem with adding the organization!";
			return false;
		}

	}

	public function set_submission_values() {

		// If no form ID is available then lets get out.
		if(!isset($_POST['_wpcf7']) && intval($_POST['_wpcf7']))
			return false;

		$submitted_form_id = intval($_POST['_wpcf7']);

		// main data about the organization
		$this->organization = array(
			// I'm keeping this as so for now. Maybe add the functionality for organization later.
		);

		// main data about the person. org_id is added later dynamically
		$person_name = 'Wordpress CF7 Person';
		$person_name_field = get_option('cf7_pipedrive_field_name_'.$submitted_form_id, '');
		if(isset($_POST[$person_name_field])) {
			$person_name = sanitize_text_field( $_POST[$person_name_field] );
		}
		$person_email = '';
		$person_email_field = get_option('cf7_pipedrive_field_email_'.$submitted_form_id, '');
		if(isset($_POST[$person_email_field])) {
			$person_email = sanitize_text_field( $_POST[$person_email_field] );
		}
		$person_phone = '';
		$person_phone_field = get_option('cf7_pipedrive_field_phone_'.$submitted_form_id, '');
		if(isset($_POST[$person_phone_field])) {
			$person_phone = sanitize_text_field( $_POST[$person_phone_field] );
		}

		if($person_name == '') {
			if($admin_settings->cf7_pipedrive_debug_mode == 'yes') {
				trigger_error('PipeDrive Error: Could not find mandatory field person name');
			}
			return false;
		}

		$this->person = array(
			'name' => $person_name,
			'email' => $person_email,
			'phone' => $person_phone,
		);

		$deal_title = 'Wordpress CF7 Submission';
		$deal_title_field = get_option('cf7_pipedrive_field_title_'.$submitted_form_id, '');
		if(isset($_POST[$deal_title_field])) {
			$deal_title = $_POST[$deal_title_field];
		}

		if($deal_title == '') {
			if($admin_settings->cf7_pipedrive_debug_mode == 'yes') {
				trigger_error('PipeDrive Error: Could not find mandatory field deal title');
			}
			return false;
		}

		// main data about the deal. person_id and org_id is added later dynamically
		$this->deal = array(
			'title' => $deal_title,
			'stage_id' => ( null !== $admin_settings->cf7_pipedrive_stage ? $admin_settings->cf7_pipedrive_stage : '' ),
			'user_id' => ( null !== $admin_settings->cf7_pipedrive_user ? $admin_settings->cf7_pipedrive_user : '' ),
		);

		return true;

	}

}
