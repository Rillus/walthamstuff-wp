<?php
/**
 * Administration class
 * 
 * @package WP_To_Buffer_Pro
 * @author  Tim Carr
 * @version 3.0.0
 */
class WP_To_Buffer_Pro_Admin {

    /**
     * Holds the class object.
     *
     * @since 3.1.4
     *
     * @var object
     */
    public static $instance;

    /**
     * Holds the base class object.
     *
     * @since 3.2.0
     *
     * @var object
     */
    public $base;

    /**
     * Constructor
     *
     * @since 3.0.0
     */
    public function __construct() {

        // Actions
        add_action( 'init', array( $this, 'oauth' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_css' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'plugins_loaded', array( $this, 'load_language_files' ) );

    }

    /**
     * Stores the access token if supplied, showing a success message
     * Displays any errors from the oAuth process
     *
     * @since   3.2.8
     */
    public function oauth() {

        // Get base instance
        $this->base = WP_To_Buffer::get_instance();

        // If we've returned from the oAuth process and an error occured, add it to the notices
        if ( isset( $_REQUEST[ 'wp-to-buffer-pro-oauth-error' ] ) ) {
            switch( $_REQUEST[ 'wp-to-buffer-pro-oauth-error' ] ) {
                /**
                 * Access Denied
                 * - User denied our app access
                 */
                case 'access_denied':
                    $this->notices['error'][] = __( 'You did not grant our Plugin access to your Buffer account. We are unable to post to Buffer until you do this. Please click on the Authorize Plugin button.', $this->base->plugin->name );
                    break;

                /**
                 * Invalid Grant
                 * - A parameter sent by the oAuth gateway to Buffer is wrong
                 */
                case 'invalid_grant':
                    $this->notices['error'][] = sprintf( __( 'We were unable to complete authentication with Buffer.  Please try again, or <a href="%s" target="_blank">contact us for support</a>.', $this->base->plugin->name ), 'https://www.wpzinc.com/support' );
                    break;

                /**
                 * Expired Token
                 * - The oAuth gateway did not exchange the code for an access token within 30 seconds
                 */
                case 'expired_token':
                    $this->notices['error'][] = sprintf( __( 'The oAuth process has expired.  Please try again, or <a href="%s" target="_blank">contact us for support</a> if this issue persists.', $this->base->plugin->name ), 'https://www.wpzinc.com/support' );
                    break;

                /**
                 * Other Error
                 */
                default:
                    $this->notices['error'][] = $_REQUEST[ 'wp-to-buffer-pro-oauth-error' ];
                    break;
            }
        }

        // If an Access Token is included in the request, store it and show a success message
        if ( isset( $_REQUEST[ 'wp-to-buffer-pro-oauth-access-token' ] ) ) {
            // Get instances
            $api        = WP_To_Buffer_Pro_Buffer_API::get_instance();
            $settings   = WP_To_Buffer_Pro_Settings::get_instance();

            // Test Token
            $api->set_access_token( $_REQUEST[ 'wp-to-buffer-pro-oauth-access-token' ] );
            $user = $api->user();
            
            // If something went wrong, show an error
            if ( is_wp_error( $user ) ) {
                $this->notices['error'][] = $result->get_error_message();
                return;
            }
            
            // Test worked! Save Token
            $settings->update_access_token( $_REQUEST[ 'wp-to-buffer-pro-oauth-access-token' ] );

            // Show success message
            $this->notices['success'][] = sprintf( __( 'Thanks, %s! You\'ve authorized our Plugin access to post updates to your Buffer account.<br />Please now configure the Post Type(s) you want to send to your Buffer account below.', $this->base->plugin->name ), $user->name );
        }

    }

    /**
     * Register and enqueue any JS and CSS for the WordPress Administration
     *
     * @since 1.0.0
     */
    public function admin_scripts_css() {

        global $id, $post;

        // Get base instance
        $this->base = WP_To_Buffer::get_instance();

        // Get current screen
        $screen = get_current_screen();

        // CSS - always load
        wp_enqueue_style( $this->base->plugin->name, $this->base->plugin->url . 'assets/css/admin.css', array(), $this->base->plugin->version );
        
        // JS - always load
        
        // Don't load anything else if we're not on a Plugin screen
        if ( ! isset( $screen->base ) ) {
            return;
        }
        if ( strpos ( $screen->base, $this->base->plugin->name ) === false && $screen->base != 'post' ) {
            return;
        }
    
        // Plugin Admin
        // These scripts are registered in _modules/dashboard/dashboard.php
        wp_enqueue_script( 'wpzinc-admin-conditional' );
        wp_enqueue_script( 'wpzinc-admin-tabs' );
        wp_enqueue_script( 'wpzinc-admin-tags' );
        wp_enqueue_script( 'wpzinc-admin' );

        // JS
        wp_enqueue_script( $this->base->plugin->name . '-admin', $this->base->plugin->url . 'assets/js/min/admin-min.js', array( 'jquery' ), $this->base->plugin->version, true );
        wp_localize_script($this->base->plugin->name . '-admin', 'wp_to_buffer_pro', array(
            'ajax'                      => admin_url( 'admin-ajax.php' ),
            'clear_log_message'         => __( 'Are you sure you want to clear the log file associated with this Post?', $this->base->plugin->name ),
            'clear_log_nonce'           => wp_create_nonce( 'wp-to-buffer-pro-clear-log' ),
            'clear_log_completed'       => __( 'No status updates have been sent to Buffer.', $this->base->plugin->name ),
            'post_id'                   => ( isset( $post->ID ) ? $post->ID : (int) $id ),
        ) );
        
    }
    
    /**
     * Add the Plugin to the WordPress Administration Menu
     *
     * @since 1.0.0
     */
    public function admin_menu() {

        // Get base instance
        $this->base = WP_To_Buffer::get_instance();

        // Menus
        add_menu_page( $this->base->plugin->displayName, $this->base->plugin->displayName, 'manage_options', $this->base->plugin->name . '-settings', array( $this, 'settings_screen' ), $this->base->plugin->url . 'assets/images/icons/small.png' );
        add_submenu_page( $this->base->plugin->name . '-settings', __( 'Settings', $this->base->plugin->name ), __( 'Settings', $this->base->plugin->name ), 'manage_options', $this->base->plugin->name . '-settings', array( $this, 'settings_screen' ) );
        add_submenu_page( $this->base->plugin->name . '-settings', __( 'Upgrade', $this->base->plugin->name ), __( 'Upgrade', $this->base->plugin->name ), 'manage_options', $this->base->plugin->name . '-upgrade', array( $this, 'upgrade_screen' ) );

    }

    /**
     * Upgrade Screen
     *
     * @since 3.2.5
     */
    public function upgrade_screen() {   
        // We never reach here, as we redirect earlier in the process
    }

    /**
     * Outputs the Settings Screen
     *
     * @since 3.0.0.0
     */
    public function settings_screen() {

        // Notices
        $notices = array(
            'success'   => array(),
            'error'     => array(),
        );

        // Maybe disconnect from Buffer
        $result = $this->disconnect();
        if ( is_string( $result ) ) {
            // Error - add to array of errors for output
            $notices['error'][] = $result; 
        } elseif ( $result === true ) {
            // Success
            $notices['success'][] = __( 'Buffer account disconnected successfully.', $this->base->plugin->name ); 
        }

        // Maybe save settings
        $result = $this->save_settings();
        if ( is_string( $result ) ) {
            // Error - add to array of errors for output
            $notices['error'][] = $result;
        } elseif ( $result === true ) {
            // Success
            $notices['success'][] = __( 'Settings saved successfully.', $this->base->plugin->name ); 
        }

        // Setup instances
        $api        = WP_To_Buffer_Pro_Buffer_API::get_instance();
        $common     = WP_To_Buffer_Pro_Common::get_instance();

        // Either define the oAuth URL, or set the access token
        $access_token = WP_To_Buffer_Pro_Settings::get_instance()->get_access_token();
        if ( ! empty( $access_token ) ) {
            $api->set_access_token( WP_To_Buffer_Pro_Settings::get_instance()->get_access_token() );
        } else {
            $oauth_url = $api->get_oauth_url();
        }

        // Get Buffer Profiles
        $profiles       = $api->profiles( true );
        
        // Get Post Types, Image Options and Roles
        $post_types     = $common->get_post_types();

        // Get URL parameters
        $tab            = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'auth' );
        $post_type      = ( isset( $_GET['type'] ) ? $_GET['type'] : '' );
        if ( ! empty( $post_type ) ) {
            $tags               = $common->get_tags( $post_type );
        }

        // Get Schedule Options and Post Actions
        $schedule       = $common->get_schedule_options();
        $post_actions   = $common->get_post_actions();
        
        // View
        $view = 'views/settings.php';

        // Load View
        include_once( $this->base->plugin->folder . $view ); 
        
    }

    /**
     * Helper method to get the setting value from the plugin settings
     *
     * @since 3.0.0
     *
     * @param string    $type       Setting Type
     * @param string    $keys       Setting Key(s)
     * @param mixed     $default    Default Value if Setting does not exist
     * @return mixed                Value
     */
    public function get_setting( $type = '', $key = '', $default = '' ) {

        // Depending on the key, return either the access token, setting or post type setting
        $instance = WP_To_Buffer_Pro_Settings::get_instance();

        // Post Type Setting or Bulk Setting
        if ( post_type_exists( $type ) || $type == 'bulk' ) {
            return $instance->get_setting( $type, $key, $default );
        }

        // Access token
        if ( $key == 'access_token' ) {
            return $instance->get_access_token();
        }

        // Roles
        if ( $type == 'roles' ) {
            return $instance->get_setting( $type, $key, $default );
        }

        // Setting
        return $instance->get_option( $key, $default );

    }

    /**
     * Disconnect from Buffer by removing the access token
     *
     * @since 3.0.0
     *
     * @return string Result
     */
    public function disconnect() {

        if ( ! isset( $_GET['wp-to-buffer-pro-disconnect'] ) ) {
            return false;
        }

        return WP_To_Buffer_Pro_Settings::get_instance()->update_access_token( '' );

    }

    /**
     * Helper method to save settings
     *
     * @since 3.0.0
     *
     * @return mixed Error String on error, true on success
     */
    public function save_settings() {

        // Check if a POST request was made
        if ( ! isset( $_POST['submit'] ) ) {
            return false;
        }

        // Run security checks
        // Missing nonce 
        if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) { 
            return __( 'Nonce field is missing. Settings NOT saved.', $this->base->plugin->name );
        }

        // Invalid nonce
        if ( ! wp_verify_nonce( $_POST[$this->base->plugin->name.'_nonce'], $this->base->plugin->name ) ) {
            return __('Invalid nonce specified. Settings NOT saved.', $this->base->plugin->name );
        }

        // Get URL parameters
        $tab            = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'auth' );
        $post_type      = ( isset( $_GET['type'] ) ? $_GET['type'] : '' );
        
        switch ( $tab ) {
            /**
            * Authentication
            */
            case 'auth':
                // Get instances
                $settings   = WP_To_Buffer_Pro_Settings::get_instance();

                // Save other Settings
                $settings->update_option( 'cron', ( isset( $_POST['cron'] ) ? 1 : 0 ) );
                $settings->update_option( 'log', ( isset( $_POST['log'] ) ? 1 : 0 ) );
                $settings->update_option( 'image_custom', ( isset( $_POST['image_custom'] ) ? absint( $_POST['image_custom'] ) : 0 ) );
                $settings->update_option( 'image_dimensions', ( isset( $_POST['image_dimensions'] ) ? 1 : 0 ) );
                $settings->update_option( 'restrict_roles', ( isset( $_POST['restrict_roles'] ) ? 1 : 0 ) );
                $settings->update_settings( 'roles', ( isset( $_POST['roles'] ) ? $_POST['roles'] : array() ) );

                return true;

                break;

            /**
            * Post Type
            */
            default:
                // Save Settings for this Post Type
                return WP_To_Buffer_Pro_Settings::get_instance()->update_settings( $post_type, $_POST[ $this->base->plugin->name ] );

                break;
        }

    }

    /**
     * Loads plugin textdomain
     *
     * @since 3.0.0
     */
    public function load_language_files() {

        load_plugin_textdomain( 'wp-to-buffer-pro', false, 'wp-to-buffer-pro/languages/' );

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
$wp_to_buffer_pro_admin = WP_To_Buffer_Pro_Admin::get_instance();