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
							<?php /* No Debug For Now
							<tr>
								<th scope="row"><label for="cf7_pipedrive_debug_mode"><?php _e( 'Debug Mode', 'cf7-pipedrive' );?></label><br/><small>No not use on production environments. This may cause the submission message to not return.</small></label></th>
								<td>
									<input type="checkbox" name="cf7_pipedrive_debug_mode" value="yes" <?php if($this->cf7_pipedrive_debug_mode == 'yes') echo 'checked="checked"';?> ><label for="cf7_pipedrive_debug_mode">Check to enable debugging messages.</label><br>
								</td>
							</tr>
							*/?>

						<?php endif; ?>

						</table>

						<p class="submit">
							<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ) ?>" />
						</p>

					</div>
				</form>