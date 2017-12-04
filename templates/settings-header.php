		<div class="wrap">
			<style>
				.pipedrive-field-label {
					width: 400px;
					display: block;
					margin-top: 12px;
				}
			</style>

			<h2><?php _e( 'CF7 Pipedrive Settings', 'cf7-pipedrive' );?></h2>
			<p>Have issues, questions, comments, suggestions? This is still in beta and I'd love to hear from you at the plugin's support form <a target="_blank" href="https://wordpress.org/support/plugin/cf7-pipedrive-integration">here</a>. If you are having technical issues please ensure you have version 4.9 of contact form 7 or later before reaching out. I'll read and respond to each forum topic.</p>
			<div class="donate-request clearfix" style="float: left; clear: both;">
				<p>Do you <b>LOVE</b> this plugin? Have I responded to your requests or included functionality you asked for? Please consider a donation. I put a lot of hours into making this plugin possible and every cent is greatly appreciated.</p>
				<div style="width: 102px; display: block; margin: 0 auto;">
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="WRG3LHLM6NDRG">
					<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
					</form>
				</div>
			</div>

			<?php if(!empty($this->cf7_pipedrive_forms) && $show_full_form) : ?>
				<h2 class="nav-tab-wrapper">
					<a href="?page=<?php echo CF7_PIPEDRIVE_PLUGIN_SLUG; ?>" class="nav-tab <?php echo $this->active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>">General Settings</a>
				<?php foreach( $this->cf7_pipedrive_forms as $cf7_form_id ) : ?>
				    <a href="?page=<?php echo CF7_PIPEDRIVE_PLUGIN_SLUG; ?>&tab=form_<?php echo $cf7_form_id; ?>" class="nav-tab <?php echo $this->active_tab == 'form_'. $cf7_form_id ? 'nav-tab-active' : ''; ?>"><?php echo $this->cf7_forms[$cf7_form_id]; ?></a>
				<?php endforeach; ?>
				</h2>
			<?php endif; ?>