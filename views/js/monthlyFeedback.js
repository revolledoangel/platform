/* ==========================================================================
   Feedback Mensual – Panel interno
   ========================================================================== */

$(function () {

    // ── DataTable ──────────────────────────────────────────────────────────
    if ($('#feedbackTable').length) {
        $('#feedbackTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: [[4, 'desc']], // Ordenar por fecha de creación DESC
            columnDefs: [
                { orderable: false, targets: [5] } // Columna Acciones
            ]
        });
    }

    // ── Inicializar Select2 en el modal ────────────────────────────────────
    $('#addFeedbackModal').on('shown.bs.modal', function () {
        $('#newFeedbackClientId').select2({
            dropdownParent: $('#addFeedbackModal'),
            width: '100%'
        });
    });

    // ── Copiar link del formulario ─────────────────────────────────────────
    $(document).on('click', '.btn-copyFormLink', function () {
        var link = $(this).data('link');
        if (!link) return;

        // Intentar con la API del portapapeles
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(link).then(function () {
                showCopySuccess();
            }).catch(function () {
                fallbackCopy(link);
            });
        } else {
            fallbackCopy(link);
        }
    });

    function fallbackCopy(text) {
        var $tmp = $('<textarea>').val(text).appendTo('body').select();
        document.execCommand('copy');
        $tmp.remove();
        showCopySuccess();
    }

    function showCopySuccess() {
        swal({
            type: 'success',
            title: '¡Link copiado!',
            text: 'El link del formulario fue copiado al portapapeles.',
            timer: 2000,
            showConfirmButton: false
        });
    }

    // ── Ver respuestas ─────────────────────────────────────────────────────
    $(document).on('click', '.btn-viewResponse', function () {
        var feedbackId = $(this).data('feedback-id');
        $('#viewResponseBody').html(
            '<div class="text-center text-muted" style="padding:30px;">' +
            '<i class="fa fa-spinner fa-spin fa-2x"></i><p>Cargando respuestas...</p></div>'
        );
        $('#viewResponseModal').modal('show');

        $.getJSON('ajax/monthlyFeedback.ajax.php', { action: 'getResponse', id: feedbackId }, function (res) {
            if (!res.success) {
                $('#viewResponseBody').html(
                    '<div class="alert alert-danger">' + (res.message || 'Error al cargar.') + '</div>'
                );
                return;
            }

            function esc(str) { return $('<div>').text(str || '').html(); }

            function qualityBadge(val) {
                var v = parseInt(val || 0);
                if (!v) return '<span class="text-muted">—</span>';
                var labels = {1:'Muy mala',2:'Mala',3:'Baja',4:'Regular',5:'Aceptable',6:'Buena',7:'Bastante buena',8:'Muy buena',9:'Excelente',10:'Excepcional'};
                var color = v <= 3 ? '#e74c3c' : v <= 5 ? '#f39c12' : v <= 7 ? '#3498db' : '#27ae60';
                return '<span style="display:inline-flex;align-items:center;gap:6px;">' +
                    '<span style="background:' + color + ';color:#fff;font-weight:700;border-radius:50%;width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;font-size:14px;">' + v + '</span>' +
                    '<span style="font-weight:600;">' + v + '/10</span>' +
                    '<span class="text-muted">— ' + (labels[v] || '') + '</span></span>';
            }

            var responses = Array.isArray(res.data) ? res.data : [res.data];
            var html = '';

            if (responses.length > 1) {
                html += '<div class="panel-group" id="responseAccordion">';
            }

            $.each(responses, function (idx, d) {
                var body =
                    '<table class="table table-bordered" style="margin-bottom:0;">' +
                    '<tr class="active"><th colspan="2" style="background:#f4f4f4;">Identificación</th></tr>' +
                    '<tr><th style="width:35%">Proyecto</th><td>' + esc(d.project_name) + '</td></tr>' +
                    '<tr><th>Contacto</th><td>' + esc(d.contact_name) + '</td></tr>' +
                    '<tr><th>Periodo</th><td>' + esc(d.report_month) + ' – ' + esc(d.report_period) + '</td></tr>' +
                    '<tr><th>Enviado</th><td>' + esc(d.submitted_at) + '</td></tr>' +

                    '<tr class="active"><th colspan="2" style="background:#f4f4f4;">Leads por fuente</th></tr>' +
                    '<tr><td colspan="2">' + (function() {
                        var sources = [];
                        try { sources = JSON.parse(d.sources_json || '[]'); } catch(e) {}
                        if (!sources.length) return '<em class="text-muted">Sin datos</em>';
                        var h = '<table class="table table-condensed table-bordered" style="margin-bottom:0;">' +
                                '<thead><tr><th>Fuente</th><th>Recibidos</th><th>Contestaron</th><th>Son perfil</th></tr></thead><tbody>';
                        var totR = 0, totC = 0, totP = 0;
                        sources.forEach(function(s) {
                            var r = parseInt(s.received)||0, c = parseInt(s.replied)||0, p = parseInt(s.profile)||0;
                            totR += r; totC += c; totP += p;
                            h += '<tr><td>' + esc(s.platform) + '</td><td>' + r + '</td><td>' + c + '</td><td>' + p + '</td></tr>';
                        });
                        h += '<tr style="font-weight:700;background:#f9f9f9;"><td>Total</td><td>' + totR + '</td><td>' + totC + '</td><td>' + totP + '</td></tr>';
                        h += '</tbody></table>';
                        return h;
                    })() + '</td></tr>' +
                    '<tr><th>Comentarios sobre leads</th><td>' + esc(d.source_comments || '—') + '</td></tr>' +

                    '<tr class="active"><th colspan="2" style="background:#f4f4f4;">Evaluación general</th></tr>' +
                    '<tr><th>Calidad de leads</th><td>' + qualityBadge(d.quality_rating) + '</td></tr>' +
                    '<tr><th>Comentario libre</th><td>' + esc(d.free_comment || '—') + '</td></tr>' +
                    '</table>';

                if (responses.length > 1) {
                    html += '<div class="panel panel-default">' +
                        '<div class="panel-heading" role="tab">' +
                        '  <h4 class="panel-title">' +
                        '    <a data-toggle="collapse" data-parent="#responseAccordion" href="#collapse' + idx + '">' +
                        '      <i class="fa fa-calendar"></i> ' + esc(d.report_month) + ' – ' + esc(d.report_period) +
                        '      <small class="pull-right text-muted">' + esc(d.submitted_at) + '</small>' +
                        '    </a>' +
                        '  </h4></div>' +
                        '<div id="collapse' + idx + '" class="panel-collapse collapse' + (idx === 0 ? ' in' : '') + '" role="tabpanel">' +
                        '  <div class="panel-body" style="padding:0;">' + body + '</div>' +
                        '</div></div>';
                } else {
                    html += body;
                }
            });

            if (responses.length > 1) html += '</div>';
            $('#viewResponseBody').html(html);
        });
    });

    // ── Eliminar feedback ──────────────────────────────────────────────────
    $(document).on('click', '.btn-deleteFeedback', function () {
        var feedbackId = $(this).data('feedback-id');
        var clientName = $(this).data('client');

        swal({
            title: '¿Eliminar este feedback?',
            text: 'Se eliminará el feedback de "' + clientName + '" y su respuesta si existe.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, eliminar'
        }).then(function (result) {
            if (!result.value) return;

            $.post('ajax/monthlyFeedback.ajax.php', { action: 'deleteFeedback', id: feedbackId }, function (res) {
                if (res.success) {
                    swal({
                        type: 'success',
                        title: 'Eliminado',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function () {
                        location.reload();
                    });
                } else {
                    swal({ type: 'error', title: 'Error', text: res.message || 'No se pudo eliminar.' });
                }
            }, 'json');
        });
    });

});
