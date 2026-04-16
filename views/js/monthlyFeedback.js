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

    // ── Especificar proyectos: toggle visibilidad ──────────────────────────
    $('#specifyProjectsCheck').on('change', function () {
        if (this.checked) {
            $('#projectListContainer').slideDown(200);
            var clientId = $('#newFeedbackClientId').val();
            if (clientId) fetchProjectsForClient(clientId);
        } else {
            $('#projectListContainer').slideUp(200);
            $('#projectIdsHidden').val('');
        }
    });

    // ── Al cambiar de cliente, recargar proyectos si checkbox marcado ──────
    $('#newFeedbackClientId').on('change', function () {
        if ($('#specifyProjectsCheck').is(':checked')) {
            fetchProjectsForClient($(this).val());
        }
    });

    function fetchProjectsForClient(clientId) {
        if (!clientId) {
            $('#projectCheckboxes').html('<p class="text-muted" style="margin:0;">Selecciona un cliente primero&hellip;</p>');
            return;
        }
        $('#projectCheckboxes').html('<p class="text-muted" style="margin:0;"><i class="fa fa-spinner fa-spin"></i> Cargando proyectos&hellip;</p>');
        $.post('ajax/projects.ajax.php', { clientId: clientId }, function (data) {
            var projects = [];
            try { projects = typeof data === 'string' ? JSON.parse(data) : data; } catch (e) {}
            if (!Array.isArray(projects) || !projects.length) {
                $('#projectCheckboxes').html('<p class="text-muted" style="margin:0;">Este cliente no tiene proyectos.</p>');
                return;
            }
            var html = '';
            projects.forEach(function (p) {
                html += '<label style="display:block;margin-bottom:4px;font-weight:normal;cursor:pointer;">' +
                    '<input type="checkbox" class="project-check" value="' + p.id + '" data-name="' + $('<span>').text(p.name).html() + '"> ' +
                    $('<span>').text(p.name).html() +
                    '</label>';
            });
            $('#projectCheckboxes').html(html);
        });
    }

    // ── Helper: executive row HTML ──────────────────────────────────────────
    function execRowHtml(name, email) {
        return '<div class="exec-row" style="display:flex;gap:6px;margin-bottom:6px;">' +
            '<input type="text" class="form-control exec-name" placeholder="Nombre" style="flex:1;" value="' + (name || '') + '">' +
            '<input type="email" class="form-control exec-email" placeholder="Correo" style="flex:1;" value="' + (email || '') + '">' +
            '<button type="button" class="btn btn-xs btn-danger" onclick="this.closest(\'.exec-row\').remove()" style="align-self:center;"><i class="fa fa-times"></i></button>' +
            '</div>';
    }

    function collectExecs(containerId) {
        var execs = [];
        $('#' + containerId + ' .exec-row').each(function () {
            var n = $(this).find('.exec-name').val().trim();
            var e = $(this).find('.exec-email').val().trim();
            if (n || e) execs.push({ name: n, email: e });
        });
        return execs;
    }

    // ── Add executive buttons ──────────────────────────────────────────────
    $('#addExecBtn').on('click', function () {
        $('#executivesContainer').append(execRowHtml('', ''));
    });
    $('#addEditExecBtn').on('click', function () {
        $('#editExecutivesContainer').append(execRowHtml('', ''));
    });

    // ── Al enviar el formulario, recoger IDs seleccionados ─────────────────
    $('#addFeedbackModal form').on('submit', function () {
        if ($('#specifyProjectsCheck').is(':checked')) {
            var selected = [];
            $('#projectCheckboxes .project-check:checked').each(function () {
                selected.push({ id: $(this).val(), name: $(this).data('name') });
            });
            $('#projectIdsHidden').val(JSON.stringify(selected));
        } else {
            $('#projectIdsHidden').val('');
        }
        // Collect executives
        var execs = collectExecs('executivesContainer');
        $('#executivesHidden').val(execs.length ? JSON.stringify(execs) : '');
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

                        /* Detect per-project format: array of {project_name, sources:[...]} */
                        var isPerProject = sources[0] && sources[0].project_name !== undefined;

                        if (isPerProject) {
                            var h = '';
                            sources.forEach(function(proj) {
                                h += '<div style="margin-bottom:12px;"><strong style="color:#333;font-size:14px;">' +
                                    '<i class="fa fa-briefcase" style="color:#FF00C8;"></i> ' + esc(proj.project_name) + '</strong>';
                                if (proj.sources && proj.sources.length) {
                                    h += '<table class="table table-condensed table-bordered" style="margin-top:6px;margin-bottom:4px;">' +
                                        '<thead><tr><th>Fuente</th><th>Recibidos</th><th>Contestaron</th><th>Son perfil</th></tr></thead><tbody>';
                                    var tR=0,tC=0,tP=0;
                                    proj.sources.forEach(function(s) {
                                        var r=parseInt(s.received)||0, c=parseInt(s.replied)||0, p=parseInt(s.profile)||0;
                                        tR+=r; tC+=c; tP+=p;
                                        h += '<tr><td>'+esc(s.platform)+'</td><td>'+r+'</td><td>'+c+'</td><td>'+p+'</td></tr>';
                                    });
                                    h += '<tr style="font-weight:700;background:#f9f9f9;"><td>Total</td><td>'+tR+'</td><td>'+tC+'</td><td>'+tP+'</td></tr>';
                                    h += '</tbody></table>';
                                } else {
                                    h += '<p class="text-muted" style="margin:4px 0;">Sin fuentes registradas</p>';
                                }
                                /* Sales */
                                if (proj.ventas || proj.separaciones) {
                                    h += '<div style="margin-top:8px;"><strong style="font-size:12px;color:#FF00C8;"><i class="fa fa-shopping-cart"></i> Ventas y Separaciones</strong>';
                                    h += '<table class="table table-condensed table-bordered" style="margin-top:4px;margin-bottom:4px;font-size:12px;">';
                                    h += '<thead><tr style="background:linear-gradient(135deg,#FF00C8,#FF6BDB);color:#fff;"><th style="text-align:center;">Ventas</th><th style="text-align:center;">Separaciones</th></tr></thead><tbody>';
                                    h += '<tr><td style="text-align:center;">'+(parseInt(proj.ventas)||0)+'</td><td style="text-align:center;">'+(parseInt(proj.separaciones)||0)+'</td></tr>';
                                    h += '</tbody></table></div>';
                                }
                                /* Lead quality */
                                if (proj.lead_quality) {
                                    var qColors = {alto:'#27AE60',medio:'#F39C12',bajo:'#E74C3C'};
                                    var qStars  = {alto:'\u2605\u2605\u2605',medio:'\u2605\u2605\u2606',bajo:'\u2605\u2606\u2606'};
                                    var qLabels = {alto:'Alto',medio:'Medio',bajo:'Bajo'};
                                    var qc = qColors[proj.lead_quality]||'#999', qs = qStars[proj.lead_quality]||'', ql = qLabels[proj.lead_quality]||proj.lead_quality;
                                    h += '<p style="margin:6px 0;font-size:13px;"><strong style="color:#333;">Calidad de los leads:</strong> <span style="color:'+qc+';font-weight:700;">'+qs+' '+ql+'</span></p>';
                                }
                                if (proj.comments) h += '<p style="margin:2px 0;color:#666;font-size:13px;"><em>Comentario: ' + esc(proj.comments) + '</em></p>';
                                /* Districts table */
                                if (proj.districts && proj.districts.length) {
                                    h += '<div style="margin-top:8px;"><strong style="font-size:12px;color:#6A0DAD;"><i class="fa fa-map-marker"></i> Distritos</strong>';
                                    h += '<table class="table table-condensed table-bordered" style="margin-top:4px;margin-bottom:4px;font-size:12px;">';
                                    h += '<thead><tr style="background:#6A0DAD;color:#fff;"><th>Zona</th><th>Distrito(s)</th><th>Cantidad</th><th>%</th></tr></thead><tbody>';
                                    var dTotal = 0;
                                    proj.districts.forEach(function(dd) {
                                        var q = parseInt(dd.quantity)||0; dTotal += q;
                                        h += '<tr><td>'+esc(dd.zone)+'</td><td>'+(dd.districts||[]).map(function(dn){return esc(dn);}).join(', ')+'</td><td style="text-align:center;">'+q+'</td><td style="text-align:center;">'+esc(dd.pct||'')+'</td></tr>';
                                    });
                                    h += '<tr style="font-weight:700;background:#f9f9f9;"><td colspan="2" style="text-align:right;">Total</td><td style="text-align:center;">'+dTotal+'</td><td></td></tr>';
                                    h += '</tbody></table></div>';
                                }
                                if (proj.attachment_path) h += '<p style="margin:4px 0;"><a href="' + proj.attachment_path + '" class="btn btn-xs btn-default" target="_blank"><i class="fa fa-download"></i> Archivo adjunto del proyecto</a></p>';
                                h += '</div>';
                            });
                            return h;
                        }

                        /* Flat format (no projects) */
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
                    '<tr><th>Archivo adjunto</th><td>' + (d.attachment_path ? '<a href="ajax/monthlyFeedback.ajax.php?action=downloadAttachment&responseId=' + d.id + '" class="btn btn-xs btn-default" target="_blank"><i class="fa fa-download"></i> Descargar archivo</a>' : '<span class="text-muted">—</span>') + '</td></tr>' +
                    '</table>' +
                    '<div style="text-align:right;padding:8px;"><button type="button" class="btn btn-sm btn-success" onclick="downloadResponsePDF(' + idx + ')">' +
                    '<i class="fa fa-file-pdf-o"></i> Descargar PDF</button></div>';

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

            /* Store data for PDF download */
            window._fbResponseData = responses;
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

    // ── Editar feedback ─────────────────────────────────────────────────
    $(document).on('click', '.btn-editFeedback', function () {
        var feedbackId = $(this).data('feedback-id');
        var clientId   = $(this).data('client-id');
        var clientName = $(this).data('client-name');
        var projectIds = $(this).data('project-ids') || '';
        var executivesRaw = $(this).attr('data-executives') || '';

        $('#editFeedbackId').val(feedbackId);
        $('#editClientName').val(clientName);

        // Populate executives
        var execs = [];
        if (executivesRaw) { try { execs = JSON.parse(executivesRaw); } catch(e) {} }
        $('#editExecutivesContainer').empty();
        if (Array.isArray(execs) && execs.length) {
            execs.forEach(function(ex) {
                $('#editExecutivesContainer').append(execRowHtml(ex.name || '', ex.email || ''));
            });
        } else {
            $('#editExecutivesContainer').append(execRowHtml('', ''));
        }

        var existing = [];
        if (projectIds) {
            try { existing = typeof projectIds === 'string' ? JSON.parse(projectIds) : projectIds; } catch(e) {}
        }
        var hasProjects = Array.isArray(existing) && existing.length > 0;
        $('#editSpecifyProjectsCheck').prop('checked', hasProjects);
        if (hasProjects) {
            $('#editProjectListContainer').show();
        } else {
            $('#editProjectListContainer').hide();
        }

        // Fetch projects
        $('#editProjectCheckboxes').html('<p class="text-muted" style="margin:0;"><i class="fa fa-spinner fa-spin"></i> Cargando proyectos&hellip;</p>');
        $.post('ajax/projects.ajax.php', { clientId: clientId }, function (data) {
            var projects = [];
            try { projects = typeof data === 'string' ? JSON.parse(data) : data; } catch(e) {}
            if (!Array.isArray(projects) || !projects.length) {
                $('#editProjectCheckboxes').html('<p class="text-muted" style="margin:0;">Este cliente no tiene proyectos.</p>');
                return;
            }
            var existingIds = existing.map(function(p) { return String(p.id); });
            var html = '';
            projects.forEach(function(p) {
                var checked = existingIds.indexOf(String(p.id)) !== -1 ? ' checked' : '';
                html += '<label style="display:block;margin-bottom:4px;font-weight:normal;cursor:pointer;">' +
                    '<input type="checkbox" class="edit-project-check" value="' + p.id + '" data-name="' + $('<span>').text(p.name).html() + '"' + checked + '> ' +
                    $('<span>').text(p.name).html() + '</label>';
            });
            $('#editProjectCheckboxes').html(html);
        });

        $('#editFeedbackModal').modal('show');
    });

    $('#editSpecifyProjectsCheck').on('change', function () {
        if (this.checked) {
            $('#editProjectListContainer').slideDown(200);
        } else {
            $('#editProjectListContainer').slideUp(200);
        }
    });

    $('#btnSaveEditFeedback').on('click', function () {
        var feedbackId = $('#editFeedbackId').val();
        var projectIds = '';
        if ($('#editSpecifyProjectsCheck').is(':checked')) {
            var selected = [];
            $('#editProjectCheckboxes .edit-project-check:checked').each(function () {
                selected.push({ id: $(this).val(), name: $(this).data('name') });
            });
            projectIds = JSON.stringify(selected);
        }
        var execs = collectExecs('editExecutivesContainer');
        var executives = execs.length ? JSON.stringify(execs) : '';

        $.post('ajax/monthlyFeedback.ajax.php', {
            action: 'updateFeedback',
            id: feedbackId,
            projectIds: projectIds,
            executives: executives
        }, function (res) {
            if (res.success) {
                $('#editFeedbackModal').modal('hide');
                swal({ type: 'success', title: 'Guardado', timer: 1500, showConfirmButton: false }).then(function () {
                    location.reload();
                });
            } else {
                swal({ type: 'error', title: 'Error', text: res.message || 'No se pudo guardar.' });
            }
        }, 'json');
    });

    // ── Guardar webhook URL global ───────────────────────────────────────
    $('#btnSaveWebhookUrl').on('click', function () {
        var url = $('#globalWebhookUrl').val().trim();
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $.post('ajax/monthlyFeedback.ajax.php', {
            action: 'saveConfig',
            key: 'webhook_url',
            value: url
        }, function (res) {
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
            if (res.success) {
                swal({ type: 'success', title: 'Webhook guardado', timer: 1500, showConfirmButton: false });
            } else {
                swal({ type: 'error', title: 'Error', text: res.message || 'No se pudo guardar.' });
            }
        }, 'json').fail(function () {
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
            swal({ type: 'error', title: 'Error', text: 'Error de conexión.' });
        });
    });

    // ══════════════════════════════════════════════════════════════════════
    //  ZONAS Y DISTRITOS – Admin CRUD
    // ══════════════════════════════════════════════════════════════════════

    function loadZones() {
        $.getJSON('ajax/monthlyFeedback.ajax.php', { action: 'getZones' }, function (res) {
            if (!res.success) return;
            var tbody = '';
            var opts = '<option value="">Zona...</option>';
            $.each(res.data, function (i, z) {
                tbody += '<tr>' +
                    '<td>' + $('<span>').text(z.name).html() + '</td>' +
                    '<td style="white-space:nowrap;">' +
                    '<button class="btn btn-xs btn-warning btn-editZone" data-id="' + z.id + '" data-name="' + $('<span>').text(z.name).html() + '"><i class="fa fa-pencil"></i></button> ' +
                    '<button class="btn btn-xs btn-danger btn-deleteZone" data-id="' + z.id + '" data-name="' + $('<span>').text(z.name).html() + '"><i class="fa fa-trash"></i></button>' +
                    '</td></tr>';
                opts += '<option value="' + z.id + '">' + $('<span>').text(z.name).html() + '</option>';
            });
            $('#zonesTable tbody').html(tbody || '<tr><td colspan="2" class="text-muted text-center">Sin zonas</td></tr>');
            $('#newDistrictZone').html(opts);
        });
    }

    function loadDistricts() {
        $.getJSON('ajax/monthlyFeedback.ajax.php', { action: 'getDistricts' }, function (res) {
            if (!res.success) return;
            var tbody = '';
            $.each(res.data, function (i, d) {
                tbody += '<tr>' +
                    '<td><span class="label label-info">' + $('<span>').text(d.zone_name).html() + '</span></td>' +
                    '<td>' + $('<span>').text(d.name).html() + '</td>' +
                    '<td style="white-space:nowrap;">' +
                    '<button class="btn btn-xs btn-warning btn-editDistrict" data-id="' + d.id + '" data-zone-id="' + d.zone_id + '" data-name="' + $('<span>').text(d.name).html() + '"><i class="fa fa-pencil"></i></button> ' +
                    '<button class="btn btn-xs btn-danger btn-deleteDistrict" data-id="' + d.id + '" data-name="' + $('<span>').text(d.name).html() + '"><i class="fa fa-trash"></i></button>' +
                    '</td></tr>';
            });
            $('#districtsTable tbody').html(tbody || '<tr><td colspan="3" class="text-muted text-center">Sin distritos</td></tr>');
        });
    }

    // Load on page init
    if ($('#zonesTable').length) { loadZones(); loadDistricts(); }

    // ── Crear zona ───────────────────────────────────────────────────────
    $('#btnAddZone').on('click', function () {
        var name = $('#newZoneName').val().trim();
        if (!name) return;
        $.post('ajax/monthlyFeedback.ajax.php', { action: 'createZone', name: name }, function (res) {
            if (res.success) { $('#newZoneName').val(''); loadZones(); loadDistricts(); }
            else swal({ type: 'error', title: 'Error', text: res.message });
        }, 'json');
    });

    // ── Editar zona ──────────────────────────────────────────────────────
    $(document).on('click', '.btn-editZone', function () {
        var id = $(this).data('id'), oldName = $(this).data('name');
        swal({
            title: 'Editar zona',
            input: 'text',
            inputValue: oldName,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.value) return;
            $.post('ajax/monthlyFeedback.ajax.php', { action: 'updateZone', id: id, name: result.value.trim() }, function (res) {
                if (res.success) { loadZones(); loadDistricts(); }
                else swal({ type: 'error', title: 'Error', text: res.message });
            }, 'json');
        });
    });

    // ── Eliminar zona ────────────────────────────────────────────────────
    $(document).on('click', '.btn-deleteZone', function () {
        var id = $(this).data('id'), name = $(this).data('name');
        swal({
            title: '¿Eliminar zona "' + name + '"?',
            text: 'Se eliminarán también todos los distritos asociados.',
            type: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.value) return;
            $.post('ajax/monthlyFeedback.ajax.php', { action: 'deleteZone', id: id }, function (res) {
                if (res.success) { loadZones(); loadDistricts(); }
                else swal({ type: 'error', title: 'Error', text: res.message });
            }, 'json');
        });
    });

    // ── Crear distrito ───────────────────────────────────────────────────
    $('#btnAddDistrict').on('click', function () {
        var zoneId = $('#newDistrictZone').val();
        var name   = $('#newDistrictName').val().trim();
        if (!zoneId || !name) return;
        $.post('ajax/monthlyFeedback.ajax.php', { action: 'createDistrict', zone_id: zoneId, name: name }, function (res) {
            if (res.success) { $('#newDistrictName').val(''); loadDistricts(); }
            else swal({ type: 'error', title: 'Error', text: res.message });
        }, 'json');
    });

    // ── Editar distrito ──────────────────────────────────────────────────
    $(document).on('click', '.btn-editDistrict', function () {
        var id = $(this).data('id'), oldName = $(this).data('name'), zoneId = $(this).data('zone-id');
        swal({
            title: 'Editar distrito',
            input: 'text',
            inputValue: oldName,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.value) return;
            $.post('ajax/monthlyFeedback.ajax.php', { action: 'updateDistrict', id: id, zone_id: zoneId, name: result.value.trim() }, function (res) {
                if (res.success) loadDistricts();
                else swal({ type: 'error', title: 'Error', text: res.message });
            }, 'json');
        });
    });

    // ── Eliminar distrito ────────────────────────────────────────────────
    $(document).on('click', '.btn-deleteDistrict', function () {
        var id = $(this).data('id'), name = $(this).data('name');
        swal({
            title: '¿Eliminar distrito "' + name + '"?',
            type: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.value) return;
            $.post('ajax/monthlyFeedback.ajax.php', { action: 'deleteDistrict', id: id }, function (res) {
                if (res.success) loadDistricts();
                else swal({ type: 'error', title: 'Error', text: res.message });
            }, 'json');
        });
    });

    // ── Clean up when modal closes ──────────────────────────────────
    $('#viewResponseModal').on('hidden.bs.modal', function () {
        window._fbResponseData = null;
    });

});

/* ==========================================================================
   Descargar PDF desde el admin – genera el mismo reporte que el formulario
   ========================================================================== */
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
    }).catch(function () {
        container.style.display = 'none';
    });
}
