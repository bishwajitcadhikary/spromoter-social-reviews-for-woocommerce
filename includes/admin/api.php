<?php
/**
 * Admin Settings
 */
namespace KinDigi\SPromoter\Admin;

class Api
{

    /**
     * API URL
     * @var string
     */
    protected string $api_url;

    /**
     * API Key
     * @var mixed|null
     */
    protected $api_key;

    /**
     * App ID
     * @var mixed|null
     */
    protected $app_id;

    public function __construct($api_key = null, $app_id = null)
    {
        $this->api_url = constant('SPROMOTER_API_URL');
        $this->api_key = $api_key;
        $this->app_id = $app_id;
    }

    /**
     * Send request to API
     * @param $endpoint
     * @param string $method
     * @param array $body
     * @param array $headers
     * @return false|array
     * @since 1.0.0
     */
    public function send_request($endpoint, string $method = 'GET', array $body = [], array $headers = [])
    {
        // Prepare the request arguments
        $args = [
            'headers' => array_merge([
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-App-ID' => $this->app_id,
            ], $headers),
            'sslverify' => !constant('SPROMOTER_DEBUG'),
        ];

        // Add query parameters for GET requests
        if ($method == 'GET') {
            $endpoint .= '?' . http_build_query($body);
        }

        // Add body for POST requests
        if ($method == 'POST') {
            $args['method'] = 'POST';
            $args['body'] = wp_json_encode($body);
        }

        // Perform the request
        $response = wp_remote_get($this->api_url . $endpoint, $args);

        // Check for errors
        if (is_wp_error($response)) {
            // Handle WP error
            error_log('WP_Error: ' . $response->get_error_message());
            return false;
        }

        // Check HTTP status code
//        $http_code = wp_remote_retrieve_response_code($response);
//        if ($http_code >= 400) {
//            // Handle HTTP error
//            error_log('HTTP error: ' . $http_code);
//            return false;
//        }

        // Parse JSON response
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}
