<?php
require_once "../controllers/campaigns.controller.php";

class AjaxCampaigns
{
    public $campaignId;

    public function ajaxEditCampaign()
    {
        $url = "https://algoritmo.digital/backend/public/api/campaigns/" . $this->campaignId;
        $response = file_get_contents($url);
        echo $response;
    }

    public function ajaxListCampaigns()
    {
        $campañas = Campaigns_controller::ctrShowCampaigns();
        $data = [];

        foreach ($campañas as $c) {

            // Convertir verticales a etiquetas
            $verticalsHTML = "";
            if (!empty($c["client_verticals"]) && is_array($c["client_verticals"])) {
                foreach ($c["client_verticals"] as $vertical) {
                    $verticalsHTML .= '<span class="label label-info" style="margin:2px;">' . htmlspecialchars($vertical) . '</span> ';
                }
            }

            // Convertir objetivos a etiquetas
            $objectivesHTML = "";
            if (!empty($c["objectives_names"]) && is_array($c["objectives_names"])) {
                foreach ($c["objectives_names"] as $obj) {
                    $objectivesHTML .= '<span class="label label-info" style="margin:2px;">' . htmlspecialchars($obj) . '</span> ';
                }
            }

            // Código: combinar client_code + project_code
            $codigo = htmlspecialchars(($c["platform_code"] ?? "") . ($c["client_code"] ?? "") . ($c["project_code"] ?? ""));

            // Botón de acciones
            $acciones = '<div class="btn-group">
                <button type="button" class="btn btn-warning btn-editCampaign" campaignId="' . $c["id"] . '" data-toggle="modal" data-target="#editCampaignModal">
                    <span class="glyphicon glyphicon-pencil"></span>
                </button>
                <button type="button" class="btn btn-danger btn-deleteCampaign" campaignId="' . $c["id"] . '">
                    <span class="glyphicon glyphicon-remove"></span>
                </button>
            </div>';

            $data[] = [
                htmlspecialchars($c["period_name"] ?? "—"),
                htmlspecialchars($c["platform_name"] ?? "—"),
                $verticalsHTML,
                htmlspecialchars($c["user_name"] ?? "—"),
                htmlspecialchars($c["project_group"] ?? "—"),
                htmlspecialchars($c["client_name"] ?? "—"),
                htmlspecialchars($c["project_name"] ?? "—"),
                $codigo,
                $objectivesHTML,
                //'$' . number_format($c["investment"] ?? 0, 0),
                //'$' . number_format($c["goal"] ?? 0, 0),
                (float)($c["investment"] ?? 0), // <-- LÍNEA NUEVA (envía el número puro)
                (float)($c["goal"] ?? 0),
                htmlspecialchars($c["state"] ?? "—"),
                $acciones
            ];
        }

        echo json_encode(["data" => $data]);
    }
}

// === DETECCIÓN DE ACCIÓN ===
if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listar = new AjaxCampaigns();
    $listar->ajaxListCampaigns();
}

// Editar cliente
if (isset($_POST["campaignId"])) {
    $editar = new AjaxCampaigns();
    $editar->clientId = $_POST["campaignId"];
    $editar->ajaxEditCampaign();
}


