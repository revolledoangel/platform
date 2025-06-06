<?php
require_once 'config.php';

class ApiClient {
    public static function post($endpoint, $body = [], $headers = []) {
        $url = Config::get('API_BASE_URL') . $endpoint;

        $payload = json_encode($body);

        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $allHeaders = array_merge($defaultHeaders, $headers);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
}
