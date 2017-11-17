<?php
/**
 * Post class
 * 
 * @package WP_To_Buffer_Pro
 * @author  Tim Carr
 * @version 3.0.0
 */
class WP_To_Buffer_Pro_Post {

    /**
     * Holds the class object.
     *
     * @since   3.1.4
     *
     * @var     object
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
     * @since   3.0.0
     */
    public function __construct() {

        // Admin Notices
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

    }

    /**
     * Outputs a notice if the user is editing a Post, which has a meta key indicating
     * that status(es) were published to Buffer API
     *
     * - A Post has been sent to Buffer and we have a valid message response
     *
     * @since   3.0.0
     */
    public function admin_notices() {

        // Check we can get the current screen the user is viewing
        $screen = get_current_screen();
        if ( ! $screen || ! isset( $screen->base ) || ! isset( $screen->parent_base ) ) {
            return;
        }

        // Check we are on a Post based screen (includes Pages + CPTs)
        if ( $screen->base != 'post' ) {
            return;
        }

        // Check we are editing a Post, Page or CPT
        if ( $screen->parent_base != 'edit' ) {
            return;
        }

        // Check we have a Post ID
        if ( ! isset( $_GET['post'] ) ) {
            return;
        }
        $post_id = absint( $_GET['post'] );

        // Check if this Post has a success or error meta key set by this plugin
        $success= get_post_meta( $post_id, '_wp_to_buffer_pro_success', true );
        $error  = get_post_meta( $post_id, '_wp_to_buffer_pro_error', true );

        // Get base instance
        $this->base = WP_To_Buffer::get_instance();

        // Check for success
        if ( $success ) {
            // Show notice and clear meta key, so we don't display this notice again
            delete_post_meta( $post_id, '_wp_to_buffer_pro_success' );
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php echo sprintf( __( '%s: Post successfully added to Buffer.', $this->base->plugin->name ), $this->base->plugin->displayName ); ?> 
                </p>
            </div>
            <?php
        }

        // Check for error
        if ( $error ) {
            // Show notice and clear meta key, so we don't display this notice again
            delete_post_meta( $post_id, '_wp_to_buffer_pro_error' );
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <?php
                    echo sprintf( __( '%s: Error(s) occured when attempting to add some/all status updates to Buffer. Please check the log at the bottom of this screen for further details.', $this->base->plugin->name ), $this->base->plugin->displayName );
                    ?> 
                </p>
            </div>
            <?php
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

// Load the class
$wp_to_buffer_pro_post = WP_To_Buffer_Pro_Post::get_instance();