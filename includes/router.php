<?php
// REST API DATA
add_action( 'rest_api_init', function () {
    register_rest_route( 'iproject/v1', '/timebank_front', array(
        'methods' => 'GET',
        'callback' => 'TimebankAPI::getData',
        'permission_callback' => '__return_true',
    ));

    register_rest_route( 'iproject/v1', '/create_new_transaction', array(
        'methods' => 'POST',
        'callback' => 'TimebankAPI::createNewTransaction',
        'permission_callback' => '__return_true',
    ));
});