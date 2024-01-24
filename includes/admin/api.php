<?php
/**
 * Admin Settings
 */
namespace WovoSoft\SPromoter\Admin;

class Api
{

    protected $api_url;
    protected $api_key;
    protected $app_id;

    public function __construct($api_key = null, $app_id = null) {
        $this->api_key = $api_key;
        $this->app_id = $app_id;

        if (defined('WP_SPROMOTER_DEV_MODE')) {
            $this->api_url = 'http://api.spromoter.test/v1/';
        } else {
            $this->api_url = 'https://api.spromoter.com/v1/';
        }
    }

    public function sendRequest( $endpoint, $method = 'GET', $body = [] , $headers = []) {
        $ch = curl_init();

        if ($method == 'GET') {
            $endpoint .= '?' . http_build_query($body);
        }

        $headers = array_merge([
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json',
            'Accept: application/json',
            'X-App-ID: '. $this->app_id,
        ], $headers);


        curl_setopt($ch, CURLOPT_URL, $this->api_url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (WP_DEBUG) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            return false;
        }

        curl_close($ch);

        return json_decode($result, true);
    }
}