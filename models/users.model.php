<?php

require_once(__DIR__ . "/api.php");

class UserModel
{
    static public function login($data)
    {

        $response = ApiClient::post('users/login', $data);

        if ($response['status'] === 200) {

            return $response['data'];

        }

        return null;
    }

    public static function createUser($data)
    {
        $url = Config::get('API_BASE_URL') . "users";
        $curl = curl_init($url);

        $headers = [
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $responseData = json_decode($response, true);

        if ($httpCode === 201 && isset($responseData["success"]) && $responseData["success"] === true) {
            return [
                "success" => true,
                "message" => "Usuario creado correctamente"
            ];
        } else {
            return [
                "success" => false,
                "message" => $responseData["error"] ?? "Error desconocido"
            ];
        }
    }

    static public function mdlActualizarEstadoUsuario($id, $estado)
    {
        $url = Config::get('API_BASE_URL') . "users/" . $id;

        $data = json_encode([
            "active" => $estado
        ]);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return "error";
        }

        return $response;
    }

    public static function mdlUpdateUser($datos)
    {
    }

    static public function mdlDeleteUser($userId)
    {
        // 1. Construir la URL del endpoint con el ID del usuario
        $url = 'https://algoritmo.digital/backend/public/api/users/' . $userId;

        // 2. Inicializar cURL
        $ch = curl_init();

        // 3. Configurar las opciones de cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); // Especificar el método DELETE
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Para que devuelva la respuesta como un string
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json'
        ]);

        // 4. Ejecutar la petición
        $response = curl_exec($ch);
        
        // (Opcional) Verificar si hubo errores en la ejecución de cURL
        if (curl_errno($ch)) {
            // Manejar el error de cURL, por ejemplo, registrarlo o devolver un error específico
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'message' => 'Error en cURL: ' . $error_msg];
        }

        // 5. Cerrar la conexión cURL
        curl_close($ch);

        // 6. Decodificar la respuesta JSON a un array de PHP y devolverla
        return json_decode($response, true);
    }


}
