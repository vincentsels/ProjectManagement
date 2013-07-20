<?php
/*
 * Requires:
 *           $t_categories
 *           $f_project_id
 */
$t_collapse_time_per_category  = 'plugin_pm_time_per_category';
collapse_open( $t_collapse_time_per_category );
?>
<table class="width100" cellspacing="1">
	<tr>
		<td colspan="100%" class="form-title">
			<?php collapse_icon( $t_collapse_time_per_category ); ?>
			<?php echo plugin_lang_get( 'time_division' ); ?> -
			<?php echo plugin_lang_get( 'per' ); ?> <?php echo lang_get( 'category' ); ?>
		</td>
	</tr>
	<tr class="row-category">
		<td>
			<div align="center"><?php echo lang_get( 'username' ) ?></div>
		</td>
		<?php
		foreach ( category_get_all_rows( $f_project_id ) as $row ) {
			?>
			<td>
				<div align="center"><?php echo $row["name"] ?></div>
			</td>
			<?php
		}
		?>
		<td>
			<div align="center"><?php echo plugin_lang_get( 'total' ) ?></div>
		</td>
	</tr>

	<?php
	foreach ( $t_per_category as $t_user => $t_categories ) {
		echo "<tr " . helper_alternate_class() . ">";
		echo '<td class="category">';
		print_user( $t_user );
		echo "</td>";
		foreach ( category_get_all_rows( $f_project_id ) as $row ) {
			echo "<td class=\"right\">" . minutes_to_time( @$t_categories[$row["id"]] ) . "</td>";
		}
		echo "<td class=\"right\">" . minutes_to_time( @$t_categories[plugin_lang_get( 'total' )] ) . "</td></tr>";
	}
	?>
</table>
<?php collapse_closed( $t_collapse_time_per_category ); ?>
<table class="width100" cellspacing="1">
	<tr>
		<td colspan="100%" class="form-title">
			<?php collapse_icon( $t_collapse_time_per_category ); ?>
			<?php echo plugin_lang_get( 'time_division' ); ?> -
			<?php echo plugin_lang_get( 'per' ); ?> <?php echo lang_get( 'category' ); ?>
		</td>
	</tr>
</table>
<?php collapse_end( $t_collapse_time_per_category ); ?>
