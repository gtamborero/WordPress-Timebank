<?php

if ( ! class_exists( 'TimebankAPI' ) ) {

	class TimebankAPI {

		public static function getData() {
			$user_id = get_current_user_id();
			$currency = self::getCurrency();
			$posts = self::getUserTransactions( $user_id, -1 );
			$balance = timebank_get_user_balance( $user_id );
			$limits = timebank_get_user_limit_bounds( $user_id );
			$completed_count = timebank_get_completed_transaction_count( $user_id );
			$pending_incoming = timebank_get_pending_transaction_count( $user_id, 'incoming' );
			$pending_outgoing = timebank_get_pending_transaction_count( $user_id, 'outgoing' );
			$running_balance = timebank_get_starting_amount();
			$balance_after_by_post = array();

			foreach ( array_reverse( $posts ) as $balance_post ) {
				if ( ! timebank_is_transaction_completed( $balance_post->ID ) ) {
					continue;
				}

				$balance_amount = (int) get_post_meta( $balance_post->ID, '_timebank_amount', true );
				$balance_receiver = (int) get_post_meta( $balance_post->ID, '_timebank_receiver', true );
				$running_balance += ( $balance_receiver === $user_id ) ? $balance_amount : -$balance_amount;
				$balance_after_by_post[ $balance_post->ID ] = $running_balance;
			}

			$visible_posts = array_slice( $posts, 0, 50 );

			ob_start();
			?>
			<div class="timebank-dashboard">
				<div class="timebank-stat">
					<span><?php esc_html_e( 'Completed', 'timebank' ); ?></span>
					<strong><?php echo esc_html( $completed_count ); ?></strong>
				</div>
				<div class="timebank-stat">
					<span><?php esc_html_e( 'Pending for me', 'timebank' ); ?></span>
					<strong><?php echo esc_html( $pending_incoming ); ?></strong>
				</div>
				<div class="timebank-stat">
					<span><?php esc_html_e( 'My proposals', 'timebank' ); ?></span>
					<strong><?php echo esc_html( $pending_outgoing ); ?></strong>
				</div>
				<div class="timebank-stat">
					<span><?php esc_html_e( 'Limits', 'timebank' ); ?></span>
					<strong><?php echo esc_html( $limits['min_balance'] . ' / ' . $limits['max_balance'] ); ?></strong>
				</div>
			</div>

			<div class="timebank-table" role="table">
				<div class="timebank-table__row timebank-table__row--head" role="row">
					<div role="columnheader"><?php esc_html_e( 'Date', 'timebank' ); ?></div>
					<div role="columnheader"><?php esc_html_e( 'User', 'timebank' ); ?></div>
					<div role="columnheader"><?php esc_html_e( 'Type', 'timebank' ); ?></div>
					<div role="columnheader"><?php esc_html_e( 'Description', 'timebank' ); ?></div>
					<div role="columnheader"><?php esc_html_e( 'Amount', 'timebank' ); ?></div>
					<div role="columnheader"><?php esc_html_e( 'Balance after', 'timebank' ); ?></div>
					<div role="columnheader"><?php esc_html_e( 'Status', 'timebank' ); ?></div>
					<div role="columnheader"><?php esc_html_e( 'Actions', 'timebank' ); ?></div>
				</div>

				<?php if ( empty( $visible_posts ) ) : ?>
					<div class="timebank-empty"><?php esc_html_e( 'No transactions yet.', 'timebank' ); ?></div>
				<?php endif; ?>

				<?php foreach ( $visible_posts as $post ) : ?>
					<?php
					$payer = (int) get_post_meta( $post->ID, '_timebank_payer', true );
					$receiver = (int) get_post_meta( $post->ID, '_timebank_receiver', true );
					$amount = (int) get_post_meta( $post->ID, '_timebank_amount', true );
					$status = timebank_get_transaction_status( $post->ID );
					$mode = get_post_meta( $post->ID, '_timebank_mode', true );
					$approver_id = (int) get_post_meta( $post->ID, '_timebank_approver', true );
					$is_receiver = ( $receiver === $user_id );
					$other_user_id = $is_receiver ? $payer : $receiver;
					$other_user_name = getUserNameById( $other_user_id );
					$other_user_url = timebank_get_user_profile_url( $other_user_id );
					$amount_label = ( $is_receiver ? '+' : '-' ) . $amount . ' ' . $currency;
					$type_label = 'request' === $mode ? __( 'Request', 'timebank' ) : __( 'Send', 'timebank' );
					$can_resolve = 'pending' === $status && $approver_id === $user_id;
					?>
					<div class="timebank-table__row" role="row">
						<div role="cell" data-label="<?php esc_attr_e( 'Date', 'timebank' ); ?>">
							<?php echo esc_html( get_the_date( 'j/m/Y', $post ) ); ?>
						</div>
						<div role="cell" data-label="<?php esc_attr_e( 'User', 'timebank' ); ?>">
							<a class="timebank-user-link" href="<?php echo esc_url( $other_user_url ); ?>">
								<?php echo esc_html( $other_user_name ); ?>
							</a>
						</div>
						<div role="cell" data-label="<?php esc_attr_e( 'Type', 'timebank' ); ?>">
							<?php echo esc_html( $type_label ); ?>
						</div>
						<div role="cell" data-label="<?php esc_attr_e( 'Description', 'timebank' ); ?>">
							<?php echo esc_html( get_the_title( $post ) ); ?>
						</div>
						<div role="cell" data-label="<?php esc_attr_e( 'Amount', 'timebank' ); ?>">
							<span class="timebank-amount <?php echo $is_receiver ? 'is-positive' : 'is-negative'; ?>">
								<?php echo esc_html( $amount_label ); ?>
							</span>
						</div>
						<div role="cell" data-label="<?php esc_attr_e( 'Balance after', 'timebank' ); ?>">
							<?php if ( 'completed' === $status && isset( $balance_after_by_post[ $post->ID ] ) ) : ?>
								<span class="timebank-balance-after">
									<?php echo esc_html( $balance_after_by_post[ $post->ID ] . ' ' . $currency ); ?>
								</span>
							<?php else : ?>
								<span class="timebank-muted"><?php esc_html_e( '-', 'timebank' ); ?></span>
							<?php endif; ?>
						</div>
						<div role="cell" data-label="<?php esc_attr_e( 'Status', 'timebank' ); ?>">
							<span class="timebank-status timebank-status--<?php echo esc_attr( $status ); ?>">
								<?php echo esc_html( self::getStatusLabel( $status ) ); ?>
							</span>
						</div>
						<div role="cell" data-label="<?php esc_attr_e( 'Actions', 'timebank' ); ?>">
							<?php if ( $can_resolve ) : ?>
								<div class="timebank-row-actions">
									<button type="button" class="timebank-mini-button is-confirm" data-transaction-action="confirm" data-transaction-id="<?php echo esc_attr( $post->ID ); ?>">
										<?php esc_html_e( 'Confirm', 'timebank' ); ?>
									</button>
									<button type="button" class="timebank-mini-button is-reject" data-transaction-action="reject" data-transaction-id="<?php echo esc_attr( $post->ID ); ?>">
										<?php esc_html_e( 'Reject', 'timebank' ); ?>
									</button>
								</div>
							<?php else : ?>
								<span class="timebank-muted"><?php esc_html_e( '-', 'timebank' ); ?></span>
							<?php endif; ?>
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

			$current_user_id = get_current_user_id();
			$mode = isset( $data['mode'] ) && 'request' === $data['mode'] ? 'request' : 'send';
			$target_user_id = isset( $data['receiver_id'] ) ? (int) $data['receiver_id'] : 0;
			$description = isset( $data['description'] ) ? sanitize_text_field( wp_unslash( $data['description'] ) ) : '';
			$amount = isset( $data['amount'] ) ? (int) $data['amount'] : 0;
			$rating = isset( $data['rate'] ) ? (int) $data['rate'] : 0;
			$comment = isset( $data['comment'] ) ? sanitize_textarea_field( wp_unslash( $data['comment'] ) ) : '';

			if ( ! $target_user_id || ! get_user_by( 'id', $target_user_id ) ) {
				return new WP_Error( 'timebank_invalid_user', __( 'Select a valid user.', 'timebank' ), array( 'status' => 400 ) );
			}

			if ( $target_user_id === $current_user_id ) {
				return new WP_Error( 'timebank_same_user', __( 'You cannot create a transaction with yourself.', 'timebank' ), array( 'status' => 400 ) );
			}

			if ( '' === $description ) {
				return new WP_Error( 'timebank_missing_description', __( 'Description is required.', 'timebank' ), array( 'status' => 400 ) );
			}

			if ( $amount <= 0 ) {
				return new WP_Error( 'timebank_invalid_amount', __( 'Amount must be greater than zero.', 'timebank' ), array( 'status' => 400 ) );
			}

			if ( 'request' === $mode ) {
				$payer_id = $target_user_id;
				$receiver_id = $current_user_id;
				$approver_id = $payer_id;
			} else {
				$payer_id = $current_user_id;
				$receiver_id = $target_user_id;
				$approver_id = $receiver_id;
			}

			$limit_check = self::validateTransactionLimits( $payer_id, $receiver_id, $amount );
			if ( is_wp_error( $limit_check ) ) {
				return $limit_check;
			}

			$rating = max( 1, min( 5, $rating ? $rating : 5 ) );

			$post_id = wp_insert_post(
				array(
					'post_type'   => 'tbank-transaction',
					'post_title'  => $description,
					'post_status' => 'publish',
					'post_author' => $current_user_id,
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
			update_post_meta( $post_id, '_timebank_mode', $mode );
			update_post_meta( $post_id, '_timebank_status', 'pending' );
			update_post_meta( $post_id, '_timebank_creator', $current_user_id );
			update_post_meta( $post_id, '_timebank_approver', $approver_id );
			update_post_meta( $post_id, '_timebank_status_changed', current_time( 'mysql' ) );

			$email_sent = self::sendTransactionEmails( $post_id );

			return rest_ensure_response(
				array(
					'id'         => $post_id,
					'email_sent' => $email_sent,
					'message'    => $email_sent
						? __( 'Proposal created and email notifications sent.', 'timebank' )
						: __( 'Proposal created, but email notifications could not be sent.', 'timebank' ),
				)
			);
		}

		public static function transactionAction( $request ) {
			$data = $request->get_params();
			$post_id = isset( $data['transaction_id'] ) ? (int) $data['transaction_id'] : 0;
			$action = isset( $data['transaction_action'] ) ? sanitize_key( $data['transaction_action'] ) : '';
			$user_id = get_current_user_id();
			$post = $post_id ? get_post( $post_id ) : null;

			if ( ! $post || 'tbank-transaction' !== $post->post_type ) {
				return new WP_Error( 'timebank_invalid_transaction', __( 'Invalid transaction.', 'timebank' ), array( 'status' => 404 ) );
			}

			if ( 'pending' !== timebank_get_transaction_status( $post_id ) ) {
				return new WP_Error( 'timebank_transaction_not_pending', __( 'This transaction is no longer pending.', 'timebank' ), array( 'status' => 400 ) );
			}

			$approver_id = (int) get_post_meta( $post_id, '_timebank_approver', true );
			if ( $approver_id !== $user_id ) {
				return new WP_Error( 'timebank_not_allowed', __( 'Only the requested user can resolve this proposal.', 'timebank' ), array( 'status' => 403 ) );
			}

			if ( 'reject' === $action ) {
				update_post_meta( $post_id, '_timebank_status', 'rejected' );
				update_post_meta( $post_id, '_timebank_status_changed', current_time( 'mysql' ) );
				update_post_meta( $post_id, '_timebank_resolved_by', $user_id );

				return rest_ensure_response(
					array(
						'id'      => $post_id,
						'message' => __( 'Proposal rejected.', 'timebank' ),
					)
				);
			}

			if ( 'confirm' !== $action ) {
				return new WP_Error( 'timebank_invalid_action', __( 'Invalid transaction action.', 'timebank' ), array( 'status' => 400 ) );
			}

			$payer_id = (int) get_post_meta( $post_id, '_timebank_payer', true );
			$receiver_id = (int) get_post_meta( $post_id, '_timebank_receiver', true );
			$amount = (int) get_post_meta( $post_id, '_timebank_amount', true );
			$limit_check = self::validateTransactionLimits( $payer_id, $receiver_id, $amount );

			if ( is_wp_error( $limit_check ) ) {
				return $limit_check;
			}

			update_post_meta( $post_id, '_timebank_status', 'completed' );
			update_post_meta( $post_id, '_timebank_status_changed', current_time( 'mysql' ) );
			update_post_meta( $post_id, '_timebank_resolved_by', $user_id );

			return rest_ensure_response(
				array(
					'id'      => $post_id,
					'message' => __( 'Proposal confirmed. The balance has been updated.', 'timebank' ),
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

		private static function validateTransactionLimits( $payer_id, $receiver_id, $amount ) {
			$payer_balance = timebank_get_user_balance( $payer_id );
			$receiver_balance = timebank_get_user_balance( $receiver_id );
			$payer_balance_after = $payer_balance - $amount;
			$receiver_balance_after = $receiver_balance + $amount;
			$payer_bounds = timebank_get_user_limit_bounds( $payer_id );
			$receiver_bounds = timebank_get_user_limit_bounds( $receiver_id );
			$currency = self::getCurrency();

			if ( ! timebank_is_balance_within_limits( $payer_id, $payer_balance_after ) ) {
				return new WP_Error(
					'timebank_payer_limit_reached',
					sprintf(
						/* translators: 1: balance after transaction, 2: minimum allowed, 3: currency */
						__( 'This transaction would leave the payer at %1$s, below the minimum allowed balance of %2$s %3$s.', 'timebank' ),
						$payer_balance_after . ' ' . $currency,
						$payer_bounds['min_balance'],
						$currency
					),
					array( 'status' => 400 )
				);
			}

			if ( ! timebank_is_balance_within_limits( $receiver_id, $receiver_balance_after ) ) {
				return new WP_Error(
					'timebank_receiver_limit_reached',
					sprintf(
						/* translators: 1: balance after transaction, 2: maximum allowed, 3: currency */
						__( 'This transaction would leave the receiver at %1$s, above the maximum allowed balance of %2$s %3$s.', 'timebank' ),
						$receiver_balance_after . ' ' . $currency,
						$receiver_bounds['max_balance'],
						$currency
					),
					array( 'status' => 400 )
				);
			}

			return true;
		}

		private static function getStatusLabel( $status ) {
			$labels = array(
				'pending'   => __( 'Pending', 'timebank' ),
				'completed' => __( 'Completed', 'timebank' ),
				'rejected'  => __( 'Rejected', 'timebank' ),
			);

			return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
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
