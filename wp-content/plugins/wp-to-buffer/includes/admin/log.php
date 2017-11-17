<?php
/**
 * Logging class
 * 
 * @package WP_To_Buffer_Pro
 * @author  Tim Carr
 * @version 3.0.0
 */
class WP_To_Buffer_Pro_Log {

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

        // Actions
        add_action( 'admin_menu', array( $this, 'admin_meta_boxes' ) );
        add_action( 'plugins_loaded', array( $this, 'export_log' ) );

    }

    /**
     * Adds Metaboxes to Post Edit Screens
     *
     * @since   3.0.0
     */
    public function admin_meta_boxes() {

        // Only load if Logging is enabled
        if ( ! WP_To_Buffer_Pro_Settings::get_instance()->get_option( 'log' ) ) {
            return;
        }

        // Get base instance
        $this->base = WP_To_Buffer::get_instance();

        // Get Post Types
        $post_types = WP_To_Buffer_Pro_Common::get_instance()->get_post_types();

        // Add meta boxes for each
        foreach ( $post_types as $post_type => $post_type_obj ) {
            add_meta_box( 'wp-to-buffer-pro-log', sprintf( __( '%s Log', $this->base->plugin->name ), $this->base->plugin->displayName ), array( $this, 'meta_log' ), $post_type, 'normal', 'low' );   
        }

    }

    /**
     * Outputs the plugin's log of existing status update calls made to the Buffer API
     *
     * @since   3.0.0
     *
     * @param   WP_Post     $post   Post
     */
    public function meta_log( $post ) {

        // Get instances
        $api = WP_To_Buffer_Pro_Buffer_API::get_instance();
        $this->base = WP_To_Buffer::get_instance();
        
        // Setup Buffer API
        $api->set_access_token( WP_To_Buffer_Pro_Settings::get_instance()->get_access_token() );

        // Get log and profiles
        $log = $this->get_log( $post->ID );
        $profiles = $api->profiles();
        ?>
        <div class="option">
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e( 'Plugin: Request Sent', $this->base->plugin->name ); ?></th>
                        <th><?php _e( 'Buffer: Status Created?', $this->base->plugin->name ); ?></th>
                        <th><?php _e( 'Buffer: Response', $this->base->plugin->name ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ( is_array( $log ) ) {
                        foreach ( $log as $count => $result ) {
                            ?>
                            <tr class="<?php echo ( $result['success'] ? 'success' : 'error' ); ?> <?php echo ( ( $count % 2 > 0 ) ? ' alternate' : '' ); ?>">
                                <td><?php echo date( 'Y-m-d H:i:s', $result['date'] ); ?></td>
                                <td><?php echo ( $result['success'] ? __( 'Yes', $this->base->plugin->name ) : __( 'No', $this->base->plugin->name ) ); ?></td>
                                <td>
                                    <?php
                                    if ( ! $result['success'] ) {
                                        // Show error
                                        ?>
                                        <strong><?php _e( 'Buffer: ', $this->base->plugin->name ); ?></strong>
                                        <?php 
                                        echo ( isset( $result['message'] ) ? $result['message'] : '' );
                                    } else {
                                        // Show dates
                                        ?>
                                        <strong><?php _e( 'Buffer: Created At: ', $this->base->plugin->name ); ?></strong>
                                        <?php echo date( 'Y-m-d H:i:s', $result['status_created_at'] ); ?><br />

                                        <strong><?php _e( 'Buffer: Status Publication Due At: ', $this->base->plugin->name ); ?></strong>
                                        <?php echo date( 'Y-m-d H:i:s', $result['status_due_at'] ); ?>
                                        <?php
                                    }
                                    ?>

                                    <br />

                                    <strong><?php _e( 'Buffer: Profile: ', $this->base->plugin->name ); ?></strong>
                                    <?php
                                    if ( is_array( $profiles ) ) {
                                        echo $profiles[ $result['status']['profile_ids'][0] ]['formatted_service'] . ': ' . $profiles[ $result['status']['profile_ids'][0] ]['formatted_username']; 
                                    }
                                    ?>
                                    <br />

                                    <strong><?php _e( 'Buffer: Status Text: ', $this->base->plugin->name ); ?></strong>
                                    <?php echo ( isset( $result['status_text'] ) ? $result['status_text'] : $result['status']['text'] ); ?><br />

                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="6"><?php _e( 'No status updates have been sent to Buffer.', $this->base->plugin->name ); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>      
            </table>
        </div>

        <div class="option">
            <a href="post.php?post=<?php echo $post->ID; ?>&action=edit&wp-to-buffer-pro-export-log=1" class="button"><?php _e( 'Export Log', $this->base->plugin->name ); ?></a>
            <a href="post.php?post=<?php echo $post->ID; ?>&action=edit&wp-to-buffer-pro-clear-log=1" class="button clear-log"><?php _e( 'Clear Log', $this->base->plugin->name ); ?></a>
        </div>
        <?php

    }

    /**
     * Retrieves the log for the given Post ID
     *
     * @since   3.0.0
     *
     * @param   int     $post_id    Post ID
     * @return  array               Log
     */
    public function get_log( $post_id ) {

        // Get log
        $log = get_post_meta( $post_id, '_wp_to_buffer_pro_log', true );

        // Allow filtering
        $log = apply_filters( 'wp_to_buffer_pro_get_log', $log, $post_id );

        // Return
        return $log;

    }

    /**
     * Stores the log results against the given Post ID
     *
     * @since   3.0.0
     *
     * @param   int    $post_id     Post ID
     * @param   array  $log         Log Entry / Log Entries
     * @return  bool                Success
     */
    public function update_log( $post_id, $log ) {

        // Get current log
        $old_log = $this->get_log( $post_id );

        // If log exist, merge it with the new log
        if ( $old_log !== false && is_array( $old_log ) ) {
            $log = array_merge( $old_log, $log );
        }

        // Allow devs to filter before saving
        $log = apply_filters( 'wp_to_buffer_pro_update_log', $log, $post_id );

        // update_option will return false if no changes were made, so we can't rely on this
        update_post_meta( $post_id, '_wp_to_buffer_pro_log', $log );
        
        return true;
    }

    /**
     * Exports a Post's Buffer log file in JSON format
     *
     * @since   3.0.0
     */
    public function export_log() {

        // Check the user requested a log
        if ( ! isset( $_GET['wp-to-buffer-pro-export-log'] ) ) {
            return;
        }

        // Get log
        $log = get_post_meta( $_GET['post'], '_wp_to_buffer_pro_log', true );

        // Build JSON
        $json = json_encode( $log );
        
        // Export
        header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=log.json");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $json;
        exit();
                
    }

    /**
     * Clears a Post's Buffer log
     *
     * @since   3.0.0
     */
    public function clear_log() {

        // Check the user requested a log
        if ( ! isset( $_REQUEST['wp-to-buffer-pro-clear-log'] ) ) {
            return;
        }

        // Clear log
        delete_post_meta( $_REQUEST['post'], '_wp_to_buffer_pro_log' );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since   3.1.4
     *
     * @return  object Class.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
            self::$instance = new self;
        }

        return self::$instance;

    }

}

// Load the class
$wp_to_buffer_pro_log = WP_To_Buffer_Pro_Log::get_instance();