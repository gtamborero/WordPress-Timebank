<?php

// ADMIN SIDEBAR BUTTONS:
add_action( 'admin_menu', 'timebank_menu' );
function timebank_menu() {
	//add_menu_page( __('TimeBank' , 'timebank'), 'TimeBank', 'manage_options', 'timebank', 'timebank_exchanges' );
	add_submenu_page( 'edit.php?post_type=tbank-transaction', __('Configuration' , 'timebank'), 'Configuration', 'manage_options', 'timebank_options', 'timebank_options');
}
