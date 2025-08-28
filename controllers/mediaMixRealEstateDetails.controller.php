<?php
class MediaMixRealEstateDetails_Controller
{
    /**
     * Muestra la lista de detalles para un Media Mix específico
     */
    static public function ctrShowDetails($mediaMixId)
    {
        $url = 'https://algoritmo.digital/backend/public/api/mmres/' . $mediaMixId . '/mmre_details';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

        // --- CÓDIGO DE DEPURACIÓN AÑADIDO ---
        // Se establece un tiempo de espera corto (15 segundos) para la respuesta de la API.
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        // ------------------------------------

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // --- CÓDIGO DE DEPURACIÓN AÑADIDO ---
        // Capturamos cualquier error específico de la conexión cURL.
        $curlError = curl_error($ch);
        // ------------------------------------

        curl_close($ch);

        // --- CÓDIGO DE DEPURACIÓN AÑADIDO ---
        // Imprimimos toda la información de la respuesta y detenemos el script.
        // Esto nos dará la respuesta definitiva.
        if ($curlError) {
            // Si hay un error de cURL, lo mostramos.
            die("Error de cURL: " . $curlError);
        }
        // ------------------------------------

        return ($httpCode === 200) ? json_decode($response, true) : [];
    }

    /**
     * Crea un nuevo registro de detalle
     */
    public function ctrCreateDetail()
    {
        if (isset($_POST["newMediaMixRealEstateId"])) {
            $mediaMixId = $_POST["newMediaMixRealEstateId"];
            // Lógica para crear el detalle...
            echo '<script>
                swal({
                    type: "success",
                    title: "¡Detalle creado (simulación)!",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then((result) => {
                    if (result.value) {
                        window.location = "index.php?route=mediaMixRealEstateDetails&mediaMixId=' . $mediaMixId . '";
                    }
                });
            </script>';
        }
    }
}