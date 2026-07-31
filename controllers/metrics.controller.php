<?php

class Metrics_Controller
{
    static public function ctrShowMetrics()
    {
        $url = 'https://algoritmo.digital/backend/public/api/metrics';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $decoded = json_decode($response, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    static public function ctrShowMetricById($id)
    {
        $metricId = (int) $id;
        $url = 'https://algoritmo.digital/backend/public/api/metrics/' . $metricId;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $decoded = json_decode($response, true);
            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    static public function ctrShowMetricPlatforms($metricId)
    {
        $id = (int) $metricId;
        $url = 'https://algoritmo.digital/backend/public/api/metrics/' . $id . '/platforms';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(new stdClass()));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $decoded = json_decode($response, true);

            if (isset($decoded['data']) && is_array($decoded['data'])) {
                return $decoded['data'];
            }

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
