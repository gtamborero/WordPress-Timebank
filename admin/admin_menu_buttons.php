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
