<?php

class Periods_controller
{
    static public function ctrShowPeriods()
    {
        $url = 'https://algoritmo.digital/backend/public/api/periods';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            return $responseData;
        } else {
            return [
                'error' => true,
                'message' => 'No se pudieron obtener los períodos',
                'status' => $httpCode
            ];
        }
    }
}
