<?php 
function getUserNameById($id){
  $args = [
    'include' => [ $id ], // ID's of users you want to get
    'fields'  => [ 'user_login', 'user_email' ],
  ];
  $users = get_users( $args );
  return $users[0]->user_login;
}

function printStarts($number){
    $count = 1;
	while($count <= $number){
    	$stars .= "&#9733;";
      $count++;
    }
    return $stars;
}