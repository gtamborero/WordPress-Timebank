<?php 
function getUserNameById($id){
  $args = [
    'include' => [ $id ], // ID's of users you want to get
    'fields'  => [ 'user_login', 'user_email' ],
  ];
  $users = get_users( $args );
  return $users[0]->user_login;
}

function printStars($number){
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