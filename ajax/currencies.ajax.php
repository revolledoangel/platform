<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../controllers/currencies.controller.php";
require_once "../controllers/fees.controller.php";

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'list') {
    $onlyActive = isset($_GET['only_active']) ? intval($_GET['only_active']) === 1 : false;
    echo json_encode(['success' => true, 'data' => Currencies_Controller::ctrGetCurrencies($onlyActive)]);
    exit;
}

if ($action === 'history') {
    $code = $_GET['code'] ?? '';
    if (!$code) {
        echo json_encode(['success' => false, 'message' => 'code requerido']);
        exit;
    }
    $data = Currencies_Controller::ctrGetRateHistory($code, 100);
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

if ($action === 'save_currency') {
    if (!Fees_Controller::ctrIsAdminSession()) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    $payload = [
        'code' => $_POST['code'] ?? '',
        'name' => $_POST['name'] ?? '',
        'symbol' => $_POST['symbol'] ?? '',
        'decimals' => $_POST['decimals'] ?? 2,
        'active' => $_POST['active'] ?? 1,
        'usd_per_unit' => $_POST['usd_per_unit'] ?? 0,
        'effective_at' => $_POST['effective_at'] ?? '',
    ];

    $result = Currencies_Controller::ctrSaveCurrency($payload);
    echo json_encode($result);
    exit;
}

if ($action === 'add_rate') {
    if (!Fees_Controller::ctrIsAdminSession()) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    $code = $_POST['code'] ?? '';
    $usdPerUnit = $_POST['usd_per_unit'] ?? 0;
    $effectiveAt = $_POST['effective_at'] ?? null;

    $result = Currencies_Controller::ctrAddRate($code, $usdPerUnit, $effectiveAt);
    echo json_encode($result);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
