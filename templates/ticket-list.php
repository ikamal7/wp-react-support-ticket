<?php
$current_user = wp_get_current_user();
$ticket_controller = new WPST_Ticket_Controller();
$tickets = $ticket_controller->get_tickets(array('user_id' => $current_user->ID));
?>
<div class="wpst-ticket-list">
    <h2><?php _e('My Support Tickets', 'wp-support-ticket'); ?></h2>

    <?php if (empty($tickets)): ?>
        <p><?php _e('No tickets found.', 'wp-support-ticket'); ?></p>
    <?php else: ?>
        <div class="tickets-table-wrapper">
            <table class="tickets-table">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'wp-support-ticket'); ?></th>
                        <th><?php _e('Subject', 'wp-support-ticket'); ?></th>
                        <th><?php _e('Department', 'wp-support-ticket'); ?></th>
                        <th><?php _e('Priority', 'wp-support-ticket'); ?></th>
                        <th><?php _e('Status', 'wp-support-ticket'); ?></th>
                        <th><?php _e('Created', 'wp-support-ticket'); ?></th>
                        <th><?php _e('Action', 'wp-support-ticket'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td>#<?php echo esc_html($ticket->id); ?></td>
                            <td><?php echo esc_html($ticket->subject); ?></td>
                            <td><?php echo esc_html(ucfirst($ticket->department)); ?></td>
                            <td><?php echo esc_html(ucfirst($ticket->priority)); ?></td>
                            <td><?php echo esc_html(ucfirst($ticket->status)); ?></td>
                            <td><?php echo esc_html(date('Y-m-d H:i', strtotime($ticket->created_at))); ?></td>
                            <td>
                                <button class="button view-ticket" data-ticket-id="<?php echo esc_attr($ticket->id); ?>">
                                    <?php _e('View', 'wp-support-ticket'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Ticket Details Modal -->
        <div id="ticket-modal" class="wpst-modal" style="display: none;">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div id="ticket-details"></div>
                <div class="ticket-replies"></div>
                <div class="reply-form">
                    <h3><?php _e('Add Reply', 'wp-support-ticket'); ?></h3>
                    <textarea id="reply-message" rows="4"></textarea>
                    <button class="button button-primary submit-reply"><?php _e('Submit Reply', 'wp-support-ticket'); ?></button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.wpst-ticket-list {
    margin: 20px 0;
}

.tickets-table-wrapper {
    overflow-x: auto;
}

.tickets-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.tickets-table th,
.tickets-table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

.tickets-table th {
    background-color: #f5f5f5;
}

.wpst-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    width: 80%;
    max-width: 800px;
    border-radius: 4px;
    position: relative;
}

.close {
    position: absolute;
    right: 10px;
    top: 10px;
    font-size: 24px;
    cursor: pointer;
}

.ticket-replies {
    margin: 20px 0;
    max-height: 400px;
    overflow-y: auto;
}

.reply {
    margin-bottom: 15px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.reply-form textarea {
    width: 100%;
    margin-bottom: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    var modal = $('#ticket-modal');
    var currentTicketId = null;

    $('.view-ticket').on('click', function() {
        currentTicketId = $(this).data('ticket-id');
        loadTicketDetails(currentTicketId);
    });

    $('.close').on('click', function() {
        modal.hide();
    });

    $(window).on('click', function(e) {
        if ($(e.target).is(modal)) {
            modal.hide();
        }
    });

    $('.submit-reply').on('click', function() {
        var message = $('#reply-message').val();
        if (!message) return;

        $(this).prop('disabled', true);

        $.ajax({
            url: wpstSettings.apiUrl + '/tickets/' + currentTicketId + '/replies',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpstSettings.nonce);
            },
            data: { message: message },
            success: function(response) {
                $('#reply-message').val('');
                loadTicketDetails(currentTicketId);
            },
            error: function(xhr) {
                alert('Error adding reply. Please try again.');
            },
            complete: function() {
                $('.submit-reply').prop('disabled', false);
            }
        });
    });

    function loadTicketDetails(ticketId) {
        $.ajax({
            url: wpstSettings.apiUrl + '/tickets/' + ticketId,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpstSettings.nonce);
            },
            success: function(ticket) {
                var html = '<h2>Ticket #' + ticket.id + ': ' + ticket.subject + '</h2>';
                html += '<p><strong>Status:</strong> ' + ticket.status + '</p>';
                html += '<p><strong>Priority:</strong> ' + ticket.priority + '</p>';
                html += '<p><strong>Department:</strong> ' + ticket.department + '</p>';
                html += '<p><strong>Message:</strong></p>';
                html += '<div class="ticket-message">' + ticket.message + '</div>';

                $('#ticket-details').html(html);
                loadReplies(ticketId);
                modal.show();
            }
        });
    }

    function loadReplies(ticketId) {
        $.ajax({
            url: wpstSettings.apiUrl + '/tickets/' + ticketId + '/replies',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpstSettings.nonce);
            },
            success: function(replies) {
                var html = '<h3>Replies</h3>';
                if (replies.length === 0) {
                    html += '<p>No replies yet.</p>';
                } else {
                    replies.forEach(function(reply) {
                        html += '<div class="reply">';
                        html += '<p>' + reply.message + '</p>';
                        html += '<small>Posted on ' + new Date(reply.created_at).toLocaleString() + '</small>';
                        html += '</div>';
                    });
                }
                $('.ticket-replies').html(html);
            }
        });
    }
});
</script>