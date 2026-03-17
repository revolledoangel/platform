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

        // Obtener mapeo canal → plataformas desde BD local
        $platformsByChannel = [];
        $conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
        if (!$conn->connect_error) {
            $res = $conn->query("SELECT cp.channel_id, p.name FROM channel_platform cp JOIN platforms p ON p.id = cp.platform_id ORDER BY p.name ASC");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $platformsByChannel[$row['channel_id']][] = $row['name'];
                }
            }
            $conn->close();
        }
        
        $data = [];
        foreach ($channels as $key => $channel) {
            $platforms_text = isset($platformsByChannel[$channel['id']]) ? implode(', ', $platformsByChannel[$channel['id']]) : '—';

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
                htmlspecialchars($platforms_text),
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

// BD helper
function getLocalDbConn() {
    return new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
}

// Acción: obtener todas las plataformas
if (isset($_GET["action"]) && $_GET["action"] === "get_platforms") {
    $conn = getLocalDbConn();
    if ($conn->connect_error) { echo json_encode([]); exit; }
    $res = $conn->query("SELECT id, name FROM platforms WHERE active = 1 ORDER BY name ASC");
    $platforms = [];
    while ($row = $res->fetch_assoc()) $platforms[] = $row;
    $conn->close();
    echo json_encode($platforms);
    exit;
}

// Acción: obtener plataformas de un canal
if (isset($_GET["action"]) && $_GET["action"] === "get_channel_platforms" && isset($_GET["channelId"])) {
    $channelId = intval($_GET["channelId"]);
    $conn = getLocalDbConn();
    if ($conn->connect_error) { echo json_encode([]); exit; }
    $res = $conn->query("SELECT platform_id FROM channel_platform WHERE channel_id = $channelId");
    $ids = [];
    while ($row = $res->fetch_assoc()) $ids[] = (string)$row['platform_id'];
    $conn->close();
    echo json_encode($ids);
    exit;
}

// Acción: guardar plataformas de un canal
if (isset($_POST["action"]) && $_POST["action"] === "save_channel_platforms") {
    $channelId = intval($_POST["channel_id"]);
    $platformIds = isset($_POST["platform_ids"]) ? array_map('intval', (array)$_POST["platform_ids"]) : [];
    $conn = getLocalDbConn();
    if ($conn->connect_error) { echo json_encode(['success' => false]); exit; }
    $conn->query("DELETE FROM channel_platform WHERE channel_id = $channelId");
    foreach ($platformIds as $pid) {
        if ($pid > 0) $conn->query("INSERT INTO channel_platform (channel_id, platform_id) VALUES ($channelId, $pid)");
    }
    $conn->close();
    echo json_encode(['success' => true]);
    exit;
}