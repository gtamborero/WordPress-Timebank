<?php 
// Main timebank view
// If no parameters then view is main user transactions
// If parameter user is set then search transactions of user and add exchange with user button
// Get user data from transactions posttype
?>

<div style="padding:15px; background-color:#f5f5f5; margin-bottom:10px;">User data, amount, stats...</div>

<button onclick="openTransaction();" class="button" style="width:100%; margin-left:auto; display:block;">New Transaction / Search user</button><br>

<!-- TIMEBANK PAYMENT CLOSE BUTTON -->
<button onclick="hideTransaction();" id="timebank_payment_close" style="padding:5px; margin-left:10px; float:right;">X</button>


<!-- TIMEBANK PAYMENT DIV -->
<!-- no tengo que cargar nada de servidor hasta que no envie transaccion. todo en local y oculto -->
<form id="payment_data">
<div id="timebank_payment" style="padding:15px 0; display:grid;">
        <div>
            <input name="userId" placeholder="Start writing the user name..." type="text" oninput="searchUser(this.value);"></input>
        </div>
        
        <style>
            #foundUsers{
                position: absolute;
                margin-top:50px;
                width:500px;
                height:40px;
                background-color: #fff;
                border:1px solid #eee;
            }
        </style>

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

        <input type="hidden" name="user-id-creator" value="<?php echo get_current_user_id() ?>">

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
        var formData = jQuery('#payment_data').serialize();
        jQuery.ajax({
            url: "<?php echo site_url(); ?>/wp-json/iproject/v1/create_new_transaction",
            type: "POST",
            data: formData,
            //ContentType: "application/json; charset=utf-8",
            cache: false,
            success: function(data){
                console.log(data);
                // No muestro nada del servidor
                // Le tengo que preguntar 
                //jQuery('#timebank_payment').html(data);

            },
            error: function(){
                console.log("Algo salió mal en createNewTransaction");
                jQuery('#timebank_front').html("Algo salió mal en createNewTransaction");
            }
        }); 
    }

    function searchUser(userName){
        
        jQuery.ajax({
            url: "<?php echo site_url(); ?>/wp-json/iproject/v1/search_user",
            type: "GET",
            data: { userName: userName },
            //ContentType: "application/json; charset=utf-8",
            cache: false,
            success: function(data){
                
                data.forEach((user)=>{
                    //console.log(user.data.user_login);
                    jQuery('#foundUsers').html(user.data.user_login);

                })

                // No muestro nada del servidor
                // Le tengo que preguntar 
                //jQuery('#timebank_payment').html(data);
            },
            error: function(){
                console.log("Algo salió mal en createNewTransaction");
                jQuery('#timebank_front').html("Algo salió mal en createNewTransaction");
            }
        }); 
    }
</script>



