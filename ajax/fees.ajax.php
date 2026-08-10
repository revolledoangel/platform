<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../controllers/fees.controller.php";

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'list_concepts') {
    echo json_encode(['success' => true, 'data' => Fees_Controller::ctrGetFeeConcepts()]);
    exit;
}

if ($action === 'create_concept') {
    if (!Fees_Controller::ctrIsAdminSession()) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
    $name = $_POST['name'] ?? '';
    $result = Fees_Controller::ctrCreateFeeConcept($name);
    echo json_encode($result);
    exit;
}

if ($action === 'get_client_config') {
    $clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : intval($_POST['client_id'] ?? 0);
    if ($clientId <= 0) {
        echo json_encode(['success' => false, 'message' => 'client_id inválido']);
        exit;
    }
    echo json_encode(['success' => true, 'data' => Fees_Controller::ctrGetClientFeeConfig($clientId)]);
    exit;
}

if ($action === 'save_client_config') {
    if (!Fees_Controller::ctrIsAdminSession()) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    $clientId = intval($_POST['client_id'] ?? 0);
    $rules = json_decode($_POST['rules_json'] ?? '[]', true);
    $charges = json_decode($_POST['charges_json'] ?? '[]', true);

    $result = Fees_Controller::ctrSaveClientFeeConfig($clientId, $rules, $charges);
    echo json_encode($result);
    exit;
}

if ($action === 'get_mix_config') {
    $mixId = isset($_GET['mix_id']) ? intval($_GET['mix_id']) : intval($_POST['mix_id'] ?? 0);
    if ($mixId <= 0) {
        echo json_encode(['success' => false, 'message' => 'mix_id inválido']);
        exit;
    }
    echo json_encode(['success' => true, 'data' => Fees_Controller::ctrGetMixFeeConfig($mixId)]);
    exit;
}

if ($action === 'save_mix_config') {
    if (!Fees_Controller::ctrIsAdminSession()) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    $mixId = intval($_POST['mix_id'] ?? 0);
    $rules = json_decode($_POST['rules_json'] ?? '[]', true);
    $charges = json_decode($_POST['charges_json'] ?? '[]', true);

    $result = Fees_Controller::ctrSaveMixFeeConfig($mixId, $rules, $charges);
    echo json_encode($result);
    exit;
}

if ($action === 'sync_mix_from_client') {
    if (!Fees_Controller::ctrIsAdminSession()) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    $mixId = intval($_POST['mix_id'] ?? 0);
    $clientId = intval($_POST['client_id'] ?? 0);

    $result = Fees_Controller::ctrSyncMixFromClient($mixId, $clientId);
    echo json_encode($result);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
