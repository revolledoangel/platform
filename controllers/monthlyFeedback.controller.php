<?php

class MonthlyFeedback_Controller
{
    // ── DB ───────────────────────────────────────────────────────────────────
    private static function getConnection()
    {
        $conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
        if ($conn->connect_error) return null;
        $conn->set_charset('utf8mb4');
        return $conn;
    }

    // ── Panel: listado de feedbacks ──────────────────────────────────────────
    static public function ctrGetFeedbacks()
    {
        $conn = self::getConnection();
        if (!$conn) return [];
        $sql = "SELECT f.id, f.token, f.created_at, f.client_id,
                       c.name AS client_name, c.code AS client_code,
                       (SELECT COUNT(*) FROM monthly_feedback_responses r WHERE r.feedback_id = f.id) AS response_count,
                       (SELECT MAX(r2.submitted_at) FROM monthly_feedback_responses r2 WHERE r2.feedback_id = f.id) AS last_response
                FROM monthly_feedbacks f
                LEFT JOIN clients c ON f.client_id = c.id
                ORDER BY f.created_at DESC";
        $res  = $conn->query($sql);
        $rows = [];
        if ($res) { while ($row = $res->fetch_assoc()) $rows[] = $row; }
        $conn->close();
        return $rows;
    }

    // ── Panel: crear link permanente (solo cliente) ──────────────────────────
    public function ctrCreateFeedback()
    {
        if (!isset($_POST['newFeedbackClientId'])) return;
        $clientId  = (int) $_POST['newFeedbackClientId'];
        $createdBy = isset($_SESSION['id']) ? (int) $_SESSION['id'] : null;
        if ($clientId <= 0) {
            echo '<script>swal({ type:"error", title:"Error", text:"Selecciona un cliente." });</script>';
            return;
        }
        $conn = self::getConnection();
        if (!$conn) {
            echo '<script>swal({ type:"error", title:"Error de conexión", text:"No se pudo conectar a la base de datos." });</script>';
            return;
        }
        $check = $conn->query("SELECT id FROM monthly_feedbacks WHERE client_id = $clientId LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $conn->close();
            echo '<script>swal({ type:"info", title:"Ya existe un link", text:"Este cliente ya tiene un link de feedback generado.", confirmButtonText:"Entendido" });</script>';
            return;
        }
        $token = substr(bin2hex(random_bytes(32)), 0, 64);
        $stmt  = $conn->prepare("INSERT INTO monthly_feedbacks (client_id, token, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param('isi', $clientId, $token, $createdBy);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        if ($ok) {
            echo '<script>swal({ type:"success", title:"Link generado", text:"Ya puedes copiar y compartir el link.", confirmButtonText:"Cerrar" }).then(() => { window.location = "monthlyFeedback"; });</script>';
        } else {
            echo '<script>swal({ type:"error", title:"Error", text:"No se pudo generar el link." });</script>';
        }
    }

    // ── Panel: eliminar link y todas sus respuestas ──────────────────────────
    static public function ctrDeleteFeedback($id)
    {
        $conn = self::getConnection();
        if (!$conn) return false;
        $id   = (int) $id;
        $conn->query("DELETE FROM monthly_feedback_responses WHERE feedback_id = $id");
        $ok   = $conn->query("DELETE FROM monthly_feedbacks WHERE id = $id");
        $conn->close();
        return (bool) $ok;
    }

    // ── Panel: obtener respuestas ────────────────────────────────────────────
    static public function ctrGetResponses($feedbackId)
    {
        $conn = self::getConnection();
        if (!$conn) return [];
        $feedbackId = (int) $feedbackId;
        $res  = $conn->query(
            "SELECT r.*, c.name AS client_name
             FROM monthly_feedback_responses r
             JOIN monthly_feedbacks f ON r.feedback_id = f.id
             LEFT JOIN clients c ON f.client_id = c.id
             WHERE r.feedback_id = $feedbackId
             ORDER BY r.submitted_at DESC"
        );
        $rows = [];
        if ($res) { while ($row = $res->fetch_assoc()) $rows[] = $row; }
        $conn->close();
        return $rows;
    }

    // ── Formulario público: obtener cliente por token ────────────────────────
    static public function ctrGetFeedbackByToken($token)
    {
        $conn  = self::getConnection();
        if (!$conn) return null;
        $token = $conn->real_escape_string(trim($token));
        $res   = $conn->query(
            "SELECT f.id, f.client_id, c.name AS client_name, c.code AS client_code
             FROM monthly_feedbacks f
             LEFT JOIN clients c ON f.client_id = c.id
             WHERE f.token = '$token' LIMIT 1"
        );
        $row = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
        $conn->close();
        return $row;
    }

    // ── Formulario público: guardar respuesta (multi-envío) ──────────────────
    static public function ctrSubmitFeedbackResponse()
    {
        if (!isset($_POST['feedbackToken'])) {
            echo json_encode(['success' => false, 'message' => 'Token requerido.']);
            return;
        }
        $token        = trim($_POST['feedbackToken']);
        $projectName  = trim($_POST['projectName']  ?? '');
        $contactName  = trim($_POST['contactName']  ?? '');
        $reportMonth  = trim($_POST['reportMonth']  ?? '');
        $reportPeriod = trim($_POST['reportPeriod'] ?? '');

        if (!$contactName || !$reportMonth || !$reportPeriod) {
            echo json_encode(['success' => false, 'message' => 'Completa los campos del paso 1.']);
            return;
        }
        $validPeriods = ['1-15','16-fin','completo'];
        if (!in_array($reportPeriod, $validPeriods)) {
            echo json_encode(['success' => false, 'message' => 'Período inválido.']);
            return;
        }

        // Sources JSON (dynamic platforms)
        $sourcesJson = trim($_POST['sources_json'] ?? '');
        if ($sourcesJson && json_decode($sourcesJson) === null) $sourcesJson = null;
        $srcCom = trim($_POST['source_comments'] ?? '');
        $intAlto  = isset($_POST['int_alto'])  && $_POST['int_alto']  !== '' ? (int)$_POST['int_alto']  : null;
        $intMedio = isset($_POST['int_medio']) && $_POST['int_medio'] !== '' ? (int)$_POST['int_medio'] : null;
        $intBajo  = isset($_POST['int_bajo'])  && $_POST['int_bajo']  !== '' ? (int)$_POST['int_bajo']  : null;
        $distJson = trim($_POST['districts_json'] ?? '');
        if ($distJson && json_decode($distJson) === null) $distJson = null;
        $qualRat  = isset($_POST['quality_rating']) && $_POST['quality_rating'] !== '' ? (int)$_POST['quality_rating'] : null;
        $freeCom  = trim($_POST['free_comment'] ?? '');

        $conn = self::getConnection();
        if (!$conn) { echo json_encode(['success'=>false,'message'=>'Error de conexión.']); return; }

        $tokenEsc = $conn->real_escape_string($token);
        $res = $conn->query("SELECT id FROM monthly_feedbacks WHERE token = '$tokenEsc' LIMIT 1");
        if (!$res || $res->num_rows === 0) {
            echo json_encode(['success'=>false,'message'=>'Link inválido.']);
            $conn->close(); return;
        }
        $feedbackId = (int)$res->fetch_assoc()['id'];

        $stmt = $conn->prepare(
            "INSERT INTO monthly_feedback_responses
             (feedback_id, project_name, contact_name, report_month, report_period,
              sources_json, source_comments, int_alto, int_medio, int_bajo, districts_json,
              quality_rating, free_comment)
             VALUES (?,?,?,?,?, ?,?,?,?,?,?, ?,?)"
        );
        $stmt->bind_param(
            'issssssiiisis',
            $feedbackId,
            $projectName, $contactName, $reportMonth, $reportPeriod,
            $sourcesJson, $srcCom, $intAlto, $intMedio, $intBajo, $distJson,
            $qualRat, $freeCom
        );
        $ok = $stmt->execute();
        $errMsg = $ok ? 'ok' : $stmt->error;
        $stmt->close(); $conn->close();
        echo json_encode(['success' => $ok, 'message' => $errMsg]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────
    static public function ctrGetClients()
    {
        $url = 'https://algoritmo.digital/backend/public/api/clients';
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($httpCode === 200) ? json_decode($response, true) : [];
    }
}
