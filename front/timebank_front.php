<?php 
// Main timebank view
// If no parameters then view is main user transactions
// If parameter user is set then search transactions of user and add exchange with user button
// Get user data from transactions posttype

ob_start();
// Start Timebank front echoing 

$userId = get_current_user_id();

$args = array(
    'post_type' => 'tbank-transaction',
    'meta_query' => array(
       
        'relation' => 'OR',
        array(
            'key' => '_timebank_payer',
            'value' => $userId,
            'compare' => '=',
        ),
        array(
            'key' => '_timebank_receiver',
            'value' => $userId,
            'compare' => '=',
        ),
        
    ),
);
$query = new WP_Query( $args );
?>

<div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr 1fr 1fr 1fr; background-color:#333; color:#fff; padding:10px;">
    <div>Fecha</div>
    <div>Descripción</div>
    <div>Receptor</div>
    <div>Importe</div>
    <div>Saldo</div>
    <div>Valoración</div>
    <div>Comentario</div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr 1fr 1fr 1fr; padding:10px;">
<?php
    if ( $query->have_posts() ){
        while ( $query->have_posts() ){
             $query->the_post(); 
            ?>
            <div>1 Feb</div>
            <div><?php the_title(); ?></div>
            <div>Receptor</div>
            <div>importe</div>
            <div>Saldo</div>
            <div>Valoración</div>
            <div>Comentario</div>
<?php
        }
    } 
?>
</div>

<?php
echo "<pre>"; 
var_dump($query->posts);
?> 

