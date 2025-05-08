<?php
$current_user = wp_get_current_user();
?>
<div class="wpst-ticket-form">
    <form id="support-ticket-form" method="post">
        <div class="form-group">
            <label for="name"><?php _e('Name', 'wp-support-ticket'); ?></label>
            <input type="text" id="name" name="name" value="<?php echo esc_attr($current_user->display_name); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="email"><?php _e('Email', 'wp-support-ticket'); ?></label>
            <input type="email" id="email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="subject"><?php _e('Subject', 'wp-support-ticket'); ?></label>
            <input type="text" id="subject" name="subject" required>
        </div>

        <div class="form-group">
            <label for="priority"><?php _e('Priority', 'wp-support-ticket'); ?></label>
            <select id="priority" name="priority" required>
                <option value="low"><?php _e('Low', 'wp-support-ticket'); ?></option>
                <option value="medium"><?php _e('Medium', 'wp-support-ticket'); ?></option>
                <option value="high"><?php _e('High', 'wp-support-ticket'); ?></option>
            </select>
        </div>

        <div class="form-group">
            <label for="department"><?php _e('Department', 'wp-support-ticket'); ?></label>
            <select id="department" name="department" required>
                <option value="accounting"><?php _e('Accounting/Billing', 'wp-support-ticket'); ?></option>
                <option value="technical"><?php _e('Technical', 'wp-support-ticket'); ?></option>
                <option value="sales"><?php _e('Sales', 'wp-support-ticket'); ?></option>
            </select>
        </div>

        <div class="form-group">
            <label for="message"><?php _e('Message', 'wp-support-ticket'); ?></label>
            <textarea id="message" name="message" rows="5" required></textarea>
        </div>

        <div class="form-group">
            <?php wp_nonce_field('wpst_create_ticket', 'wpst_ticket_nonce'); ?>
            <button type="submit" class="button button-primary"><?php _e('Submit Ticket', 'wp-support-ticket'); ?></button>
        </div>
    </form>
</div>

<style>
.wpst-ticket-form .form-group {
    margin-bottom: 15px;
}

.wpst-ticket-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.wpst-ticket-form input[type="text"],
.wpst-ticket-form input[type="email"],
.wpst-ticket-form select,
.wpst-ticket-form textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.wpst-ticket-form textarea {
    resize: vertical;
}

.wpst-ticket-form .button {
    padding: 8px 16px;
}

.ticket-message {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
    display: none;
}

.ticket-message.success {
    background-color: #dff0d8;
    border: 1px solid #d6e9c6;
    color: #3c763d;
}

.ticket-message.error {
    background-color: #f2dede;
    border: 1px solid #ebccd1;
    color: #a94442;
}
</style>

<div id="ticket-message" class="ticket-message"></div>

<script>
jQuery(document).ready(function($) {
    $('#support-ticket-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var messageDiv = $('#ticket-message');
        
        submitButton.prop('disabled', true);
        messageDiv.hide();
        
        $.ajax({
            url: wpstSettings.apiUrl + '/tickets',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpstSettings.nonce);
            },
            data: {
                subject: $('#subject').val(),
                message: $('#message').val(),
                priority: $('#priority').val(),
                department: $('#department').val()
            },
            success: function(response) {
                messageDiv.removeClass('error').addClass('success')
                    .html('Ticket created successfully!')
                    .show();
                form[0].reset();
            },
            error: function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Error creating ticket. Please try again.';
                messageDiv.removeClass('success').addClass('error')
                    .html(message)
                    .show();
            },
            complete: function() {
                submitButton.prop('disabled', false);
            }
        });
    });
});
</script>