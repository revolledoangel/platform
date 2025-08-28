<?php

// MEJORA: Es una buena práctica definir que la respuesta siempre será JSON.
header('Content-Type: application/json');

// Se llama al controlador correspondiente.
require_once "../controllers/campaignTypes.controller.php";

class AjaxCampaignTypes
{
    /**
     * @var int El ID del Tipo de Campaña a editar.
     */
    public $campaignTypeId;

    /**
     * Obtiene los datos de un solo registro para llenar el modal de edición.
     * MEJORA: Se usa cURL para hacer la petición a la API de forma más segura y robusta.
     */
    public function ajaxEditCampaignType()
    {
        // Sanitizar la entrada para seguridad.
        $id = (int)$this->campaignTypeId;
        
        $url = "https://algoritmo.digital/backend/public/api/campaign_types/" . $id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Si la API devuelve un error (ej. 404), no se envía nada.
        if ($httpCode >= 400) {
            echo json_encode(null);
            return;
        }

        echo $response;
    }

    /**
     * Obtiene la lista completa de registros para mostrar en el DataTable.
     */
    public function ajaxListCampaignTypes()
    {
        $campaignTypes = CampaignTypes_Controller::ctrShowCampaignTypes();

        if (empty($campaignTypes)) {
            echo json_encode(["data" => []]);
            return;
        }

        $data = [];
        foreach ($campaignTypes as $key => $campaignType) {
            
            // MEJORA: Se usan data-attributes y clases consistentes con el JS.
            $acciones = '<div class="btn-group">
                            <button class="btn btn-warning btn-editCampaignType" data-campaigntypeid="' . $campaignType["id"] . '" data-toggle="modal" data-target="#editCampaignTypeModal">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-deleteCampaignType" data-campaigntypeid="' . $campaignType["id"] . '">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>';

            // Se prepara el array de datos con la estructura simplificada.
            $data[] = [
                ($key + 1),
                htmlspecialchars($campaignType["name"]),
                $acciones
            ];
        }

        echo json_encode(["data" => $data]);
    }
}

/* =============================================
 GESTOR DE PETICIONES AJAX
============================================= */

// Si la petición es para listar (viene de DataTable).
if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listCampaignTypes = new AjaxCampaignTypes();
    $listCampaignTypes->ajaxListCampaignTypes();
    exit();
}

// Si la petición es para obtener datos para editar (viene del botón de editar).
// CORRECCIÓN: Se usa $_GET para coincidir con la petición del JS.
if (isset($_GET["campaignTypeId"])) {
    $editCampaignType = new AjaxCampaignTypes();
    $editCampaignType->campaignTypeId = $_GET["campaignTypeId"];
    $editCampaignType->ajaxEditCampaignType();
    exit();
}