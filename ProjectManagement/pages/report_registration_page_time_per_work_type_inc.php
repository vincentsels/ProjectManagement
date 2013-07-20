<?php
/*
 * Requires:
 *           $t_per_work_type
 */
$t_collapse_time_per_work_type = 'plugin_pm_time_per_work_type';
collapse_open( $t_collapse_time_per_work_type );
?>
<table class="width100" cellspacing="1">
	<tr>
		<td colspan="100%" class="form-title">
			<?php collapse_icon( $t_collapse_time_per_work_type ); ?>
			<?php echo plugin_lang_get( 'time_division' ); ?> -
			<?php echo plugin_lang_get( 'per' ); ?> <?php echo plugin_lang_get( 'work_type' ); ?>
		</td>
	</tr>
	<tr class="row-category">
		<td>
			<div align="center"><?php echo lang_get( 'username' ) ?></div>
		</td>
		<?php
		foreach ( $t_work_types as $t_work_type_value => $t_work_type_label ) {
			?>
			<td>
				<div align="center"><?php echo $t_work_type_label ?></div>
			</td>
			<?php
		}
		?>
		<td>
			<div align="center"><?php echo plugin_lang_get( 'total' ) ?></div>
		</td>
	</tr>

	<?php
	foreach ( $t_per_work_type as $t_user => $t_categories ) {
		echo "<tr " . helper_alternate_class() . ">";
		echo '<td class="category">';
		print_user( $t_user );
		echo "</td>";
		foreach ( $t_work_types as $t_work_type_value => $t_work_type_label ) {
			echo "<td class=\"right\">" . minutes_to_time( @$t_categories[$t_work_type_value] ) . "</td>";
		}
		echo "<td class=\"right\">" . minutes_to_time( @$t_categories[plugin_lang_get( 'total' )] ) . "</td></tr>";
	}
	?>

</table>
<?php collapse_closed( $t_collapse_time_per_work_type ); ?>
<table class="width100" cellspacing="1">
	<tr>
		<td colspan="100%" class="form-title">
			<?php collapse_icon( $t_collapse_time_per_work_type ); ?>
			<?php echo plugin_lang_get( 'time_division' ); ?> -
			<?php echo plugin_lang_get( 'per' ); ?> <?php echo plugin_lang_get( 'work_type' ); ?>
		</td>
	</tr>
</table>
<?php collapse_end( $t_collapse_time_per_work_type ); ?>
