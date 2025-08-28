<?php

class CampaignTypes_Controller
{
    /**
     * Muestra todos los Tipos de Campaña
     */
    static public function ctrShowCampaignTypes()
    {
        // Se apunta al endpoint de la API para los tipos de campaña
        $url = 'https://algoritmo.digital/backend/public/api/campaign_types';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            // Devuelve un array vacío si hay un error para que la tabla no falle
            return [];
        }
    }

    /**
     * Crea un nuevo Tipo de Campaña
     */
    public function ctrCreateCampaignType()
    {
        // Se verifica el input del formulario con el nombre correcto
        if (isset($_POST["newCampaignTypeName"])) {
            
            // Se valida que el nombre no contenga caracteres inválidos
            if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ  \-\/\(\)]+$/', $_POST["newCampaignTypeName"])) {

                $name = $_POST["newCampaignTypeName"];

                // El cuerpo de la petición solo contiene el nombre
                $body = ["name" => $name];
                $jsonData = json_encode($body);

                $ch = curl_init('https://algoritmo.digital/backend/public/api/campaign_types');
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
                $responseData = json_decode($response, true);

                if ($httpCode === 201) { // 201 = Creado exitosamente
                    echo '<script>
                        swal({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El tipo de campaña ha sido creado correctamente.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                // Redirige a la página de tipos de campaña
                                window.location = "campaignTypes";
                            }
                        });
                    </script>';
                } else {
                    // Manejo de errores de la API
                    $errorMsg = $responseData['message'] ?? 'Ocurrió un error inesperado.';
                    echo '<script>
                        swal({
                            icon: "error",
                            title: "Error al crear",
                            text: "' . addslashes($errorMsg) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }
            } else {
                // Error de validación de caracteres
                echo '<script>
                    swal({
                        icon: "error",
                        title: "Error de validación",
                        text: "El nombre no puede contener caracteres especiales.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /**
     * Elimina un Tipo de Campaña
     */
    static public function ctrDeleteCampaignType()
    {
        // Se busca el ID correcto en la URL
        if (isset($_GET["deleteCampaignTypeId"])) {
            $campaignTypeId = $_GET["deleteCampaignTypeId"];
            $url = 'https://algoritmo.digital/backend/public/api/campaign_types/' . $campaignTypeId;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 || $httpCode === 204) { // 200 o 204 = Éxito en borrado
                echo '<script>
                    swal({
                        icon: "success",
                        title: "¡Eliminado!",
                        text: "El tipo de campaña ha sido eliminado correctamente.",
                        confirmButtonText: "Cerrar"
                    }).then((result) => {
                        if (result.value) {
                            window.location = "campaignTypes";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    swal({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo eliminar el tipo de campaña. Puede que esté en uso.",
                        confirmButtonText: "Cerrar"
                    }).then((result) => {
                        if (result.value) {
                            window.location = "campaignTypes";
                        }
                    });
                </script>';
            }
        }
    }
}