<?php


class ClientModel
{
    static public function mdlActualizarEstadoCliente($id, $estado)
    {
        $url = "https://algoritmo.digital/backend/public/api/clients/" . $id;

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

}
