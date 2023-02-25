<?php

// ADMIN SIDEBAR BUTTONS:
add_action( 'admin_menu', 'timebank_menu' );
function timebank_menu() {
	add_menu_page( __('TimeBank' , 'timebank'), 'TimeBank', 'manage_options', 'timebank', 'timebank_exchanges' );
	add_submenu_page( 'timebank', __('New Exchange' , 'timebank'), 'New Exchange', 'manage_options', 'timebank_newexchange', 'timebank_newexchange');
	add_submenu_page( 'timebank', __('Users' , 'timebank'), 'Users', 'manage_options', 'timebank_users', 'timebank_users');
	add_submenu_page( 'timebank', __('Configuration' , 'timebank'), 'Configuration', 'manage_options', 'timebank_options', 'timebank_options');
	// Timebank edit user must be here with 'null' property to have access to the admin page without a menu button
	add_submenu_page( 'null', __('Edit User' , 'timebank'), 'Edit User', 'manage_options', 'timebank_edituser', 'timebank_edituser');
	add_submenu_page( 'null', __('Edit Exchange' , 'timebank'), 'Edit Exchange', 'manage_options', 'timebank_editexchange', 'timebank_editexchange');

	//Register CSS Admin Style
	//wp_register_style( 'timebank-style', plugins_url('css/adminstyle.css', __FILE__) );
    //wp_enqueue_style( 'timebank-style' );
}
