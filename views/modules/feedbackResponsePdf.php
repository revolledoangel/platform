<?php
/* =====================================================================
   Feedback – Vista pública de respuesta (descarga PDF automática)
   Acceso sin sesión: valida token del formulario + responseId.
   ===================================================================== */

$token      = trim($_GET['token']      ?? '');
$responseId = (int)($_GET['responseId'] ?? 0);

$response   = null;
$errorMsg   = null;

if (!$token || !$responseId) {
    $errorMsg = 'Enlace inválido o incompleto.';
} else {
    $conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
    if ($conn->connect_error) {
        $errorMsg = 'No se pudo conectar a la base de datos.';
    } else {
        $conn->set_charset('utf8mb4');
        $tokenEsc = $conn->real_escape_string($token);
        $rid      = (int)$responseId;
        $res = $conn->query(
            "SELECT r.*, c.name AS client_name
             FROM monthly_feedback_responses r
             JOIN monthly_feedbacks f ON r.feedback_id = f.id
             LEFT JOIN clients c ON f.client_id = c.id
             WHERE r.id = $rid AND f.token = '$tokenEsc'
             LIMIT 1"
        );
        if ($res && $res->num_rows > 0) {
            $response = $res->fetch_assoc();
        } else {
            $errorMsg = 'El enlace no es válido o la respuesta no existe.';
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reporte de Feedback – Descargando PDF…</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: 'Helvetica Neue', Arial, sans-serif; background:#f0edff; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:flex-start; padding:40px 20px; color:#333; }
  .card { background:#fff; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,.1); padding:32px 40px; max-width:520px; width:100%; text-align:center; }
  .logo { font-size:32px; margin-bottom:8px; }
  h2 { color:#4614FF; font-size:20px; margin-bottom:8px; }
  p  { color:#666; font-size:14px; margin-bottom:16px; }
  .spinner { width:40px; height:40px; border:4px solid #e0d7ff; border-top-color:#4614FF; border-radius:50%; animation:spin .8s linear infinite; margin:16px auto; }
  @keyframes spin { to { transform:rotate(360deg); } }
  .btn { display:inline-block; background:#4614FF; color:#fff; padding:10px 24px; border-radius:8px; text-decoration:none; font-weight:700; font-size:14px; cursor:pointer; border:none; margin-top:8px; }
  .btn:hover { background:#2d0ec2; }
  .error-card h2 { color:#e74c3c; }
</style>
</head>
<body>

<?php if ($errorMsg): ?>
<div class="card error-card">
    <div class="logo">⚠️</div>
    <h2>Enlace inválido</h2>
    <p><?php echo htmlspecialchars($errorMsg); ?></p>
</div>
<?php else: ?>

<div class="card" id="statusCard">
    <div class="logo">📄</div>
    <h2>Preparando tu PDF…</h2>
    <p>El reporte se descargará automáticamente en unos segundos.<br>Si no inicia, haz clic en el botón.</p>
    <div class="spinner" id="spinner"></div>
    <button class="btn" id="btnManual" style="display:none;" onclick="downloadResponsePDF(0)">⬇ Descargar PDF</button>
</div>

<!-- Mismo contenedor que usa el admin para captura del PDF -->
<div id="adminPdfContainer" style="display:none; position:fixed; top:0; left:0; width:700px; background:#fff; padding:20px; z-index:-1;"></div>

<!-- jQuery (requerido por downloadResponsePDF para $.each y $('<div>')) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- html2pdf.js — misma versión que usa el admin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
// Inyectar datos de la respuesta en el mismo formato que usa el admin
window._fbResponseData = [<?php echo json_encode($response, JSON_UNESCAPED_UNICODE); ?>];

// ─── FUNCIÓN EXACTAMENTE IGUAL QUE EN monthlyFeedback.js ────────────────────
function downloadResponsePDF(idx) {
    var responses = window._fbResponseData;
    if (!responses || !responses.length) return;
    if (idx === undefined) idx = 0;
    var d = responses[idx];
    if (!d) return;

    function e(s) { return $('<div>').text(s || '').html(); }

    var h = '';
    h += '<div style="font-family:Helvetica Neue,Arial,sans-serif;color:#222;">';

    /* Process single response */
    (function(d) {

        h += '<div style="text-align:center;margin-bottom:18px;">';
        h += '<h2 style="color:#4614FF;margin:0 0 4px;font-size:24px;">Reporte Mensual de Leads</h2>';
        h += '<p style="color:#666;font-size:13px;margin:0;">' + e(d.project_name) + '</p>';
        h += '</div>';

        h += '<table style="width:100%;font-size:13px;margin-bottom:14px;border-collapse:collapse;">';
        h += '<tr><td style="padding:5px 0;color:#888;width:140px;">Responsable</td><td style="padding:5px 0;font-weight:600;">' + e(d.contact_name) + '</td></tr>';
        h += '<tr><td style="padding:5px 0;color:#888;">Periodo</td><td style="padding:5px 0;font-weight:600;">' + e(d.report_month) + ' – ' + e(d.report_period) + '</td></tr>';
        h += '<tr><td style="padding:5px 0;color:#888;">Enviado</td><td style="padding:5px 0;font-weight:600;">' + e(d.submitted_at) + '</td></tr>';
        h += '</table>';

        var sources = [];
        try { sources = JSON.parse(d.sources_json || '[]'); } catch (ex) {}
        var isPerProject = sources.length && sources[0] && sources[0].project_name !== undefined;

        if (isPerProject) {
            $.each(sources, function (pi, proj) {
                h += '<div style="border:1px solid #E0E0E0;border-radius:10px;padding:14px;margin-bottom:12px;">';
                h += '<h3 style="color:#4614FF;font-size:14px;margin:0 0 8px;border-bottom:2px solid #4614FF;padding-bottom:5px;">' + e(proj.project_name) + '</h3>';

                /* Sources table */
                if (proj.sources && proj.sources.length) {
                    h += '<p style="font-weight:700;font-size:11px;color:#555;margin:0 0 4px;">Plataformas</p>';
                    h += '<table style="width:100%;border-collapse:collapse;font-size:11px;margin-bottom:8px;">';
                    h += '<tr style="background:#4614FF;color:#fff;"><th style="padding:5px 8px;text-align:left;">Plataforma</th><th style="padding:5px 8px;text-align:center;">Recibidos</th><th style="padding:5px 8px;text-align:center;">Contestaron</th><th style="padding:5px 8px;text-align:center;">Son perfil</th></tr>';
                    var tR = 0, tC = 0, tP = 0;
                    $.each(proj.sources, function (si, s) {
                        var r = parseInt(s.received) || 0, c = parseInt(s.replied) || 0, p = parseInt(s.profile) || 0;
                        tR += r; tC += c; tP += p;
                        h += '<tr style="border-bottom:1px solid #eee;"><td style="padding:4px 8px;">' + e(s.platform) + '</td><td style="padding:4px 8px;text-align:center;">' + r + '</td><td style="padding:4px 8px;text-align:center;">' + c + '</td><td style="padding:4px 8px;text-align:center;">' + p + '</td></tr>';
                    });
                    h += '<tr style="font-weight:700;background:#f4f4f4;"><td style="padding:4px 8px;">Total</td><td style="padding:4px 8px;text-align:center;">' + tR + '</td><td style="padding:4px 8px;text-align:center;">' + tC + '</td><td style="padding:4px 8px;text-align:center;">' + tP + '</td></tr>';
                    h += '</table>';
                }

                /* Districts */
                if (proj.districts && proj.districts.length) {
                    h += '<p style="font-weight:700;font-size:11px;color:#555;margin:6px 0 4px;">Distritos</p>';
                    h += '<table style="width:100%;border-collapse:collapse;font-size:11px;margin-bottom:8px;">';
                    h += '<tr style="background:#6A0DAD;color:#fff;"><th style="padding:5px 8px;text-align:left;">Zona</th><th style="padding:5px 8px;text-align:left;">Distrito(s)</th><th style="padding:5px 8px;text-align:center;">Cantidad</th><th style="padding:5px 8px;text-align:center;">%</th></tr>';
                    $.each(proj.districts, function (di, dd) {
                        h += '<tr style="border-bottom:1px solid #eee;"><td style="padding:4px 8px;">' + e(dd.zone) + '</td><td style="padding:4px 8px;">' + (dd.districts || []).map(function (dn) { return e(dn); }).join(', ') + '</td><td style="padding:4px 8px;text-align:center;">' + (parseInt(dd.quantity) || 0) + '</td><td style="padding:4px 8px;text-align:center;">' + e(dd.pct || '') + '</td></tr>';
                    });
                    h += '</table>';
                }

                /* Sales */
                if (proj.ventas || proj.separaciones) {
                    h += '<p style="font-weight:700;font-size:11px;color:#555;margin:6px 0 4px;">Ventas y Separaciones</p>';
                    h += '<table style="width:100%;border-collapse:collapse;font-size:11px;margin-bottom:8px;">';
                    h += '<tr style="background:#A400F6;color:#fff;"><th style="padding:5px 8px;text-align:center;">Ventas</th><th style="padding:5px 8px;text-align:center;">Separaciones</th></tr>';
                    h += '<tr><td style="padding:4px 8px;text-align:center;">' + (parseInt(proj.ventas) || 0) + '</td><td style="padding:4px 8px;text-align:center;">' + (parseInt(proj.separaciones) || 0) + '</td></tr></table>';
                }

                /* Lead quality */
                if (proj.lead_quality) {
                    var qLabels = { alto: 'Alto', medio: 'Medio', bajo: 'Bajo' };
                    var qColors = { alto: '#27AE60', medio: '#F39C12', bajo: '#E74C3C' };
                    var qStars = { alto: '\u2605\u2605\u2605', medio: '\u2605\u2605\u2606', bajo: '\u2605\u2606\u2606' };
                    h += '<p style="font-size:11px;margin:5px 0;"><strong>Calidad de los leads:</strong> <span style="color:' + (qColors[proj.lead_quality] || '#999') + ';font-weight:700;">' + (qStars[proj.lead_quality] || '') + ' ' + (qLabels[proj.lead_quality] || proj.lead_quality) + '</span></p>';
                }

                /* Comments */
                if (proj.comments) {
                    h += '<p style="font-size:11px;color:#666;margin:4px 0;"><em>Comentario: ' + e(proj.comments) + '</em></p>';
                }
                h += '</div>';
            });
        } else {
            /* Flat sources */
            if (sources.length) {
                h += '<table style="width:100%;border-collapse:collapse;font-size:11px;margin-bottom:8px;">';
                h += '<tr style="background:#4614FF;color:#fff;"><th style="padding:5px 8px;text-align:left;">Plataforma</th><th style="padding:5px 8px;text-align:center;">Recibidos</th><th style="padding:5px 8px;text-align:center;">Contestaron</th><th style="padding:5px 8px;text-align:center;">Son perfil</th></tr>';
                $.each(sources, function (si, s) {
                    h += '<tr style="border-bottom:1px solid #eee;"><td style="padding:4px 8px;">' + e(s.platform) + '</td><td style="padding:4px 8px;text-align:center;">' + (parseInt(s.received) || 0) + '</td><td style="padding:4px 8px;text-align:center;">' + (parseInt(s.replied) || 0) + '</td><td style="padding:4px 8px;text-align:center;">' + (parseInt(s.profile) || 0) + '</td></tr>';
                });
                h += '</table>';
            }
            if (d.source_comments) h += '<p style="font-size:11px;color:#666;"><em>' + e(d.source_comments) + '</em></p>';
        }

        /* General quality */
        if (d.quality_rating) {
            var v = parseInt(d.quality_rating);
            var labels = { 1: 'Muy mala', 2: 'Mala', 3: 'Baja', 4: 'Regular', 5: 'Aceptable', 6: 'Buena', 7: 'Bastante buena', 8: 'Muy buena', 9: 'Excelente', 10: 'Excepcional' };
            h += '<p style="font-size:12px;margin:8px 0 4px;"><strong>Calidad general:</strong> ' + v + '/10 – ' + (labels[v] || '') + '</p>';
        }
        if (d.free_comment) h += '<p style="font-size:12px;margin:4px 0;"><strong>Comentario adicional:</strong> ' + e(d.free_comment) + '</p>';
    })(d);

    h += '<p style="text-align:center;color:#999;font-size:9px;margin-top:16px;">Generado el ' + new Date().toLocaleDateString('es-PE') + '</p>';
    h += '</div>';

    /* Create off-screen container for capture */
    var container = document.getElementById('adminPdfContainer');
    container.innerHTML = h;
    container.style.display = 'block';

    var fname = 'Reporte_Leads_' + (d.project_name || 'Reporte').replace(/[^a-zA-Z0-9]/g, '_') + '_' + (d.report_month || '').replace(/\s/g,'') + '.pdf';

    var opt = {
        margin: [8, 8, 8, 8],
        filename: fname,
        image: { type: 'png', quality: 1 },
        html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff', scrollY: 0, scrollX: 0 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };

    html2pdf().set(opt).from(container).save().then(function () {
        container.style.display = 'none';
        document.getElementById('spinner').style.display = 'none';
        document.getElementById('statusCard').querySelector('h2').textContent = '✅ PDF descargado';
        document.getElementById('statusCard').querySelector('p').textContent = 'El archivo se ha guardado en tu carpeta de descargas.';
        document.getElementById('btnManual').style.display = 'inline-block';
        document.getElementById('btnManual').textContent = '⬇ Descargar de nuevo';
    }).catch(function () {
        container.style.display = 'none';
        document.getElementById('spinner').style.display = 'none';
        document.getElementById('btnManual').style.display = 'inline-block';
    });
}
// ─── FIN FUNCIÓN (idéntica a monthlyFeedback.js) ────────────────────────────

// Auto-trigger al cargar
$(document).ready(function () {
    setTimeout(function () {
        // Botón de respaldo visible a los 4s por si el navegador bloquea la descarga automática
        setTimeout(function () {
            document.getElementById('btnManual').style.display = 'inline-block';
        }, 4000);
        downloadResponsePDF(0);
    }, 600);
});
</script>

<?php endif; ?>
</body>
</html>
