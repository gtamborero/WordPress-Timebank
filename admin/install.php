<?php

//FIRST INSTALL FILE
//Here we set up the TIME BANK DATABASE

$jal_db_version = "1.33";
$installed_ver = get_option( "jal_db_version" );
//echo "VERS:" . $installed_ver ;

function jal_install() {
   global $wpdb;
   global $jal_db_version;

   $table_name = $wpdb->prefix . "tbank_conf";
   $table1 = "CREATE TABLE $table_name (
  id tinyint(4) NOT NULL AUTO_INCREMENT,
  default_anonymous varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  default_min_limit int(11) NOT NULL,
  default_max_limit int(11) NOT NULL,
  exchange_timeout int(11) NOT NULL,
  currency varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  path_to_timebank varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  admin_mail tinyint(1) NOT NULL,
  starting_amount int(11) NOT NULL,
  email_original_text text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  email_text text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY id (id)
);
";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $table1 );

update_option( "jal_db_version", $jal_db_version );
}

function jal_install_data() {
   global $wpdb;

   // INSERT CONFIGURATION
   $id = "1";
   $default_anonymous = "(deleted user)";
   $default_min_limit = "120";
   $default_max_limit = "180";
   $currency = "minutes";
   $exchange_timeout = "48";
   $starting_amount = "0";
   $admin_mail = "1";
   $email_original_text = 'Hello!
A new timebank transfer has been $status_name on your timebank $siteUrl

Concept: $data->concept
Exchange: $data->amount minutes
Exchange status: $status_name

Buyer: $data->buyer_name , $data->buyer_email
Seller: $data->seller_name , $data->seller_email

Date Creation: $data->datetime_created
Date Accept: " .  showIfSet($data->datetime_accepted) . "
Date Rejected: " . showIfSet($data->datetime_denied) . "

Please Accept or Reject the transfer as soon as possible on $siteUrl
If you don\'t Accept within 48 hours the transfer will be automaticaly rejected.

The $siteUrl Team.';

   $table_name = $wpdb->prefix . "tbank_conf";
   $rows_affected = $wpdb->insert( $table_name, array( 'id' => $id, 'default_anonymous' => $default_anonymous, 'default_min_limit' => $default_min_limit, 'default_max_limit' => $default_max_limit, 'exchange_timeout' => $exchange_timeout, 'currency' => $currency, 'starting_amount' => $starting_amount, 'admin_mail' => $admin_mail, 'email_original_text' => $email_original_text,  'email_text' => $email_original_text ) );
}

function jal_uninstall() {
   global $wpdb;

   // DROP SUPPORT TABLES (not data)
   // Provisional hasta resolver problema del foro wp insert
   $table_name = $wpdb->prefix . "tbank_conf";
   $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

?>
