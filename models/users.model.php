<?php

require_once "models/api.php";

class User {
    public static function login($username, $password) {

        $response = ApiClient::post('users/login', [
            'username' => $username,
            'password' => $password
        ]);

        if ($response['status'] === 200) {
            return $response['data'];
        }

        return null;
    }
}
