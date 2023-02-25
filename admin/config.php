<?php

// ADD CUSTOM TIMEBANK BOX INSIDE ADD / EDIT TRANSACTION
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


// ADD CUSTOM HTML BOX INSIDE ADD / EDIT TRANSACTION
function tbank_custom_box_html( $post ) {
	$timebank_payer = get_post_meta( $post->ID, '_timebank_payer', true );
    $timebank_receiver = get_post_meta( $post->ID, '_timebank_receiver', true );
    $timebank_amount = get_post_meta( $post->ID, '_timebank_amount', true );
    $timebank_rating = get_post_meta( $post->ID, '_timebank_rating', true );
    $timebank_comment = get_post_meta( $post->ID, '_timebank_comment', true );
    
?>
<div class="tbank-edit-grid">

    <label>Time Payer:<br>
        <select name="timebank_payer" id="timebank_payer" class="postbox">

            <option value="">Select user...</option>
            <?php
            $users = get_users();
            foreach ($users as $user){ 
            ?>

                <option value="<?php echo $user->ID; ?>" <?php selected( $timebank_payer, $user->user_login ); ?>>
                    <?php echo $user->user_login; ?>
                </option>

            <?php } ?>
        </select>
    </label>

    <label>Time Receiver:<br>
	    <select name="timebank_receiver" id="timebank_receiver" class="postbox">
    
            <option value="">Select user...</option>
            <?php
            $users = get_users();
            foreach ($users as $user){ 
            ?>

                <option value="<?php echo $user->ID; ?>" <?php selected( $timebank_receiver, $user->user_login ); ?>>
                    <?php echo $user->user_login; ?>
                </option>

            <?php } ?>
        </select>
    </label>

    <label> Amount:<br>
        <input type="text" id="timebank_amount" name="timebank_amount" value="<?php echo $timebank_amount; ?>">
    </label>

    <label> Rating: (1 to 5 stars)<br>
        <input type="number" step="1" min="1" max="5" id="timebank_rating" name="timebank_rating" 
        value="<?php echo $timebank_rating; ?>">
    </label>

    <label style="grid-column: 1 / -1;"> Payer Comment:<br>
        <textarea id="timebank_comment" name="timebank_comment" rows="3" maxlength="200"><?php echo $timebank_comment; ?>
        </textarea>
    </label>

</div>

<style>
    .tbank-edit-grid{
        display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px 20px; padding:10px 10px 10px 0;
    }
    .tbank-edit-grid input{ width:100%;}
    .tbank-edit-grid textarea{ width:100%;}
    .tbank-edit-grid select{ width:100%;}

    @media (max-width:990px){
        .tbank-edit-grid{
        grid-template-columns:1fr; 
    }        
    }
</style>


<?php
}

// SAVE TIMEBANK CUSTO DATA ON PUBLISH
function tbank_save_postdata( $post_id ) {
	if ( array_key_exists( 'timebank_payer', $_POST ) ) {
		update_post_meta(
			$post_id,
			'_timebank_payer',
			$_POST['timebank_payer']
		);
	}
    if ( array_key_exists( 'timebank_receiver', $_POST ) ) {
		update_post_meta(
			$post_id,
			'_timebank_receiver',
			$_POST['timebank_receiver']
		);
	}
    if ( array_key_exists( 'timebank_amount', $_POST ) ) {
		update_post_meta(
			$post_id,
			'_timebank_amount',
			trim($_POST['timebank_amount'])
		);
	}
    if ( array_key_exists( 'timebank_rating', $_POST ) ) {
		update_post_meta(
			$post_id,
			'_timebank_rating',
			$_POST['timebank_rating']
		);
	}
    if ( array_key_exists( 'timebank_comment', $_POST ) ) {
		update_post_meta(
			$post_id,
			'_timebank_comment',
			trim($_POST['timebank_comment'])
		);
	}
}
add_action( 'save_post', 'tbank_save_postdata' );


// ADD COLUMN ON ADMIN LIST VIEW OF TRANSACTIONS POST TYPE
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


// CUSTOM ADMIN COLUMNS
tbank_add_admin_column(__('Time Payer'), 'tbank-transaction', function($post_id){
//var_dump(get_post_meta( $post_id ));
    echo get_post_meta( $post_id , '_timebank_payer' , true ); 
}, '_timebank_payer', true);

tbank_add_admin_column(__('Time Giver'), 'tbank-transaction', function($post_id){
    echo get_post_meta( $post_id , '_timebank_receiver' , true ); 
}, '_timebank_receiver', true);

tbank_add_admin_column(__('Amount'), 'tbank-transaction', function($post_id){
    echo get_post_meta( $post_id , '_timebank_amount' , true ); 
}, '_timebank_amount', true);

tbank_add_admin_column(__('Rating'), 'tbank-transaction', function($post_id){
    echo get_post_meta( $post_id , '_timebank_rating' , true ); 
}, '_timebank_rating', true);


// CUSTOM SEARCH INSIDE META (para buscar usuarios timebank)
// busca bien por titulo y payer y seller
if (!function_exists('extend_admin_search')) {
    add_action('admin_init', 'extend_admin_search');

    /**
     * hook the posts search if we're on the admin page for our type
     */
    function extend_admin_search() {
        global $typenow;

        // Only search if post type is tbank
        if ($typenow === 'tbank-transaction') {
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
