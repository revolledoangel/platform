<?php
require_once "../controllers/projects.controller.php";
require_once "../models/projects.model.php";

class AjaxProjects
{
    public $projectId;
    public $projectActive;

    public function ajaxEditProject()
    {
        $url = "https://algoritmo.digital/backend/public/api/projects/" . $this->projectId;
        $response = file_get_contents($url);
        echo $response;
    }

    public function ajaxActiveProject()
    {
        if (!$this->projectId || $this->projectActive === null) {
            echo json_encode([
                "success" => false,
                "message" => "Datos incompletos para actualizar estado"
            ]);
            return;
        }

        // Llamar al controlador y capturar la respuesta (la API devuelve JSON)
        $apiResponse = Projects_controller::ctrCambiarEstadoProyecto($this->projectId, $this->projectActive);

        // Decodificar la respuesta JSON si es string
        $decoded = is_string($apiResponse) ? json_decode($apiResponse, true) : $apiResponse;

        // Envolver en una estructura de éxito
        echo json_encode([
            "success" => true,
            "message" => "Estado actualizado correctamente",
            "data" => $decoded
        ]);
    }
    
    public function ajaxListProjects()
    {
        $proyectos = Projects_controller::ctrShowProjects();

        $data = [];

        foreach ($proyectos as $proyecto) {
            
            $estado = '<label class="switch-project text-center">
                <input type="checkbox" class="toggle-active" data-id="' . $proyecto["id"] . '" ' . ($proyecto["active"] == 1 ? "checked" : "") . '>
                <span class="slider round"></span>
            </label>';

            $acciones = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-warning btn-editProject" projectId="' . $proyecto["id"] . '" data-toggle="modal" data-target="#editProjectModal">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </button>
                            <button type="button" class="btn btn-default btn-danger btn-deleteProject" projectId="' . $proyecto["id"] . '">
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                        </div>';

            $data[] = [
                htmlspecialchars($proyecto["name"]),
                htmlspecialchars($proyecto["code"]),
                htmlspecialchars($proyecto["client_name"] ?? ''),
                htmlspecialchars($proyecto["group"]?? ''),
                $estado,
                $acciones
            ];
        }

        echo json_encode(["data" => $data]);
    }
}

// Detectar la acción:
if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listar = new AjaxProjects();
    $listar->ajaxListProjects();
    return;
}

// Editar proyecto
if (isset($_POST["projectId"]) && !isset($_POST["active"])) {
    $editar = new AjaxProjects();
    $editar->projectId = $_POST["projectId"];
    $editar->ajaxEditProject();
}

// Activar/desactivar proyecto
if (isset($_POST["active"])) {

    $activar = new AjaxProjects();
    $activar->projectId = $_POST["id"];
    $activar->projectActive = $_POST["active"];
    $activar->ajaxActiveProject();
}
