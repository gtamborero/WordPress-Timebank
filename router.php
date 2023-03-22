<?php
// REST API DATA
add_action( 'rest_api_init', function () {
    register_rest_route( 'iproject/v1', '/timebank_front', array(
        'methods' => 'GET',
        'callback' => 'TimebankAPI::getData',
        'permission_callback' => '__return_true',
    ));

    register_rest_route( 'iproject/v1', '/open_new_transaction', array(
        'methods' => 'GET',
        'callback' => 'TimebankAPI::newTransaction',
        'permission_callback' => '__return_true',
    ));
});