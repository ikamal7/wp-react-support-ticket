<?php
/**
 * Plugin Name: WP Support Ticket React
 * Plugin URI: https://github.com/ikamal7/wp-react-support-ticket
 * Description: A simple support ticket system with react admin panel
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.2
 * Author: Kamal Hosen
 * Author URI: https://github.com/ikamal7
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-support-ticket
 * Domain Path: /languages
 *
 * @package WP_Support_Ticket
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WPST_VERSION', '1.0.0');
define('WPST_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPST_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class for WP Support Ticket React
 *
 * @since 1.0.0
 */
class WPSupportTicket {
    /**
     * Holds the single instance of this class
     *
     * @since 1.0.0
     * @var WPSupportTicket|null
     */
    private static $instance = null;

    /**
     * Returns the single instance of this class
     *
     * @since 1.0.0
     * @return WPSupportTicket The single instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation
     *
     * @since 1.0.0
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
    }

    /**
     * Plugin activation hook callback
     * Creates database tables and flushes rewrite rules
     *
     * @since 1.0.0
     * @return void
     */
    public function activate() {
        $this->create_tables();
        flush_rewrite_rules();
    }

    /**
     * Initialize the plugin
     * Loads text domain, includes required files, and sets up admin and frontend hooks
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('wp-support-ticket', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Include required files
        $this->include_files();
        
        // Initialize admin
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_menu_page'));
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        }

        // Initialize frontend
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_shortcode('support_ticket_form', array($this, 'render_ticket_form'));
        add_shortcode('support_ticket_list', array($this, 'render_ticket_list'));
    }

    /**
     * Include required files for the plugin
     *
     * @since 1.0.0
     * @return void
     */
    private function include_files() {
        require_once WPST_PLUGIN_DIR . 'includes/class-ticket-controller.php';
        require_once WPST_PLUGIN_DIR . 'includes/class-rest-api.php';
    }

    /**
     * Create database tables for tickets and replies
     *
     * Creates two tables:
     * - support_tickets: Stores ticket information
     * - support_ticket_replies: Stores replies to tickets
     *
     * @since 1.0.0
     * @global wpdb $wpdb WordPress database abstraction object
     * @return void
     */
    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Create support_tickets table
        $sql_tickets = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}support_tickets (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            subject varchar(255) NOT NULL,
            message text NOT NULL,
            priority varchar(20) NOT NULL,
            department varchar(50) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'open',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        dbDelta($sql_tickets);

        // Create support_ticket_replies table
        $sql_replies = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}support_ticket_replies (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            ticket_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            message text NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY ticket_id (ticket_id)
        ) $charset_collate;";

        dbDelta($sql_replies);

        // Check if tables were created successfully
        $tickets_table = $wpdb->prefix . 'support_tickets';
        $replies_table = $wpdb->prefix . 'support_ticket_replies';

        if ($wpdb->get_var("SHOW TABLES LIKE '$tickets_table'") != $tickets_table) {
            error_log('Failed to create support_tickets table');
        }

        if ($wpdb->get_var("SHOW TABLES LIKE '$replies_table'") != $replies_table) {
            error_log('Failed to create support_ticket_replies table');
        }
    }

    /**
     * Add admin menu page for the plugin
     *
     * @since 1.0.0
     * @return void
     */
    public function add_menu_page() {
        add_menu_page(
            'Support Tickets',
            'Support Tickets',
            'manage_options',
            'wp-support-ticket',
            array($this, 'render_admin_page'),
            'dashicons-tickets',
            30
        );
    }

    /**
     * Enqueue admin scripts and styles
     *
     * Loads React app scripts and styles on the admin page
     *
     * @since 1.0.0
     * @return void
     */
    public function admin_scripts() {
        $screen = get_current_screen();
        if ($screen->id === 'toplevel_page_wp-support-ticket') {
            $script_path = 'build/index.js';
            $script_asset_path = WPST_PLUGIN_DIR . 'build/index.asset.php';
            $script_asset = file_exists($script_asset_path)
                ? require($script_asset_path)
                : array('dependencies' => array(), 'version' => filemtime(WPST_PLUGIN_DIR . $script_path));

            wp_enqueue_script(
                'wpst-admin',
                WPST_PLUGIN_URL . $script_path,
                $script_asset['dependencies'],
                $script_asset['version'],
                true
            );

            wp_localize_script('wpst-admin', 'wpstSettings', array(
                'apiUrl' => rest_url('wp-support-ticket/v1'),
                'nonce' => wp_create_nonce('wp_rest')
            ));

            wp_enqueue_style(
                'wpst-admin',
                WPST_PLUGIN_URL . 'build/index.css',
                array(),
                $script_asset['version']
            );
        }
    }

    /**
     * Render the admin page content
     *
     * Outputs the container div where the React app will be mounted
     *
     * @since 1.0.0
     * @return void
     */
    public function render_admin_page() {
        echo '<div id="wpst-admin"></div>';
    }

    /**
     * Render the ticket submission form
     *
     * Handles the [support_ticket_form] shortcode
     *
     * @since 1.0.0
     * @return string HTML content of the ticket form
     */
    public function render_ticket_form() {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to submit a ticket.', 'wp-support-ticket') . '</p>';
        }

        ob_start();
        include WPST_PLUGIN_DIR . 'templates/ticket-form.php';
        return ob_get_clean();
    }

    /**
     * Render the list of user tickets
     *
     * Handles the [support_ticket_list] shortcode
     *
     * @since 1.0.0
     * @return string HTML content of the ticket list
     */
    public function render_ticket_list() {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view tickets.', 'wp-support-ticket') . '</p>';
        }

        ob_start();
        include WPST_PLUGIN_DIR . 'templates/ticket-list.php';
        return ob_get_clean();
    }

    /**
     * Enqueue frontend scripts
     *
     * Loads jQuery and localizes script data for API access
     *
     * @since 1.0.0
     * @return void
     */
    public function frontend_scripts() {
        wp_enqueue_script('jquery');
        
        wp_localize_script('jquery', 'wpstSettings', array(
            'apiUrl' => rest_url('wp-support-ticket/v1'),
            'nonce' => wp_create_nonce('wp_rest')
        ));
    }
}

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 */
WPSupportTicket::get_instance();