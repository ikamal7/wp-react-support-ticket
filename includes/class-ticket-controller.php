<?php

class WPST_Ticket_Controller {
    private $wpdb;
    private $tickets_table;
    private $replies_table;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tickets_table = $wpdb->prefix . 'support_tickets';
        $this->replies_table = $wpdb->prefix . 'support_ticket_replies';
    }

    public function create_ticket($data) {
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        $ticket_data = array(
            'user_id' => $user_id,
            'subject' => sanitize_text_field($data['subject']),
            'message' => wp_kses_post($data['message']),
            'priority' => sanitize_text_field($data['priority']),
            'department' => sanitize_text_field($data['department']),
            'status' => 'open'
        );

        $inserted = $this->wpdb->insert(
            $this->tickets_table,
            $ticket_data,
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );

        if ($inserted) {
            return $this->wpdb->insert_id;
        }

        return false;
    }

    public function get_tickets($args = array()) {
        $defaults = array(
            'user_id' => 0,
            'status' => '',
            'department' => '',
            'priority' => '',
            'per_page' => 10,
            'page' => 1,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );

        $args = wp_parse_args($args, $defaults);
        $where = array('1=1');
        $where_values = array();

        if ($args['user_id']) {
            $where[] = 'user_id = %d';
            $where_values[] = $args['user_id'];
        }

        if ($args['status']) {
            $where[] = 'status = %s';
            $where_values[] = $args['status'];
        }

        if ($args['department']) {
            $where[] = 'department = %s';
            $where_values[] = $args['department'];
        }

        if ($args['priority']) {
            $where[] = 'priority = %s';
            $where_values[] = $args['priority'];
        }

        $offset = ($args['page'] - 1) * $args['per_page'];
        
        $query = $this->wpdb->prepare(
            "SELECT * FROM {$this->tickets_table} WHERE " . implode(' AND ', $where) . 
            " ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d",
            array_merge($where_values, array($args['per_page'], $offset))
        );

        return $this->wpdb->get_results($query);
    }

    public function get_ticket($ticket_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tickets_table} WHERE id = %d",
                $ticket_id
            )
        );
    }

    public function update_ticket_status($ticket_id, $status) {
        return $this->wpdb->update(
            $this->tickets_table,
            array('status' => $status),
            array('id' => $ticket_id),
            array('%s'),
            array('%d')
        );
    }

    public function add_reply($ticket_id, $message) {
        $user_id = get_current_user_id();

        $reply_data = array(
            'ticket_id' => $ticket_id,
            'user_id' => $user_id,
            'message' => wp_kses_post($message)
        );

        $inserted = $this->wpdb->insert(
            $this->replies_table,
            $reply_data,
            array('%d', '%d', '%s')
        );

        if ($inserted) {
            return $this->wpdb->insert_id;
        }

        return false;
    }

    public function get_replies($ticket_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->replies_table} WHERE ticket_id = %d ORDER BY created_at ASC",
                $ticket_id
            )
        );
    }

    public function count_tickets($args = array()) {
        $defaults = array(
            'user_id' => 0,
            'status' => '',
            'department' => '',
            'priority' => ''
        );

        $args = wp_parse_args($args, $defaults);
        $where = array('1=1');
        $where_values = array();

        if ($args['user_id']) {
            $where[] = 'user_id = %d';
            $where_values[] = $args['user_id'];
        }

        if ($args['status']) {
            $where[] = 'status = %s';
            $where_values[] = $args['status'];
        }

        if ($args['department']) {
            $where[] = 'department = %s';
            $where_values[] = $args['department'];
        }

        if ($args['priority']) {
            $where[] = 'priority = %s';
            $where_values[] = $args['priority'];
        }

        $query = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->tickets_table} WHERE " . implode(' AND ', $where),
            $where_values
        );

        return $this->wpdb->get_var($query);
    }
}