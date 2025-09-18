<?php
// Archivo limpio para comenzar desde cero

class MediaMixRealEstateDetails_Controller {
    static public function ctrGetMediaMixById($mmreId) {
        $url = 'https://algoritmo.digital/backend/public/api/mmres/' . intval($mmreId) . '/mmre_details';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['success']) && $data['success'] && isset($data['mmre'])) {
                // Devuelve ambas partes: mmre y details
                return [
                    'mmre' => $data['mmre'],
                    'details' => $data['details'] ?? []
                ];
            }
        }
        return false;
    }

    static public function ctrGetProjectsByClientId($clientId) {
        $url = 'https://algoritmo.digital/backend/public/api/clients/' . intval($clientId) . '/projects';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['success']) && $data['success'] && isset($data['projects'])) {
                return $data['projects'];
            }
        }
        return [];
    }

    static public function ctrGetObjectives() {
        $url = 'https://algoritmo.digital/backend/public/api/objectives';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    static public function ctrGetPlatforms() {
        $url = 'https://algoritmo.digital/backend/public/api/platforms';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    static public function ctrGetChannels() {
        $url = 'https://algoritmo.digital/backend/public/api/channels';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    static public function ctrGetFormatsByPlatformId($platformId) {
        $url = 'https://algoritmo.digital/backend/public/api/platforms/' . intval($platformId) . '/formats';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['success']) && $data['success'] && isset($data['formats'])) {
                return $data['formats'];
            }
        }
        return [];
    }

    static public function ctrGetCampaignTypes() {
        $url = 'https://algoritmo.digital/backend/public/api/campaign_types';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }
}