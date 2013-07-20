<a name="targets" id="targets"></a>
<?php
collapse_open( 'plugin_pm_targets' );
?>
<form name="targets" method="post" action="<?php echo plugin_page( 'target_update' ) ?>">
	<?php
	echo form_security_field( 'plugin_pm_targets_update' );

	# Rather strange way to pass an array of bug id's with only the selected bug_id in it.
	printf( "<input type=\"hidden\" name=\"bug_ids[]\" value=\"%d\" />", $p_bug_id );
	?>

	<table class="width100" cellspacing="1">
		<tr>
			<td colspan="100%" class="form-title">
				<?php
				collapse_icon( 'plugin_pm_targets' );
				echo plugin_lang_get( 'targets' );
				?>
			</td>
		</tr>
		<tr class="row-category">
			<td style="min-width:145px">
				<div align="center"><?php echo plugin_lang_get( 'work_type' ) ?></div>
			</td>
			<td style="min-width:115px">
				<div align="center"><?php echo plugin_lang_get( 'target_date' ) ?></div>
			</td>
			<td>
			</td>
			<td>
				<div align="center"><?php echo plugin_lang_get( 'owner' ) ?></div>
			</td>
			<td style="min-width:115px">
				<div align="center"><?php echo plugin_lang_get( 'completed' ) ?></div>
			</td>
		</tr>
		<?php
		foreach ( $t_work_types as $t_work_type_code => $t_work_type_label ) {
			if ( $t_work_type_code == PLUGIN_PM_WORKTYPE_TOTAL ) {
				continue;
			} else if ( array_key_exists( $t_work_type_code, $t_targets ) ) {
				$t_target             = $t_targets[$t_work_type_code];
				$t_target_date        = date( config_get( 'short_date_format' ), $t_target["target_date"] );
				$t_owner_id           = $t_target["owner_id"];
				$t_completed_date     = format_short_date( $t_target["completed_date"] );
				$t_days_overdue       = days_between( $t_target["target_date"], $t_target["completed_date"] ) * -1;
				$t_days_overdue_class = $t_days_overdue < 0 ? 'class="negative"' : 'class="positive"';

				$t_target_date_class = '';
				if ( is_null( $t_completed_date ) ) {
					if ( $t_owner_id == auth_get_current_user_id() ) {
						if ( $t_days_overdue < 0 ) {
							$t_target_date_class = 'class="target-date-overdue"';
						} else if ( $t_days_overdue == 0 ) {
							$t_target_date_class = 'class="target-date-notice"';
						}
					} else if ( $t_days_overdue < 0 ) {
						$t_target_date_class = 'class="target-date-notice"';
					}
				}

				$t_days_overdue = abs( $t_days_overdue );
			} else {
				# target for this worktype has not yet been defined
				$t_target_date        = null;
				$t_target_date_class  = '';
				$t_days_overdue       = null;
				$t_days_overdue_class = '';
				$t_completed_date     = null;
				# Find the default owner for this work type
				$t_customer_work_types = plugin_config_get( 'work_types_for_customer' );
				$t_work_type_for_customer = false;
				foreach ( $t_customer_work_types as $work_type ) {
					if ( $work_type == $t_work_type_code ) {
						$t_work_type_for_customer = true;
					}
				}
				if ( $t_work_type_for_customer ) {
					$t_reporter_id = bug_get_field( $p_bug_id, 'reporter_id' );
					$t_owner_id    = is_null( $t_reporter_id ) ? -1 : $t_reporter_id;
				} else {
					$t_handler_id = bug_get_field( $p_bug_id, 'handler_id' );
					$t_owner_id   = is_null( $t_handler_id ) ? -1 : $t_handler_id;
				}
			}

			$t_can_edit_targets = access_has_global_level( plugin_config_get( 'edit_targets_threshold' ) );
			?>
			<tr <?php echo helper_alternate_class() ?>>

				<td class="category"><?php echo $t_work_type_label ?></td>

				<td <?php echo $t_target_date_class ?>>
					<?php
					if ( $t_can_edit_targets || empty( $t_target_date ) ) {
					?>
					<input type="text" size="8" maxlength="10" autocomplete="off"
						   id="<?php echo $p_bug_id . '_target_date_' . $t_work_type_code ?>"
						   name="<?php echo $p_bug_id . '_target_date_' . $t_work_type_code ?>"
						   value="<?php echo $t_target_date ?>">
					<?php
					date_print_calendar( 'target_date_cal_' . $t_work_type_code );
					date_finish_calendar( $p_bug_id . '_target_date_' . $t_work_type_code, 'target_date_cal_' . $t_work_type_code );
					} else {
						echo $t_target_date;
						echo '<input type="hidden" name="' . $p_bug_id . '_target_date_' . $t_work_type_code .
							'" value="' . $t_target_date . '"';
					} ?>
				</td>

				<td <?php echo $t_days_overdue_class ?>><?php echo $t_days_overdue ?></td>

				<td>
					<?php
					if ( $t_can_edit_targets || empty( $t_target_date ) ) {
					?>
					<select name="<?php echo $p_bug_id . '_owner_id_' . $t_work_type_code ?>">
						<option value="-1" selected="selected"></option>
						<?php print_user_option_list( $t_owner_id ) ?>
					</select>
					<?php
					} else {
						echo user_get_name( $t_owner_id );
						echo '<input type="hidden" name="' . $p_bug_id . '_owner_id_' . $t_work_type_code .
							'" value="' . $t_owner_id . '"';
					} ?>
				</td>

				<td>
					<?php
					if ( $t_can_edit_targets || empty( $t_completed_date ) ) {
					?>
					<input type="text" size="8" maxlength="10" autocomplete="off"
						   id="<?php echo $p_bug_id . '_completed_date_' . $t_work_type_code ?>"
						   name="<?php echo $p_bug_id . '_completed_date_' . $t_work_type_code ?>"
						   value="<?php echo $t_completed_date ?>">
					<?php
					date_print_calendar( 'completed_date_cal_' . $t_work_type_code );
					date_finish_calendar( $p_bug_id . '_completed_date_' . $t_work_type_code, 'completed_date_cal_' . $t_work_type_code );
					} else {
						echo $t_completed_date;
						echo '<input type="hidden" name="' . $p_bug_id . '_completed_date_' . $t_work_type_code .
							'" value="' . $t_completed_date . '"';
					}
					?>
				</td>
			</tr>
			<?php
		}
		?>

		<tr>
			<td colspan="100%">
				<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
			</td>
		</tr>
	</table>

	<?php
	collapse_closed( 'plugin_pm_targets' );
	?>

	<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php
				collapse_icon( 'plugin_pm_targets' );
				echo plugin_lang_get( 'targets' );
				?></td>
		</tr>
	</table>
</form>

<?php
collapse_end( 'plugin_pm_targets' );
?>