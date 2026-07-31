$(document).ready(function () {

    // ─── MENÚ VISUAL ─────────────────────────────────────────────────────────

    var activeExecutiveBtn = null;

    $(document).on('click', '.btn-executive', function () {
        var $btn = $(this);

        // Toggle: si ya estaba activo, cerrarlo
        if (activeExecutiveBtn && activeExecutiveBtn[0] === $btn[0]) {
            $btn.removeClass('active');
            $btn.find('.fa').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $('#clientsPanel').fadeOut(150);
            activeExecutiveBtn = null;
            return;
        }

        // Desactivar anterior
        if (activeExecutiveBtn) {
            activeExecutiveBtn.removeClass('active');
            activeExecutiveBtn.find('.fa').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }

        $btn.addClass('active');
        $btn.find('.fa').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        activeExecutiveBtn = $btn;

        var clients = $btn.data('clients');
        var userName = $btn.text().trim().replace(/[\u2193\u2191]/, '').trim();

        // Renderizar clientes
        var $row = $('#clientButtonsRow').empty();
        $.each(clients, function (i, client) {
            var $cbtn = $('<button class="btn-client-looker"></button>')
                .text(client.name)
                .attr('data-looker-url', client.looker_url)
                .attr('data-client-name', client.name);
            $row.append($cbtn);
        });

        $('#executiveLabel').html('<i class="fa fa-user-circle-o"></i> Clientes de <strong>' + $btn.clone().children().remove().end().text().trim() + '</strong>');
        $('#clientsPanel').stop(true, true).fadeIn(200);
    });

    // Click en cliente → copiar link
    $(document).on('click', '.btn-client-looker', function () {
        var url        = $(this).data('looker-url');
        var clientName = $(this).data('client-name');

        if (!url) {
            swal({ type: 'warning', title: 'Sin enlace', text: 'Este cliente no tiene URL de Looker configurada.' });
            return;
        }

        // Copiar al portapapeles
        var copied = false;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(function () {
                showCopiedModal(clientName, url);
            }).catch(function () {
                fallbackCopy(url, clientName);
            });
        } else {
            fallbackCopy(url, clientName);
        }
    });

    function fallbackCopy(url, clientName) {
        var ta = document.createElement('textarea');
        ta.value = url;
        ta.style.position = 'fixed'; ta.style.top = '0'; ta.style.left = '0';
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        showCopiedModal(clientName, url);
    }

    function showCopiedModal(clientName, url) {
        $('#lookerCopiedClientName').text(clientName);
        $('#lookerCopiedUrl').text(url);
        $('#btnGoToLooker').attr('href', url);
        $('#lookerCopiedModal').modal('show');
    }

    // ─── PANEL ADMIN: ASIGNACIONES ────────────────────────────────────────────

    var $selectExec    = $('#selectExecutive');
    var $selectClients = $('#selectClients');
    var $btnSave       = $('#btnSaveAssignments');

    // Inicializar select2 en el panel admin
    if ($selectExec.length) {
        $selectExec.select2({ placeholder: '-- Selecciona un ejecutivo --' });
        $selectClients.select2({ placeholder: 'Selecciona clientes...' });
    }

    // Al cambiar ejecutivo, cargar sus asignaciones actuales
    $selectExec.on('change', function () {
        var userId = $(this).val();
        $btnSave.prop('disabled', !userId);

        if (!userId) {
            $selectClients.val(null).trigger('change');
            return;
        }

        $.get('ajax/lookerMenu.ajax.php', { action: 'get_assignments', user_id: userId }, function (res) {
            if (res.success) {
                $selectClients.val(res.data.map(String)).trigger('change');
            }
        }, 'json');
    });

    // Guardar asignaciones
    $btnSave.on('click', function () {
        var userId    = $selectExec.val();
        var clientIds = $selectClients.val() || [];

        if (!userId) return;

        var formData = new FormData();
        formData.append('action', 'save_assignments');
        formData.append('user_id', userId);
        clientIds.forEach(function (id) { formData.append('client_ids[]', id); });

        fetch('ajax/lookerMenu.ajax.php', { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    swal({
                        type: 'success',
                        title: 'Asignaciones guardadas',
                        text: 'Los cambios se reflejarán en el menú automáticamente.',
                        timer: 2000
                    }).then(function () { location.reload(); });
                } else {
                    swal({ type: 'error', title: 'Error', text: res.message || 'Error desconocido' });
                }
            });
    });
});
