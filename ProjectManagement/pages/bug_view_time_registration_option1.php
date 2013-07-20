 	<a name="time_registration" id="time_registration"></a>
		<?php
		collapse_open( 'plugin_pm_time_reg' );
		?>
	<form name="time_registration" method="post" action="<?php echo plugin_page( 'time_registration_update' ) ?>">
		<?php
		echo form_security_field( 'plugin_ProjectManagement_time_registration_update' );

		# Rather strange way to pass an array of bug id's with only the selected bug_id in it.
		printf( "<input type=\"hidden\" name=\"bug_ids[]\" value=\"%d\" />", $p_bug_id );
		?>


		<table class="width100" cellspacing="1">
			<tr>
				<td colspan="100%" class="form-title">
					<?php
					collapse_icon( 'plugin_pm_time_reg' );
					echo plugin_lang_get( 'time_registration' );
					?>
				</td>
			</tr>
			<tr class="row-category">
				<td width="20%" style="min-width:145px">
					<div align="center"><?php echo plugin_lang_get( 'work_type' ) ?></div>
				</td>
				<td style="min-width:130px">
					<div align="center"><?php echo plugin_lang_get( 'est' ) ?></div>
				</td>
				<td width="20%" style="min-width:130px">
					<div align="center"><?php echo plugin_lang_get( 'done' ) ?></div>
				</td>
				<td width="30%" style="min-width:130px">
					<div align="center"><?php echo plugin_lang_get( 'todo' ) ?></div>
				</td>
				<td style="min-width:100px">
					<div align="center"><?php echo plugin_lang_get( 'diff' ) ?></div>
				</td>
			</tr>
			<?php
			foreach ( $t_work_types as $t_work_type_code => $t_work_type_label ) {
				?>
				<tr <?php echo ( $t_work_type_code == PLUGIN_PM_WORKTYPE_TOTAL ?
					'class="row-category-history"' : helper_alternate_class() ) ?>>

					<td class="category"><?php echo $t_work_type_label ?></td>

					<td>
						<?php
						if ( isset( $t_work[PLUGIN_PM_EST][$t_work_type_code] ) ) {
							echo minutes_to_time( $t_work[PLUGIN_PM_EST][$t_work_type_code], true );
						}
						if ( $t_work_type_code != PLUGIN_PM_WORKTYPE_TOTAL &&
							( !isset( $t_work[PLUGIN_PM_EST][$t_work_type_code] ) ||
								access_has_bug_level( plugin_config_get( 'edit_estimates_threshold' ), $p_bug_id ) )
						) {
							# Check whether est was already supplied, or user has rights to alter it regardless
							?>
							-> <input type="text" size="4" maxlength="7" autocomplete="off"
									  name= <?php echo '"change_' . $p_bug_id . '_' . PLUGIN_PM_EST . '_' . $t_work_type_code . '"' ?>>
							<?php
						}
						?>
					</td>

					<td>
						<?php
						if ( isset( $t_work[PLUGIN_PM_DONE][$t_work_type_code] ) ) {
							echo minutes_to_time( $t_work[PLUGIN_PM_DONE][$t_work_type_code], false );
						} else {
							echo minutes_to_time( 0, false );
						}
						if ( $t_work_type_code != PLUGIN_PM_WORKTYPE_TOTAL ) {
							?>
							+ <input type="text" size="4" maxlength="7" autocomplete="off"
									 name= <?php echo '"add_' . $p_bug_id . '_' . PLUGIN_PM_DONE . '_' . $t_work_type_code . '"' ?>>
							<?php
						}
						?>
					</td>

					<?php
					if ( !isset( $t_work[PLUGIN_PM_TODO][$t_work_type_code] ) &&
						isset( $t_work[PLUGIN_PM_REMAINING][$t_work_type_code] )
					) {
						# When todo was not supplied, display calculated remainder instead, in italic
						?>
					<td class="italic"><?php echo minutes_to_time( $t_work[PLUGIN_PM_REMAINING][$t_work_type_code], false ) ?>
						<?php
					} else if ( isset( $t_work[PLUGIN_PM_TODO][$t_work_type_code] ) ) {
						# Todo was supplied, so display that
						?>
					<td><?php echo minutes_to_time( $t_work[PLUGIN_PM_TODO][$t_work_type_code], false ) ?>
						<?php
					} else {
						echo '<td>';
					}
					if ( $t_work_type_code != PLUGIN_PM_WORKTYPE_TOTAL ) {
						?>
						-> <input type="text" size="4" maxlength="7" autocomplete="off"
								  name= <?php echo '"change_' . $p_bug_id . '_' . PLUGIN_PM_TODO . '_' . $t_work_type_code . '"' ?>>
						<?php
						if ( isset( $t_work[PLUGIN_PM_TODO][$t_work_type_code] ) ) {
							?>
							<input type="checkbox"
									name= <?php echo '"clear_' . $p_bug_id . '_' . PLUGIN_PM_TODO . '_' . $t_work_type_code . '"' ?>> <?php echo plugin_lang_get( 'clear' ) ?>
							<?php
						}
					}
					echo '</td>';
					if ( isset( $t_work[PLUGIN_PM_DIFF][$t_work_type_code] ) ) {
						?>
						<td <?php echo ( $t_work[PLUGIN_PM_DIFF][$t_work_type_code] < 0 ? 'class="negative"' : 'class="positive"' )  ?>>
							<?php echo minutes_to_time( abs( $t_work[PLUGIN_PM_DIFF][$t_work_type_code] ) ) ?></td>
						<?php
					} else {
						echo '<td />';
					} ?>
				</tr>
				<?php
			}
			?>

			<tr>
				<td colspan="100%">
					<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
					<?php
					if ( access_has_bug_level( plugin_config_get( 'include_bookdate_threshold' ), $p_bug_id ) ) {
						echo plugin_lang_get( 'book_date' ) . ': ';
						echo '<input type="text" size="8" maxlength="10" autocomplete="off" id="book_date" name="book_date" value="' .
							date( config_get( 'short_date_format' ) ) . '">';
						date_print_calendar( 'book_date_cal' );
						date_finish_calendar( 'book_date', 'book_date_cal' );
					}
					?>
				</td>
			</tr>
		</table>
		

		<?php
		collapse_closed( 'plugin_pm_time_reg' );
		?>

		<table class="width100" cellspacing="1">
			<tr>
				<td class="form-title" colspan="2">
					<?php
					collapse_icon( 'plugin_pm_time_reg' );
					echo plugin_lang_get( 'time_registration' );
					?></td>
			</tr>
		</table>
	</form>

		<?php
		collapse_end( 'plugin_pm_time_reg' );
		?>