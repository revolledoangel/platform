<?php

class Config {
    const API_BASE_URL = 'https://algoritmo.digital/backend/public/api/';

    public static function get($key) {
        return constant("self::$key");
    }
}
