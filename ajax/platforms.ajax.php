<?php
require_once "../controllers/platforms.controller.php";

class AjaxPlatforms
{
    public $platformId;
    public $platformActive;

    public function ajaxEditPlatform()
    {
        $url = "https://algoritmo.digital/backend/public/api/platforms/" . $this->platformId;
        $response = file_get_contents($url);
        echo $response;
    }

    public function ajaxActivePlatform()
    {
        if (!$this->platformId || $this->platformActive === null) {
            echo json_encode([
                "success" => false,
                "message" => "Datos incompletos para actualizar estado"
            ]);
            return;
        }

        // Llamar al controlador y capturar la respuesta (la API devuelve JSON)
        $apiResponse = Platforms_controller::ctrCambiarEstadoPlataforma($this->platformId, $this->platformActive);

        // Decodificar la respuesta JSON si es string
        $decoded = is_string($apiResponse) ? json_decode($apiResponse, true) : $apiResponse;

        // Envolver en una estructura de éxito
        echo json_encode([
            "success" => true,
            "message" => "Estado actualizado correctamente",
            "data" => $decoded
        ]);
    }
    
    public function ajaxListPlatforms()
    {
        $plataformas = Platforms_controller::ctrShowPlatforms();

        $data = [];

        foreach ($plataformas as $plataforma) {
            
            $estado = '<label class="switch-platform text-center">
                <input type="checkbox" class="toggle-active" data-id="' . $plataforma["id"] . '" ' . ($plataforma["active"] == 1 ? "checked" : "") . '>
                <span class="slider round"></span>
            </label>';

            $acciones = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-warning btn-editPlatform" platformId="' . $plataforma["id"] . '" data-toggle="modal" data-target="#editPlatformModal">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </button>
                            <button type="button" class="btn btn-default btn-danger btn-deletePlatform" platformId="' . $plataforma["id"] . '">
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                        </div>';

            $data[] = [
                htmlspecialchars($plataforma["name"]),
                htmlspecialchars($plataforma["code"]),
                $estado,
                $acciones
            ];
        }

        echo json_encode(["data" => $data]);
    }

     public function ajaxFetchSimpleList() 
    {
        $platforms = Platforms_Controller::ctrShowPlatforms();
        echo json_encode($platforms); // Devuelve un array simple
    }
}

// Ruta para la tabla principal (con GET)
if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listar = new AjaxPlatforms();
    $listar->ajaxListPlatforms(); // <-- Esta es para la tabla de plataformas
    return;
}

// Ruta para el selector del modal (con POST)
if (isset($_POST['action']) && $_POST['action'] == 'list') {
    $ajaxPlatforms = new AjaxPlatforms();
    $ajaxPlatforms->ajaxFetchSimpleList(); // <-- Esta es para el modal
}

// Editar proyecto
if (isset($_POST["platformId"]) && !isset($_POST["active"])) {
    $editar = new AjaxPlatforms();
    $editar->platformId = $_POST["platformId"];
    $editar->ajaxEditPlatform();
}

// Activar/desactivar proyecto
if (isset($_POST["active"])) {

    $activar = new AjaxPlatforms();
    $activar->platformId = $_POST["id"];
    $activar->platformActive = $_POST["active"];
    $activar->ajaxActivePlatform();
}
