<?php
/**
 * This template renders the filter tab.
 *
 * @package Antispam Bee Templates
 */

if ( ! isset( $tab_data ) ) {
	return;
}
$active_filters = $tab_data->active_filters;
$tab_type       = $tab_data->type;
/**
 * The active filters.
 *
 * @var array $active_filters
 */
?>

<form method="post" action="#">
	<input
		type="hidden"
		name="<?php echo esc_attr( $tab_data->nonce_name ); ?>"
		value="<?php echo esc_attr( $tab_data->nonce ); ?>"
		/>
	<input
		type="hidden"
		name="type"
		value="<?php echo esc_attr( $tab_data->type ); ?>"
	/>
<ul>
	<?php
	foreach ( $tab_data->filters as $filter ) :
		/**
		 * The filter to render.
		 *
		 * @var \Pluginkollektiv\AntispamBee\Filter\FilterInterface $filter
		 */
		$type = $filter->id();
		?>

		<li>
			<?php if ( $filter->options()->activateable() ) : ?>
			<select
				id="filter-<?php echo esc_attr( $filter->id() ); ?>"
				type="checkbox"
				name="antispambee_fields[<?php echo esc_attr( $filter->id() ); ?>]"
				>
				<option
					value="0"
					<?php selected( ! in_array( $filter->id(), $active_filters, true ) ); ?>
				>
					<?php esc_html_e( 'Off', 'antispam-bee' ); ?>
				</option>
				<option
					value="1"
					<?php selected( in_array( $filter->id(), $active_filters, true ) ); ?>
				>
					<?php esc_html_e( 'On', 'antispam-bee' ); ?>
				</option>
			</select>
			<?php endif; ?>
			<h3>

				<?php if ( $filter->options()->activateable() ) : ?>
				<label
					for="filter-<?php echo esc_attr( $filter->id() ); ?>"
				>
					<?php echo esc_html( $filter->options()->name() ); ?>
				</label>
				<?php else : ?>
					<?php echo esc_html( $filter->options()->name() ); ?>
				<?php endif; ?>
			</h3>
			<p>
				<?php echo esc_html( $filter->options()->description() ); ?>
			</p>

			<?php foreach ( $filter->options()->fields() as $field ) : ?>
				<?php include __DIR__ . '/field.php'; ?>
			<?php endforeach; ?>
		</li>
	<?php endforeach; ?>
</ul>
<?php submit_button(); ?>
</form>