<?php

/* =====================================================================
   Feedback Mensual – AJAX Handler
   ===================================================================== */

require_once '../controllers/monthlyFeedback.controller.php';
require_once '../models/monthlyFeedback.model.php';

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

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
        break;
}
