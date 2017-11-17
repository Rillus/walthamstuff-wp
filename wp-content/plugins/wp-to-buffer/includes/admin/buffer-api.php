<?php
/**
 * Buffer API class
 * 
 * @package WP_To_Buffer_Pro
 * @author  Tim Carr
 * @version 3.0.0
 */
class WP_To_Buffer_Pro_Buffer_API {

    /**
     * Holds the class object.
     *
     * @since   3.1.4
     *
     * @var     object
     */
    public static $instance;

    /**
     * Holds the Buffer Application's Client ID
     *
     * @since   3.3.3
     *
     * @var     string
     */
    private $client_id = '592d41d14d97ab7e4e571edb';

    /**
     * Holds the oAuth Gateway endpoint, used to exchange a code for an access token
     *
     * @since   3.3.3
     *
     * @var     string
     */
    private $oauth_gateway_endpoint = 'https://www.wpzinc.com/?oauth=buffer';
    
    /**
     * Access Token
     *
     * @since   3.0.0
     *
     * @var     string
     */
    public $access_token = '';

    /**
     * Returns the oAuth 2 URL used to begin the oAuth process
     *
     * @since   3.3.3
     *
     * @return  string  oAuth URL
     */
    public function get_oauth_url() {

        // Get base instance
        $this->base = WP_To_Buffer::get_instance();

        // Return oAuth URL
        return 'https://bufferapp.com/oauth2/authorize?client_id=' . $this->client_id . '&redirect_uri=' . urlencode( $this->oauth_gateway_endpoint ) . '&response_type=code&state=' . urlencode( admin_url( 'admin.php?page=' . $this->base->plugin->name . '-settings' ) );

    }

    /**
     * Sets this class' access token
     *
     * @since   3.0.0
     *
     * @param   string  $access_token   Access Token
     */
    public function set_access_token( $access_token ) {

        $this->access_token = $access_token;

    }

    /**
     * Checks if an access token was set.  Called by any function which 
     * performs a call to the Buffer API
     *
     * @since   3.0.0
     *
     * @return  bool    Token Exists
     */
    private function check_access_token_exists() {

        if ( empty( $this->access_token ) ) {
            return false;
        }

        return true;

    }

    /**
     * Returns the User object
     *
     * @since   3.0.0
     *
     * @return  mixed   WP_Error | User object
     */
    public function user() {

        // Check access token
        if ( ! $this->check_access_token_exists() ) {
            return false;
        }

        return $this->get( 'user.json' );

    }

    /**
     * Returns a list of Social Media Profiles attached to the Buffer Account.
     *
     * @since   3.0.0
     *
     * @param   bool    $force  Force API call (false = use WordPress transient)
     * @return  mixed           WP_Error | Profiles object
     */
    public function profiles( $force = false ) {

        // Check access token
        if ( ! $this->check_access_token_exists() ) {
            return false;
        }

        // Setup profiles array
        $profiles = array();

        // Check if our WordPress transient already has this data.
        // This reduces the number of times we query the API
        if ( $force || false === ( $profiles = get_transient( 'wp_to_buffer_pro_buffer_api_profiles' ) ) ) {
            // Get profiles
            $results = $this->get( 'profiles.json?subprofiles=1' );

            // Check for errors
            if ( is_wp_error( $results ) ) {
                return $results;
            }

            // Check data is valid
            foreach ( $results as $result ) {
                // We don't support Instagram or Pinterest in the Free version, as there's no Featured Image option.
                if ( class_exists( 'WP_To_Buffer' ) ) {
                    if ( $result->service == 'instagram' || $result->service == 'pinterest' ) {
                        continue;
                    }
                }
                
                // Add profile to array
                $profiles[ $result->id ] = array(
                    'id'                => $result->id,
                    'formatted_service' => $result->formatted_service,
                    'formatted_username'=> $result->formatted_username,
                    'avatar'            => $result->avatar,
                    'service'           => $result->service,
                );

                // Pinterest: add subprofiles
                if ( isset( $result->subprofiles ) && count( $result->subprofiles ) > 0 ) {
                    $profiles[ $result->id ]['subprofiles'] = array();
                    foreach ( $result->subprofiles as $sub_profile ) {
                        $profiles[ $result->id ]['subprofiles'][ $sub_profile->id ] = array(
                            'id'        => $sub_profile->id,
                            'name'      => $sub_profile->name,
                            'service'   => $sub_profile->service,
                        );
                    }
                }
            }
            
            // Store profiles in transient
            set_transient( 'wp_to_buffer_pro_buffer_api_profiles', $profiles, WP_To_Buffer_Pro_Common::get_instance()->get_transient_expiration_time() );
        }

        // Return results
        return $profiles;

    }

    /**
     * Creates an Update
     *
     * @since   3.0.0
     *
     * @return  mixed   WP_Error | Update object
     */
    public function updates_create( $params ) {

        return $this->post( 'updates/create.json', $params );

    }

    /**
     * Private function to perform a GET request
     *
     * @since  3.0.0
     *
     * @param  string  $cmd        Command (required)
     * @param  array   $params     Params (optional)
     * @return mixed               WP_Error | object
     */
    private function get( $cmd, $params = array() ) {

        return $this->request( $cmd, 'get', $params );

    }

    /**
     * Private function to perform a POST request
     *
     * @since  3.0.0
     *
     * @param  string  $cmd        Command (required)
     * @param  array   $params     Params (optional)
     * @return mixed               WP_Error | object
     */
    private function post( $cmd, $params = array() ) {

        return $this->request( $cmd, 'post', $params );

    }

    /**
     * Main function which handles sending requests to the Buffer API
     *
     * @since   3.0.0
     *
     * @param   string  $cmd        Command
     * @param   string  $method     Method (get|post)
     * @param   array   $params     Parameters (optional)
     * @return mixed                WP_Error | object
     */
    private function request( $cmd, $method = 'get', $params = array() ) {

        // Check required parameters exist
        if ( empty( $this->access_token ) ) {
            return new WP_Error( 'missing_access_token', __( 'No access token was specified' ) );
        }

        // Add access token to command, depending on the command's format
        if ( strpos ( $cmd, '?' ) !== false ) {
            $cmd .= '&access_token=' . $this->access_token;
        } else {
            $cmd .= '?access_token=' . $this->access_token;
        }

        // Build endpoint URL
        $url = 'https://api.bufferapp.com/1/' . $cmd;

        // Request via WordPress functions
        $result = $this->request_wordpress( $url, $method, $params );

        // Request via cURL if WordPress functions failed
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
            if ( is_wp_error( $result ) ) {
                $result = $this->request_curl( $url, $method, $params );
            }
        }

        // Result will be WP_Error or the data we expect
        return $result;

    }

    /**
     * Performs POST and GET requests through WordPress wp_remote_post() and
     * wp_remote_get() functions
     *
     * @since   3.2.6
     *
     * @param   string  $url        URL
     * @param   string  $method     Method (post|get)
     * @param   array   $params     Parameters
     * @return  mixed               WP_Error | object
     */
    private function request_wordpress( $url, $method, $params ) {

        // Send request
        switch ( $method ) {
            /**
             * GET
             */
            case 'get':
                $result = wp_remote_get( $url, array(
                    'body'      => $params,
                ) );
                break;
            
            /**
             * POST
             */
            case 'post':
                $result = wp_remote_post( $url, array(
                    'body'      => $params,
                ) );
                break;
        }

        // If an error occured, return it now
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // If the HTTP code isn't 200, something went wrong
        if ( $result['response']['code'] != 200 ) {
            // Decode error message
            $body = json_decode( $result['body'] );

            // Return WP_Error
            return new WP_Error( 
                $result['response']['code'], 
                'Buffer API Error: HTTP Code ' . $result['response']['code'] . '. #' . $body->code . ' - ' . $body->error 
            );
        }

        // All OK, return response
        return json_decode( $result['body'] );

    }

    /**
     * Performs POST and GET requests through PHP's curl_exec() function
     *
     * @since   3.2.6
     *
     * @param   string  $url        URL
     * @param   string  $method     Method (post|get)
     * @param   array   $params     Parameters
     * @return  mixed               WP_Error | object
     */
    private function request_curl( $url, $method, $params ) {

        // Init
        $ch = curl_init();

        // Set request specific options
        switch ( $method ) {
            /**
             * GET
             */
            case 'get':
                curl_setopt_array( $ch, array(
                    CURLOPT_URL             => $url . '&' . http_build_query( $params ),
                ) );
                break;

            /**
             * POST
             */
            case 'post':
                curl_setopt_array( $ch, array(
                    CURLOPT_URL             => $url,
                    CURLOPT_POST            => true,
                    CURLOPT_POSTFIELDS      => http_build_query( $params ),
                ) );
                break;
        }

        // Set shared options
        curl_setopt_array( $ch, array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => false,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_CONNECTTIMEOUT  => 5,
            CURLOPT_TIMEOUT         => 5,
        ) );

        // Execute
        $result     = curl_exec( $ch );
        $http_code  = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );

        // If HTTP code isn't 200, something went wrong
        if ( $http_code != 200 ) {
            // Decode error message
            $result = json_decode( $result );

            // Return basic WP_Error if we don't have any more information
            if ( is_null( $result ) ) {
                return new WP_Error(
                    $http_code,
                    'Buffer API Error: HTTP Code ' . $http_code . '. Sorry, we don\'t have any more information about this error. Please try again.'
                );
            }

            // Return WP_Error
            return new WP_Error( $http_code, 'Buffer API Error: HTTP Code ' . $http_code . '. #' . $result->code. ' - ' . $result->error );
        }
        
        // All OK, return response
        return json_decode( $result );

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
$wp_to_buffer_pro_buffer_api = WP_To_Buffer_Pro_Buffer_API::get_instance();