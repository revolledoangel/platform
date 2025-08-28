<?php
require_once "../controllers/channels.controller.php";

class AjaxChannels
{
    public $channelId;

    public function ajaxEditChannel()
    {
        $url = "https://algoritmo.digital/backend/public/api/channels/" . $this->channelId;
        $response = file_get_contents($url);
        echo $response;
    }

    public function ajaxListChannels()
    {
        $channels = Channels_Controller::ctrShowChannels();

        if (empty($channels)) {
            echo json_encode(["data" => []]);
            return;
        }
        
        $data = [];
        foreach ($channels as $key => $channel) {
            $acciones = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-warning btn-editChannel" channelId="' . $channel["id"] . '" data-toggle="modal" data-target="#editChannelModal">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </button>
                            <button type="button" class="btn btn-default btn-danger btn-deleteChannel" channelId="' . $channel["id"] . '">
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                        </div>';

            $data[] = [
                ($key + 1),
                htmlspecialchars($channel["name"]),
                $acciones
            ];
        }

        echo json_encode(["data" => $data]);
    }
}

// Acción: listar
if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listar = new AjaxChannels();
    $listar->ajaxListChannels();
    return;
}

// Acción: editar (Ajustado a GET para que coincida con el fetch de JS)
if (isset($_GET["channelId"])) {
    $editar = new AjaxChannels();
    $editar->channelId = $_GET["channelId"];
    $editar->ajaxEditChannel();
}