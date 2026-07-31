<?php
require_once "../controllers/formats.controller.php";

class AjaxFormats
{
    public $formatId;
    public $formatActive;

    public function ajaxEditFormat()
    {
        $url = "https://algoritmo.digital/backend/public/api/formats/" . $this->formatId;
        $response = file_get_contents($url);
        echo $response;
    }

    public function ajaxActiveFormat()
    {
        if (!$this->formatId || $this->formatActive === null) {
            echo json_encode([
                "success" => false,
                "message" => "Datos incompletos para actualizar estado"
            ]);
            return;
        }

        // Llamar al controlador y capturar la respuesta (la API devuelve JSON)
        $apiResponse = Formats_controller::ctrCambiarEstadoFormato($this->formatId, $this->formatActive);

        // Decodificar la respuesta JSON si es string
        $decoded = is_string($apiResponse) ? json_decode($apiResponse, true) : $apiResponse;

        // Envolver en una estructura de éxito
        echo json_encode([
            "success" => true,
            "message" => "Formato actualizado correctamente",
            "data" => $decoded
        ]);
    }
    
    public function ajaxListFormats()
    {
        $formatos = Formats_controller::ctrShowFormats();

        $data = [];

        foreach ($formatos as $formato) {
            
            $estado = '<label class="switch-format text-center">
                <input type="checkbox" class="toggle-active" data-id="' . $formato["id"] . '" ' . ($formato["active"] == 1 ? "checked" : "") . '>
                <span class="slider round"></span>
            </label>';

            $acciones = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-warning btn-editFormat" formatId="' . $formato["id"] . '" data-toggle="modal" data-target="#editFormatModal">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </button>
                            <button type="button" class="btn btn-default btn-danger btn-deleteFormat" formatId="' . $formato["id"] . '">
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                        </div>';

            $data[] = [
                htmlspecialchars($formato["name"]),
                htmlspecialchars($formato["code"]),
                htmlspecialchars($formato["platform_name"] ?? ''),
                $estado,
                $acciones
            ];
        }

        echo json_encode(["data" => $data]);
    }
}

// Detectar la acción:
if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listar = new AjaxFormats();
    $listar->ajaxListFormats();
    return;
}

// Editar formato
if (isset($_POST["formatId"]) && !isset($_POST["active"])) {
    $editar = new AjaxFormats();
    $editar->formatId = $_POST["formatId"];
    $editar->ajaxEditFormat();
}

// Activar/desactivar formato
if (isset($_POST["active"])) {

    $activar = new AjaxFormats();
    $activar->formatId = $_POST["id"];
    $activar->formatActive = $_POST["active"];
    $activar->ajaxActiveFormat();
}
