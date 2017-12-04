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
	const VERSION = '1.3.1';

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
		$this->admin_settings = Cf7_Pipedrive_Admin_Settings::get_instance();

		// Load front end function
		if( $this->admin_settings->cf7_installed && $this->admin_settings->cf7_pipedrive_api_key != '' ) {
			$this->pipedrive = new Cf7_Pipedrive_Pipedrive_API($this->admin_settings->cf7_pipedrive_api_key);
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
		if(in_array($submission->id(), $this->admin_settings->cf7_pipedrive_forms)) {
			$cf7_sends_deal = true;
		}
		if($cf7_sends_deal) {
			$this->process_submission($submission);
		}
	}

	/**
	 * Print popup html code
	 *	 
	 * @since 1.0
	 */
	public function process_submission($submission){

		// If no form ID is available then lets get out.
		if(!isset($_POST['_wpcf7']) && intval($_POST['_wpcf7']))
			return false;

		$submission_values = $this->get_submission_values();

		if(!empty($submission_values['organization'])) {
			$organization_id = $this->pipedrive->add_organization($submission_values['organization']);
		}
		if(!empty($submission_values['person'])) {
			$person_id = $this->pipedrive->add_person($submission_values['person']);
		}

		if(isset($submission_values['deal']['org_id']) && $submission_values['deal']['org_id'] == 'yes' && $organization_id) {
			$submission_values['deal']['org_id'] = $organization_id;
		}
		if(isset($submission_values['deal']['person_id']) && $submission_values['deal']['person_id'] == 'yes' && $person_id) {
			$submission_values['deal']['person_id'] = $person_id;
		}

		if(!empty($submission_values['deal'])) {
			$deal_id = $this->pipedrive->add_deal($submission_values['deal']);
		}

	}

	public function get_submission_values() {
		$submission_values = $this->get_default_submission_values();
		$submitted_form_id = intval($_POST['_wpcf7']);
		$form_meta = get_post_meta($submitted_form_id);
		foreach ($form_meta as $key => $meta) {
			if(isset($meta[0]) && $meta[0] != '') {
				$data = $meta[0];
				if(isset($_POST[$data])) {
				
					if(strpos($key, 'person_') === 0) {
						$pipedrive_key = str_replace('person_', '', $key);
						if($_POST[$data] != '') {
							$submission_values['person'][$pipedrive_key] = $_POST[$data];
						}
					}
					if(strpos($key, 'organization_') === 0) {
						$pipedrive_key = str_replace('organization_', '', $key);
						if($_POST[$data] != '') {
							$submission_values['organization'][$pipedrive_key] = $_POST[$data];
						}
					}
					if(strpos($key, 'deal_') === 0) {
						$pipedrive_key = str_replace('deal_', '', $key);
						if($_POST[$data] != '') {
							$submission_values['deal'][$pipedrive_key] = $_POST[$data];
						}
					}

				}
			}
		}

		// People to attach to deals
		if(isset($form_meta['attach_to_person'][0]) && $form_meta['attach_to_person'][0] == 'yes') {
			$submission_values['deal']['person_id'] = 'yes';
		}
		if(isset($form_meta['attach_to_organization'][0]) && $form_meta['attach_to_organization'][0] == 'yes') {
			$submission_values['deal']['org_id'] = 'yes';
		}

		// Pipedrive Fields that are not in the CF7 form input
		if(isset($form_meta['person_owner_id'][0]) && $form_meta['person_owner_id'][0] != '') {
			$submission_values['person']['owner_id'] = $form_meta['person_owner_id'][0];
		}
		if(isset($form_meta['organization_owner_id'][0]) && $form_meta['organization_owner_id'][0] != '') {
			$submission_values['organization']['owner_id'] = $form_meta['organization_owner_id'][0];
		}
		if(isset($form_meta['deal_user_id'][0]) && $form_meta['deal_user_id'][0] != '') {
			$submission_values['deal']['user_id'] = $form_meta['deal_user_id'][0];
		}
		if(isset($form_meta['deal_stage_id'][0]) && $form_meta['deal_stage_id'][0] != '') {
			$submission_values['deal']['stage_id'] = $form_meta['deal_stage_id'][0];
		}

		return $submission_values;
	}

	public function get_default_submission_values() {
		return array(
			'person' => array(
				'name' => 'Default Person Name',
				),
			'organization' => array(
				'name' => 'Default Org Name',
				),
			'deal' => array(
				'title' => 'Wordpress Form Submission',
				),
		);
	}

}
