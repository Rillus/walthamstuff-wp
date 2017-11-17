<?php
/**
* Plugin Name: WP to Buffer
* Plugin URI: http://www.wpzinc.com/plugins/wp-to-buffer-pro
* Version: 3.2.9
* Author: WP Zinc
* Author URI: http://www.wpzinc.com
* Description: Send WordPress Pages, Posts or Custom Post Types to your Buffer (bufferapp.com) account for scheduled publishing to social networks.
*/

/**
 * WP to Buffer Class
 * 
 * @package   WP_To_Buffer
 * @author    Tim Carr
 * @version   1.0.0
 * @copyright WP Zinc
 */
class WP_To_Buffer {

    /**
     * Holds the class object.
     *
     * @since 3.1.4
     *
     * @var object
     */
    public static $instance;

    /**
     * Plugin
     *
     * @since 3.0.0
     *
     * @var object
     */
    public $plugin = '';

    /**
     * Dashboard
     *
     * @since 3.1.4
     *
     * @var object
     */
    public $dashboard = '';

    /**
     * Constructor. Acts as a bootstrap to load the rest of the plugin
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Plugin Details
        $this->plugin                   = new stdClass;
        $this->plugin->name             = 'wp-to-buffer';
        $this->plugin->settingsName     = 'wp-to-buffer'; // Settings key - ensures upgrade users don't lose settings
        $this->plugin->displayName      = 'WP to Buffer';
        $this->plugin->version          = '3.2.9';
        $this->plugin->buildDate        = '2017-07-29 18:00:00';
        $this->plugin->requires         = 3.6;
        $this->plugin->tested           = '4.8';
        $this->plugin->folder           = plugin_dir_path( __FILE__ );
        $this->plugin->url              = plugin_dir_url( __FILE__ );
        $this->plugin->documentation_url= 'https://www.wpzinc.com/documentation/wordpress-to-buffer-pro';
        $this->plugin->support_url      = 'https://www.wpzinc.com/support';
        $this->plugin->upgrade_url      = 'https://www.wpzinc.com/plugins/wordpress-to-buffer-pro';
        $this->plugin->review_name      = 'wp-to-buffer';
        $this->plugin->review_notice    = sprintf( __( 'Thanks for using %s to schedule your social media statuses on Buffer!', $this->plugin->name ), $this->plugin->displayName );

        // Default Settings
        $this->plugin->publish_default_status  = __( 'New Post: {title} {url}', $this->plugin->name );
        $this->plugin->update_default_status   = __( 'Updated Post: {title} {url}', $this->plugin->name );

        // Upgrade Reasons
        $this->plugin->upgrade_reasons = array(
            array(
                __( 'Pinterest', $this->plugin->name ), 
                __( 'Post to your Pinterest boards', $this->plugin->name ),
            ),
            array(
                __( 'Separate Options per Social Network', $this->plugin->name ), 
                __( 'Define different statuses for each Post Type and Social Network', $this->plugin->name ),
            ),
            array(
                __( 'Post, Author and Custom Meta Tags', $this->plugin->name ), 
                __( 'Dynamically build status updates with Post, Author and Meta tags', $this->plugin->name ),
            ),
            array(
                __( 'Featured Images', $this->plugin->name ), 
                __( 'Choose to display WordPress Featured Images with your status updates', $this->plugin->name ),
            ),
            array(
                __( 'Unlimited Statuses per Profile', $this->plugin->name ), 
                __( 'Send your publish/update statuses any number of times', $this->plugin->name ),
            ),
            array(
                __( 'Individual Settings per Status', $this->plugin->name ), 
                __( 'Each status update can have its own unique settings', $this->plugin->name ),
            ),
            array(
                __( 'Powerful Scheduling', $this->plugin->name ), 
                __( 'Each status update can be added to the start/end of your Buffer queue, posted immediately or scheduled at a specific time', $this->plugin->name ),
            ),
            array(
                __( 'Conditional Publishing', $this->plugin->name ), 
                __( 'Require taxonomy term(s) to be present for Posts to publish to Buffer', $this->plugin->name ),
            ),
            array(
                __( 'Individual Post Settings', $this->plugin->name ), 
                __( 'Each Post can have its own Buffer settings', $this->plugin->name ),
            ),
            array(
                __( 'Bulk Publishing', $this->plugin->name ), 
                __( 'Publish multiple Posts, Pages and Custom Post Types to Buffer', $this->plugin->name ),
            ),
            array(
                __( 'WP-Cron', $this->plugin->name ), 
                __( 'Optionally enable WP-Cron to send status updates via Cron, speeding up UI performance', $this->plugin->name ),
            )
        );
    
        // Dashboard Submodule
        if ( ! class_exists( 'WPZincDashboardWidget' ) ) {
            require_once( $this->plugin->folder . '_modules/dashboard/dashboard.php' );
        }
        $this->dashboard = new WPZincDashboardWidget( $this->plugin );

        // Load required files
        require_once( $this->plugin->folder . 'includes/admin/admin.php' );
        require_once( $this->plugin->folder . 'includes/admin/ajax.php' );
        require_once( $this->plugin->folder . 'includes/admin/buffer-api.php' );
        require_once( $this->plugin->folder . 'includes/admin/common.php' );
        require_once( $this->plugin->folder . 'includes/admin/log.php' );
        require_once( $this->plugin->folder . 'includes/admin/post.php' );
        require_once( $this->plugin->folder . 'includes/admin/publish.php' );
        require_once( $this->plugin->folder . 'includes/admin/settings.php' );

        // Run the migration routine from Free + Pro v2.x --> Pro v3.x
        if ( is_admin() ) {
            WP_To_Buffer_Pro_Settings::get_instance()->migrate_settings();
        }
        
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

// Initialise class
$wp_to_buffer = WP_To_Buffer::get_instance();