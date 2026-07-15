<?php 
function getUserNameById($id){
  $args = [
    'include' => [ $id ], // ID's of users you want to get
    'fields'  => [ 'user_login', 'user_email' ],
  ];
  $users = get_users( $args );
  return isset( $users[0] ) ? $users[0]->user_login : __( '(deleted user)', 'timebank' );
}

function printStars($number){
    $number = max( 0, min( 5, (int) $number ) );
    $count = 1;
    $stars = "";
	while($count <= $number){
    	$stars .= "&#9733;";
      $count++;
    }
    return '<span style="color:goldenrod">' . $stars . '</span>';
}

function isUserTimeReceiver($receiverId){
  $userId = get_current_user_id();
  if ($userId == $receiverId) return true;
}

function timebank_get_config_value( $key, $default = null ) {
  if ( function_exists( 'timebank_get_configuration' ) ) {
    $config = timebank_get_configuration();
    if ( $config && isset( $config->$key ) ) {
      return $config->$key;
    }
  }

  return $default;
}

function timebank_get_starting_amount() {
  return (int) timebank_get_config_value( 'starting_amount', 0 );
}

function timebank_get_user_limits( $user_id ) {
  $default_min_limit = (int) timebank_get_config_value( 'default_min_limit', 0 );
  $default_max_limit = (int) timebank_get_config_value( 'default_max_limit', 0 );
  $saved_min_limit   = get_user_meta( $user_id, '_timebank_min_limit', true );
  $saved_max_limit   = get_user_meta( $user_id, '_timebank_max_limit', true );

  return array(
    'min_limit' => '' !== $saved_min_limit ? (int) $saved_min_limit : $default_min_limit,
    'max_limit' => '' !== $saved_max_limit ? (int) $saved_max_limit : $default_max_limit,
  );
}

function timebank_get_user_balance( $user_id ) {
  $posts   = get_posts(
    array(
      'post_type'      => 'tbank-transaction',
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'fields'         => 'ids',
      'meta_query'     => array(
        'relation' => 'OR',
        array(
          'key'     => '_timebank_payer',
          'value'   => $user_id,
          'compare' => '=',
        ),
        array(
          'key'     => '_timebank_receiver',
          'value'   => $user_id,
          'compare' => '=',
        ),
      ),
    )
  );
  $balance = timebank_get_starting_amount();

  foreach ( $posts as $post_id ) {
    $amount   = (int) get_post_meta( $post_id, '_timebank_amount', true );
    $receiver = (int) get_post_meta( $post_id, '_timebank_receiver', true );
    $balance += ( $receiver === (int) $user_id ) ? $amount : -$amount;
  }

  return $balance;
}

function timebank_get_user_limit_bounds( $user_id ) {
  $limits = timebank_get_user_limits( $user_id );

  return array(
    'min_balance' => -abs( (int) $limits['min_limit'] ),
    'max_balance' => abs( (int) $limits['max_limit'] ),
  );
}

function timebank_is_balance_within_limits( $user_id, $balance ) {
  $bounds = timebank_get_user_limit_bounds( $user_id );

  return $balance >= $bounds['min_balance'] && $balance <= $bounds['max_balance'];
}

function timebank_initialize_user_limits( $user_id ) {
  $limits = timebank_get_user_limits( $user_id );

  if ( '' === get_user_meta( $user_id, '_timebank_min_limit', true ) ) {
    update_user_meta( $user_id, '_timebank_min_limit', (int) $limits['min_limit'] );
  }

  if ( '' === get_user_meta( $user_id, '_timebank_max_limit', true ) ) {
    update_user_meta( $user_id, '_timebank_max_limit', (int) $limits['max_limit'] );
  }
}
add_action( 'user_register', 'timebank_initialize_user_limits' );
