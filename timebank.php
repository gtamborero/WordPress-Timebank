<?php
/*
Plugin Name: TimeBank
Plugin URI: https://www.iproject.cat/timebank
Description: The timebank-sharing system for your WordPress users! Create a transactional system (minutes, hours, tokens, any currency) for all your WordPress users.
Author: iproject.cat
Domain Path: /languages
Version: 0.1
Author URI: https://www.iproject.cat/timebank
*/

if ( ! defined('ABSPATH')){
  die;
}

if (!function_exists ('add_action')){
  echo "Algo estas haciendo mal"; exit();
}

define( 'TB_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) );
define( 'TB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include backend buttons
include_once( plugin_dir_path( __FILE__ ) . 'admin/admin_buttons.php');

// Include timebank post-type
include_once( plugin_dir_path( __FILE__ ) . 'admin/tbank-post-type.php');

// Include backend admin configuration
include_once( plugin_dir_path( __FILE__ ) . 'admin/config.php');

// INSTALL HOOK when plugin is activated
function timebank_install(){
	include_once "admin/install.php";
	jal_install();
	jal_install_data();
}
register_activation_hook( __FILE__ ,'timebank_install');

// UPDATE HOOK when plugin is updated / reactivated
add_action( 'plugins_loaded', 'timebank_update' );
function timebank_update(){
	include_once "admin/install.php";
	jal_install();
}

// UNINSTALL hook
register_deactivation_hook( __FILE__ ,'timebank_uninstall');
function timebank_uninstall(){
	include_once "admin/install.php";
	jal_uninstall();
}

// Save errors on log file
add_action('activated_plugin','save_error');
function save_error(){
file_put_contents(plugin_dir_path( __FILE__ ). 'log_error_activation.txt', ob_get_contents());
}

//USER FUNCTIONS (public)
function timebank_user_exchanges_view(){
  echo "go timebank";
  return "Lanzo TB";
  //if(!is_admin()) include_once "user/exchanges_view.php";
}

//CSS STYLE FOR PUBLIC
add_action( 'wp_enqueue_scripts', 'timebank_stylesheet' );
function timebank_stylesheet(){
    wp_register_style( 'timebank-style', plugins_url('css/style.css', __FILE__) );
    wp_enqueue_style( 'timebank-style' );
}

//include_once "admin/tbank_widget.php";

//BUDDY PRESS HOOK
add_action( 'bp_setup_nav', 'add_timebank_nav_tab' , 100 );
function add_timebank_nav_tab() {
bp_core_new_nav_item( array(
    'name' => __( 'TimeBank', 'timebank' ),
    'slug' => 'timebank',
    'position' => 80,
    'screen_function' => 'timebank_info',
    'default_subnav_slug' => 'timebank'
) );
}

// show feedback when 'Feedback’ tab is clicked
function timebank_info() {
bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
//tiene que ir detras este ad action. busca el bp_post template
add_action( 'bp_template_content','timebank_user_exchanges_view' );
}

// AJAX FUNCTIONS
//include( plugin_dir_path( __FILE__ ) . 'user/ajax.php');

// TRANSLATION
add_action( 'plugins_loaded', 'timebank_load_textdomain' );
function timebank_load_textdomain() {
  load_plugin_textdomain( 'timebank', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

//SHORT CODE CREATION inside block
add_shortcode('timebank_exchange', 'timebank_user_exchanges_view');


add_action( 'loop_end', 'timebank_author_loop_end' );
function timebank_author_loop_end()
{
  if( is_author() )
  {
    echo '<div style="width:100%;"> TBank here! on author when not buddy... configurable? </div>';
  }
}  
