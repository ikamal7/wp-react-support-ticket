<?php

class WPST_REST_API {
    private $namespace = 'wp-support-ticket/v1';
    private $ticket_controller;

    public function __construct() {
        $this->ticket_controller = new WPST_Ticket_Controller();
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
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

        register_rest_route($this->namespace, '/tickets/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_ticket'),
                'permission_callback' => array($this, 'check_permissions')
            )
        ));
    }

    public function check_permissions() {
        return is_user_logged_in();
    }

    public function get_tickets($request) {
        try {
            $tickets = $this->ticket_controller->get_tickets();
            return new WP_REST_Response($tickets, 200);
        } catch (Exception $e) {
            return new WP_Error('server_error', $e->getMessage(), array('status' => 500));
        }
    }

    public function get_ticket($request) {
        $ticket_id = (int) $request['id'];
        try {
            $ticket = $this->ticket_controller->get_ticket($ticket_id);
            if (!$ticket) {
                return new WP_Error('not_found', 'Ticket not found', array('status' => 404));
            }
            return new WP_REST_Response($ticket, 200);
        } catch (Exception $e) {
            return new WP_Error('server_error', $e->getMessage(), array('status' => 500));
        }
    }

    public function create_ticket($request) {
        $params = $request->get_params();
        
        if (empty($params['subject']) || empty($params['message'])) {
            return new WP_Error('invalid_params', 'Subject and message are required', array('status' => 400));
        }

        try {
            $ticket_id = $this->ticket_controller->create_ticket(array(
                'subject' => sanitize_text_field($params['subject']),
                'message' => wp_kses_post($params['message']),
                'priority' => sanitize_text_field($params['priority']),
                'department' => sanitize_text_field($params['department'])
            ));

            if (!$ticket_id) {
                return new WP_Error('creation_failed', 'Failed to create ticket', array('status' => 500));
            }

            $ticket = $this->ticket_controller->get_ticket($ticket_id);
            return new WP_REST_Response($ticket, 201);
        } catch (Exception $e) {
            return new WP_Error('server_error', $e->getMessage(), array('status' => 500));
        }
    }
}

new WPST_REST_API();