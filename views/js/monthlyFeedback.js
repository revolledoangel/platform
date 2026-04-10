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
                                if (proj.comments) h += '<p style="margin:2px 0;color:#666;font-size:13px;"><em>Comentario: ' + esc(proj.comments) + '</em></p>';
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

});
