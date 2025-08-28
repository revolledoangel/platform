<?php
require_once "../controllers/mediaMixRealEstateDetails.controller.php";

class AjaxMediaMixRealEstateDetails
{
    public $mediaMixId;

    public function ajaxListDetails()
    {
        $response = MediaMixRealEstateDetails_Controller::ctrShowDetails($this->mediaMixId);
        $records = $response['details'] ?? [];
        
        if (empty($records)) {
            echo json_encode(["data" => []]);
            return;
        }
        
        $data = [];
        foreach ($records as $key => $record) {
            $acciones = '<div class="btn-group">
                            <button type="button" class="btn btn-warning btn-editDetail" detailId="' . $record["id"] . '">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-deleteDetail" detailId="' . $record["id"] . '">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>';

            $data[] = [
                htmlspecialchars($record["project_name"] ?? 'N/A'),
                htmlspecialchars($record["platform_name"] ?? 'N/A'),
                htmlspecialchars(implode(', ', $record["objectives_names"] ?? [])),
                ($record["aon"] == 1) ? 'Sí' : 'No',
                htmlspecialchars($record["campaign_type_name"] ?? 'N/A'),
                htmlspecialchars($record["channel_name"] ?? 'N/A'),
                htmlspecialchars($record["segmentation"] ?? ''),
                htmlspecialchars(implode(', ', $record["formats_names"] ?? [])),
                htmlspecialchars(($record["currency"] ?? '') . ' ' . number_format($record["investment"] ?? 0, 2)),
                '',
                htmlspecialchars(number_format($record["projection"] ?? 0, 2)),
                htmlspecialchars($record["result_type"] ?? ''),
                '',
                '',
                htmlspecialchars($record["comments"] ?? ''),
                htmlspecialchars($record["state"] ?? 'N/A'),
                $acciones
            ];
        }
        echo json_encode(["data" => $data]);
    }
}

if (isset($_GET["mediaMixId"]) && is_numeric($_GET["mediaMixId"])) {
    if (isset($_GET["action"]) && $_GET["action"] === "list") {
        $listar = new AjaxMediaMixRealEstateDetails();
        $listar->mediaMixId = $_GET["mediaMixId"];
        $listar->ajaxListDetails();
    }
}