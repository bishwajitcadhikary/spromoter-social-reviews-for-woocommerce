<?php
/**
 * Admin Settings
 */
namespace WovoSoft\SPromoter\Admin;

class Api
{

    protected $api_url = 'http://api.spromoter.test/v1/';
    protected $api_key;
    protected $app_id;

    public function __construct($api_key = null, $app_id = null) {
        $this->api_key = $api_key;
        $this->app_id = $app_id;
    }

    public function sendRequest( $endpoint, $method = 'GET', $body = array() ) {
        $ch = curl_init();

        if ($method == 'GET') {
            $endpoint .= '?' . http_build_query($body);
        }

        curl_setopt($ch, CURLOPT_URL, $this->api_url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json',
            'Accept: application/json'
        ));

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