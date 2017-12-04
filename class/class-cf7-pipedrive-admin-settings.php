<?php

/**
* Admin Settings Pages
*
* @since 1.3
*/
class Cf7_Pipedrive_Admin_Settings {

	/**
	 * Instance of this class.
	 *
	 * @since 1.3
	 * 
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Stores CF7 Pipedrive API key
	 *
	 * @since 1.3
	 * 
	 * @var string
	 */
	public $cf7_pipedrive_api_key = '';

	/**
	 * Stores if contact form 7 is installed
	 *
	 * @since 1.3
	 * 
	 * @var boolean
	 */
	public $cf7_installed = false;

	/**
	 * Stores pipedrive API Object
	 *
	 * @since 1.3
	 * 
	 * @var object
	 */
	public $pipedrive = null;

	/**
	 * Stores the active tab in the admin
	 *
	 * @since 1.3
	 * 
	 * @var object
	 */
	public $active_tab = '';

	
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {
		
		// Define some variables
		$this->cf7_forms 							= $this->get_cf7_forms();
		$this->cf7_pipedrive_api_key 	= get_option( 'cf_pipedrive_api_key' );
		$this->cf7_pipedrive_forms 		= ( false != get_option( 'my_cf7_pipedrive_forms' ) ? get_option( 'my_cf7_pipedrive_forms' ) : array() );
		$this->cf7_pipedrive_debug_mode = ( false != get_option( 'cf7_pipedrive_debug_mode' ) ? get_option( 'cf7_pipedrive_debug_mode' ) : 'no');
		$this->pipedrive = new Cf7_Pipedrive_Pipedrive_API($this->cf7_pipedrive_api_key);
		if(class_exists('WPCF7_ContactForm')) {
			$this->cf7_installed = true;
		}

		// If it is not installed give admin warning
		if ( !$this->cf7_installed ) {
			add_action('admin_notices', array($this, 'no_cf7_admin_notice'));
		}

		// If there is no API Key set, send a warning
		if($this->cf7_pipedrive_api_key == '') {
			add_action('admin_notices', array($this, 'no_api_key_admin_notice'));
		}

		if($this->cf7_installed && is_admin()) {
			// Add the settings page and menu item.
			add_action( 'admin_menu', array( $this, 'plugin_admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );
			// Add an action link pointing to the settings page.
			add_filter( 'plugin_action_links_' . CF7_PIPEDRIVE_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
		}

	}

	/**
	 * Return notice string
	 *
	 * @since 1.3
	 * 
	 * @return string admin notice if CF7 is not installed
	 */
	function no_api_key_admin_notice(){
		echo '<div class="notice notice-warning is-dismissible">
			<p>Please enter your Pipedrive API in the <a href="' . admin_url( 'admin.php?page=cf7_pipedrive' ) . '">settings</a> to use Contact Form 7 Pipedrive Integration.</p>
			</div>';
	}

	/**
	 * Return notice string
	 *
	 * @since 1.3
	 * 
	 * @return string admin notice if no API key entered
	 */
	function no_cf7_admin_notice(){
		echo '<div class="notice notice-warning is-dismissible">
			<p>It looks like Contact Form 7 is not installed and is required for CF7 Pipedrive Deal on Submission. Please download CF7 to use this plugin.</p>
			</div>';
	}

	/**
	 * Register the settings menu for this plugin into the WordPress Settings menu.
	 * 
	 * @since 1.3
	 */
	public function plugin_admin_menu() {
		add_submenu_page( 'wpcf7', __( 'Pipedrive Integration Settings', 'cf7-pipedrive' ), __( 'Pipedrive Integration', 'cf7-pipedrive' ), 'manage_options', CF7_PIPEDRIVE_PLUGIN_SLUG, array( $this, 'cf7_pipedrive_options' ) );
	}

	/**
	 * Enqueue Admin Scripts
	 * 
	 * @since 1.3
	 */
	public function admin_enqueue_scripts($hook) {
		if(isset($_GET['page']) && $_GET['page'] == 'cf7_pipedrive') {
			wp_enqueue_script( 'cf7_pipedrive_admin_js', plugins_url( '../js/admin.js', __FILE__ ), array(), rand(0,999) );
		}
	}

	/**
	 * Add settings action link to the plugins page.
	 * 
	 * @param array $links
	 *
	 * @since 1.3
	 *
	 * @return array Plugin settings links
	 */
	public function add_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . CF7_PIPEDRIVE_PLUGIN_SLUG ) . '">' . __( 'Settings', CF7_PIPEDRIVE_PLUGIN_SLUG ) . '</a>'
			),
			$links
		);	
	}

	/**
	 * Render the settings page for this plugin.
	 * 
	 * @since 1.3
	 */
	public function cf7_pipedrive_options() {
		if ( ! current_user_can( 'edit_posts' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// Set the active tab
		$this->active_tab = 'general_settings';
		$contact_form_tab = false;

		if( isset( $_GET[ 'tab' ] ) ) {
			$this->active_tab = $_GET[ 'tab' ];
		}
		if(strpos($this->active_tab, 'form_') !== false) {
			$contact_form_tab = true;

			// Assign variables for saving and displaying contact tabs
			$form_id = str_replace('form_', '', $this->active_tab);
			$form_fields = $this->get_cf7_form_fields($form_id);

			$person_fields = $this->get_person_fields();
			$organization_fields = $this->get_organization_fields();
			$deal_fields = $this->get_deal_fields();

			$person_values = array();
			$organization_values = array();
			$deal_values = array();

			$pipedrive_users = $this->pipedrive->get_users();
			$pipedrive_stages = $this->pipedrive->get_stages();
			$pipedrive_pipelines = $this->pipedrive->get_pipelines();

			$attach_to_person = get_post_meta( $form_id, 'attach_to_person', true );
			$attach_to_organization = get_post_meta( $form_id, 'attach_to_organization', true );

			foreach ($person_fields as $key => $field) {
				$person_values[$key] = get_post_meta($form_id, 'person_'.$key, true);
			}
			foreach ($organization_fields as $key => $field) {
				$organization_values[$key] = get_post_meta($form_id, 'organization_'.$key, true);	
			}
			foreach ($deal_fields as $key => $field) {
				$deal_values[$key] = get_post_meta($form_id, 'deal_'.$key, true);
			}
		}

		// No point in showing the form without an api key
		$show_full_form = false;
		if($this->cf7_pipedrive_api_key != '') {
			$show_full_form = true;
		}

		// save a tab if we have a tab, otherwise save the general settings
		if($contact_form_tab && $show_full_form) {
			if ( ! empty( $_POST ) && check_admin_referer( 'cf7_pipedrive', 'save_cf7_pipedrive' ) ) {
				$this->save_cf7_form_settings();
			}
		} else {
			if ( ! empty( $_POST ) && check_admin_referer( 'cf7_pipedrive', 'save_cf7_pipedrive' ) ) {			
				$this->save_general_settings_form();
			}
		}

		// Display the Header
		include(plugin_dir_path( __FILE__ ) . '../templates/settings-header.php');

		// Display the form based on the tab
		if($contact_form_tab && $show_full_form) {
			include(plugin_dir_path( __FILE__ ) . '../templates/contact-form-settings-form.php');
		} else {
			include(plugin_dir_path( __FILE__ ) . '../templates/general-settings-form.php');
		}

		// Display the footer
		include(plugin_dir_path( __FILE__ ) . '../templates/settings-footer.php');

	}

	protected function save_cf7_form_settings() {

		// Get the fields for each object
		$person_fields = $this->get_person_fields();
		$deal_fields = $this->get_deal_fields();
		$organization_fields = $this->get_organization_fields();

		// Sanity check
		if(isset($_POST['form_id'])) {
			$form_id = $_POST['form_id'];
		} else {
			trigger_error('PipeDrive Error: No Form ID with contact form saved data');
			return;
		}

		// Person fields 
		$person_fields_active = false;
		foreach($person_fields as $key => $field) {
			if(isset($_POST['person_'.$key])) {
				update_post_meta($form_id, 'person_'.$key, $_POST['person_'.$key]);
				if($_POST['person_'.$key] != '') {
					$person_fields_active = true;
				}
			}
		}
		// Organization Fields
		$organization_fields_active = false;
		foreach($organization_fields as $key => $field) {
			if(isset($_POST['organization_'.$key])) {
				update_post_meta($form_id, 'organization_'.$key, $_POST['organization_'.$key]);
				if($_POST['organization_'.$key] != '') {
					$organization_fields_active = true;
				}
			}
		}
		// Deal Fields
		foreach($deal_fields as $key => $field) {
			if(isset($_POST['deal_'.$key])) {
				update_post_meta($form_id, 'deal_'.$key, $_POST['deal_'.$key]);
			}
		}

		// Person and org attachments
		if(isset($_POST['attach_to_person']) && $person_fields_active) {
			if($_POST['attach_to_person'] == 'yes') {
				$attach_to_person = sanitize_text_field( $_POST['attach_to_person'] );
				update_post_meta($form_id, 'attach_to_person', $attach_to_person );
			}
		} else {
			update_post_meta($form_id, 'attach_to_person', 'no' );
		}
		if(isset($_POST['attach_to_organization']) && $organization_fields_active) {
			if($_POST['attach_to_organization'] == 'yes') {
				$attach_to_organization = sanitize_text_field( $_POST['attach_to_organization'] );
				update_post_meta($form_id, 'attach_to_organization', $attach_to_organization );
			}
		} else {
			update_post_meta($form_id, 'attach_to_organization', 'no' );
		}

		/*
		 * Delete any left over meta data if a user removes a filter
		 */

		// Get the data we need first
		$form_meta = get_post_meta($form_id);
		$all_fields = array();
		$all_fields['organization'] = $this->get_organization_fields();
		$all_fields['person'] = $this->get_person_fields();
		$all_fields['deal'] = $this->get_deal_fields();
		// unset anything that isn't a person, org, or deal field
		foreach($form_meta as $key => $meta) {
			if(preg_match('/(deal_|organization_|person_)/', $key) === 0)
				unset($form_meta[$key]);
		}
		// Unset set anything that is a field
		foreach($all_fields as $type_key => $type_of_field) {
			foreach($type_of_field as $individual_field) {
				$potential_post_key = $type_key . '_' . $individual_field['api_key'];
				if(isset($form_meta[$potential_post_key])) {
					unset($form_meta[$potential_post_key]);
				}
			}
		}
		// Delete anything left over
		foreach($form_meta as $key => $value) {
			delete_post_meta( $form_id, $key );
		}

		wp_redirect( admin_url( 'admin.php?page='.$_GET['page'].'&tab='.$this->active_tab.'&updated=1' ) );

	}

	protected function save_general_settings_form() {
		//add or update cf7 pipedrive API Key
		$pipedrive_api_key = sanitize_text_field( $_POST['cf7_pipedrive_api_key'] );
		if ( $this->cf7_pipedrive_api_key !== false ) {
			update_option( 'cf_pipedrive_api_key', $pipedrive_api_key );
		} else {
			add_option( 'cf_pipedrive_api_key', $pipedrive_api_key, null, 'no' );
		}

		//add or update cf7 pipedrive CF7 Forms
		if ( $this->cf7_forms !== false ) {
			if( isset($_POST['cf7_pipedrive_forms']) && is_array($_POST['cf7_pipedrive_forms']) ) {
				$cf7_pipedrive_forms = $_POST['cf7_pipedrive_forms'];
				$cf7_pipedrive_forms = array_map('absint', $cf7_pipedrive_forms);
				if(is_array($cf7_pipedrive_forms)) {
					update_option( 'my_cf7_pipedrive_forms', $cf7_pipedrive_forms );
					$this->cf7_pipedrive_forms = get_option('my_cf7_pipedrive_forms');
				}
			} else {
				update_option( 'my_cf7_pipedrive_forms', array() );
				$this->cf7_pipedrive_forms = array();
			}
		}

		// Add or update debug mode
		if(isset($_POST['cf7_pipedrive_debug_mode'])) {
			if($_POST['cf7_pipedrive_debug_mode'] == 'yes') {
				$cf7_pipedrive_debug_mode = sanitize_text_field( $_POST['cf7_pipedrive_debug_mode'] );
				update_option( 'cf7_pipedrive_debug_mode', $cf7_pipedrive_debug_mode );
				$this->cf7_pipedrive_debug_mode = get_option('cf7_pipedrive_debug_mode');
			}
		} else {
			update_option( 'cf7_pipedrive_debug_mode', 'no' );
			$this->cf7_pipedrive_debug_mode = 'no';
		}

		wp_redirect( admin_url( 'admin.php?page='.$_GET['page'].'&updated=1' ) );

	}

	public function get_deal_fields() {
		$desired_fields = array(
			'title' => array(
											'api_key' => 'title',
											'display_name' => 'Deal Title',
											),
			'user_id' => array(
											'api_key' => 'user_id',
											'display_name' => 'Deal Owner', // This should come from pipedrive data
										),
			'value' => array(
											'api_key' => 'value',
											'display_name' => 'Deal Value',
											),
			'stage_id' => array(
											'api_key' => 'stage_id',
											'display_name' => 'Deal Stage', // This should come from pipedrive data
										),
			'status' => array(
											'api_key' => 'status',
											'display_name' => 'Deal Status',
											),
			'probability' => array(
											'api_key' => 'probability',
											'display_name' => 'Probability',
											),
		);
		$desired_fields = apply_filters('cf7_pipedrive_deal_fields', $desired_fields);

		$pipedrive_deal_fields = $this->pipedrive->get_deal_fields();

		$deal_fields = array();
		foreach($pipedrive_deal_fields as $pipedrive_deal_field) {
			if(isset($desired_fields[$pipedrive_deal_field['key']])) {
				$deal_fields[$pipedrive_deal_field['key']] = $desired_fields[$pipedrive_deal_field['key']];
			}
		}

		return $deal_fields;

	}

	public function get_person_fields() {
		$desired_fields = array(
			'name' => array(
									'api_key' => 'name',
									'display_name' => 'Person Name'
									),
			'phone' => array(
									'api_key' => 'phone',
									'display_name' => 'Person Phone Number'
									),
			'email' => array(
									'api_key' => 'email',
									'display_name' => 'Person Email'
									),
			'owner_id' => array(
									'api_key' => 'owner_id',
									'display_name' => 'Person Owner'
									),
		);
		$desired_fields = apply_filters('cf7_pipedrive_person_fields', $desired_fields);

		$pipedrive_person_fields = $this->pipedrive->get_person_fields();

		$person_fields = array();
		foreach($pipedrive_person_fields as $pipedrive_person_field) {
			if(isset($desired_fields[$pipedrive_person_field['key']])) {
				$person_fields[$pipedrive_person_field['key']] = $desired_fields[$pipedrive_person_field['key']];
			}
		}

		return $person_fields;

	}

	public function get_organization_fields() {

		$desired_fields = array(
			'name' => array(
			  'api_key' => 'name',
		  	'display_name' => 'Name',
			),
			'owner_id' => array(
			  'api_key' => 'owner_id',
			  'display_name' => 'Owner',
			),
			'people_count' => array(
			  'api_key' => 'people_count',
			  'display_name' => 'Number of People',
			),
			'address' => array(
			  'api_key' => 'address',
			  'display_name' => 'Address',
			),
			'address_subpremise' => array(
			  'api_key' => 'address_subpremise',
			  'display_name' => 'Apartment/suite no',
			),
			'address_street_number' => array(
			  'api_key' => 'address_street_number',
			  'display_name' => 'House number',
			),
			'address_route' => array(
			  'api_key' => 'address_route',
			  'display_name' => 'Street/road name',
			),
			'address_sublocality' => array(
			  'api_key' => 'address_sublocality',
			  'display_name' => 'District/sublocality',
			),
			'address_locality' => array(
			  'api_key' => 'address_locality',
			  'display_name' => 'City/town/village/locality',
			),
			'address_admin_area_level_1' => array(
			  'api_key' => 'address_admin_area_level_1',
			  'display_name' => 'State/county',
			),
			'address_admin_area_level_2' => array(
			  'api_key' => 'address_admin_area_level_2',
			  'display_name' => 'Region',
			),
			'address_country' => array(
			  'api_key' => 'address_country',
			  'display_name' => 'Country',
			),
			'address_postal_code' => array(
			  'api_key' => 'address_postal_code',
			  'display_name' => 'ZIP/Postal code',
			),
			'address_formatted_address' => array(
			  'api_key' => 'address_formatted_address',
			  'display_name' => 'Full/combined address',
			),
		);
		$desired_fields = apply_filters('cf7_pipedrive_organization_fields', $desired_fields);

		$pipedrive_organization_fields = $this->pipedrive->get_organization_fields();

		$organization_fields = array();
		foreach($pipedrive_organization_fields as $pipedrive_organization_field) {
			if(isset($desired_fields[$pipedrive_organization_field['key']])) {
				$organization_fields[$pipedrive_organization_field['key']] = $desired_fields[$pipedrive_organization_field['key']];
			}
		}

		return $organization_fields;

	}

	/**
	 * Returns list of Popup Place
	 * 
	 * @since 1.0
	 *
	 * @return array Popup Place
	 */
	public function get_cf7_forms() {

		// Get all the contact forms
		$args = array(
			'posts_per_page' => 50,
			'orderby' => 'title',
			'order' => 'ASC',
			);

		$items = WPCF7_ContactForm::find( $args );
		foreach ($items as $contact_form) {
			$this->cf7_forms[$contact_form->id()] = $contact_form->title();
		}
		return $this->cf7_forms;

	}

	public function get_cf7_form_fields($form_id) {
		$contact_form = WPCF7_ContactForm::get_instance($form_id);
		$manager = WPCF7_FormTagsManager::get_instance();

		$scanned_form_tags = $manager->scan( $contact_form->prop( 'form' ) );
		// $filtered_form_tags = $manager->filter( $scanned_form_tags, NULL );

		return $scanned_form_tags;
	}

}