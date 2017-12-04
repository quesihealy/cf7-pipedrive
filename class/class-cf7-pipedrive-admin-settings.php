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
		$this->cf7_pipedrive_forms 		= ( false != get_option( 'my_cf7_pipedrive_forms' ) ? get_option( 'my_cf7_pipedrive_forms' ) : array() );
		$this->cf7_pipedrive_stage 		= ( false != get_option( 'my_cf7_pipedrive_stage' ) ? get_option( 'my_cf7_pipedrive_stage' ) : '' );
		$this->cf7_pipedrive_user 		= ( false != get_option( 'my_cf7_pipedrive_user' ) ? get_option( 'my_cf7_pipedrive_user' ) : '' );
		$this->cf7_pipedrive_debug_mode = ( false != get_option( 'cf7_pipedrive_debug_mode' ) ? get_option( 'cf7_pipedrive_debug_mode' ) : 'no');
		$this->cf7_pipedrive_api_key 	= get_option( 'cf_pipedrive_api_key' );
		if(class_exists('WPCF7_ContactForm')) {
			$this->cf7_installed = true;
		}
		$this->pipedrive = new Cf7_Pipedrive_Pipedrive_API($this->cf7_pipedrive_api_key);

		// If it is not installed give admin warning
		if ( !$this->cf7_installed ) {
			add_action('admin_notices', array($this, 'no_cf7_admin_notice'));
		}

		// If there is no API Key set, send a warning
		if($this->cf7_pipedrive_api_key == '') {
			add_action('admin_notices', array($this, 'no_api_key_admin_notice'));
		}

		if($this->cf7_installed && $this->cf7_pipedrive_api_key != '' && is_admin()) {
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
			<p>Have questions, comments, suggestions? This is still in beta and I'd love to hear from you at lucas@everythinghealy.com. If you are having technical issues please ensure you have version 4.9 of contact form 7 or later before reaching out. I'll read and respond to every e-mail.</p>
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


		DEPRACATED


 **/

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

	public function contact_form_field_keys() {
		return array(
			'cf7_pipedrive_field_name_',
			'cf7_pipedrive_field_email_',
			'cf7_pipedrive_field_phone_',
			'cf7_pipedrive_field_title_',
		);
	}

	public function populate_stages() {
		$response = $this->pipedrive->get_stages();
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
		$response = $this->pipedrive->get_users();
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

}