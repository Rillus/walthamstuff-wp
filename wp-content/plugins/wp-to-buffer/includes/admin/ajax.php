<?php
/**
 * AJAX class
 * 
 * @package WP_To_Buffer_Pro
 * @author  Tim Carr
 * @version 3.0.0
 */
class WP_To_Buffer_Pro_Ajax {

    /**
     * Holds the class object.
     *
     * @since   3.1.4
     *
     * @var     object
     */
    public static $instance;

    /**
     * Constructor
     *
     * @since   3.0.0
     */
    public function __construct() {

        // Actions
        add_action( 'wp_ajax_wp_to_buffer_pro_clear_log', array( $this, 'clear_log' ) );
        
    }

    /**
     * Clears the plugin log for the given Post ID
     *
     * @since   3.0.0
     */
    public function clear_log() {

        // Run a security check first.
        check_ajax_referer( 'wp-to-buffer-pro-clear-log', 'nonce' );

        // Clear log
        WP_To_Buffer_Pro_Log::get_instance()->clear_log();

        // Done
        wp_die( 1 );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 3.1.4
     *
     * @return object Class.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
            self::$instance = new self;
        }

        return self::$instance;

    }

}

// Load the class
$wp_to_buffer_pro_ajax = WP_To_Buffer_Pro_Ajax::get_instance();