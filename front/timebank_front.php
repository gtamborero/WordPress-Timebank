<?php 
// Main timebank view
// If no parameters then view is main user transactions
// If parameter user is set then search transactions of user and add exchange with user button
// Get user data from transactions posttype


?>
<script>
// Carga VideoId + UserId
    //function getTimebank(){
    jQuery.ajax({
        url: "<?php echo site_url(); ?>/wp-json/iproject/v1/timebank_front",
        type: "GET",
        data: { 
            'userId':<?php echo get_current_user_id() ?>,
            '_wpnonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
        },
        contentType: "application/json; charset=utf-8",
        cache: false,
        success: function(data){
            //console.log(data);
            jQuery('#timebank_front').html(data);
        },
        error: function(){
            console.log("Algo salió mal en la consulta api");
            jQuery('#timebank_front').html("Algo salió mal en la consulta api");
        }
    }); 
</script>

<div id="timebank_front">Loading...</div>

