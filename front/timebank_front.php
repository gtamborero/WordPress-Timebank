<?php
if ( ! is_user_logged_in() ) {
	?>
	<div class="timebank-app timebank-app--notice">
		<p><?php esc_html_e( 'Please log in to view and send TimeBank transactions.', 'timebank' ); ?></p>
	</div>
	<?php
	return;
}

$current_user = wp_get_current_user();
$rest_base    = esc_url_raw( rest_url( 'iproject/v1' ) );
$rest_nonce   = wp_create_nonce( 'wp_rest' );
?>

<div
	class="timebank-app"
	data-rest-base="<?php echo esc_url( $rest_base ); ?>"
	data-rest-nonce="<?php echo esc_attr( $rest_nonce ); ?>"
>
	<section class="timebank-summary" aria-live="polite">
		<div>
			<span class="timebank-eyebrow"><?php esc_html_e( 'TimeBank account', 'timebank' ); ?></span>
			<h2><?php echo esc_html( $current_user->display_name ? $current_user->display_name : $current_user->user_login ); ?></h2>
		</div>
		<div class="timebank-balance">
			<span><?php esc_html_e( 'Balance', 'timebank' ); ?></span>
			<strong id="timebank_balance">...</strong>
		</div>
	</section>

	<div class="timebank-actions">
		<button type="button" class="timebank-button timebank-button--primary" id="timebank_open_form">
			<?php esc_html_e( 'New transaction', 'timebank' ); ?>
		</button>
	</div>

	<div id="timebank_modal" class="timebank-modal" hidden>
		<div class="timebank-modal__backdrop" data-timebank-close></div>
		<form id="timebank_payment" class="timebank-form timebank-form--modal" role="dialog" aria-modal="true" aria-labelledby="timebank_modal_title">
			<div class="timebank-form__header">
				<h3 id="timebank_modal_title"><?php esc_html_e( 'Send time', 'timebank' ); ?></h3>
				<button type="button" class="timebank-icon-button" id="timebank_close_form" aria-label="<?php esc_attr_e( 'Close form', 'timebank' ); ?>">
					&times;
				</button>
			</div>

			<label class="timebank-field timebank-field--wide">
				<span><?php esc_html_e( 'Receiver user', 'timebank' ); ?></span>
				<input id="timebank_user_search" type="search" autocomplete="off" placeholder="<?php esc_attr_e( 'Start typing a username...', 'timebank' ); ?>">
				<input id="timebank_receiver_id" name="receiver_id" type="hidden">
				<div id="timebank_found_users" class="timebank-user-results" role="listbox" hidden></div>
			</label>

			<label class="timebank-field timebank-field--wide">
				<span><?php esc_html_e( 'Description', 'timebank' ); ?></span>
				<input name="description" type="text" maxlength="120" required>
			</label>

			<label class="timebank-field">
				<span><?php esc_html_e( 'Amount', 'timebank' ); ?></span>
				<input name="amount" type="number" min="1" step="1" required>
			</label>

			<div class="timebank-field">
				<span><?php esc_html_e( 'Rating', 'timebank' ); ?></span>
				<div class="timebank-rating" role="radiogroup" aria-label="<?php esc_attr_e( 'Rating', 'timebank' ); ?>">
					<input id="timebank_rating_value" name="rate" type="hidden" value="5">
					<?php for ( $star = 1; $star <= 5; $star++ ) : ?>
						<button
							type="button"
							class="timebank-rating__star is-active"
							data-rating="<?php echo esc_attr( $star ); ?>"
							role="radio"
							aria-checked="<?php echo 5 === $star ? 'true' : 'false'; ?>"
							aria-label="<?php echo esc_attr( sprintf( __( '%d stars', 'timebank' ), $star ) ); ?>"
						>&#9733;</button>
					<?php endfor; ?>
					<span id="timebank_rating_label" class="timebank-rating__label"><?php esc_html_e( 'Excellent', 'timebank' ); ?></span>
				</div>
			</div>

			<label class="timebank-field timebank-field--wide">
				<span><?php esc_html_e( 'Comment', 'timebank' ); ?></span>
				<textarea name="comment" rows="3" maxlength="200"></textarea>
			</label>

			<div class="timebank-form__footer">
				<p id="timebank_form_message" class="timebank-message" aria-live="polite"></p>
				<button type="submit" class="timebank-button timebank-button--primary">
					<?php esc_html_e( 'Send time', 'timebank' ); ?>
				</button>
			</div>
		</form>
	</div>

	<div id="timebank_front" class="timebank-transactions" aria-live="polite">
		<div class="timebank-loading"><?php esc_html_e( 'Loading transactions...', 'timebank' ); ?></div>
	</div>
</div>

<script>
(function($) {
	var app = $('.timebank-app').last();
	var restBase = app.data('rest-base');
	var restNonce = app.data('rest-nonce');
	var searchRequest = null;

	function apiHeaders(xhr) {
		xhr.setRequestHeader('X-WP-Nonce', restNonce);
	}

	function showMessage(message, type) {
		$('#timebank_form_message')
			.removeClass('is-error is-success')
			.addClass(type ? 'is-' + type : '')
			.text(message || '');
	}

	function resetForm() {
		$('#timebank_payment').trigger('reset');
		$('#timebank_receiver_id').val('');
		setRating(5);
		$('#timebank_found_users').prop('hidden', true).empty();
		showMessage('');
	}

	function paintRating(rating) {
		rating = Math.max(1, Math.min(5, parseInt(rating, 10) || 5));

		$('.timebank-rating__star').each(function() {
			var starValue = parseInt($(this).data('rating'), 10);
			var isActive = starValue <= rating;
			$(this)
				.toggleClass('is-active', isActive)
				.attr('aria-checked', starValue === rating ? 'true' : 'false');
		});
	}

	function setRating(rating) {
		var labels = {
			1: '<?php echo esc_js( __( 'Too bad', 'timebank' ) ); ?>',
			2: '<?php echo esc_js( __( 'Bad', 'timebank' ) ); ?>',
			3: '<?php echo esc_js( __( 'Normal', 'timebank' ) ); ?>',
			4: '<?php echo esc_js( __( 'Good', 'timebank' ) ); ?>',
			5: '<?php echo esc_js( __( 'Excellent', 'timebank' ) ); ?>'
		};

		rating = Math.max(1, Math.min(5, parseInt(rating, 10) || 5));
		$('#timebank_rating_value').val(rating);
		$('#timebank_rating_label').text(labels[rating]);
		paintRating(rating);
	}

	function openTransactionModal() {
		$('#timebank_modal').prop('hidden', false);
		$('body').addClass('timebank-modal-open');
		$('#timebank_user_search').trigger('focus');
	}

	function closeTransactionModal() {
		$('#timebank_modal').prop('hidden', true);
		$('body').removeClass('timebank-modal-open');
		resetForm();
	}

	function loadTransactions() {
		$('#timebank_front').html('<div class="timebank-loading"><?php echo esc_js( __( 'Loading transactions...', 'timebank' ) ); ?></div>');

		$.ajax({
			url: restBase + '/timebank_front',
			type: 'GET',
			beforeSend: apiHeaders,
			cache: false,
			success: function(response) {
				$('#timebank_front').html(response.html);
				$('#timebank_balance').text(response.balance_label);
			},
			error: function() {
				$('#timebank_front').html('<div class="timebank-empty"><?php echo esc_js( __( 'Transactions could not be loaded.', 'timebank' ) ); ?></div>');
				$('#timebank_balance').text('-');
			}
		});
	}

	$('#timebank_open_form').on('click', function() {
		openTransactionModal();
	});

	$('#timebank_close_form').on('click', function() {
		closeTransactionModal();
	});

	$('#timebank_modal').on('click', '[data-timebank-close]', function() {
		closeTransactionModal();
	});

	$(document).on('keydown', function(event) {
		if (event.key === 'Escape' && !$('#timebank_modal').prop('hidden')) {
			closeTransactionModal();
		}
	});

	$('#timebank_user_search').on('input', function() {
		var userName = $(this).val().trim();
		var results = $('#timebank_found_users');

		$('#timebank_receiver_id').val('');
		results.prop('hidden', true).empty();

		if (userName.length < 2) {
			return;
		}

		if (searchRequest) {
			searchRequest.abort();
		}

		searchRequest = $.ajax({
			url: restBase + '/search_user',
			type: 'GET',
			data: { userName: userName },
			beforeSend: apiHeaders,
			cache: false,
			success: function(users) {
				results.empty();

				if (!users.length) {
					results.append('<div class="timebank-user-results__empty"><?php echo esc_js( __( 'No users found.', 'timebank' ) ); ?></div>');
				}

				users.forEach(function(user) {
					var button = $('<button type="button" class="timebank-user-result"></button>');
					button.text(user.label);
					button.data('user-id', user.id);
					button.data('user-label', user.label);
					results.append(button);
				});

				results.prop('hidden', false);
			}
		});
	});

	$('#timebank_found_users').on('click', '.timebank-user-result', function() {
		$('#timebank_receiver_id').val($(this).data('user-id'));
		$('#timebank_user_search').val($(this).data('user-label'));
		$('#timebank_found_users').prop('hidden', true).empty();
	});

	$('.timebank-rating').on('click', '.timebank-rating__star', function() {
		setRating($(this).data('rating'));
	});

	$('.timebank-rating').on('mouseenter focus', '.timebank-rating__star', function() {
		paintRating($(this).data('rating'));
	});

	$('.timebank-rating').on('mouseleave', function() {
		paintRating($('#timebank_rating_value').val());
	});

	$('.timebank-rating').on('focusout', function(event) {
		if (!$(this).find(event.relatedTarget).length) {
			paintRating($('#timebank_rating_value').val());
		}
	});

	$('.timebank-rating').on('keydown', '.timebank-rating__star', function(event) {
		var current = parseInt($('#timebank_rating_value').val(), 10) || 5;

		if (event.key === 'ArrowLeft' || event.key === 'ArrowDown') {
			event.preventDefault();
			setRating(current - 1);
			$('.timebank-rating__star[data-rating="' + $('#timebank_rating_value').val() + '"]').trigger('focus');
		}

		if (event.key === 'ArrowRight' || event.key === 'ArrowUp') {
			event.preventDefault();
			setRating(current + 1);
			$('.timebank-rating__star[data-rating="' + $('#timebank_rating_value').val() + '"]').trigger('focus');
		}
	});

	$('#timebank_payment').on('submit', function(event) {
		event.preventDefault();

		if (!$('#timebank_receiver_id').val()) {
			showMessage('<?php echo esc_js( __( 'Select a receiver user from the search results.', 'timebank' ) ); ?>', 'error');
			return;
		}

		var submitButton = $(this).find('button[type="submit"]');
		submitButton.prop('disabled', true);
		showMessage('<?php echo esc_js( __( 'Sending transaction...', 'timebank' ) ); ?>');

		$.ajax({
			url: restBase + '/create_new_transaction',
			type: 'POST',
			data: $(this).serialize(),
			beforeSend: apiHeaders,
			cache: false,
			success: function(response) {
				showMessage(response.message, 'success');
				resetForm();
				$('#timebank_modal').prop('hidden', true);
				$('body').removeClass('timebank-modal-open');
				loadTransactions();
			},
			error: function(xhr) {
				var message = xhr.responseJSON && xhr.responseJSON.message
					? xhr.responseJSON.message
					: '<?php echo esc_js( __( 'The transaction could not be created.', 'timebank' ) ); ?>';
				showMessage(message, 'error');
			},
			complete: function() {
				submitButton.prop('disabled', false);
			}
		});
	});

	loadTransactions();
})(jQuery);
</script>
