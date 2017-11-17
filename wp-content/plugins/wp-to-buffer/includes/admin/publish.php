<?php
/**
 * Post class
 * 
 * @package WP_To_Buffer_Pro
 * @author  Tim Carr
 * @version 3.0.0
 */
class WP_To_Buffer_Pro_Publish {

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
     * @since 3.2.4
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
        add_action( 'wp_loaded', array( $this, 'register_publish_hooks' ), 1 );
        add_action( 'wp_to_buffer_pro', array( $this, 'publish' ), 1, 2 );

    }

    /**
     * Registers publish hooks against all public Post Types
     *
     * @since 3.0.0
     */
    public function register_publish_hooks() {

        add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 10, 3 );
        
    }

    /**
     * Handles all Post transitions, checking to see whether a Post is going to be published
     * or updated.
     *
     * @since 3.1.6
     *
     * @param   string      $new_status     New Status
     * @param   string      $old_status     Old Status
     * @param   WP_Post     $post           Post
     */
    public function transition_post_status( $new_status, $old_status, $post ) {

        // New Post Screen loading
        // Draft saved
        if ( $new_status == 'auto-draft' || $new_status == 'draft' || $new_status == 'inherit' ) {
            return;
        }

        // Publish
        if ( $new_status == 'publish' && $new_status != $old_status ) {
            $this->publish( $post->ID, 'publish' );
        }

        // Update
        if ( $new_status == 'publish' && $old_status == 'publish' ) {
            $this->publish( $post->ID, 'update' );
        }

    }

    /**
     * Main function. Called when any Page, Post or CPT is published or updated
     *
     * @param   int         $post_id                Post ID
     * @param   string      $action                 Action (publish|update)
     * @param   bool        $is_bulk_publish_action Is Bulk Publish Action
     * @return  mixed                               WP_Error | Buffer API Results array
     */
    public function publish( $post_id, $action, $is_bulk_publish_action = false ) {

        // Get base instance
        $this->base = WP_To_Buffer::get_instance();

        // Get Post
        global $post;
        $post = get_post( $post_id );
        if ( ! $post ) {
            return new WP_Error( 'no_post', sprintf( __( 'No WordPress Post could be found for Post ID %s', $this->base->plugin->name ), $post_id ) );
        }

        // Determine post type
        $post_type = $post->post_type;

        // Use Plugin Settings
        $settings = WP_To_Buffer_Pro_Settings::get_instance()->get_settings( $post_type );

        // Check a valid access token exists
        $access_token = WP_To_Buffer_Pro_Settings::get_instance()->get_access_token();
        if ( ! $access_token ) {
            WP_To_Buffer_Pro_Log::get_instance()->update_log( $post_id, array(
                'date'              => strtotime( 'now' ),
                'success'           => false,
                'message'           => __( 'No access token was specified.', $this->plugin->name ),
            ) );
            return new WP_Error( 'missing_access_token', __( 'No access token was specified', $this->base->plugin->name ) );
        }
        
        // Check settings exist
        // If not, this means the CPT or Post-level settings have not been configured, so we
        // don't need to do anything
        if ( ! $settings ) {
            return new WP_Error( 'no_settings', sprintf( __( 'No settings have been defined! Go to %s > Settings to setup the plugin.', $this->base->plugin->name ), $this->base->plugin->displayName ) );
        }

        // Get instances
        $api                = WP_To_Buffer_Pro_Buffer_API::get_instance();
        $common             = WP_To_Buffer_Pro_Common::get_instance();
        $settings_instance  = WP_To_Buffer_Pro_Settings::get_instance();

        // Setup Buffer API
        $api->set_access_token( $access_token );

        // Get Profiles from Buffer
        $profiles = $api->profiles();

        // Array for storing statuses we'll send to Buffer
        $statuses = array();

        // Run profiles and settings through role restriction
        $user = get_user_by( 'id', $post->post_author );
        if ( is_object( $user ) ) {
            $profiles = $common->maybe_remove_profiles_by_role( $profiles, $user->roles[0] );
            $settings = $common->maybe_remove_profiles_by_role( $settings, $user->roles[0] );
        }

        // Iterate through each social media profile
        foreach ( $settings as $profile_id => $profile_settings ) {

            // Get detailed settings from Post or Plugin
            // Use Plugin Settings
            $profile_enabled = $settings_instance->get_setting( $post_type, '[' . $profile_id . '][enabled]', 0 );
            $action_enabled = $settings_instance->get_setting( $post_type, '[default][' . $action . '][enabled]', 0 );
            $status_settings = $settings_instance->get_setting( $post_type, '[default][' . $action . '][status]', array() );
            
            // Check if this profile is enabled
            if ( ! $profile_enabled ) {
                continue;
            }

            // Check if this profile's action is enabled
            if ( ! $action_enabled ) {
                continue;
            }

            // Determine which social media service this profile ID belongs to
            foreach ( $profiles as $profile ) {
                if ( $profile['id'] == $profile_id ) {
                    $service = $profile['service'];
                    break;
                }
            }

            // Iterate through each $status_settings, building arguments
            foreach ( $status_settings as $index => $status ) {
                $statuses[] = $this->build_args( $post, $profile_id, $service, $status, $action );
            }

        }

        // Check if any statuses exist
        // If not, exit
        if ( count( $statuses ) == 0 ) {
            return new WP_Error( 'no_statuses', __( 'No statuses are defined for this Post Type! Please check the Plugin Settings. If you are using Bulk Publish, check the Bulk Publish settings.', $this->base->plugin->name ) );
        }

        // Allow developers to filter statuses before sending them
        $statuses = apply_filters( 'wp_to_buffer_pro_publish_statuses', $statuses, $post_id, $action );

        // Send status messages to Buffer API
        $results = $this->send( $statuses, $post_id, $action );

        // If no results, we're finished
        if ( empty( $results ) || count( $results ) == 0 ) {
            return;
        }

        // Check each result, to see if an error occured
        $errors = false;
        foreach ( $results as $result ) {
            if ( is_wp_error( $result ) ) {
                $errors = true;
                break;
            }
        }

        // Request that the user review the plugin. Notification displayed later,
        // can be called multiple times and won't re-display the notification if dismissed.
        if ( ! $errors ) {
            $this->base->dashboard->request_review();
        }

    }

    /**
     * Helper method to build arguments and create a status via the Buffer API
     *
     * @since   3.0.0
     *
     * @param   obj     $post               Post
     * @param   string  $profile_id         Profile ID
     * @param   string  $service            Service
     * @param   array   $status_settings    Status Settings
     * @return  bool                        Success
     */
    private function build_args( $post, $profile_id, $service, $status, $action ) {

        // Build each API argument
        // Profile ID
        $args = array(
            'profile_ids'   => array( $profile_id ),
        );

        // Text
        $args['text'] = $this->parse_text( $post, $status['message'] );

        // Shorten URLs
        $args['shorten'] = true;

        // Schedule
        switch( $status['schedule'] ) {

            case 'queue_bottom':
                // This is the default for the API, so nothing more to do here
                break;

            case 'queue_top':
                $args['top'] = true;
                break;

            case 'now':
                $args['now'] = true;
                break;

            case 'custom':
                // Check days, hours, minutes are set
                if ( empty( $status['days'] ) ) {
                    $status['days'] = 0;
                }
                if ( empty( $status['hours'] ) ) {
                    $status['hours'] = 0;
                }
                if ( empty( $status['minutes'] ) ) {
                    $status['minutes'] = 0;
                }
                
                // Add days, hours and minutes to either the Post's post_date_gmt (if publish) or post_modified_gmt (if update)
                $post_date = ( ( $action == 'publish' ) ? $post->post_date_gmt : $post->post_modified_gmt );
                $timestamp = strtotime( '+' . $status['days'] . ' days ' . $status['hours'] . ' hours ' . $status['minutes'] . ' minutes', strtotime( $post_date ) );
                $args['scheduled_at'] = date( 'Y-m-d H:i:s', $timestamp );
                break;

        }

        // Media
        $args['media']['link'] = rtrim( get_permalink( $post->ID ), '/' );
        $featured_image_id = get_post_thumbnail_id( $post->ID );
        if ( $featured_image_id > 0 ) {
            $featured_image = wp_get_attachment_image_src( $featured_image_id, 'large' );
            if ( is_array( $featured_image ) ) {
                $args['media'] = array();
                $args['media']['title']         = $post->post_title;// Required for LinkedIn to work
                $args['media']['picture']       = $featured_image[0];
                $args['media']['thumbnail']     = $featured_image[0];
                $args['media']['description']   = $post->post_title;
                unset( $args['media']['link'] );                    // Important: if set, this attaches a link and drops the image!
            }
        }

        // Pinterest
        if ( $service == 'pinterest' ) {
            $args['subprofile_ids'] = array(
                $status['sub_profile'],
            );
            $args['source_url'] = get_permalink( $post->ID );
        }

        // Allow devs to filter before returning
        $args = apply_filters( 'wp_to_buffer_pro_publish_build_args', $args, $post, $profile_id, $service, $status );

        // Return args
        return $args;

    }

    /**
     * Populates the status message by replacing tags with Post/Author data
     *
     * @since 3.0
     *
     * @param   WP_Post     $post       Post
     * @param   string      $message    Status Message to parse
     * @return  string                  Parsed Status Message
     */
    public function parse_text( $post, $message ) {

        // 1. Get author
        $author = get_user_by( 'id', $post->post_author );
        
        // 2. Check if we have an excerpt. If we don't (i.e. it's a Page or CPT with no excerpt functionality), we need
        // to create an excerpt
        if ( empty( $post->post_excerpt ) ) {
            $excerpt = strip_tags( wp_trim_words( strip_shortcodes( $post->post_content ) ) );
        } else {
            $excerpt = strip_tags( wp_trim_words( strip_shortcodes( $post->post_excerpt ) ) );
        }

        // 2a. Decode certain entities for FB + G+ compatibility
        $excerpt = html_entity_decode( $excerpt );

        // 2b. Parse content, by removing shortcodes and HTML tags
        $content = html_entity_decode( strip_tags( apply_filters( 'the_content', wp_trim_words( strip_shortcodes( $post->post_content ) ) ) ) );
        
        // 3. Parse message
        $text = $message;
        $text = str_replace( '{sitename}', get_bloginfo( 'name' ), $text );
        $text = str_replace( '{title}', $post->post_title, $text );
        $text = str_replace( '{excerpt}', $excerpt, $text );
        $text = str_replace( '{content}', $content, $text );
        $text = str_replace( '{date}', date( 'dS F Y', strtotime( $post->post_date ) ), $text );
        $text = str_replace( '{url}', rtrim( get_permalink( $post->ID ), '/' ), $text );
        $text = str_replace( '{author}', $author->display_name, $text ); // Historical; 2.3.7+ uses {author_display_name}
        $text = str_replace( '{author_user_login}', $author->user_login, $text ); 
        $text = str_replace( '{author_user_nicename}', $author->user_nicename, $text );   
        $text = str_replace( '{author_user_email}', $author->user_email, $text ); 
        $text = str_replace( '{author_user_url}', $author->user_url, $text ); 
        $text = str_replace( '{author_display_name}', $author->display_name, $text ); 
        
        // 4. Go through available taxonomies, checking if any tags exist
        $taxonomies = get_object_taxonomies( $post->post_type, 'names' );
        if ( count( $taxonomies ) > 0 ) {
            foreach ( $taxonomies as $taxonomy ) {
                // Check if this taxonomy template tag exists in status
                $tag = '{taxonomy_' . $taxonomy . '}';
                if ( strpos( $text, $tag ) === false ) {
                    continue;
                }
                
                // Check if the taxonomy has any terms
                $terms = wp_get_post_terms( $post->ID, $taxonomy );
                $term_names = '';
                
                // Iterate through terms
                if ( is_array( $terms ) && count( $terms ) > 0 ) {
                    foreach ( $terms as $term ) {
                        $term_name = strtolower( str_replace(' ', '', $term->name ) );
                        $term_name = '#' . $term_name;
                        $term_name = apply_filters( 'wp_to_buffer_pro_term', $term_name, $term->name );
                        
                        // Add term to term names string
                        $term_names .= $term_name . ' ';
                    }   
                }
                
                // Replace tags with terms, or a blank string if we don't have any terms
                $text = str_replace( $tag, trim( $term_names ), $text );
            }
        }
        
        // Replace custom_field_XXX and author_field_XXX
        $text = $this->replace_partial_tags( $text, '{custom_field_', $post->ID );
        $text = $this->replace_partial_tags( $text, '{author_field_', $author->ID );
        
        return $text;

    }

    /**
    * Replaces partial tag matches in a string
    *
    * @since 3.0
    * 
    * @param string $text   Text
    * @param string $needle Tag Needle
    * @param int    $id     Post or Author Object
    * @return string        Text
    */
    private function replace_partial_tags( $text, $needle, $id ) {

        $customFieldStartPos = 0;
        $positions = array();
        
        // Keep iterating through the string from the start every time until there are no {custom_field_'s left
        // This won't result in an endless loop as we replace each occurance we find, so eventually this will be false
        // and break the loop.
        while ( ( $customFieldStartPos = strpos( $text, $needle ) ) !== false ) {
            // Get tag
            $customFieldEndPos = strpos( $text, '}', $customFieldStartPos );
            $customFieldTag = substr( $text, $customFieldStartPos, ( ( $customFieldEndPos - $customFieldStartPos ) + 1 ) );
            
            // Get custom field name and value
            $customFieldName = strtolower( str_replace( '}', '', str_replace( $needle, '', $customFieldTag ) ) );
            
            switch ( $needle ) {
                case '{custom_field_':
                    $customFieldValue = get_post_meta( $id, $customFieldName, true );
                    break;
                case '{author_field_':
                    $customFieldValue = get_user_meta( $id, $customFieldName, true);
                    break;
            }
            
            
            // Replace tag with value
            $text = str_replace( $customFieldTag, $customFieldValue, $text );
        }

        return $text;

    }

    /**
    * Helper method to iterate through statuses, sending each via a separate API call
    * to the Buffer API
    *
    * @since    3.0.0
    *
    * @param    array   $statuses   Statuses
    * @param    int     $post_id    Post ID
    * @param    string  $action     Action
    * @return   array               Buffer API Result for each status
    */
    public function send( $statuses, $post_id, $action ) {

        // Assume no errors
        $errors = false;

        // Get instances
        $api       = WP_To_Buffer_Pro_Buffer_API::get_instance();
        $settings  = WP_To_Buffer_Pro_Settings::get_instance();

        // Setup Buffer API
        $api->set_access_token( $settings->get_access_token() );

        // Setup logging
        $log = array();
        $log_enabled = $settings->get_option( 'log' );

        // Setup results array
        $results = array();

        foreach ( $statuses as $index => $status ) {
            // Send request
            $result = $api->updates_create( $status );
            
            // Store result in array
            $results[] = $result;

            // Only continue if logging is enabled
            if ( ! $log_enabled ) {
                continue;
            }

            // Store result
            if ( is_wp_error( $result ) ) {
                // Error
                $error = true;
                $log[] = array(
                    'date'              => strtotime( 'now' ),
                    'success'           => false,
                    'status'            => $status,
                    'profile'           => $status['profile_ids'][0],
                    'message'           => $result->get_error_message(),
                );
            } else {
                // OK
                $log[] = array(
                    'date'              => strtotime( 'now' ),
                    'success'           => true,
                    'status'            => $status,
                    'profile'           => $result->updates[0]->profile_id,
                    'message'           => $result->message,
                    'status_text'       => $result->updates[0]->text,
                    'status_created_at' => $result->updates[0]->created_at,
                    'status_due_at'     => $result->updates[0]->due_at,
                );
            }
        }

        // If no errors were reported, set a meta key to show a success message
        // This triggers admin_notices() to tell the user what happened
        if ( ! $errors ) {
            update_post_meta( $post_id, '_wp_to_buffer_pro_success', 1 );
            update_post_meta( $post_id, '_wp_to_buffer_pro_error', 0 );
        } else {
            update_post_meta( $post_id, '_wp_to_buffer_pro_success', 0 );
            update_post_meta( $post_id, '_wp_to_buffer_pro_error', 1 );
        }

        // Save log
        if ( $log_enabled ) {
            WP_To_Buffer_Pro_Log::get_instance()->update_log( $post_id, $log );
        }

        // Return results
        return $results;
        
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
$wp_to_buffer_pro_publish = WP_To_Buffer_Pro_Publish::get_instance();