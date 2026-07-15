<?php

if ( ! class_exists( 'TimebankAPI' ) ) {

	class TimebankAPI {

		public static function getData() {
			$user_id = get_current_user_id();
			$currency = self::getCurrency();
			$posts = self::getUserTransactions( $user_id, -1 );
			$balance = 0;

			foreach ( $posts as $post ) {
				$amount = (int) get_post_meta( $post->ID, '_timebank_amount', true );
				$receiver = (int) get_post_meta( $post->ID, '_timebank_receiver', true );
				$balance += ( $receiver === $user_id ) ? $amount : -$amount;
			}

			$visible_posts = array_slice( $posts, 0, 50 );

			ob_start();
			?>
			<div class="timebank-table" role="table">
				<div class="timebank-table__row timebank-table__row--head" role="row">
					<div role="columnheader"><?php esc_html_e( 'Date', 'timebank' ); ?></div>
					<div role="columnheader"><?php esc_html_e( 'User', 'timebank' ); ?></div>
					<div role="columnheader"><?php esc_html_e( 'Description', 'timebank' ); ?></div>
					<div role="columnheader"><?php esc_html_e( 'Amount', 'timebank' ); ?></div>
					<div role="columnheader"><?php esc_html_e( 'Rating', 'timebank' ); ?></div>
					<div role="columnheader"><?php esc_html_e( 'Comment', 'timebank' ); ?></div>
				</div>

				<?php if ( empty( $visible_posts ) ) : ?>
					<div class="timebank-empty"><?php esc_html_e( 'No transactions yet.', 'timebank' ); ?></div>
				<?php endif; ?>

				<?php foreach ( $visible_posts as $post ) : ?>
					<?php
					$payer = (int) get_post_meta( $post->ID, '_timebank_payer', true );
					$receiver = (int) get_post_meta( $post->ID, '_timebank_receiver', true );
					$amount = (int) get_post_meta( $post->ID, '_timebank_amount', true );
					$rating = (int) get_post_meta( $post->ID, '_timebank_rating', true );
					$comment = get_post_meta( $post->ID, '_timebank_comment', true );
					$is_receiver = ( $receiver === $user_id );
					$other_user_id = $is_receiver ? $payer : $receiver;
					$amount_label = ( $is_receiver ? '+' : '-' ) . $amount . ' ' . $currency;
					?>
					<div class="timebank-table__row" role="row">
						<div role="cell" data-label="<?php esc_attr_e( 'Date', 'timebank' ); ?>">
							<?php echo esc_html( get_the_date( 'j/m/Y', $post ) ); ?>
						</div>
						<div role="cell" data-label="<?php esc_attr_e( 'User', 'timebank' ); ?>">
							<?php echo esc_html( getUserNameById( $other_user_id ) ); ?>
						</div>
						<div role="cell" data-label="<?php esc_attr_e( 'Description', 'timebank' ); ?>">
							<?php echo esc_html( get_the_title( $post ) ); ?>
						</div>
						<div role="cell" data-label="<?php esc_attr_e( 'Amount', 'timebank' ); ?>">
							<span class="timebank-amount <?php echo $is_receiver ? 'is-positive' : 'is-negative'; ?>">
								<?php echo esc_html( $amount_label ); ?>
							</span>
						</div>
						<div role="cell" data-label="<?php esc_attr_e( 'Rating', 'timebank' ); ?>">
							<?php echo wp_kses_post( printStars( $rating ) ); ?>
						</div>
						<div role="cell" data-label="<?php esc_attr_e( 'Comment', 'timebank' ); ?>">
							<?php echo esc_html( $comment ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php

			return rest_ensure_response(
				array(
					'html'          => ob_get_clean(),
					'balance'       => $balance,
					'balance_label' => $balance . ' ' . $currency,
				)
			);
		}

		public static function createNewTransaction( $request ) {
			$data = $request->get_params();

			$payer_id = get_current_user_id();
			$receiver_id = isset( $data['receiver_id'] ) ? (int) $data['receiver_id'] : 0;
			$description = isset( $data['description'] ) ? sanitize_text_field( wp_unslash( $data['description'] ) ) : '';
			$amount = isset( $data['amount'] ) ? (int) $data['amount'] : 0;
			$rating = isset( $data['rate'] ) ? (int) $data['rate'] : 0;
			$comment = isset( $data['comment'] ) ? sanitize_textarea_field( wp_unslash( $data['comment'] ) ) : '';

			if ( ! $receiver_id || ! get_user_by( 'id', $receiver_id ) ) {
				return new WP_Error( 'timebank_invalid_receiver', __( 'Select a valid receiver user.', 'timebank' ), array( 'status' => 400 ) );
			}

			if ( $receiver_id === $payer_id ) {
				return new WP_Error( 'timebank_same_user', __( 'You cannot send time to yourself.', 'timebank' ), array( 'status' => 400 ) );
			}

			if ( '' === $description ) {
				return new WP_Error( 'timebank_missing_description', __( 'Description is required.', 'timebank' ), array( 'status' => 400 ) );
			}

			if ( $amount <= 0 ) {
				return new WP_Error( 'timebank_invalid_amount', __( 'Amount must be greater than zero.', 'timebank' ), array( 'status' => 400 ) );
			}

			$rating = max( 1, min( 5, $rating ? $rating : 5 ) );

			$post_id = wp_insert_post(
				array(
					'post_type'   => 'tbank-transaction',
					'post_title'  => $description,
					'post_status' => 'publish',
					'post_author' => $payer_id,
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				return $post_id;
			}

			update_post_meta( $post_id, '_timebank_payer', $payer_id );
			update_post_meta( $post_id, '_timebank_receiver', $receiver_id );
			update_post_meta( $post_id, '_timebank_amount', $amount );
			update_post_meta( $post_id, '_timebank_rating', $rating );
			update_post_meta( $post_id, '_timebank_comment', $comment );

			$email_sent = self::sendTransactionEmails( $post_id );

			return rest_ensure_response(
				array(
					'id'         => $post_id,
					'email_sent' => $email_sent,
					'message'    => $email_sent
						? __( 'Transaction created and email notifications sent.', 'timebank' )
						: __( 'Transaction created, but email notifications could not be sent.', 'timebank' ),
				)
			);
		}

		public static function searchUser( $request ) {
			$data = $request->get_params();
			$user_name_partial = isset( $data['userName'] ) ? sanitize_text_field( wp_unslash( $data['userName'] ) ) : '';

			if ( strlen( $user_name_partial ) < 2 ) {
				return rest_ensure_response( array() );
			}

			$users = get_users(
				array(
					'search'         => '*' . $user_name_partial . '*',
					'search_columns' => array( 'user_login', 'display_name' ),
					'exclude'        => array( get_current_user_id() ),
					'number'         => 8,
					'fields'         => array( 'ID', 'user_login', 'display_name' ),
				)
			);

			$results = array_map(
				function ( $user ) {
					$label = $user->display_name && $user->display_name !== $user->user_login
						? $user->display_name . ' (' . $user->user_login . ')'
						: $user->user_login;

					return array(
						'id'    => (int) $user->ID,
						'login' => $user->user_login,
						'label' => $label,
					);
				},
				$users
			);

			return rest_ensure_response( $results );
		}

		private static function getUserTransactions( $user_id, $limit ) {
			return get_posts(
				array(
					'post_type'      => 'tbank-transaction',
					'post_status'    => 'publish',
					'posts_per_page' => $limit,
					'orderby'        => 'date',
					'order'          => 'DESC',
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
		}

		private static function getCurrency() {
			$config = self::getConfig();

			return ( $config && ! empty( $config->currency ) ) ? $config->currency : 'minutes';
		}

		private static function getConfig() {
			global $wpdb;

			$table_name = $wpdb->prefix . 'tbank_conf';
			return $wpdb->get_row( "SELECT * FROM {$table_name} WHERE id = 1" );
		}

		private static function sendTransactionEmails( $post_id ) {
			$config = self::getConfig();
			$payer_id = (int) get_post_meta( $post_id, '_timebank_payer', true );
			$receiver_id = (int) get_post_meta( $post_id, '_timebank_receiver', true );
			$payer = get_user_by( 'id', $payer_id );
			$receiver = get_user_by( 'id', $receiver_id );

			if ( ! $payer || ! $receiver ) {
				return false;
			}

			$recipients = array_filter(
				array_unique(
					array(
						$payer->user_email,
						$receiver->user_email,
					)
				)
			);

			if ( $config && ! empty( $config->admin_mail ) ) {
				$recipients[] = get_option( 'admin_email' );
			}

			$recipients = array_filter( array_unique( $recipients ), 'is_email' );

			if ( empty( $recipients ) ) {
				return false;
			}

			$subject = sprintf(
				/* translators: %s is the transaction title. */
				__( 'New TimeBank transaction: %s', 'timebank' ),
				get_the_title( $post_id )
			);

			$message = self::buildTransactionEmailMessage( $post_id, $config, $payer, $receiver );
			$from_email = is_email( get_option( 'admin_email' ) ) ? get_option( 'admin_email' ) : 'wordpress@timebank.local';
			$from_name = sanitize_text_field( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
			$headers = array(
				'Content-Type: text/plain; charset=UTF-8',
				'From: ' . $from_name . ' <' . $from_email . '>',
			);
			$all_sent = true;

			foreach ( $recipients as $recipient ) {
				if ( ! wp_mail( $recipient, $subject, $message, $headers ) ) {
					$all_sent = false;
				}
			}

			return $all_sent;
		}

		private static function buildTransactionEmailMessage( $post_id, $config, $payer, $receiver ) {
			$currency = ( $config && ! empty( $config->currency ) ) ? $config->currency : 'minutes';
			$amount = (int) get_post_meta( $post_id, '_timebank_amount', true );
			$comment = get_post_meta( $post_id, '_timebank_comment', true );
			$site_url = home_url();
			$template = ( $config && ! empty( $config->email_text ) )
				? $config->email_text
				: "Hello!\nA new TimeBank transaction has been created on {site_url}.\n\nDescription: {description}\nAmount: {amount} {currency}\nPayer: {payer_name} <{payer_email}>\nReceiver: {receiver_name} <{receiver_email}>\nComment: {comment}\n\nThe {site_name} Team.";

			$replacements = array(
				'{site_name}'       => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
				'{site_url}'        => $site_url,
				'{description}'     => get_the_title( $post_id ),
				'{amount}'          => $amount,
				'{currency}'        => $currency,
				'{payer_name}'      => self::getUserDisplayName( $payer ),
				'{payer_email}'     => $payer->user_email,
				'{receiver_name}'   => self::getUserDisplayName( $receiver ),
				'{receiver_email}'  => $receiver->user_email,
				'{comment}'         => $comment,
				'{transaction_url}' => admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
				'$siteUrl'          => $site_url,
				'$status_name'      => __( 'created', 'timebank' ),
				'$data->concept'    => get_the_title( $post_id ),
				'$data->amount'     => $amount,
				'$data->buyer_name' => self::getUserDisplayName( $payer ),
				'$data->buyer_email' => $payer->user_email,
				'$data->seller_name' => self::getUserDisplayName( $receiver ),
				'$data->seller_email' => $receiver->user_email,
				'$data->datetime_created' => get_the_date( 'Y-m-d H:i:s', $post_id ),
			);

			return strtr( $template, $replacements );
		}

		private static function getUserDisplayName( $user ) {
			return $user->display_name ? $user->display_name : $user->user_login;
		}
	}
}
