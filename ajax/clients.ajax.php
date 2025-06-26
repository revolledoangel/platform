<?php
require_once "../controllers/clients.controller.php";
require_once "../models/clients.model.php";

class AjaxClients
{
    public $clientId;
    public $clientActive;

    public function ajaxEditClient()
    {
        $url = "https://algoritmo.digital/backend/public/api/clients/" . $this->clientId;
        $response = file_get_contents($url);
        echo $response;
    }

    public function ajaxActiveClient()
    {
        if (!$this->clientId || $this->clientActive === null) {
            echo json_encode([
                "success" => false,
                "message" => "Datos incompletos para actualizar estado"
            ]);
            return;
        }

        Clients_controller::ctrCambiarEstadoCliente($this->clientId, $this->clientActive);
    }

    public function ajaxListClients()
    {
        $clientes = Clients_controller::ctrShowClients();

        $data = [];

        foreach ($clientes as $cliente) {
            $verticales = '';
            if (!empty($cliente["verticals"]) && is_array($cliente["verticals"])) {
                foreach ($cliente["verticals"] as $vertical) {
                    $verticales .= '<span class="label label-primary" style="margin-left: 5px;">' . htmlspecialchars($vertical["name"]) . '</span>';
                }
            }

            $estado = '<label class="switch-client text-center">
                <input type="checkbox" class="toggle-active" data-id="' . $cliente["id"] . '" ' . ($cliente["active"] == 1 ? "checked" : "") . '>
                <span class="slider round"></span>
            </label>';

            $acciones = '<div class="btn-group">
                <button type="button" class="btn btn-default btn-warning btn-editClient" clientId="' . $cliente["id"] . '" data-toggle="modal" data-target="#editClientModal">
                    <span class="glyphicon glyphicon-pencil"></span>
                </button>
                <button type="button" class="btn btn-default btn-danger btn-deleteClient" clientId="' . $cliente["id"] . '">
                    <span class="glyphicon glyphicon-remove"></span>
                </button>
            </div>';

            $data[] = [
                htmlspecialchars($cliente["name"]),
                htmlspecialchars($cliente["code"]),
                !empty($cliente["user_id"]) ? htmlspecialchars($cliente["user_name"]) : '',
                $verticales,
                $estado,
                $acciones
            ];
        }

        echo json_encode(["data" => $data]);
    }
}

// Detectar la acción:
if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listar = new AjaxClients();
    $listar->ajaxListClients();
    return;
}

// Editar cliente
if (isset($_POST["clientId"]) && !isset($_POST["active"])) {
    $editar = new AjaxClients();
    $editar->clientId = $_POST["clientId"];
    $editar->ajaxEditClient();
}

// Activar/desactivar cliente
if (isset($_POST["active"])) {
    $activar = new AjaxClients();
    $activar->clientId = $_POST["id"];
    $activar->clientActive = $_POST["active"];
    $activar->ajaxActiveClient();
}
