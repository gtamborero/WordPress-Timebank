<?php 
// Main timebank view
// If no parameters then view is main user transactions
// If parameter user is set then search transactions of user and add exchange with user button
// Get user data from transactions posttype

ob_start();
// Start Timebank front echoing 

$userId = get_current_user_id();

$args = array(
    'post_type' => 'tbank-transaction',
    'meta_query' => array(
        array(
            'key' => '_timebank_payer',
            'value' => 'iproject',
            'compare' => 'LIKE',
        ),
    ),
);
$query = new WP_Query( $args );

echo "<pre>"; 
var_dump($query);
?> 

