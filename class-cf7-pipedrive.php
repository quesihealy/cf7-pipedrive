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
	const VERSION = '1.1.2';

	/**
	 * Unique identifier for plugin.
	 *
	 * @since 1.0
	 * 
	 * @var string
	 */
	protected $plugin_slug = 'cf7_pipedrive';

	/**
	 * Instance of this class.
	 *
	 * @since 1.0
	 * 
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Stores CF7 Pipedrive API key
	 *
	 * @since 1.0
	 * 
	 * @var string
	 */
	protected $cf7_pipedrive_api_key = '';

	/**
	 * Stores CF7 for for creating deals @TODO
	 *
	 * @since 1.0
	 * 
	 * @var string
	 */
	protected $cf7_pipedrive_forms = array();

	/**
	 * Stores CF7 for for creating deals @TODO
	 *
	 * @since 1.0
	 * 
	 * @var string
	 */
	protected $cf7_forms = array();

	/**
	 * Stores CF7 for for creating deals @TODO
	 *
	 * @since 1.1
	 * 
	 * @var string
	 */
	protected $cf7_pipedrive_debug_mode = 'no';

	/**
	 * Stores Pipedrive organization data
	 *
	 * @since 1.0
	 * 
	 * @var array
	 */
	protected $organization;

	/**
	 * Stores Pipedrive person data
	 *
	 * @since 1.0
	 * 
	 * @var array
	 */
	protected $person;

	/**
	 * Stores Pipedrive deal data
	 *
	 * @since 1.0
	 * 
	 * @var array
	 */
	protected $deal;

	/**
	 * Stores pipeline data
	 *
	 * @since 1.0
	 * 
	 * @var array
	 *
	 * @todo create populate_pipelines function
	 */
	protected $pipelines;

	/**
	 * Stores stage data
	 *
	 * @since 1.0
	 * 
	 * @var array
	 */
	protected $stages;
	protected $pipedrive_users;
	protected $cf7_pipedrive_stage;

	/**
	 * Initialize the plugin by loading public scripts and styels or admin page
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// Check if CF7, dependant, is installed
		$cf7_installed = false;
		
		if(class_exists('WPCF7_ContactForm'))
			$cf7_installed = true;

		// If it is not installed give admin warning
		if ( !$cf7_installed ) {
			add_action('admin_notices', array($this, 'no_cf7_admin_notice'));
			return;
		}

		// Define Variations
		$this->cf7_pipedrive_api_key 	= get_option( 'cf_pipedrive_api_key' );
		$this->cf7_forms 							= $this->get_cf7_forms();
		$this->cf7_pipedrive_forms 		= ( false != get_option( 'my_cf7_pipedrive_forms' ) ? get_option( 'my_cf7_pipedrive_forms' ) : array() );
		$this->cf7_pipedrive_stage 		= ( false != get_option( 'my_cf7_pipedrive_stage' ) ? get_option( 'my_cf7_pipedrive_stage' ) : '' );
		$this->cf7_pipedrive_user 		= ( false != get_option( 'my_cf7_pipedrive_user' ) ? get_option( 'my_cf7_pipedrive_user' ) : '' );
		$this->cf7_pipedrive_debug_mode = ( false != get_option( 'cf7_pipedrive_debug_mode' ) ? get_option( 'cf7_pipedrive_debug_mode' ) : 'no');

		// If there is no API Key set, send a warning
		if($this->cf7_pipedrive_api_key == '') {
			add_action('admin_notices', array($this, 'no_api_key_admin_notice'));
		} 

		// Load Admin Functions
		if ( is_admin() ) {
			// Add the settings page and menu item.
			add_action( 'admin_menu', array( $this, 'plugin_admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );
			// Add an action link pointing to the settings page.
			$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . $this->plugin_slug . '.php' );
			add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
		} 

		// Load front end function
		if( $cf7_installed ) {
			add_action( 'wpcf7_mail_sent', array( $this, 'init_pipedrive' ) );
		}
	}

	/**
	 * Return notice string
	 *
	 * @since 1.0
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
	 * @since 1.0
	 * 
	 * @return string admin notice if no API key entered
	 */
	function no_cf7_admin_notice(){
		echo '<div class="notice notice-warning is-dismissible">
			<p>It looks like Contact Form 7 is not installed and is required for CF7 Pipedrive Deal on Submission. Please download CF7 to use this plugin.</p>
			</div>';
	}

	/**
	 * Return boolean
	 *
	 * @since 1.0
	 * 
	 * @return boolean If a deal was created
	 */
	public function init_pipedrive($submission) {
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		$cf7_sends_deal = false;
		if(in_array($submission->id(), $this->cf7_pipedrive_forms)) {
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
	 * Register the settings menu for this plugin into the WordPress Settings menu.
	 * 
	 * @since 1.0
	 */
	public function plugin_admin_menu() {
		add_submenu_page( 'wpcf7', __( 'Pipedrive Integration Settings', 'cf7-pipedrive' ), __( 'Pipedrive Integration', 'cf7-pipedrive' ), 'manage_options', $this->plugin_slug, array( $this, 'cf7_pipedrive_options' ) );
	}

	/**
	 * Add settings action link to the plugins page.
	 * 
	 * @param array $links
	 *
	 * @since 1.0
	 *
	 * @return array Plugin settings links
	 */
	public function add_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);	
	}

	/**
	 * Render the settings page for this plugin.
	 * 
	 * @since 1.0
	 */
	public function cf7_pipedrive_options() {
		if ( ! current_user_can( 'edit_posts' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		if ( ! empty( $_POST ) && check_admin_referer( 'cf7_pipedrive', 'save_cf7_pipedrive' ) ) {
			
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

			//add or update cf7 pipedrive stage
			if ( $this->cf7_pipedrive_stage !== false ) {
				if(isset($_POST['cf7_pipedrive_stage'])) {
					$cf7_pipedrive_stage = absint($_POST['cf7_pipedrive_stage']);
					update_option( 'my_cf7_pipedrive_stage', $cf7_pipedrive_stage );
					$this->cf7_pipedrive_stage = get_option('my_cf7_pipedrive_stage');
				} else {
					update_option( 'my_cf7_pipedrive_stage', array() );
					$this->cf7_pipedrive_stage = array();
				}
			}

			//add or update cf7 pipedrive stage
			if ( $this->cf7_pipedrive_user !== false ) {
				if(isset($_POST['cf7_pipedrive_user'])) {
					$cf7_pipedrive_user = absint( $_POST['cf7_pipedrive_user'] );
					update_option( 'my_cf7_pipedrive_user', $cf7_pipedrive_user );
					$this->cf7_pipedrive_user = $cf7_pipedrive_user;
				} else {
					update_option( 'my_cf7_pipedrive_user', array() );
					$this->cf7_pipedrive_user = array();
				}
			}

			//add or update cf7 fields for each form
			$this->cf7_pipedrive_fields = array();
			foreach ($_POST as $key => $value) {
				if(strpos($key, 'cf7_pipedrive_field') !== false) {
					$option = sanitize_text_field( $_POST[$key] );
					update_option($key, $option);
					$this->cf7_pipedrive_fields[$key] = $value;
				} else {
					update_option( $key, array() );
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

		$show_full_form = false;
		if($this->cf7_pipedrive_api_key != '') {
			$this->populate_stages();
			$this->populate_pipedrive_users();
			$this->populate_cf7_pipedrive_field_values();
			$this->cf7_pipedrive_forms = get_option('my_cf7_pipedrive_forms');
			$show_full_form = true;
		}

		?>
		<div class="wrap">
			<style>
				.pipedrive-field-label {
					width: 400px;
					display: block;
					margin-top: 12px;
				}
				.cf7_pipedrive_field_value {
					display: none;
				}
			</style>
			<h2><?php _e( 'CF7 Pipedrive Settings', 'cf7-pipedrive' );?></h2>
			<p>Have questions, comments, suggestions? This is still in beta and I'd love to hear from you at lucas@everythinghealy.com. I'll read and respond to every e-mail.</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page='.$_GET['page'].'&noheader=true' ) ); ?>" enctype="multipart/form-data">
				<?php wp_nonce_field( 'cf7_pipedrive', 'save_cf7_pipedrive' ); ?>
				<div class="cf7_pipedrive_form">
					<table class="form-table" width="100%">
						<tr>
							<th scope="row"><label for="cf7_pipedrive_api_key"><?php _e( 'Pipedrive API Key', 'cf7-pipedrive' );?></label></th>
							<td><input type="text" name="cf7_pipedrive_api_key" id="cf7_pipedrive_api_key" maxlength="255" size="75" value="<?php echo $this->cf7_pipedrive_api_key; ?>"></td>
						</tr>

					<?php if($show_full_form) : ?>

						<tr>
							<th scope="row"><label for="cf7_pipedrive_form"><?php _e( 'Contact Form 7', 'cf7-pipedrive' );?></label><br/><small>Select the Contact Forms you want to send a deal on submission.</small></label></th>
							<td>
								<?php foreach ( $this->cf7_forms as $form_id => $form_title ): ?>
								<input type="checkbox" name="cf7_pipedrive_forms[]" value="<?php echo $form_id; ?>" <?php if(in_array($form_id, $this->cf7_pipedrive_forms)) echo 'checked="checked"';?> ><label for="<?php echo $form_title; ?>"><?php echo $form_title; ?></label><br>
								<?php endforeach;?>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="cf7_pipedrive_form_fields"><?php _e( 'Contact Form 7 Fields', 'cf7-pipedrive' );?></label><br/><small>Select the Fields you want included in the deal.</small></label></th>
							<td>
								<label class='pipedrive-field-label'>Person Name:</label>
								<?php 
								foreach ( $this->cf7_forms as $form_id => $form_title ): ?>
									<div class='cf7_pipedrive_field_value field_value_<?php echo $form_id; ?>'>
										<span><?php echo $form_title; ?>:</span>
										<select name="cf7_pipedrive_field_name_<?php echo $form_id; ?>" id="cf7_pipedrive_field_name_<?php echo $form_id; ?>">
											<option value="cf7-user">-</option>
											<?php 
											$form_fields = $this->populate_pipedrive_form_fields($form_id);
											foreach($form_fields as $form_field) : 
												if($form_field->name != '') : 
													$name_value = 'cf7_pipedrive_field_name_'.$form_id ?>
													<option value="<?php echo $form_field->name; ?>" <?php selected( $this->$name_value, $form_field->name ); ?>><?php echo $form_field->name; ?></option>
													<?php
												endif;
											endforeach;
											?>
										</select>
									</div>
								<?php endforeach;?>
								<br/>
								<label class='pipedrive-field-label'>Person Email:</label>
								<?php 
								foreach ( $this->cf7_forms as $form_id => $form_title ): ?>
									<div class='cf7_pipedrive_field_value field_value_<?php echo $form_id; ?>'>
										<span><?php echo $form_title; ?>:</span>
										<select name="cf7_pipedrive_field_email_<?php echo $form_id; ?>" id="cf7_pipedrive_field_email_<?php echo $form_id; ?>">
											<option value="">-</option>
											<?php 
											$form_fields = $this->populate_pipedrive_form_fields($form_id);
											foreach($form_fields as $form_field) : 
												if($form_field->name != '') : 
													$email_value = 'cf7_pipedrive_field_email_'.$form_id; ?>
													<option value="<?php echo $form_field->name; ?>" <?php selected( $this->$email_value, $form_field->name ); ?>><?php echo $form_field->name; ?></option>
													<?php
												endif;
											endforeach;
											?>
										</select>
									</div>
								<?php endforeach;?>
								<br/>
								<label class='pipedrive-field-label'>Person Phone:</label>
								<?php 
								foreach ( $this->cf7_forms as $form_id => $form_title ): ?>
									<div class='cf7_pipedrive_field_value field_value_<?php echo $form_id; ?>'>
										<span><?php echo $form_title; ?>:</span>
										<select name="cf7_pipedrive_field_phone_<?php echo $form_id; ?>" id="cf7_pipedrive_field_phone_<?php echo $form_id; ?>">
											<option value="">-</option>
											<?php 
											$form_fields = $this->populate_pipedrive_form_fields($form_id);
											foreach($form_fields as $form_field) : 
												if($form_field->name != '') : 
													$name_value = 'cf7_pipedrive_field_phone_'.$form_id; ?>
													<option value="<?php echo $form_field->name; ?>" <?php selected( $this->$name_value, $form_field->name ); ?>><?php echo $form_field->name; ?></option>
													<?php
												endif;
											endforeach;
											?>
										</select>
									</div>
								<?php endforeach;?>
								<br/>
								<label class='pipedrive-field-label'>Deal Title:</label>
								<?php 
								foreach ( $this->cf7_forms as $form_id => $form_title ): ?>
									<div class='cf7_pipedrive_field_value field_value_<?php echo $form_id; ?>'>
										<span><?php echo $form_title; ?>:</span>
										<select name="cf7_pipedrive_field_title_<?php echo $form_id; ?>" id="cf7_pipedrive_field_title_<?php echo $form_id; ?>">
											<option value="">-</option>
											<?php 
											$form_fields = $this->populate_pipedrive_form_fields($form_id);
											foreach($form_fields as $form_field) : 
												if($form_field->name != '') :
													$title_value = 'cf7_pipedrive_field_title_'.$form_id; ?>
													<option value="<?php echo $form_field->name; ?>" <?php selected( $this->$title_value, $form_field->name ); ?>><?php echo $form_field->name; ?></option>
													<?php
												endif;
											endforeach;
											?>
										</select>
									</div>
								<?php endforeach;?>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="cf7_pipedrive_stage"><?php _e( 'Stage', 'cf7-pipedrive' );?></label><br/><small>Select the stage you want the customer to be placed in.</small></label></th>
							<td>
								<select name="cf7_pipedrive_stage" id="cf7_pipedrive_stage">
									<?php foreach ( $this->stages as $stage_data ): ?>
										<option value="<?php echo $stage_data['id']; ?>" <?php selected( $this->cf7_pipedrive_stage, $stage_data['id'] ); ?>><?php echo $stage_data['name']; ?></option>
									<?php endforeach;?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="cf7_pipedrive_user"><?php _e( 'Pipedrive User', 'cf7-pipedrive' );?></label><br/><small>Select the user you want associated with the deal.</small></label></th>
							<td>
								<select name="cf7_pipedrive_user" id="cf7_pipedrive_user">
									<?php foreach ( $this->pipedrive_users as $pipedrive_user ): ?>
										<option value="<?php echo $pipedrive_user['id']; ?>" <?php selected( $this->cf7_pipedrive_user, $pipedrive_user['id'] ); ?>><?php echo $pipedrive_user['name']; ?><?php echo ($pipedrive_user['active_flag'] == false ? ' (Inactive)' : ''); ?></option>
									<?php endforeach;?>
								</select>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="cf7_pipedrive_debug_mode"><?php _e( 'Debug Mode', 'cf7-pipedrive' );?></label><br/><small>No not use on production environments. This may cause the submission message to not return.</small></label></th>
							<td>
								<input type="checkbox" name="cf7_pipedrive_debug_mode" value="yes" <?php if($this->cf7_pipedrive_debug_mode == 'yes') echo 'checked="checked"';?> ><label for="cf7_pipedrive_debug_mode">Check to enable debugging messages.</label><br>
							</td>
						</tr>

					<?php endif; ?>

					</table>

					<p class="submit">
						<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ) ?>" />
					</p>

				</div>
			</form>
			<?php
			$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) );
			?>
		</div>
		<?php
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

	public function populate_stages() {
		$response = $this->make_pipedrive_request('stages', 'get', true);
		if(isset($response['data'])) {
			$this->stages = array();
			foreach ($response['data'] as $data) {
				if($data['name'] != NULL)
					$this->stages[] = $data;
			}
			return;
		}
		return array();
	}

	public function populate_pipedrive_users() {
		$response = $this->make_pipedrive_request('users', 'get', true);
		if(isset($response['data'])) {
			$this->pipedrive_users = array();
			foreach ($response['data'] as $data) {
				if($data['name'] != NULL)
					$this->pipedrive_users[] = $data;
			}
			return;
		}
		return array();
	}

	public function populate_pipedrive_form_fields($form_id) {
		$contact_form = WPCF7_ContactForm::get_instance($form_id);
		$manager = WPCF7_FormTagsManager::get_instance();

		$scanned_form_tags = $manager->scan( $contact_form->prop( 'form' ) );
		// $filtered_form_tags = $manager->filter( $scanned_form_tags, NULL );

		return $scanned_form_tags;
	}

	public function populate_cf7_pipedrive_field_values() {
		if(!isset($this->cf7_forms)) {
			$this->get_cf7_forms();
		}
		$contact_form_field_keys = $this->contact_form_field_keys();

		foreach ($this->cf7_forms as $form_id => $value) {
			foreach($contact_form_field_keys as $field_key) {
				$new_property = $field_key . $form_id;
				$this->$new_property = get_option($new_property);
			}
		}

	}

	public function contact_form_field_keys() {
		return array(
			'cf7_pipedrive_field_name_',
			'cf7_pipedrive_field_email_',
			'cf7_pipedrive_field_phone_',
			'cf7_pipedrive_field_title_',
		);
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since 1.0
	 */
	public function enqueue_styles() {
		// wp_enqueue_style( $this->plugin_slug . '-style', plugins_url( 'css/cf7-pipedrive.css', __FILE__ ), array(), self::VERSION );
		// @TODO Remove this if you're not using the .css
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since 1.0
	 */
	public function enqueue_scripts() {}
	
	public function admin_enqueue_scripts($hook) {
		if('contact_page_cf7_pipedrive' == $hook) {
			wp_enqueue_script( 'cf7_pipedrive_admin_js', plugins_url( '/js/admin.js', __FILE__ ), array(), rand(0,999) );
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
			if($this->cf7_pipedrive_debug_mode == 'yes') {
				trigger_error('PipeDrive Error: Could not add submission values');
			}
			return false;
		}

		// try adding an organization and get back the ID
		// $org_id = make_pipedrive_request('organization');
		$org_id = false; 

		// if the organization was added successfully add the person and link it to the organization - But for now I'm leaving out organizations.
		// Sorry. Good news is if you're a developer all the code to add an org is here.
		if ($org_id || 0 == 0) {
			// $person['org_id'] = $org_id;
			// try adding a person and get back the ID
			$person_id = $this->make_pipedrive_request('persons');

			// if the person was added successfully add the deal and link it to the organization and the person
			if ($person_id) {
			 
				// $this->deal['org_id'] = $org_id; // Not yet
				$this->deal['person_id'] = $person_id;
				// try adding a person and get back the ID
				$deal_id = $this->make_pipedrive_request('deals');

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
			if($this->cf7_pipedrive_debug_mode == 'yes') {
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
			if($this->cf7_pipedrive_debug_mode == 'yes') {
				trigger_error('PipeDrive Error: Could not find mandatory field deal title');
			}
			return false;
		}

		// main data about the deal. person_id and org_id is added later dynamically
		$this->deal = array(
			'title' => $deal_title,
			'stage_id' => ( null !== $this->cf7_pipedrive_stage ? $this->cf7_pipedrive_stage : '' ),
			'user_id' => ( null !== $this->cf7_pipedrive_user ? $this->cf7_pipedrive_user : '' ),
		);

		return true;

	}

	function make_pipedrive_request($type, $request_type = 'post', $return_object = false) {
	
		$url = "https://api.pipedrive.com/v1/".$type."?api_token=" . $this->cf7_pipedrive_api_key;
	
		if($request_type == 'post') {
			// Try type without the plural S if there is no data.
			if(!isset($this->$type) && substr($type, -1) == 's') {
				$type = substr($type, 0, -1);
			}
			$output = wp_remote_post( $url, array( 'body' => $this->$type ) );
		} else {
			$output = wp_remote_get( $url );
		}

		// create an array from the data that is sent back from the API
		if(isset($output['body'])) {
			$result = json_decode($output['body'], 1);
		}

		// Report Errors
		if(isset($result['error'])) {
			if($this->cf7_pipedrive_debug_mode == 'yes') {
				trigger_error('PipeDrive Error: Could not add ' . $type . '. MSG: ' . $result['error']);
			}
		}

		if($return_object) {
			return $result;
		}

		// check if an id came back
		if (!empty($result['data']['id'])) {
			$object_id = $result['data']['id'];
			return $object_id;
		} else {
			return false;
		}
	}

}
