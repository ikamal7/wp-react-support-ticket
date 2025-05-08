<?php

class WPST_Ticket_API {
    private $namespace = 'wp-support-ticket/v1';
    private $ticket_controller;

    public function __construct() {
        $this->ticket_controller = new WPST_Ticket_Controller();
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        // Get tickets
        register_rest_route($this->namespace, '/tickets', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_tickets'),
                'permission_callback' => array($this, 'check_permissions')
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_ticket'),
                'permission_callback' => array($this, 'check_permissions')
            )
        ));

        // Single ticket operations
        register_rest_route($this->namespace, '/tickets/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_ticket'),
                'permission_callback' => array($this, 'check_permissions')
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_ticket_status'),
                'permission_callback' => array($this, 'check_permissions')
            )
        ));

        // Ticket replies
        register_rest_route($this->namespace, '/tickets/(?P<id>\d+)/replies', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_replies'),
                'permission_callback' => array($this, 'check_permissions')
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'add_reply'),
                'permission_callback' => array($this, 'check_permissions')
            )
        ));
    }

    public function check_permissions() {
        return current_user_can('manage_options');
    }

    public function get_tickets($request) {
        $args = array(
            'page' => $request->get_param('page') ? (int) $request->get_param('page') : 1,
            'per_page' => $request->get_param('per_page') ? (int) $request->get_param('per_page') : 10,
            'status' => $request->get_param('status'),
            'department' => $request->get_param('department'),
            'priority' => $request->get_param('priority')
        );

        $tickets = $this->ticket_controller->get_tickets($args);
        $total = $this->ticket_controller->count_tickets($args);

        $response = rest_ensure_response($tickets);
        $response->header('X-WP-Total', $total);
        $response->header('X-WP-TotalPages', ceil($total / $args['per_page']));

        return $response;
    }

    public function create_ticket($request) {
        $ticket_id = $this->ticket_controller->create_ticket(array(
            'subject' => $request->get_param('subject'),
            'message' => $request->get_param('message'),
            'priority' => $request->get_param('priority'),
            'department' => $request->get_param('department')
        ));

        if (!$ticket_id) {
            return new WP_Error('ticket_creation_failed', 'Failed to create ticket', array('status' => 500));
        }

        $ticket = $this->ticket_controller->get_ticket($ticket_id);
        return rest_ensure_response($ticket);
    }

    public function get_ticket($request) {
        $ticket_id = (int) $request['id'];
        $ticket = $this->ticket_controller->get_ticket($ticket_id);

        if (!$ticket) {
            return new WP_Error('ticket_not_found', 'Ticket not found', array('status' => 404));
        }

        return rest_ensure_response($ticket);
    }

    public function update_ticket_status($request) {
        $ticket_id = (int) $request['id'];
        $status = $request->get_param('status');

        $updated = $this->ticket_controller->update_ticket_status($ticket_id, $status);

        if (!$updated) {
            return new WP_Error('status_update_failed', 'Failed to update ticket status', array('status' => 500));
        }

        $ticket = $this->ticket_controller->get_ticket($ticket_id);
        return rest_ensure_response($ticket);
    }

    public function get_replies($request) {
        $ticket_id = (int) $request['id'];
        $replies = $this->ticket_controller->get_replies($ticket_id);
        return rest_ensure_response($replies);
    }

    public function add_reply($request) {
        $ticket_id = (int) $request['id'];
        $message = $request->get_param('message');

        $reply_id = $this->ticket_controller->add_reply($ticket_id, $message);

        if (!$reply_id) {
            return new WP_Error('reply_creation_failed', 'Failed to create reply', array('status' => 500));
        }

        $replies = $this->ticket_controller->get_replies($ticket_id);
        return rest_ensure_response($replies);
    }
}

// Initialize the API
new WPST_Ticket_API();