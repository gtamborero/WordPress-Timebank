<?php

// ADMIN SIDEBAR BUTTONS:
add_action( 'admin_menu', 'timebank_menu' );
function timebank_menu() {
	add_menu_page(
		__( 'TimeBank', 'timebank' ),
		'TimeBank',
		'edit_pages',
		'timebank',
		'timebank_transactions',
		'dashicons-database',
		5
	);

	add_submenu_page( 
		'timebank',
		__( 'Transactions', 'timebank' ),
		__( 'Transactions', 'timebank' ),
		'edit_pages',
		'edit.php?post_type=tbank-transaction'
	);

	add_submenu_page(
		'timebank',
		__( 'Shortcodes', 'timebank' ),
		__( 'Shortcodes', 'timebank' ),
		'edit_pages',
		'timebank_shortcodes',
		'timebank_shortcodes_page'
	);

	add_submenu_page(
		'timebank',
		__('Configuration' , 'timebank'), 
		__('Configuration' , 'timebank'),
		'manage_options', 
		'timebank_options', 
		'timebank_options'
	);

	remove_submenu_page( 'timebank', 'timebank' );
}

function timebank_transactions() {
	wp_safe_redirect( admin_url( 'edit.php?post_type=tbank-transaction' ) );
	exit;
}

function timebank_shortcodes_page() {
	$shortcodes = array(
		array(
			'code'        => '[timebank_view]',
			'status'      => __( 'Active', 'timebank' ),
			'description' => __( 'Displays the TimeBank front-end view, including the transfer form and the current user transaction list.', 'timebank' ),
			'source'      => 'timebank.php',
		),
		array(
			'code'        => '[timebank_transaction]',
			'status'      => __( 'Mentioned in readme, not registered', 'timebank' ),
			'description' => __( 'Legacy documentation mention. This shortcode is not registered in the plugin code, so WordPress will not render it unless it is added later.', 'timebank' ),
			'source'      => 'readme.txt',
		),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'TimeBank Shortcodes', 'timebank' ); ?></h1>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Shortcode', 'timebank' ); ?></th>
					<th><?php esc_html_e( 'Status', 'timebank' ); ?></th>
					<th><?php esc_html_e( 'Description', 'timebank' ); ?></th>
					<th><?php esc_html_e( 'Found in', 'timebank' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $shortcodes as $shortcode ) : ?>
					<tr>
						<td><code><?php echo esc_html( $shortcode['code'] ); ?></code></td>
						<td><?php echo esc_html( $shortcode['status'] ); ?></td>
						<td><?php echo esc_html( $shortcode['description'] ); ?></td>
						<td><code><?php echo esc_html( $shortcode['source'] ); ?></code></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
