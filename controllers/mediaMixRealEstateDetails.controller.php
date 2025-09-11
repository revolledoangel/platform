<?php
class MediaMixRealEstateDetails_Controller
{
    /**
     * Muestra la lista de detalles para un Media Mix específico.
     * VERSIÓN ROBUSTA Y CORREGIDA.
     */
    static public function ctrShowDetails($mediaMixId)
    {
        $url = 'https://algoritmo.digital/backend/public/api/mmres/' . $mediaMixId . '/mmre_details';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Un timeout generoso de 30 segundos

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Si hubo un error en la conexión (como un timeout) O el código no es 200 (OK)
        if ($curlError || $httpCode !== 200) {
            // Guardamos el error en el log del servidor para futura referencia
            error_log("Error de cURL al llamar a la API de detalles: " . $curlError . " - HTTP Code: " . $httpCode);
            // Devolvemos un array vacío para que la tabla no se rompa
            return [];
        }

        // Si todo salió bien, devolvemos los datos decodificados
        return json_decode($response, true);
    }

    /**
     * Crea un nuevo registro de detalle
     */
    public function ctrCreateDetail()
    {
        if (isset($_POST["newMediaMixRealEstateId"])) {
            $mediaMixId = (int)$_POST["newMediaMixRealEstateId"];
            $aon = isset($_POST["newAon"]) ? 1 : 0;
            $segmentation = $_POST["newSegmentation"] ?? '';
            $investment = isset($_POST["newInvestment"]) ? floatval($_POST["newInvestment"]) : 0;
            $projection = isset($_POST["newProjection"]) ? intval($_POST["newProjection"]) : 0;
            $comments = $_POST["newComments"] ?? '';
            $state = $_POST["newStatus"] ?? '';
            $campaign_type_id = isset($_POST["newCampaignTypeId"]) ? intval($_POST["newCampaignTypeId"]) : null;
            $channel_id = isset($_POST["newChannelId"]) ? intval($_POST["newChannelId"]) : null;
            $project_id = isset($_POST["newProjectId"]) ? intval($_POST["newProjectId"]) : null;
            $objectives_ids = isset($_POST["newObjectiveId"]) ? [intval($_POST["newObjectiveId"])] : [];
            $formats_ids = isset($_POST["newFormat"]) ? array_map('intval', $_POST["newFormat"]) : [];
            // result_type: lo obtenemos del JS y lo mandamos por hidden o lo buscamos por el objetivo
            $result_type = $_POST["result_type"] ?? '';

            $body = [
                "aon" => $aon,
                "segmentation" => $segmentation,
                "result_type" => $result_type,
                "investment" => $investment,
                "projection" => $projection,
                "comments" => $comments,
                "state" => $state,
                "campaign_type_id" => $campaign_type_id,
                "channel_id" => $channel_id,
                "mediamixrealestate_id" => $mediaMixId,
                "project_id" => $project_id,
                "objectives_ids" => $objectives_ids,
                "formats_ids" => $formats_ids
            ];

            $jsonData = json_encode($body);
            $ch = curl_init('https://algoritmo.digital/backend/public/api/mmre_details');
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

            if ($httpCode === 201 || $httpCode === 200) {
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Detalle creado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then((result) => {
                        if (result.value) {
                            window.location = "mediaMixRealEstateDetails?mediaMixId=' . $mediaMixId . '";
                        }
                    });
                </script>';
            } else {
                $errorMsg = $responseData['message'] ?? 'Error desconocido al crear el detalle.';
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al crear el detalle",
                        text: "' . addslashes($errorMsg) . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /**
     * Obtiene los proyectos de un cliente específico.
     */
    static public function getProjectsByClient($clientId)
    {
        $url = 'https://algoritmo.digital/backend/public/api/clients/' . $clientId . '/projects';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['projects'] ?? [];
        } else {
            return [];
        }
    }
}