<?php
/**
 * Common class
 * 
 * @package WP_To_Buffer_Pro
 * @author  Tim Carr
 * @version 3.0.0
 */
class WP_To_Buffer_Pro_Common {

    /**
     * Holds the class object.
     *
     * @since 3.1.4
     *
     * @var object
     */
    public static $instance;

    /**
     * Helper method to retrieve schedule options
     *
     * @since   3.0.0
     *
     * @return array Schedule Options
     */
    public function get_schedule_options() {

        // Build schedule options
        $schedule = array(
            'queue_bottom'  => __( 'Add to End of Buffer Queue', 'wp-to-buffer-pro' ),
        );

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_schedule_options', $schedule );

    }

    /**
     * Helper method to retrieve public Post Types
     *
     * @since   3.0.0
     *
     * @return  array   Public Post Types
     */
    public function get_post_types() {

        // Get public Post Types
        $types = get_post_types( array(
            'public' => true,
        ), 'objects' );

        // Filter out excluded post types
        $excluded_types = $this->get_excluded_post_types();
        if ( is_array( $excluded_types ) ) {
            foreach ( $excluded_types as $excluded_type ) {
                unset( $types[ $excluded_type ] );
            }
        }

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_post_types', $types );

    }

    /**
     * Helper method to retrieve excluded Post Types
     *
     * @since   3.0.0
     *
     * @return  array   Excluded Post Types
     */
    public function get_excluded_post_types() {

        // Get excluded Post Types
        $types = array(
            'attachment',
        );

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_excluded_post_types', $types );

    }

    /**
     * Helper method to retrieve excluded Taxonomies
     *
     * @since   3.0.5
     *
     * @return  array   Excluded Post Types
     */
    public function get_excluded_taxonomies() {

        // Get excluded Post Types
        $taxonomies = array(
            'post_format',
        );

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_excluded_taxonomies', $taxonomies );

    }

    /**
     * Helper method to retrieve available tags for status updates
     *
     * @since   3.0.0
     *
     * @param   string  $post_type  Post Type
     * @return  array               Tags
     */
    public function get_tags( $post_type ) {

        // Get post type
        $post_types = $this->get_post_types();

        // Build tags array
        $tags = array(
            'post' => array(
                '{sitename}'            => __( 'Site Name', 'wp-to-buffer-pro' ),
                '{title}'               => __( 'Post Title', 'wp-to-buffer-pro' ),
                '{excerpt}'             => __( 'Post Excerpt', 'wp-to-buffer-pro' ),
                '{content}'             => __( 'Post Content', 'wp-to-buffer-pro' ),
                '{date}'                => __( 'Post Date', 'wp-to-buffer-pro' ),
                '{url}'                 => __( 'Post URL', 'wp-to-buffer-pro' ),
            ),
        );

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_tags', $tags, $post_type );

    }

    /**
     * Helper method to retrieve Post actions
     *
     * @since   3.0.0
     *
     * @return  array           Post Actions
     */
    public function get_post_actions() {

        // Build post actions
        $actions = array(
            'publish'   => __( 'Publish', 'wp-to-buffer-pro' ),
            'update'    => __( 'Update', 'wp-to-buffer-pro' ),
        );

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_post_actions', $actions );

    }

    /**
     * Helper method to retrieve transient expiration time
     *
     * @since   3.0.0
     *
     * @return  int     Expiration Time (seconds)
     */
    public function get_transient_expiration_time() {

        // Set expiration time for all transients = 12 hours
        $expiration_time = ( 12 * HOUR_IN_SECONDS );

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_transient_expiration_time', $expiration_time );

    }

    /**
     * Helper method to remove array keys that the given WordPress User Role doesn't have access to
     *
     * Checks if Restrict Roles is enabled
     *
     * The array can be either Post Type Settings or Post Settings, as the top level keys will always
     * be profile_ids
     *
     * @since   3.0.6
     *
     * @param   array   $arr    Post Type or Post Settings
     * @param   string  $role   Role
     * @return                  Post Type or Post Settings
     */
    function maybe_remove_profiles_by_role( $arr, $role ) {

        // Check if restrict roles is enabled
        $restrict_roles = (bool) WP_To_Buffer_Pro_Settings::get_instance()->get_option( 'restrict_roles', 0 );
        if ( ! $restrict_roles ) {
            return $arr;
        }

        // If role is Administrator, return
        if ( $role == 'administrator' ) {
            return $arr;
        }

        // Iterate through profiles, checking if the role has access to the profile
        foreach ( $arr as $profile_id => $data ) {
            // Always grant access to default
            if ( $profile_id == 'default' ) {
                continue;
            }

            // Get access for this role and profile combination
            $access = (bool) WP_To_Buffer_Pro_Settings::get_instance()->get_setting( 'roles', '[' . $role . '][' . $profile_id . ']', 0 );

            // If no access, remove profile from array
            if ( ! $access ) {
                unset( $arr[ $profile_id ] );
            }
        }

        // Return
        return apply_filters( 'wp_to_buffer_pro_maybe_remove_profiles_by_role', $arr, $role );

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
$wp_to_buffer_pro_common = WP_To_Buffer_Pro_Common::get_instance();