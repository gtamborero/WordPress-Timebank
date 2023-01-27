<?php

function tbank_custom_box() {
		add_meta_box(
			'tbank_box_id',                 // Unique ID
			'Timebank - User Transactions',      // Box title
			'tbank_custom_box_html',  // Content callback, must be of type callable
			'tbank-transaction',      // Post type
            'normal',
            'core'
		);
}
add_action( 'add_meta_boxes', 'tbank_custom_box' );

function tbank_custom_box_html( $post ) {
	$timebank_payer = get_post_meta( $post->ID, '_timebank_payer', true );
    $timebank_giver = get_post_meta( $post->ID, '_timebank_giver', true );
	?>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

    <label >Time Payer:</label>
    <label>Time Receiver:</label>

	<select name="timebank_payer" id="timebank_payer" class="postbox">

		<option value="">Select user...</option>
        <?php
        $users = get_users();
        foreach ($users as $user){ 
        ?>

            <option value="<?php echo $user->user_login; ?>" <?php selected( $timebank_payer, $user->user_login ); ?>>
                <?php echo $user->user_login; ?>
            </option>

        <?php } ?>
	</select>

    
	<select name="timebank_giver" id="timebank_giver" class="postbox">
    
		<option value="">Select user...</option>
        <?php
        $users = get_users();
        foreach ($users as $user){ 
        ?>

            <option value="<?php echo $user->user_login; ?>" <?php selected( $timebank_giver, $user->user_login ); ?>>
                <?php echo $user->user_login; ?>
            </option>

        <?php } ?>
	</select>

        </div>
	<?php
}

function tbank_save_postdata( $post_id ) {
	if ( array_key_exists( 'timebank_payer', $_POST ) ) {
		update_post_meta(
			$post_id,
			'_timebank_payer',
			$_POST['timebank_payer']
		);
	}
    if ( array_key_exists( 'timebank_giver', $_POST ) ) {
		update_post_meta(
			$post_id,
			'_timebank_giver',
			$_POST['timebank_giver']
		);
	}
}
add_action( 'save_post', 'tbank_save_postdata' );


// ADD COLUMN ON TRANSACTIONS POST TYPE
function tbank_add_admin_column( $column_title, $post_type, $cb, $order_by = false, $order_by_field_is_meta = false ){

    // Column Header
    add_filter( 'manage_' . $post_type . '_posts_columns', function( $columns ) use ($column_title) {
        $columns[ sanitize_title($column_title) ] = $column_title;
        return $columns;
    } );

    // Column Content
    add_action( 'manage_' . $post_type . '_posts_custom_column' , function( $column, $post_id ) use ($column_title, $cb) {
        if( sanitize_title($column_title) === $column)
          $cb($post_id);
    }, 10, 2 );

    // OrderBy Set?
    if( !empty( $order_by ) ) {

      // Column Sorting
      add_filter( 'manage_edit-' . $post_type . '_sortable_columns', function ( $columns ) use ($column_title, $order_by) {
          $columns[ sanitize_title($column_title) ] = $order_by;
          return $columns;
      } );

      // Column Ordering
      add_action( 'pre_get_posts', function ( $query ) use ($order_by, $order_by_field_is_meta) {
          if( ! is_admin() || ! $query->is_main_query() )
            return;

          if ( sanitize_key($order_by) === $query->get( 'orderby') ) {
              if($order_by_field_is_meta){
                  $query->set( 'orderby', 'meta_value' );
                  $query->set( 'meta_key', sanitize_key($order_by) );
              }
              else {
                  $query->set( 'orderby', sanitize_key($order_by) );
              }
          }
      } );
  
    }

}

tbank_add_admin_column(__('Time Payer'), 'tbank-transaction', function($post_id){
    //var_dump(get_post_meta( $post_id ));
    echo get_post_meta( $post_id , '_timebank_payer' , true ); 
}, '_timebank_payer', true);

tbank_add_admin_column(__('Time Giver'), 'tbank-transaction', function($post_id){
    //var_dump(get_post_meta( $post_id ));
    echo get_post_meta( $post_id , '_timebank_giver' , true ); 
}, '_timebank_giver', true);

// CUSTOM SEARCH INSIDE META (para buscar usuarios timebank)
// busca bien por titulo y payer y seller
if (!function_exists('extend_admin_search')) {
    add_action('admin_init', 'extend_admin_search');

    /**
     * hook the posts search if we're on the admin page for our type
     */
    function extend_admin_search() {
        global $typenow;

        if ($typenow === 'post') {
            add_filter('posts_search', 'posts_search_custom_post_type', 10, 2);
        }
    }

    /**
     * add query condition for custom meta
     * @param string $search the search string so far
     * @param WP_Query $query
     * @return string
     */
    function posts_search_custom_post_type($search, $query) {
        global $wpdb;

        if ($query->is_main_query() && !empty($query->query['s'])) {
            $sql    = "
            or exists (
                select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
                and meta_key in ('_timebank_payer','_timebank_payer')
                and meta_value like %s
            )
        ";
            $like   = '%' . $wpdb->esc_like($query->query['s']) . '%';
            $search = preg_replace("#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
                $wpdb->prepare($sql, $like), $search);
        }

        return $search;
    }
}
