<?php
// REST API DATA
add_action( 'rest_api_init', function () {
    register_rest_route( 'iproject/v1', '/timebank_front', array(
        'methods' => 'GET',
        'callback' => 'TimebankAPI::getData',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route( 'iproject/v1', '/create_new_transaction', array(
        'methods' => 'POST',
        'callback' => 'TimebankAPI::createNewTransaction',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route( 'iproject/v1', '/transaction_action', array(
        'methods' => 'POST',
        'callback' => 'TimebankAPI::transactionAction',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route( 'iproject/v1', '/search_user', array(
        'methods' => 'GET',
        'callback' => 'TimebankAPI::searchUser',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));
});
