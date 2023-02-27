<?php

// Academy-block PLUGIN
if ( !class_exists( 'TimebankAPI' ) ) {

    class TimebankAPI
    {
        private static $data;

        // Main API function
        public static function getData() {
           
            

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
    <div>Date</div>
    <div>Description</div>
    <div>Receptor</div>
    <div>Amount</div>
    <div>Total</div>
    <div>Rating</div>
    <div>Comment</div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr 1fr 1fr 1fr; padding:10px; gap:15px;">
<?php
    if ( $query->have_posts() ){
        while ( $query->have_posts() ){
            $query->the_post();
            $post = $query->post; 
            ?>
            <div>1 Feb</div>
            <div><?php the_title(); ?></div>
            <div><?php echo getUserNameById ($post->_timebank_receiver); ?></div>
            <div><?php echo $post->_timebank_amount; ?></div>
            <div>Total</div>
            <div><?php echo printStarts ($post->_timebank_rating); ?></div>
            <div><?php echo $post->_timebank_comment; ?></div>
<?php
        }
    } 
?>
</div>

<?php
//echo "<pre>"; 
//var_dump($query->posts);

return ob_get_clean();
        }
    }
}