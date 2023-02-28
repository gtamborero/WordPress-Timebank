<?php

// Academy-block PLUGIN
if ( !class_exists( 'TimebankAPI' ) ) {

    class TimebankAPI
    {
 
        // Main API function
        public static function getData() {
        
        // Start Timebank front echoing 
        ob_start();
        
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

        <div class="timebank-grid" style="background-color:#333; color:#fff;">
            <div>Date</div>
            <div>Description</div>
            <div>User</div>
            <div>Amount</div>
            <div>Total</div>
            <div>Rating</div>
            <div>Comment</div>
        </div>

        <div class="timebank-grid">
        <?php
            if ( $query->have_posts() ){
                while ( $query->have_posts() ){
                    $query->the_post();
                    $post = $query->post; 
                    ?>

                    <div><?php the_time( 'l, j/m/Y' ); ?></div>
                    <div><?php the_title(); ?></div>

                    <div><?php
                    // Show the name of the sibling user
                    if (isUserTimeReceiver($post->_timebank_receiver)){
                        echo getUserNameById ($post->_timebank_payer);
                    }else{
                        echo getUserNameById ($post->_timebank_receiver);
                    }
                    ?></div>

                    <div><?php
                    // Show amount as positive or negative value
                    if (isUserTimeReceiver($post->_timebank_receiver)){
                        echo $post->_timebank_amount; 
                    }else{
                        echo "<span style='color:#900'>- " . $post->_timebank_amount . "</span>";
                    }?></div>

                    <div>Total</div>
                    <div><?php echo printStars ($post->_timebank_rating); ?></div>
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