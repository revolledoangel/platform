<?php
require_once "../controllers/verticals.controller.php";

class AjaxVerticals
{
    public $verticalId;
    public $verticalActive;

    public function ajaxEditVertical()
    {
        $url = "https://algoritmo.digital/backend/public/api/verticals/" . $this->verticalId;
        $response = file_get_contents($url);
        echo $response;
    }
    
    public function ajaxListVerticals()
    {
        $verticales = Verticals_controller::ctrShowVerticals();

        $data = [];

        foreach ($verticales as $vertical) {
            

            $acciones = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-warning btn-editVertical" verticalId="' . $vertical["id"] . '" data-toggle="modal" data-target="#editVerticalModal">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </button>
                            <button type="button" class="btn btn-default btn-danger btn-deleteVertical" verticalId="' . $vertical["id"] . '">
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                        </div>';

            $data[] = [
                htmlspecialchars($vertical["name"]),
                date("Y-m-d H:i", strtotime($vertical["created_at"])),
                $acciones
            ];
        }

        echo json_encode(["data" => $data]);
    }
}

// Detectar la acción:
if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listar = new AjaxVerticals();
    $listar->ajaxListVerticals();
    return;
}

// Editar proyecto
if (isset($_POST["verticalId"]) && !isset($_POST["active"])) {
    $editar = new AjaxVerticals();
    $editar->verticalId = $_POST["verticalId"];
    $editar->ajaxEditVertical();
}