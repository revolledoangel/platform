<?php
require_once "../controllers/lookerMenu.controller.php";

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Guardar asignaciones de ejecutivo
if ($action === 'save_assignments') {
    $userId   = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $clientIds = isset($_POST['client_ids']) ? array_map('intval', (array)$_POST['client_ids']) : [];
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'user_id inválido']);
        exit;
    }
    LookerMenu_Controller::ctrSaveAssignments($userId, $clientIds);
    echo json_encode(['success' => true]);
    exit;
}

// Obtener asignaciones actuales de un ejecutivo
if ($action === 'get_assignments') {
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    $ids = LookerMenu_Controller::ctrGetAssignmentsForUser($userId);
    echo json_encode(['success' => true, 'data' => $ids]);
    exit;
}

// Obtener datos del menú (ejecutivos + clientes con looker_url)
if ($action === 'get_menu') {
    $data = LookerMenu_Controller::ctrGetMenuData();
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

// Guardar looker_url de un cliente
if ($action === 'save_looker_url') {
    $clientId  = isset($_POST['client_id']) ? (int)$_POST['client_id'] : 0;
    $lookerUrl = $_POST['looker_url'] ?? '';
    if (!$clientId) {
        echo json_encode(['success' => false, 'message' => 'client_id inválido']);
        exit;
    }
    LookerMenu_Controller::ctrSaveLookerUrl($clientId, $lookerUrl);
    echo json_encode(['success' => true]);
    exit;
}

// Obtener looker_url de un cliente
if ($action === 'get_looker_url') {
    $clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;
    $url = LookerMenu_Controller::ctrGetLookerUrl($clientId);
    echo json_encode(['success' => true, 'looker_url' => $url]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
