<?php
// Archivo limpio para comenzar desde cero
require_once "../controllers/mediaMixRealEstateDetails.controller.php";
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
    $objectives = MediaMixRealEstateDetails_Controller::ctrGetObjectives();
    header('Content-Type: application/json');
    echo json_encode($objectives);
    exit;
}

if (isset($_POST['get_platforms'])) {
    $platforms = MediaMixRealEstateDetails_Controller::ctrGetPlatforms();
    header('Content-Type: application/json');
    echo json_encode($platforms);
    exit;
}

if (isset($_POST['get_channels'])) {
    $channels = MediaMixRealEstateDetails_Controller::ctrGetChannels();
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
    $types = MediaMixRealEstateDetails_Controller::ctrGetCampaignTypes();
    header('Content-Type: application/json');
    echo json_encode($types);
    exit;
}