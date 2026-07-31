<?php

class Campaigns_controller
{
    static public function ctrShowCampaigns()
    {
        $url = 'https://algoritmo.digital/backend/public/api/campaigns';

        // Inicializa cURL
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
                'message' => 'No se pudieron obtener las campañas',
                'status' => $httpCode
            ];
        }
    }

    public function ctrCreateCampaign()
    {
        if (isset($_POST["newCampaignClient"])) {

            $periodId = (int) $_POST["newCampaignPeriod"];
            $clientId = (int) $_POST["newCampaignClient"];
            $projectId = (int) $_POST["newCampaignProject"];
            $platformId = (int) $_POST["newCampaignPlatform"];
            $formatIds = !empty($_POST["newCampaignFormats"]) ? array_map('intval', $_POST["newCampaignFormats"]) : [];
            $objectiveIds = !empty($_POST["newCampaignObjectives"]) ? array_map('intval', $_POST["newCampaignObjectives"]) : [];

            $status = $_POST["newCampaignStatus"] ?? null;
            $investment = $_POST["newCampaignInvestment"] ?? null;
            $goal = $_POST["newCampaignGoal"] ?? null;
            $comments = $_POST["newCampaignComments"] ?? null;
            $name = $_POST["newCampaignName"] ?? null;

            // Validaciones básicas
            if (!$periodId || !$projectId || !$platformId || empty($formatIds) || empty($objectiveIds) || !$status || !ctype_digit($investment) || !ctype_digit($goal)) {
                echo '<script>
                swal({
                    type: "error",
                    title: "Campos inválidos o incompletos",
                    text: "Verifica que todos los campos obligatorios estén completos y sean válidos.",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
                return;
            }

            $body = [
                "period_id" => $periodId,
                "client_id" => $clientId,
                "project_id" => $projectId,
                "platform_id" => $platformId,
                "formats_ids" => $formatIds,
                "objectives_ids" => $objectiveIds,
                "state" => $status,
                "investment" => (int) $investment,
                "goal" => (int) $goal
            ];

            if (!empty($comments))
                $body["comments"] = trim($comments);
            if (!empty($name))
                $body["name"] = trim($name);

            $jsonData = json_encode($body);

            $ch = curl_init('https://algoritmo.digital/backend/public/api/campaigns');
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
                    title: "Campaña creada correctamente",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(() => { window.location = "campaigns"; });
            </script>';
            } else {
                $errorMsg = 'Error desconocido';
                if (isset($responseData['errors'])) {
                    foreach ($responseData['errors'] as $field => $messages) {
                        if (is_array($messages) && count($messages) > 0) {
                            $errorMsg = $messages[0];
                            break;
                        }
                    }
                } elseif (isset($responseData['message'])) {
                    $errorMsg = $responseData['message'];
                }

                echo '<script>
                swal({
                    type: "error",
                    title: "Error al crear la Campaña",
                    text: "' . addslashes($errorMsg) . '",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
            }
        }
    }

    static public function ctrDeleteCampaign()
{
    if (isset($_GET["campaignId"])) {
        $campaignId = $_GET["campaignId"];
        
        $url = 'https://algoritmo.digital/backend/public/api/campaigns/' . $campaignId;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $respuesta = json_decode($response, true);

        if ($httpCode === 200 && isset($respuesta["success"]) && $respuesta["success"] === true) {
            echo '<script>
                    swal({
                        type: "success",
                        title: "¡Campaña eliminada!",
                        text: "La campaña se eliminó correctamente.",
                        confirmButtonText: "Cerrar"
                    }).then((result) => {
                        if (result.value) {
                            //window.location = "campaigns";
                        }
                    });
                </script>';
        } else {
            echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "No se pudo eliminar la campaña.",
                        confirmButtonText: "Cerrar"
                    }).then((result) => {
                        if (result.value) {
                            window.location = "campaigns";
                        }
                    });
                </script>';
        }
    }
}


}
