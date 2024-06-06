<?php
/**
 * Admin Settings
 */
namespace WovoSoft\SPromoter\Admin;

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
        $this->api_url = constant('SP_API_URL');
        $this->api_key = $api_key;
        $this->app_id = $app_id;
    }

    /**
     * Send request to API
     * @param $endpoint
     * @param string $method
     * @param array $body
     * @param array $headers
     * @return false|mixed
     * @since 1.0.0
     */
    public function send_request($endpoint, string $method = 'GET', array $body = [], array $headers = [])
    {
        $ch = curl_init();

        if ($method == 'GET') {
            $endpoint .= '?' . http_build_query($body);
        }

        $headers = array_merge([
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json',
            'Accept: application/json',
            'X-App-ID: ' . $this->app_id,
        ], $headers);

        curl_setopt($ch, CURLOPT_URL, $this->api_url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Set SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            // Handle curl error
            error_log('cURL error: ' . curl_error($ch));
            return false;
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code >= 400) {
            // Handle HTTP error
            error_log('HTTP error: ' . $http_code);
            return false;
        }

        curl_close($ch);

        return json_decode($result, true);
    }
}
