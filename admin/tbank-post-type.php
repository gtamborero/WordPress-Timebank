<?php

if ( ! function_exists('tbank_transaction') ) {

    // Register Custom Post Type for timebank transaction
    function tbank_transaction() {
    
        $labels = array(
            'name'                  => _x( 'Transactions', 'Post Type General Name', 'tbank' ),
            'singular_name'         => _x( 'Transaction', 'Post Type Singular Name', 'tbank' ),
            'menu_name'             => __( 'Transactions', 'tbank' ),
            'name_admin_bar'        => __( 'Transactions', 'tbank' ),
            'archives'              => __( 'Item Archives', 'tbank' ),
            'attributes'            => __( 'Item Attributes', 'tbank' ),
            'parent_item_colon'     => __( 'Parent Item:', 'tbank' ),
            'all_items'             => __( 'View Transactions', 'tbank' ),
            'add_new_item'          => __( 'Add Transaction', 'tbank' ),
            'add_new'               => __( 'Add Transaction', 'tbank' ),
            'new_item'              => __( 'New Item', 'tbank' ),
            'edit_item'             => __( 'Edit Transaction', 'tbank' ),
            'update_item'           => __( 'Update Item', 'tbank' ),
            'view_item'             => __( 'View Item', 'tbank' ),
            'view_items'            => __( 'View Items', 'tbank' ),
            'search_items'          => __( 'Search Item', 'tbank' ),
            'not_found'             => __( 'Not found', 'tbank' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'tbank' ),
            'featured_image'        => __( 'Featured Image', 'tbank' ),
            'set_featured_image'    => __( 'Set featured image', 'tbank' ),
            'remove_featured_image' => __( 'Remove featured image', 'tbank' ),
            'use_featured_image'    => __( 'Use as featured image', 'tbank' ),
            'insert_into_item'      => __( 'Insert into item', 'tbank' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'tbank' ),
            'items_list'            => __( 'Items list', 'tbank' ),
            'items_list_navigation' => __( 'Items list navigation', 'tbank' ),
            'filter_items_list'     => __( 'Filter items list', 'tbank' ),
        );
        $args = array(
            'label'                 => __( 'Transaction', 'tbank' ),
            'description'           => __( 'TimeBank Transactions view', 'tbank' ),
            'labels'                => $labels,
            'supports'              => array( 'title' ),
            /*'taxonomies'            => array( 'category', 'post_tag' ),*/
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-database',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'rewrite'               => false,
            'capability_type'       => 'page',
        );
        register_post_type( 'tbank-transaction', $args );
    
    }
    add_action( 'init', 'tbank_transaction', 0 );
    
    }
