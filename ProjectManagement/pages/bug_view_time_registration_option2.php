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

	
<script>
function updateHiddenTimeValue() {
	valueEstimateMinutesType = "<?php echo PLUGIN_PM_EST ?>";
	valueDoneMinutesType = "<?php echo PLUGIN_PM_DONE ?>";
	valueTodoMinutesType = "<?php echo PLUGIN_PM_TODO ?>";
	valueCompletedMinutesType = "<?php echo PLUGIN_PM_COMPLETED ?>";
	
	idMinutesType = "minutes_type";
	idTimeValue = "time_value";
	idEstimateTimeValue = <?php echo '"change_' . $p_bug_id . '_' . PLUGIN_PM_EST . '"' ?>;
	idDoneTimeValue = <?php echo '"add_' . $p_bug_id . '_' . PLUGIN_PM_DONE . '"' ?>;
	idTodoTimeValue = <?php echo '"change_' . $p_bug_id . '_' . PLUGIN_PM_TODO . '"' ?>;
	idCompletedTimeValue = <?php echo '"add_' . $p_bug_id . '_' . PLUGIN_PM_COMPLETED . '"' ?>;
	
	oMinutesType = document.getElementById(idMinutesType);
	oTimeValue = document.getElementById(idTimeValue);
	oEstimateTime = document.getElementById(idEstimateTimeValue);
	oDoneTimeValue = document.getElementById(idDoneTimeValue);
	oTodoTimeValue = document.getElementById(idTodoTimeValue);
	oCompletedTimeValue = document.getElementById(idCompletedTimeValue);
	
	
	valueMinutesType = oMinutesType.options[oMinutesType.selectedIndex].value;
	
	if (valueMinutesType == valueEstimateMinutesType) {
		oEstimateTime.value = oTimeValue.value;
		oDoneTimeValue.value = '';
		oTodoTimeValue.value = '';
		oCompletedTimeValue.value = '';
	}
	else if (valueMinutesType == valueDoneMinutesType) {
		oEstimateTime.value = '';
		oDoneTimeValue.value = oTimeValue.value;
		oTodoTimeValue.value = '';
		oCompletedTimeValue.value = '';
	}
	else if (valueMinutesType == valueTodoMinutesType) {
		oEstimateTime.value = '';
		oDoneTimeValue.value = '';
		oTodoTimeValue.value = oTimeValue.value;
		oCompletedTimeValue.value = '';
	}
	else if (valueMinutesType == valueCompletedMinutesType) {
		oEstimateTime.value = '';
		oDoneTimeValue.value = '';
		oTodoTimeValue.value = '';
		oCompletedTimeValue.value = oTimeValue.value;
	}
}
</script>

<table class="width100" cellspacing="1">
	<tr>
		<td colspan="6" class="form-title">
<?php
		collapse_icon( 'time_registration' );
		echo plugin_lang_get( 'time_registration' );

?>
		</td>
	</tr>
	<tr class="row-category">
		<td><div align="center"><?php echo plugin_lang_get( 'user' ); ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'work_type' ); ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'minutes_type' ); ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'book_date' ); ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'hours' ); ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'information' ); ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'entry_date' ); ?></div></td>
		<td>&nbsp;</td>
	</tr>

	<tr <?php echo helper_alternate_class() ?>>
		<td><?php echo user_get_name( auth_get_current_user_id() ) ?></td>
		<td>
			<div align="center">
				<select name="work_type_<?php echo $p_bug_id ?>">
					<?php print_plugin_enum_string_option_list( 'work_types', plugin_config_get( 'default_worktype' ) ) ?>
				</select>
			</div>
		</td>
	 
		<td nowrap>
			<div align="center">
				<select id="minutes_type" name="minutes_type" onchange="updateHiddenTimeValue();">
					<?php
					if ( access_has_bug_level( plugin_config_get( 'edit_estimates_threshold' ), $p_bug_id ) ) {
					?>
					<option value="<?php echo PLUGIN_PM_EST ?>"><?php echo plugin_lang_get( 'est' ) ?></option>
					<?php } ?>
					<option value="<?php echo PLUGIN_PM_DONE ?>"><?php echo plugin_lang_get( 'done' ) ?></option>
					<option value="<?php echo PLUGIN_PM_TODO ?>"><?php echo plugin_lang_get( 'todo' ) ?></option>
					<option value="<?php echo PLUGIN_PM_COMPLETED ?>"><?php echo plugin_lang_get( 'completed' ) ?></option>
				</select>
			</div>
		</td>
		<td>
			<div align="center" nowrap>
				<?php
				if ( access_has_bug_level( plugin_config_get( 'include_bookdate_threshold' ), $p_bug_id ) ) {
					echo '<input type="text" size="12" maxlength="10" autocomplete="off" id="book_date" name="book_date" value="' . date( config_get( 'short_date_format' ) ) . '" style="text-align: center;">';
					date_print_calendar( 'book_date_cal' );
					date_finish_calendar( 'book_date', 'book_date_cal', false );
				}
				?>
				
				<?php
				$current_date = explode("-", date("Y-m-d"));
				?>
				<select tabindex="5" name="day" style="display: none;"><?php print_day_option_list( $current_date[2] ) ?></select>
				<select tabindex="6" name="month" style="display: none;"><?php print_month_option_list( $current_date[1] ) ?></select>
				<select tabindex="7" name="year" style="display: none;"><?php print_year_option_list( $current_date[0] ) ?></select>
			</div>
		</td>
		<td>
			<div align="center" nowrap>
				<input type="text" id="time_value" name="time_value" value="00:00" size="5" style="text-align: center;" onchange="updateHiddenTimeValue();">
				<input type="hidden" id=<?php echo '"change_' . $p_bug_id . '_' . PLUGIN_PM_EST . '"' ?> name=<?php echo '"change_' . $p_bug_id . '_' . PLUGIN_PM_EST . '"' ?> value="00:00">
				<input type="hidden" id=<?php echo '"add_' . $p_bug_id . '_' . PLUGIN_PM_DONE . '"' ?> name=<?php echo '"add_' . $p_bug_id . '_' . PLUGIN_PM_DONE . '"' ?> value="00:00">
				<input type="hidden" id=<?php echo '"change_' . $p_bug_id . '_' . PLUGIN_PM_TODO . '"' ?> name=<?php echo '"change_' . $p_bug_id . '_' . PLUGIN_PM_TODO . '"' ?> value="00:00">
				<input type="hidden" id=<?php echo '"add_' . $p_bug_id . '_' . PLUGIN_PM_COMPLETED . '"' ?> name=<?php echo '"add_' . $p_bug_id . '_' . PLUGIN_PM_COMPLETED . '"' ?> value="00:00">
			</div>
		</td>
		<td>
			<div align="center">
				<input type="text" id="pm_time_info" name="pm_time_info" style="width: 100%">
			</div>
		</td>
		<td>&nbsp;</td>
		<td>
			<div align="center">
				<input type="submit" name="submit" value="<?php echo plugin_lang_get( 'add_time_registration' ) ?>">
			</div>
		</td>
	</tr>

<?php
foreach ( $t_time_registered as $row ) {
	$t_user_id = auth_get_current_user_id();
	if ( access_has_bug_level( plugin_config_get( 'view_time_reg_summary_threshold' ), $p_bug_id ) || $row["user_id"] == $t_user_id ) {
?>
	<tr <?php echo helper_alternate_class() ?>>
		<td><?php echo $row["realname"] ?></td>
		<td>
			<div align="center">
				<?php echo print_plugin_enum_string_selected_value( 'work_types', $row["work_type"] ) ?>
			</div>
		</td>
		 
		 <td nowrap>
			<div align="center">
				<?php echo print_plugin_minutes_type_string( $row["minutes_type"] ) ?>
			</div>
		</td>
		 <td nowrap>
			<div align="center">
				<?php echo format_short_date( $row["book_date"] ) ?>
			</div>
		 </td>
		 <td>
			<div align="center">
				<?php echo minutes_to_time( $row["minutes"], false ) ?>
			</div>
		</td>
		<td>
			<div align="left">
				<?php echo $row["info"] ?>
			</div>
		</td>
		 <td>
			<div align="center">
				<?php echo format_date_complete( $row["timestamp"] ) ?>
			</div>
		 </td>
		 <td>
			<div align="center">
			<?php
			if ( ( $row["user_id"] == $t_user_id && access_has_bug_level( plugin_config_get( 'admin_own_threshold' ), $p_bug_id ) ) 
				|| access_has_bug_level( plugin_config_get( 'admin_threshold' ), $p_bug_id ) ) {
			?>
			
				<a href="<?php echo plugin_page('time_registration_delete') ?>&bug_id=<?php echo $p_bug_id; ?>&delete_id=<?php echo $row["id"]; ?><?php echo form_security_param( 'plugin_ProjectManagement_time_registration_delete' ) ?>">
					<?php echo plugin_lang_get( 'delete_time_registration' ) ?>
				</a>
			<?php } ?>
			</div>
		</td>
	</tr>
<?php	
	}
}
?>
</form>


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