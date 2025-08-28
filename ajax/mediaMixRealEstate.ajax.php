<?php
require_once "../controllers/mediaMixRealEstate.controller.php";

class AjaxMediaMixRealEstate
{
    public $mediaMixId;

    public function ajaxEditMediaMixRealEstate()
    {
        $url = "https://algoritmo.digital/backend/public/api/mmres/" . $this->mediaMixId;
        $response = file_get_contents($url);
        echo $response;
    }

    public function ajaxListMediaMixRealEstate()
    {
        $records = MediaMixRealEstate_Controller::ctrShowMediaMixRealEstate();
        if (empty($records)) {
            echo json_encode(["data" => []]);
            return;
        }
        
        $data = [];
        foreach ($records as $key => $record) {
            $acciones = '<div class="btn-group">
                <a href="index.php?route=mediaMixRealEstateDetails&mediaMixId=' . $record["id"] . '" class="btn btn-info">
                    <i class="fa fa-eye"></i>
                </a>
                <button type="button" class="btn btn-warning btn-editMediaMix" mediaMixId="' . $record["id"] . '" data-toggle="modal" data-target="#editMediaMixRealEstateModal">
                    <i class="fa fa-pencil"></i>
                </button>
                <button type="button" class="btn btn-danger btn-deleteMediaMix" mediaMixId="' . $record["id"] . '">
                    <i class="fa fa-trash"></i>
                </button>
            </div>';

            $data[] = [
                ($key + 1),
                htmlspecialchars($record["name"]),
                htmlspecialchars($record["client_id"]),
                htmlspecialchars($record["period_id"]),
                htmlspecialchars($record["currency"]),
                htmlspecialchars($record["fee"]),
                htmlspecialchars($record["igv"]),
                $acciones
            ];
        }
        echo json_encode(["data" => $data]);
    }
}

// Acción: listar
if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listar = new AjaxMediaMixRealEstate();
    $listar->ajaxListMediaMixRealEstate();
    return;
}

// Acción: editar
if (isset($_GET["mediaMixId"])) {
    $editar = new AjaxMediaMixRealEstate();
    $editar->mediaMixId = $_GET["mediaMixId"];
    $editar->ajaxEditMediaMixRealEstate();
}