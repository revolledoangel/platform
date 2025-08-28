<?php
require_once "../controllers/objectives.controller.php";

class AjaxObjectives
{
    public $objectiveId;

    public function ajaxEditObjective()
    {
        $url = "https://algoritmo.digital/backend/public/api/objectives/" . $this->objectiveId;
        $response = file_get_contents($url);
        echo $response;
    }

    public function ajaxListObjectives()
    {
        $objectives = Objectives_Controller::ctrShowObjectives();

        $data = [];

        foreach ($objectives as $objective) {

            $acciones = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-warning btn-editObjective" objectiveId="' . $objective["id"] . '" data-toggle="modal" data-target="#editObjectiveModal">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </button>
                            <button type="button" class="btn btn-default btn-danger btn-deleteObjective" objectiveId="' . $objective["id"] . '">
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                        </div>';

            $data[] = [
                htmlspecialchars($objective["name"]),
                htmlspecialchars($objective["default_result"]),
                htmlspecialchars($objective["code"]),
                $acciones
            ];
        }

        echo json_encode(["data" => $data]);
    }
}

// Acción: listar
if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listar = new AjaxObjectives();
    $listar->ajaxListObjectives();
    return;
}

// Acción: editar
if (isset($_POST["objectiveId"])) {
    $editar = new AjaxObjectives();
    $editar->objectiveId = $_POST["objectiveId"];
    $editar->ajaxEditObjective();
}
