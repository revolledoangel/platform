<?php
class Urls_controller
{
    static public function ctrShowUrls()
    {
        $url = 'https://algoritmo.digital/backend/public/api/urls';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            return [];
        }
    }

    public static function ctrCreateUrls($data)
    {
        $jsonData = json_encode($data);

        $ch = curl_init('https://algoritmo.digital/backend/public/api/urls');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ["http" => $httpCode, "response" => json_decode($response, true)];
    }

    static public function ctrDeleteUrl()
    {
        if (isset($_GET["urlToDelete"])) {

            $urlId = $_GET["urlToDelete"];
            $apiUrl = 'https://algoritmo.digital/backend/public/api/urls/' . $urlId;

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $respuesta = json_decode($response, true);

            if ($httpCode === 200) {
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡URL eliminada!",
                        text: "La URL ha sido eliminada correctamente.",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "No se pudo eliminar la URL. Intente nuevamente.",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }



}
