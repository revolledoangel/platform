<?php
date_default_timezone_set('America/Lima');

class MonthlyFeedback_Controller
{
    // ── DB ───────────────────────────────────────────────────────────────────
    private static function getConnection()
    {
        $conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
        if ($conn->connect_error) return null;
        $conn->set_charset('utf8mb4');
        $conn->query("SET time_zone = '-05:00'");
        return $conn;
    }

    // ── Panel: listado de feedbacks ──────────────────────────────────────────
    static public function ctrGetFeedbacks()
    {
        $conn = self::getConnection();
        if (!$conn) return [];
        $sql = "SELECT f.id, f.token, f.project_ids, f.executives, f.created_at, f.client_id,
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
        $projectIds = isset($_POST['projectIds']) && $_POST['projectIds'] !== '' ? trim($_POST['projectIds']) : null;
        if ($projectIds && json_decode($projectIds) === null) $projectIds = null;
        $executives = isset($_POST['executives']) && $_POST['executives'] !== '' ? trim($_POST['executives']) : null;
        if ($executives && json_decode($executives) === null) $executives = null;
        $stmt  = $conn->prepare("INSERT INTO monthly_feedbacks (client_id, token, project_ids, executives, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('isssi', $clientId, $token, $projectIds, $executives, $createdBy);
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
            "SELECT f.id, f.client_id, f.project_ids, f.executives, f.webhook_url,
                    c.name AS client_name, c.code AS client_code
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

        // Handle file upload (general)
        $attachPath = null;
        $allowed = ['pdf','xls','xlsx','doc','docx'];
        $dir = __DIR__ . '/../uploads/feedback/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        if (!empty($_FILES['attachment']['name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $_FILES['attachment']['size'] <= 10 * 1024 * 1024) {
                $filename = uniqid('fb_') . '.' . $ext;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dir . $filename)) {
                    $attachPath = 'uploads/feedback/' . $filename;
                }
            }
        }

        // Handle per-project file uploads — inject attachment_path into sources_json
        $sourcesArr = json_decode($sourcesJson, true);
        if (is_array($sourcesArr)) {
            $projIdx = 0;
            foreach ($sourcesArr as &$proj) {
                if (isset($proj['project_name'])) { // per-project format
                    $key = 'proj_attachment_' . $projIdx;
                    if (!empty($_FILES[$key]['name']) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, $allowed) && $_FILES[$key]['size'] <= 10 * 1024 * 1024) {
                            $fname = uniqid('fp_') . '.' . $ext;
                            if (move_uploaded_file($_FILES[$key]['tmp_name'], $dir . $fname)) {
                                $proj['attachment_path'] = 'uploads/feedback/' . $fname;
                            }
                        }
                    }
                    $projIdx++;
                }
            }
            unset($proj);
            $sourcesJson = json_encode($sourcesArr, JSON_UNESCAPED_UNICODE);
        }

        $conn = self::getConnection();
        if (!$conn) { echo json_encode(['success'=>false,'message'=>'Error de conexión.']); return; }

        $tokenEsc = $conn->real_escape_string($token);
        $res = $conn->query(
            "SELECT f.id, f.client_id, f.executives, c.name AS client_name
             FROM monthly_feedbacks f
             LEFT JOIN clients c ON f.client_id = c.id
             WHERE f.token = '$tokenEsc' LIMIT 1"
        );
        if (!$res || $res->num_rows === 0) {
            echo json_encode(['success'=>false,'message'=>'Link inválido.']);
            $conn->close(); return;
        }
        $feedbackRow = $res->fetch_assoc();
        $feedbackId  = (int)$feedbackRow['id'];

        $stmt = $conn->prepare(
            "INSERT INTO monthly_feedback_responses
             (feedback_id, project_name, contact_name, report_month, report_period,
              sources_json, source_comments, int_alto, int_medio, int_bajo, districts_json,
              quality_rating, free_comment, attachment_path)
             VALUES (?,?,?,?,?, ?,?,?,?,?,?, ?,?,?)"
        );
        $stmt->bind_param(
            'issssssiiisiss',
            $feedbackId,
            $projectName, $contactName, $reportMonth, $reportPeriod,
            $sourcesJson, $srcCom, $intAlto, $intMedio, $intBajo, $distJson,
            $qualRat, $freeCom, $attachPath
        );
        $ok = $stmt->execute();
        $responseId = $ok ? $stmt->insert_id : 0;
        $errMsg = $ok ? 'ok' : $stmt->error;
        $stmt->close();
        $conn->close();

        // ── POST to webhook if configured (global config) ──
        $webhookUrl = self::ctrGetConfig('webhook_url');
        if ($ok && !empty($webhookUrl)) {
            self::postWebhook($feedbackRow, $responseId, [
                'contact_name'   => $contactName,
                'report_month'   => $reportMonth,
                'report_period'  => $reportPeriod,
                'sources_json'   => $sourcesJson,
                'source_comments'=> $srcCom,
                'quality_rating' => $qualRat,
                'free_comment'   => $freeCom,
                'attachment_path'=> $attachPath,
            ], $webhookUrl);
        }

        echo json_encode(['success' => $ok, 'message' => $errMsg]);
    }

    // ── Webhook helper ───────────────────────────────────────────────────────
    private static function postWebhook($feedbackRow, $responseId, $responseData, $webhookUrl)
    {
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                 . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

        // Build download URLs
        $attachments = [];
        if (!empty($responseData['attachment_path'])) {
            $attachments[] = [
                'type' => 'general',
                'url'  => $baseUrl . '/platform/ajax/monthlyFeedback.ajax.php?action=downloadAttachment&responseId=' . $responseId,
                'filename' => basename($responseData['attachment_path']),
            ];
        }
        // Per-project attachments
        $sources = json_decode($responseData['sources_json'], true);
        if (is_array($sources)) {
            foreach ($sources as $s) {
                if (!empty($s['attachment_path'])) {
                    $attachments[] = [
                        'type'    => 'project',
                        'project' => $s['project_name'] ?? '',
                        'url'     => $baseUrl . '/platform/' . $s['attachment_path'],
                        'filename'=> basename($s['attachment_path']),
                    ];
                }
            }
        }

        // Executives
        $execs = json_decode($feedbackRow['executives'] ?? '[]', true) ?: [];
        $emailsList = implode(', ', array_filter(array_column($execs, 'email')));

        $clientName   = $feedbackRow['client_name'] ?? '';
        $contactName  = $responseData['contact_name'];
        $reportMonth  = $responseData['report_month'];
        $reportPeriod = $responseData['report_period'];
        $qualRating   = (int)($responseData['quality_rating'] ?? 0);
        $freeCom      = $responseData['free_comment'];
        $srcCom       = $responseData['source_comments'];

        // Quality label
        $qualLabels = [1=>'Muy mala',2=>'Mala',3=>'Baja',4=>'Regular',5=>'Aceptable',
                       6=>'Buena',7=>'Bastante buena',8=>'Muy buena',9=>'Excelente',10=>'Excepcional'];
        $qualLabel = $qualLabels[$qualRating] ?? '';
        $qualColor = $qualRating <= 3 ? '#e74c3c' : ($qualRating <= 5 ? '#f39c12' : ($qualRating <= 7 ? '#3498db' : '#27ae60'));

        // Subject
        $subject = "Feedback Mensual – {$clientName} – {$reportMonth} {$reportPeriod}";

        // ── Build HTML summary ──
        $h  = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:640px;margin:0 auto;color:#333;">';
        $h .= '<div style="background:#3B20E8;padding:18px 24px;border-radius:8px 8px 0 0;">';
        $h .= '<h2 style="margin:0;color:#fff;font-size:20px;">📋 Feedback Mensual</h2></div>';
        $h .= '<div style="border:1px solid #e0e0e0;border-top:none;border-radius:0 0 8px 8px;padding:24px;">';

        // Identification
        $h .= '<table style="width:100%;border-collapse:collapse;margin-bottom:16px;">';
        $h .= '<tr><td style="padding:6px 10px;font-weight:700;width:35%;color:#555;">Cliente</td><td style="padding:6px 10px;">' . htmlspecialchars($clientName) . '</td></tr>';
        $h .= '<tr style="background:#f9f9f9;"><td style="padding:6px 10px;font-weight:700;color:#555;">Contacto</td><td style="padding:6px 10px;">' . htmlspecialchars($contactName) . '</td></tr>';
        $h .= '<tr><td style="padding:6px 10px;font-weight:700;color:#555;">Período</td><td style="padding:6px 10px;">' . htmlspecialchars($reportMonth) . ' – ' . htmlspecialchars($reportPeriod) . '</td></tr>';
        $h .= '</table>';

        // Sources / leads
        $h .= '<h3 style="margin:20px 0 10px;font-size:16px;color:#3B20E8;border-bottom:2px solid #3B20E8;padding-bottom:4px;">Leads por fuente</h3>';
        if (is_array($sources) && count($sources)) {
            $isPerProject = isset($sources[0]['project_name']);
            if ($isPerProject) {
                foreach ($sources as $proj) {
                    $pname = htmlspecialchars($proj['project_name'] ?? '');
                    $h .= '<p style="font-weight:700;margin:14px 0 6px;font-size:14px;">📁 ' . $pname . '</p>';
                    if (!empty($proj['sources'])) {
                        $h .= '<table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:6px;">';
                        $h .= '<tr style="background:#3B20E8;color:#fff;"><th style="padding:6px 8px;text-align:left;">Fuente</th><th style="padding:6px 8px;text-align:center;">Recibidos</th><th style="padding:6px 8px;text-align:center;">Contestaron</th><th style="padding:6px 8px;text-align:center;">Son perfil</th></tr>';
                        $tR=$tC=$tP=0;
                        foreach ($proj['sources'] as $s) {
                            $r=(int)($s['received']??0); $c=(int)($s['replied']??0); $p=(int)($s['profile']??0);
                            $tR+=$r; $tC+=$c; $tP+=$p;
                            $h .= '<tr style="border-bottom:1px solid #eee;"><td style="padding:5px 8px;">' . htmlspecialchars($s['platform']??'') . '</td><td style="padding:5px 8px;text-align:center;">'.$r.'</td><td style="padding:5px 8px;text-align:center;">'.$c.'</td><td style="padding:5px 8px;text-align:center;">'.$p.'</td></tr>';
                        }
                        $h .= '<tr style="font-weight:700;background:#f4f4f4;"><td style="padding:5px 8px;">Total</td><td style="padding:5px 8px;text-align:center;">'.$tR.'</td><td style="padding:5px 8px;text-align:center;">'.$tC.'</td><td style="padding:5px 8px;text-align:center;">'.$tP.'</td></tr>';
                        $h .= '</table>';
                    }
                    // Sales
                    $ventas = (int)($proj['ventas'] ?? 0);
                    $separaciones = (int)($proj['separaciones'] ?? 0);
                    if ($ventas || $separaciones) {
                        $h .= '<p style="font-weight:700;margin:10px 0 4px;font-size:13px;color:#FF00C8;">🛒 Ventas y Separaciones</p>';
                        $h .= '<table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:6px;">';
                        $h .= '<tr style="background:linear-gradient(135deg,#FF00C8,#FF6BDB);color:#fff;"><th style="padding:6px 8px;text-align:center;">Ventas</th><th style="padding:6px 8px;text-align:center;">Separaciones</th></tr>';
                        $h .= '<tr style="border-bottom:1px solid #eee;"><td style="padding:5px 8px;text-align:center;">'.$ventas.'</td><td style="padding:5px 8px;text-align:center;">'.$separaciones.'</td></tr>';
                        $h .= '</table>';
                    }
                    // Lead quality
                    if (!empty($proj['lead_quality'])) {
                        $qColors = ['alto'=>'#27AE60','medio'=>'#F39C12','bajo'=>'#E74C3C'];
                        $qStars  = ['alto'=>'★★★','medio'=>'★★☆','bajo'=>'★☆☆'];
                        $qLabels = ['alto'=>'Alto','medio'=>'Medio','bajo'=>'Bajo'];
                        $lq = $proj['lead_quality'];
                        $qc = $qColors[$lq] ?? '#999';
                        $h .= '<p style="margin:6px 0;font-size:13px;"><strong>Calidad de los leads:</strong> <span style="color:'.$qc.';font-weight:700;">'.($qStars[$lq] ?? '').' '.htmlspecialchars($qLabels[$lq] ?? $lq).'</span></p>';
                    }
                    if (!empty($proj['comments'])) $h .= '<p style="color:#666;font-size:13px;margin:4px 0;"><em>💬 ' . htmlspecialchars($proj['comments']) . '</em></p>';
                    // Districts
                    if (!empty($proj['districts'])) {
                        $h .= '<p style="font-weight:700;margin:10px 0 4px;font-size:13px;color:#6A0DAD;">📍 Distritos</p>';
                        $h .= '<table style="width:100%;border-collapse:collapse;font-size:12px;margin-bottom:6px;">';
                        $h .= '<tr style="background:#6A0DAD;color:#fff;"><th style="padding:5px 8px;text-align:left;">Zona</th><th style="padding:5px 8px;text-align:left;">Distrito(s)</th><th style="padding:5px 8px;text-align:center;">Cantidad</th><th style="padding:5px 8px;text-align:center;">%</th></tr>';
                        $dTotal = 0;
                        foreach ($proj['districts'] as $dd) {
                            $q = (int)($dd['quantity'] ?? 0); $dTotal += $q;
                            $dnames = is_array($dd['districts'] ?? null) ? implode(', ', $dd['districts']) : '';
                            $h .= '<tr style="border-bottom:1px solid #eee;"><td style="padding:4px 8px;">' . htmlspecialchars($dd['zone'] ?? '') . '</td><td style="padding:4px 8px;">' . htmlspecialchars($dnames) . '</td><td style="padding:4px 8px;text-align:center;">' . $q . '</td><td style="padding:4px 8px;text-align:center;">' . htmlspecialchars($dd['pct'] ?? '') . '</td></tr>';
                        }
                        $h .= '<tr style="font-weight:700;background:#f4f4f4;"><td colspan="2" style="padding:4px 8px;text-align:right;">Total</td><td style="padding:4px 8px;text-align:center;">' . $dTotal . '</td><td></td></tr>';
                        $h .= '</table>';
                    }
                }
            } else {
                $h .= '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
                $h .= '<tr style="background:#3B20E8;color:#fff;"><th style="padding:6px 8px;text-align:left;">Fuente</th><th style="padding:6px 8px;text-align:center;">Recibidos</th><th style="padding:6px 8px;text-align:center;">Contestaron</th><th style="padding:6px 8px;text-align:center;">Son perfil</th></tr>';
                $tR=$tC=$tP=0;
                foreach ($sources as $s) {
                    $r=(int)($s['received']??0); $c=(int)($s['replied']??0); $p=(int)($s['profile']??0);
                    $tR+=$r; $tC+=$c; $tP+=$p;
                    $h .= '<tr style="border-bottom:1px solid #eee;"><td style="padding:5px 8px;">' . htmlspecialchars($s['platform']??'') . '</td><td style="padding:5px 8px;text-align:center;">'.$r.'</td><td style="padding:5px 8px;text-align:center;">'.$c.'</td><td style="padding:5px 8px;text-align:center;">'.$p.'</td></tr>';
                }
                $h .= '<tr style="font-weight:700;background:#f4f4f4;"><td style="padding:5px 8px;">Total</td><td style="padding:5px 8px;text-align:center;">'.$tR.'</td><td style="padding:5px 8px;text-align:center;">'.$tC.'</td><td style="padding:5px 8px;text-align:center;">'.$tP.'</td></tr>';
                $h .= '</table>';
            }
        } else {
            $h .= '<p style="color:#999;">Sin datos de fuentes</p>';
        }
        if (!empty($srcCom)) {
            $h .= '<p style="margin:10px 0 0;font-size:13px;color:#666;"><strong>Comentarios sobre leads:</strong> ' . htmlspecialchars($srcCom) . '</p>';
        }

        // Quality rating
        $h .= '<h3 style="margin:20px 0 10px;font-size:16px;color:#3B20E8;border-bottom:2px solid #3B20E8;padding-bottom:4px;">Evaluación general</h3>';
        $h .= '<p style="margin:8px 0;"><strong>Calidad de leads:</strong> <span style="display:inline-block;background:'.$qualColor.';color:#fff;font-weight:700;border-radius:50%;width:28px;height:28px;line-height:28px;text-align:center;font-size:14px;vertical-align:middle;">'.$qualRating.'</span> <span style="font-weight:600;">'.$qualRating.'/10</span> — '.$qualLabel.'</p>';

        if (!empty($freeCom)) {
            $h .= '<p style="margin:8px 0;"><strong>Comentario libre:</strong> ' . htmlspecialchars($freeCom) . '</p>';
        }

        // Attachments
        if (!empty($attachments)) {
            $h .= '<h3 style="margin:20px 0 10px;font-size:16px;color:#3B20E8;border-bottom:2px solid #3B20E8;padding-bottom:4px;">Archivos adjuntos</h3><ul style="padding-left:20px;">';
            foreach ($attachments as $att) {
                $label = ($att['type'] === 'project' && !empty($att['project'])) ? htmlspecialchars($att['project']) . ' — ' : '';
                $h .= '<li style="margin-bottom:4px;">' . $label . '<a href="' . htmlspecialchars($att['url']) . '" style="color:#3B20E8;">' . htmlspecialchars($att['filename']) . '</a></li>';
            }
            $h .= '</ul>';
        }

        $h .= '<p style="margin-top:20px;font-size:12px;color:#999;text-align:center;">Enviado el ' . date('d/m/Y H:i') . '</p>';
        $h .= '</div></div>';

        $payload = [
            'event'          => 'feedback_submitted',
            'subject'        => $subject,
            'emails'         => $emailsList,
            'html_summary'   => $h,
            'client_name'    => $clientName,
            'client_id'      => $feedbackRow['client_id'] ?? '',
            'executives'     => $execs,
            'response_id'    => $responseId,
            'contact_name'   => $contactName,
            'report_month'   => $reportMonth,
            'report_period'  => $reportPeriod,
            'quality_rating' => $qualRating,
            'free_comment'   => $freeCom,
            'source_comments'=> $srcCom,
            'sources'        => $sources ?: [],
            'attachments'    => $attachments,
            'submitted_at'   => date('Y-m-d H:i:s'),
        ];

        $ch = curl_init($webhookUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    // ── Panel: actualizar feedback (project_ids, executives) ──────────────
    static public function ctrUpdateFeedback($id, $projectIds, $executives = null)
    {
        $conn = self::getConnection();
        if (!$conn) return false;
        $id = (int) $id;
        if ($projectIds && json_decode($projectIds) === null) $projectIds = null;
        if ($executives && json_decode($executives) === null) $executives = null;
        $stmt = $conn->prepare("UPDATE monthly_feedbacks SET project_ids = ?, executives = ? WHERE id = ?");
        $stmt->bind_param('ssi', $projectIds, $executives, $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return (bool) $ok;
    }

    // ── Panel: obtener un feedback por ID ────────────────────────────────
    static public function ctrGetFeedbackById($id)
    {
        $conn = self::getConnection();
        if (!$conn) return null;
        $id = (int) $id;
        $res = $conn->query(
            "SELECT f.id, f.client_id, f.project_ids, f.executives, c.name AS client_name
             FROM monthly_feedbacks f
             LEFT JOIN clients c ON f.client_id = c.id
             WHERE f.id = $id LIMIT 1"
        );
        $row = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
        $conn->close();
        return $row;
    }

    // ── Global config: get ───────────────────────────────────────────────────
    static public function ctrGetConfig($key)
    {
        $conn = self::getConnection();
        if (!$conn) return null;
        $key = $conn->real_escape_string($key);
        $res = $conn->query("SELECT config_value FROM monthly_feedback_config WHERE config_key = '$key' LIMIT 1");
        $val = ($res && $row = $res->fetch_assoc()) ? $row['config_value'] : null;
        $conn->close();
        return $val;
    }

    // ── Global config: set ───────────────────────────────────────────────────
    static public function ctrSetConfig($key, $value)
    {
        $conn = self::getConnection();
        if (!$conn) return false;
        $stmt = $conn->prepare("INSERT INTO monthly_feedback_config (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)");
        $stmt->bind_param('ss', $key, $value);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return (bool) $ok;
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

    // =====================================================================
    //  ZONAS Y DISTRITOS
    // =====================================================================

    // ── Listar zonas ─────────────────────────────────────────────────────
    static public function ctrGetZones()
    {
        $conn = self::getConnection();
        if (!$conn) return [];
        $res = $conn->query("SELECT * FROM feedback_zones ORDER BY sort_order, name");
        $rows = [];
        if ($res) { while ($r = $res->fetch_assoc()) $rows[] = $r; }
        $conn->close();
        return $rows;
    }

    // ── Crear zona ───────────────────────────────────────────────────────
    static public function ctrCreateZone($name)
    {
        $conn = self::getConnection();
        if (!$conn) return false;
        $stmt = $conn->prepare("INSERT INTO feedback_zones (name) VALUES (?)");
        $stmt->bind_param('s', $name);
        $ok = $stmt->execute();
        $id = $ok ? $stmt->insert_id : 0;
        $stmt->close(); $conn->close();
        return $id;
    }

    // ── Actualizar zona ──────────────────────────────────────────────────
    static public function ctrUpdateZone($id, $name)
    {
        $conn = self::getConnection();
        if (!$conn) return false;
        $stmt = $conn->prepare("UPDATE feedback_zones SET name = ? WHERE id = ?");
        $stmt->bind_param('si', $name, $id);
        $ok = $stmt->execute();
        $stmt->close(); $conn->close();
        return $ok;
    }

    // ── Eliminar zona ────────────────────────────────────────────────────
    static public function ctrDeleteZone($id)
    {
        $conn = self::getConnection();
        if (!$conn) return false;
        $id = (int) $id;
        $ok = $conn->query("DELETE FROM feedback_zones WHERE id = $id");
        $conn->close();
        return $ok;
    }

    // ── Listar distritos (opcionalmente filtrados por zona) ──────────────
    static public function ctrGetDistricts($zoneId = null)
    {
        $conn = self::getConnection();
        if (!$conn) return [];
        $sql = "SELECT d.*, z.name AS zone_name FROM feedback_districts d LEFT JOIN feedback_zones z ON d.zone_id = z.id";
        if ($zoneId) $sql .= " WHERE d.zone_id = " . (int) $zoneId;
        $sql .= " ORDER BY z.sort_order, z.name, d.name";
        $res = $conn->query($sql);
        $rows = [];
        if ($res) { while ($r = $res->fetch_assoc()) $rows[] = $r; }
        $conn->close();
        return $rows;
    }

    // ── Crear distrito ───────────────────────────────────────────────────
    static public function ctrCreateDistrict($zoneId, $name)
    {
        $conn = self::getConnection();
        if (!$conn) return false;
        $stmt = $conn->prepare("INSERT INTO feedback_districts (zone_id, name) VALUES (?, ?)");
        $stmt->bind_param('is', $zoneId, $name);
        $ok = $stmt->execute();
        $id = $ok ? $stmt->insert_id : 0;
        $stmt->close(); $conn->close();
        return $id;
    }

    // ── Actualizar distrito ──────────────────────────────────────────────
    static public function ctrUpdateDistrict($id, $zoneId, $name)
    {
        $conn = self::getConnection();
        if (!$conn) return false;
        $stmt = $conn->prepare("UPDATE feedback_districts SET zone_id = ?, name = ? WHERE id = ?");
        $stmt->bind_param('isi', $zoneId, $name, $id);
        $ok = $stmt->execute();
        $stmt->close(); $conn->close();
        return $ok;
    }

    // ── Eliminar distrito ────────────────────────────────────────────────
    static public function ctrDeleteDistrict($id)
    {
        $conn = self::getConnection();
        if (!$conn) return false;
        $id = (int) $id;
        $ok = $conn->query("DELETE FROM feedback_districts WHERE id = $id");
        $conn->close();
        return $ok;
    }

    // ── Zonas + distritos agrupados (para el formulario público) ─────────
    static public function ctrGetZonesWithDistricts()
    {
        $conn = self::getConnection();
        if (!$conn) return [];
        $res = $conn->query(
            "SELECT z.id AS zone_id, z.name AS zone_name,
                    d.id AS district_id, d.name AS district_name
             FROM feedback_zones z
             LEFT JOIN feedback_districts d ON d.zone_id = z.id
             ORDER BY z.sort_order, z.name, d.name"
        );
        $zones = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $zid = $r['zone_id'];
                if (!isset($zones[$zid])) {
                    $zones[$zid] = ['id' => $zid, 'name' => $r['zone_name'], 'districts' => []];
                }
                if ($r['district_id']) {
                    $zones[$zid]['districts'][] = ['id' => $r['district_id'], 'name' => $r['district_name']];
                }
            }
        }
        $conn->close();
        return array_values($zones);
    }
}
