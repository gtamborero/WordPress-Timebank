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

// Check if user can manage timebank Options
function userCanManageOptions(){
    if ( !current_user_can( 'manage_options' ) )  {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
}

//ADMIN MENU CONFIGURATION
function timebank_options() {
    userCanManageOptions();
    $timebank_notice = '';

    if ( isset( $_POST['option'] ) && 'edit' === $_POST['option'] ) {
        $timebank_notice = timebank_save_configuration();
    }

    $config = timebank_get_configuration();
    if ( ! $config ) {
        $config = timebank_default_configuration();
        $timebank_notice = '<div class="notice notice-warning"><p>' . esc_html__( 'TimeBank configuration row was not found. Default values are being shown.', 'timebank' ) . '</p></div>';
    }

    include plugin_dir_path( __FILE__ ) . 'configuration_page.php';
}

function timebank_get_configuration() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'tbank_conf';
    return $wpdb->get_row( "SELECT * FROM {$table_name} WHERE id = 1" );
}

function timebank_default_configuration() {
    return (object) array(
        'default_anonymous'  => '(deleted user)',
        'default_min_limit'  => 120,
        'default_max_limit'  => 180,
        'exchange_timeout'   => 48,
        'currency'           => 'minutes',
        'path_to_timebank'   => '',
        'admin_mail'         => 1,
        'starting_amount'    => 0,
        'email_original_text' => '',
        'email_text'         => '',
    );
}

function timebank_save_configuration() {
    global $wpdb;

    check_admin_referer( 'timebank_save_configuration' );

    $table_name = $wpdb->prefix . 'tbank_conf';
    $data       = array(
        'default_anonymous' => isset( $_POST['defaultanonymous'] ) ? sanitize_text_field( wp_unslash( $_POST['defaultanonymous'] ) ) : '',
        'default_min_limit' => isset( $_POST['defaultminlimit'] ) ? (int) $_POST['defaultminlimit'] : 0,
        'default_max_limit' => isset( $_POST['defaultmaxlimit'] ) ? (int) $_POST['defaultmaxlimit'] : 0,
        'exchange_timeout'  => isset( $_POST['exchangetimeout'] ) ? (int) $_POST['exchangetimeout'] : 0,
        'currency'          => isset( $_POST['currency'] ) ? sanitize_text_field( wp_unslash( $_POST['currency'] ) ) : '',
        'admin_mail'        => isset( $_POST['adminmail'] ) ? (int) $_POST['adminmail'] : 0,
        'starting_amount'   => isset( $_POST['startingamount'] ) ? (int) $_POST['startingamount'] : 0,
        'email_text'        => isset( $_POST['emailtext'] ) ? sanitize_textarea_field( wp_unslash( $_POST['emailtext'] ) ) : '',
    );

    $updated = $wpdb->update(
        $table_name,
        $data,
        array( 'id' => 1 ),
        array( '%s', '%d', '%d', '%d', '%s', '%d', '%d', '%s' ),
        array( '%d' )
    );

    if ( false === $updated ) {
        return '<div class="notice notice-error"><p>' . esc_html__( 'TimeBank configuration could not be saved.', 'timebank' ) . '</p></div>';
    }

    return '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'TimeBank configuration saved.', 'timebank' ) . '</p></div>';
}

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

            <option value="<?php echo $user->ID; ?>" <?php selected( $timebank_payer, $user->ID ); ?>>
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

            <option value="<?php echo $user->ID; ?>" <?php selected( $timebank_receiver, $user->ID ); ?>>
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

function timebank_user_limits_fields( $user ) {
    $limits   = timebank_get_user_limits( $user->ID );
    $currency = timebank_get_config_value( 'currency', 'minutes' );
    ?>
    <h2><?php esc_html_e( 'TimeBank Limits', 'timebank' ); ?></h2>
    <table class="form-table" role="presentation">
        <tr>
            <th><label for="timebank_min_limit"><?php esc_html_e( 'Minimum balance limit', 'timebank' ); ?></label></th>
            <td>
                <input
                    type="number"
                    name="timebank_min_limit"
                    id="timebank_min_limit"
                    value="<?php echo esc_attr( $limits['min_limit'] ); ?>"
                    class="regular-text"
                    step="1"
                />
                <p class="description"><?php echo esc_html( sprintf( __( 'Lowest balance allowed for this user. It is applied as -%1$s %2$s.', 'timebank' ), abs( (int) $limits['min_limit'] ), $currency ) ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="timebank_max_limit"><?php esc_html_e( 'Maximum balance limit', 'timebank' ); ?></label></th>
            <td>
                <input
                    type="number"
                    name="timebank_max_limit"
                    id="timebank_max_limit"
                    value="<?php echo esc_attr( $limits['max_limit'] ); ?>"
                    class="regular-text"
                    step="1"
                />
                <p class="description"><?php echo esc_html( sprintf( __( 'Highest balance allowed for this user in %s.', 'timebank' ), $currency ) ); ?></p>
                <?php wp_nonce_field( 'timebank_save_user_limits', 'timebank_user_limits_nonce' ); ?>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'timebank_user_limits_fields' );
add_action( 'edit_user_profile', 'timebank_user_limits_fields' );

function timebank_save_user_limits( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }

    if ( ! isset( $_POST['timebank_user_limits_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['timebank_user_limits_nonce'] ) ), 'timebank_save_user_limits' ) ) {
        return;
    }

    if ( isset( $_POST['timebank_min_limit'] ) ) {
        update_user_meta( $user_id, '_timebank_min_limit', absint( $_POST['timebank_min_limit'] ) );
    }

    if ( isset( $_POST['timebank_max_limit'] ) ) {
        update_user_meta( $user_id, '_timebank_max_limit', absint( $_POST['timebank_max_limit'] ) );
    }
}
add_action( 'personal_options_update', 'timebank_save_user_limits' );
add_action( 'edit_user_profile_update', 'timebank_save_user_limits' );


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

//order by no funciona  pq se esta buscando el id no el nombre a la vista idem search)
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
        });
    }
}

// CUSTOM ADMIN COLUMNS
tbank_add_admin_column(__('Time Payer'), 'tbank-transaction', function($post_id){
    $userId = get_post_meta( $post_id , '_timebank_payer' , true );
    $user = get_user_by('id', $userId);
    echo $user->user_login;
}, '_timebank_payer', true);

tbank_add_admin_column(__('Time Receiver'), 'tbank-transaction', function($post_id){
    $userId = get_post_meta( $post_id , '_timebank_receiver' , true );
    $user = get_user_by('id', $userId);
    echo $user->user_login;
}, '_timebank_receiver', true);

tbank_add_admin_column(__('Amount'), 'tbank-transaction', function($post_id){
    echo get_post_meta( $post_id , '_timebank_amount' , true ); 
}, '_timebank_amount', true);

tbank_add_admin_column(__('Rating'), 'tbank-transaction', function($post_id){
    echo printStars(get_post_meta( $post_id , '_timebank_rating' , true)); 
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
                select ID from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
                and meta_key in ('_timebank_payer','_timebank_receiver')
                and meta_value like %s
            )
            ";
      
            // Podemos alterar este like para transformar la busqueda a ID de usuario
            // Nos entra nombre de usuario
            $user = get_user_by('login', $wpdb->esc_like($query->query['s']));
            $like   = $user->ID;
            $search = preg_replace("#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
                $wpdb->prepare($sql, $like), $search);
        }
        return $search;
    }
}
