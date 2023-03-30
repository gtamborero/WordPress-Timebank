<?php 
// Main timebank view
// If no parameters then view is main user transactions
// If parameter user is set then search transactions of user and add exchange with user button
// Get user data from transactions posttype
?>

<button onclick="openTransaction();" class="button" style="margin-left:auto; display:block;">New Transaction</button><br>

<!-- TIMEBANK PAYMENT CLOSE BUTTON -->
<button onclick="hideTransaction();" id="timebank_payment_close" style="padding:5px; margin-left:10px; float:right;">X</button>

<style>
    #timebank_payment{
        grid-template-columns: repeat(3, 1fr);
    }
    #timebank_payment > div{
        padding:5px;
    }
    @media (max-width:990px){
        #timebank_payment{
            grid-template-columns: 1fr; 
        }
    }
</style>

<!-- TIMEBANK PAYMENT DIV -->
<!-- no tengo que cargar nada de servidor hasta que no envie transaccion. todo en local y oculto -->
<form id="payment_data">
<div id="timebank_payment" style="padding:15px 0; display:grid;">
        <div>
            User*: <input name="user" type="text"></input>
        </div>
        <div>
            Description*: <input name="description" type="text"></input>
        </div>
        <div>
            Amount*: <input name="amount" type="text"></input>
        </div>
        <div>
            Rate: <input name="rate" type="text"></input>
        </div>
        <div>
            Comment: <input name="comment" type="text"></input>
        </div>

        <button onclick="createNewTransaction();" type="button">SEND TIME</button>

<?php 
/*$users = get_users();
// Array of WP_User objects.
foreach ( $users as $user ) {
	echo '<span>' . esc_html( $user->display_name ) . '</span><br>';
}*/
?>

</div>
</form>

<!-- TIMEBANK DATA DIV -->
<div id="timebank_front">
    <!-- LOADING -->
    <div style="padding:15px; text-align:center;">LOADING ...<div>
</div>

<script>
// Carga VideoId + UserId
    //function getTimebank(){
    jQuery(function(){
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
    })

    function openTransaction(){
        // Show newTransaction (preloaded) + close button
        jQuery('#timebank_payment').fadeIn();
        jQuery('#timebank_payment_close').fadeIn();
    }

    function hideTransaction(){
        jQuery('#timebank_payment').fadeOut();
        jQuery('#timebank_payment_close').fadeOut();
    }

    function createNewTransaction(){
        jQuery.ajax({
            url: "<?php echo site_url(); ?>/wp-json/iproject/v1/create_new_transaction",
            type: "POST",
            data: { 
                'userId':<?php echo get_current_user_id() ?>,
                '_wpnonce-transaction': '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
            },
            contentType: "application/json; charset=utf-8",
            cache: false,
            success: function(data){
                console.log(data);
                // No muestro nada del servidor
                // Le tengo que preguntar 
                jQuery('#timebank_payment').html(data);

            },
            error: function(){
                console.log("Algo salió mal en createNewTransaction");
                jQuery('#timebank_front').html("Algo salió mal en createNewTransaction");
            }
        }); 
    }
</script>



