<?php

/* =====================================================================
   Feedback Mensual – AJAX Handler
   ===================================================================== */

require_once '../controllers/monthlyFeedback.controller.php';
require_once '../models/monthlyFeedback.model.php';

// ── Download attachment (must run before JSON header) ──
if (($_GET['action'] ?? '') === 'downloadAttachment') {
    $responseId = isset($_GET['responseId']) ? (int)$_GET['responseId'] : 0;
    if ($responseId > 0) {
        $conn = new mysqli('srv1013.hstgr.io','u961992735_plataforma','Peru+*963.','u961992735_plataforma',3306);
        if (!$conn->connect_error) {
            $res = $conn->query("SELECT attachment_path FROM monthly_feedback_responses WHERE id = $responseId LIMIT 1");
            if ($res && $row = $res->fetch_assoc()) {
                $path = __DIR__ . '/../' . $row['attachment_path'];
                if ($row['attachment_path'] && file_exists($path)) {
                    $ext = pathinfo($path, PATHINFO_EXTENSION);
                    $mime = [
                        'pdf'=>'application/pdf','xls'=>'application/vnd.ms-excel',
                        'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'doc'=>'application/msword',
                        'docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ];
                    header('Content-Type: '.($mime[$ext] ?? 'application/octet-stream'));
                    header('Content-Disposition: attachment; filename="feedback_'.$responseId.'.'.$ext.'"');
                    header('Content-Length: '.filesize($path));
                    readfile($path);
                    $conn->close();
                    exit;
                }
            }
            $conn->close();
        }
    }
    http_response_code(404);
    echo 'Archivo no encontrado.';
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ── Enviar respuesta desde el formulario público ──────────────────
    case 'submitResponse':
        MonthlyFeedback_Controller::ctrSubmitFeedbackResponse();
        break;

    // ── Obtener respuestas para mostrar en el panel ──────────────────
    case 'getResponse':
        $feedbackId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($feedbackId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            break;
        }
        $rows = MonthlyFeedback_Controller::ctrGetResponses($feedbackId);
        if (!empty($rows)) {
            echo json_encode(['success' => true, 'data' => $rows]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Sin respuestas todavía.']);
        }
        break;

    // ── Eliminar feedback ─────────────────────────────────────────────
    case 'deleteFeedback':
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            break;
        }
        $ok = MonthlyFeedback_Controller::ctrDeleteFeedback($id);
        echo json_encode(['success' => $ok]);
        break;

    // ── Obtener un feedback (para editar) ─────────────────────────────
    case 'getFeedback':
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            break;
        }
        $fb = MonthlyFeedback_Controller::ctrGetFeedbackById($id);
        if ($fb) {
            echo json_encode(['success' => true, 'data' => $fb]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No encontrado.']);
        }
        break;

    // ── Actualizar feedback (project_ids + executives) ───────────────
    case 'updateFeedback':
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $projectIds = $_POST['projectIds'] ?? '';
        $executives = $_POST['executives'] ?? '';
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            break;
        }
        $ok = MonthlyFeedback_Controller::ctrUpdateFeedback($id, $projectIds, $executives);
        echo json_encode(['success' => $ok]);
        break;

    // ── Obtener configuración global ───────────────────────────────────
    case 'getConfig':
        $key = $_GET['key'] ?? '';
        if (!$key) {
            echo json_encode(['success' => false, 'message' => 'Clave requerida.']);
            break;
        }
        $value = MonthlyFeedback_Controller::ctrGetConfig($key);
        echo json_encode(['success' => true, 'value' => $value]);
        break;

    // ── Guardar configuración global ───────────────────────────────────
    case 'saveConfig':
        $key   = $_POST['key'] ?? '';
        $value = $_POST['value'] ?? '';
        if (!$key) {
            echo json_encode(['success' => false, 'message' => 'Clave requerida.']);
            break;
        }
        $ok = MonthlyFeedback_Controller::ctrSetConfig($key, $value);
        echo json_encode(['success' => $ok]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
        break;
}
