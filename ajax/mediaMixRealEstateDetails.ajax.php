<?php
// Archivo limpio para comenzar desde cero
require_once "../controllers/mediaMixRealEstateDetails.controller.php";

if (isset($_POST['client_id'])) {
    $clientId = intval($_POST['client_id']);
    $projects = MediaMixRealEstateDetails_Controller::ctrGetProjectsByClientId($clientId);
    header('Content-Type: application/json');
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
    $platformId = intval($_POST['platform_id']);
    $formats = MediaMixRealEstateDetails_Controller::ctrGetFormatsByPlatformId($platformId);
    header('Content-Type: application/json');
    echo json_encode($formats);
    exit;
}

if (isset($_POST['get_campaign_types'])) {
    $types = MediaMixRealEstateDetails_Controller::ctrGetCampaignTypes();
    header('Content-Type: application/json');
    echo json_encode($types);
    exit;
}