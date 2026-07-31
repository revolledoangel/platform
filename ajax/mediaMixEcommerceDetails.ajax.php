<?php
// Archivo limpio para comenzar desde cero
require_once "../controllers/mediaMixEcommerceDetails.controller.php";
header('Content-Type: application/json');

if (isset($_POST['client_id'])) {
    $host = 'srv1013.hstgr.io';
    $port = 3306;
    $db   = 'u961992735_plataforma';
    $user = 'u961992735_plataforma';
    $pass = 'Peru+*963.';
    $client_id = intval($_POST['client_id']);
    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) {
        echo json_encode([]);
        exit;
    }
    $sql = "SELECT id, name FROM projects WHERE client_id = $client_id AND active = 1 ORDER BY name ASC";
    $result = $conn->query($sql);
    $projects = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
    }
    $conn->close();
    echo json_encode($projects);
    exit;
}

if (isset($_POST['get_objectives'])) {
    $objectives = MediaMixEcommerceDetails_Controller::ctrGetObjectives();
    header('Content-Type: application/json');
    echo json_encode($objectives);
    exit;
}

if (isset($_POST['get_platforms'])) {
    $platforms = MediaMixEcommerceDetails_Controller::ctrGetPlatforms();
    header('Content-Type: application/json');
    echo json_encode($platforms);
    exit;
}

if (isset($_POST['get_channels'])) {
    $channels = MediaMixEcommerceDetails_Controller::ctrGetChannels();
    header('Content-Type: application/json');
    echo json_encode($channels);
    exit;
}

if (isset($_POST['platform_id'])) {
    $host = 'srv1013.hstgr.io';
    $port = 3306;
    $db   = 'u961992735_plataforma';
    $user = 'u961992735_plataforma';
    $pass = 'Peru+*963.';
    $platform_id = intval($_POST['platform_id']);
    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) {
        echo json_encode([]);
        exit;
    }
    $sql = "SELECT id, name FROM formats WHERE platform_id = $platform_id AND active = 1 ORDER BY name ASC";
    $result = $conn->query($sql);
    $formats = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $formats[] = $row;
        }
    }
    $conn->close();
    echo json_encode($formats);
    exit;
}

if (isset($_POST['get_campaign_types'])) {
    $types = MediaMixEcommerceDetails_Controller::ctrGetCampaignTypes();
    header('Content-Type: application/json');
    echo json_encode($types);
    exit;
}

if (isset($_POST['delete_detail_id'])) {
    require_once '../controllers/mediaMixEcommerceDetails.controller.php';
    $id = intval($_POST['delete_detail_id']);
    $success = MediaMixEcommerceDetails_Controller::eliminarDetalle($id);
    echo json_encode([ 'success' => $success ]);
    exit;
}

if (isset($_POST['get_detail_id'])) {
    $id = intval($_POST['get_detail_id']);
    $host = 'srv1013.hstgr.io';
    $port = 3306;
    $db   = 'u961992735_plataforma';
    $user = 'u961992735_plataforma';
    $pass = 'Peru+*963.';
    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) {
        echo json_encode([]);
        exit;
    }
    $sql = "SELECT d.*, mmr.client_id, p.name AS project_name, p.code AS project_code, p.group AS project_group, p.active AS project_active,
                   ch.name AS channel_name, ct.name AS campaign_type_name
            FROM mediamixecommerce_details d
            LEFT JOIN mediamixecommerces mmr ON d.mediamixecommerce_id = mmr.id
            LEFT JOIN projects p ON d.project_id = p.id
            LEFT JOIN channels ch ON d.channel_id = ch.id
            LEFT JOIN campaign_types ct ON d.campaign_type_id = ct.id
            WHERE d.id = $id";
    $res = $conn->query($sql);
    $detail = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    if ($detail) {
        // Platform
        $sqlPlat = "SELECT f.platform_id, pl.name AS platform_name, pl.code AS platform_code, pl.active AS platform_active
                    FROM mme_details_formats mf
                    LEFT JOIN formats f ON mf.format_id = f.id
                    LEFT JOIN platforms pl ON f.platform_id = pl.id
                    WHERE mf.mme_detail_id = {$detail['id']} LIMIT 1";
        $resPlat = $conn->query($sqlPlat);
        if ($resPlat && $platRow = $resPlat->fetch_assoc()) {
            $detail['platform_id'] = $platRow['platform_id'];
            $detail['platform_name'] = $platRow['platform_name'];
            $detail['platform_code'] = $platRow['platform_code'];
            $detail['platform_active'] = $platRow['platform_active'];
        } else {
            $detail['platform_id'] = null;
            $detail['platform_name'] = null;
            $detail['platform_code'] = null;
            $detail['platform_active'] = null;
        }
        // Formats
        $sqlF = "SELECT f.id, f.name, f.code, f.active FROM mme_details_formats mf LEFT JOIN formats f ON mf.format_id = f.id WHERE mf.mme_detail_id = {$detail['id']}";
        $resF = $conn->query($sqlF);
        $formats_ids = [];
        while ($resF && $f = $resF->fetch_assoc()) {
            $formats_ids[] = intval($f['id']);
        }
        $detail['formats_ids'] = $formats_ids;
        // Objectives
        $sqlO = "SELECT o.id FROM mme_details_objectives mo LEFT JOIN objectives o ON mo.objective_id = o.id WHERE mo.mme_detail_id = {$detail['id']}";
        $resO = $conn->query($sqlO);
        $objectives_ids = [];
        while ($resO && $o = $resO->fetch_assoc()) {
            $objectives_ids[] = intval($o['id']);
        }
        $detail['objectives_ids'] = $objectives_ids;
    }
    $conn->close();
    echo json_encode($detail);
    exit;
}
