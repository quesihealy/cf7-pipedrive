	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page='.$_GET['page'].'&tab='.$this->active_tab.'&noheader=true' ) ); ?>" enctype="multipart/form-data">
		<?php wp_nonce_field( 'cf7_pipedrive', 'save_cf7_pipedrive' ); ?>
		<div class="cf7_pipedrive_form">
			<table class="form-table" width="100%">
				<tr>
					<th scope="row"><label for="cf7_pipedrive_form_fields"><?php _e( 'Person Fields', 'cf7-pipedrive' );?></label><br/><small>Set any Pipedrive person fields.</small></label></th>
					<td>
						<?php foreach($person_fields as $key => $field) : ?>

							<label class='pipedrive-field-label'><?php echo $field['display_name']; ?></label>
							<div class='cf7_pipedrive_field_value field_value_<?php echo $form_id; ?>'>
								<select name="person_<?php echo $key; ?>" id="person_<?php echo $key; ?>">
									<option value="">-</option>
									<?php
									if($key == 'owner_id') {
										foreach($pipedrive_users as $value) : ?>
											<option value="<?php echo $value['id']; ?>" <?php selected( $person_values[$key], $value['id'] ); ?>><?php echo $value['name']; ?></option>
										<?php
										endforeach;
									} else {
										foreach($form_fields as $form_field) : 
											if($form_field->name != '') : ?>
												<option value="<?php echo $form_field->name; ?>" <?php selected( $person_values[$key], $form_field->name ); ?>><?php echo $form_field->name; ?></option>
												<?php
											endif;
										endforeach;
									}
									?>
								</select>
							</div>
							<br/>

						<?php endforeach; ?>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="cf7_pipedrive_form_fields"><?php _e( 'Organization Fields', 'cf7-pipedrive' );?></label><br/><small>Set any Pipedrive person fields.</small></label></th>
					<td>
						<?php foreach($organization_fields as $key => $field) : ?>

							<label class='pipedrive-field-label'><?php echo $field['display_name']; ?></label>
							<div class='cf7_pipedrive_field_value field_value_<?php echo $form_id; ?>'>
								<select name="organization_<?php echo $key; ?>" id="organization_<?php echo $key; ?>">
									<option value="">-</option>
									<?php
									if($key == 'owner_id') {
										foreach($pipedrive_users as $value) : ?>
											<option value="<?php echo $value['id']; ?>" <?php selected( $organization_values[$key], $value['id'] ); ?>><?php echo $value['name']; ?></option>
										<?php
										endforeach;
									} else {
										foreach($form_fields as $form_field) : 
											if($form_field->name != '') : ?>
												<option value="<?php echo $form_field->name; ?>" <?php selected( $organization_values[$key], $form_field->name ); ?>><?php echo $form_field->name; ?></option>
												<?php
											endif;
										endforeach;
									}
									?>
								</select>
							</div>
							<br/>

						<?php endforeach; ?>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="cf7_pipedrive_form_fields"><?php _e( 'Deal Fields', 'cf7-pipedrive' );?></label><br/><small>Set any Pipedrive person fields.</small></label></th>
					<td>
						<input type="checkbox" name="attach_to_person" value="yes" <?php if($attach_to_person == 'yes') echo 'checked="checked"';?> ><label for="cf7_pipedrive_debug_mode">Attach Person to Deal.</label><br>
						<input type="checkbox" name="attach_to_organization" value="yes" <?php if($attach_to_organization == 'yes') echo 'checked="checked"';?> ><label for="cf7_pipedrive_debug_mode">Attach Organization to Deal.</label><br>
						<?php foreach($deal_fields as $key => $field) : ?>

							<label class='pipedrive-field-label'><?php echo $field['display_name']; ?></label>
							<div class='cf7_pipedrive_field_value field_value_<?php echo $form_id; ?>'>
								<select name="deal_<?php echo $key; ?>" id="deal_<?php echo $key; ?>">
									<option value="">-</option>
									<?php
									if($key == 'user_id' || $key == 'pipeline' || $key == 'stage_id') {
										switch ($key) {
											case 'user_id':
												$foreach_value = $pipedrive_users;
												break;
											case 'pipeline':
												$foreach_value = $pipedrive_pipelines;
												break;
											case 'stage_id':
												$foreach_value = $pipedrive_stages;
												break;
										}
										foreach($foreach_value as $value) : ?>
											<option value="<?php echo $value['id']; ?>" <?php selected( $deal_values[$key], $value['id'] ); ?>><?php echo $value['name']; ?></option>
										<?php 
										endforeach;
									} else {
										foreach($form_fields as $form_field) : 
											if($form_field->name != '') : ?>
												<option value="<?php echo $form_field->name; ?>" <?php selected( $deal_values[$key], $form_field->name ); ?>><?php echo $form_field->name; ?></option>
												<?php
											endif;
										endforeach;
									}
									?>
								</select>
							</div>
							<br/>
							
						<?php endforeach; ?>
					</td>

				</tr>
			</table>

			<input type="hidden" id="form_id" name="form_id" value="<?php echo $form_id; ?>" />

			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ) ?>" />
			</p>

		</div>
	</form>