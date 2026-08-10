$(document).ready(function () {
    if (!$('#currenciesTable').length) {
        return;
    }

    function toDatetimeLocalValue(mysqlDateTime) {
        if (!mysqlDateTime) return '';
        return String(mysqlDateTime).replace(' ', 'T').slice(0, 16);
    }

    function oneUsdToCurrencyText(code, usdPerUnit, decimals) {
        var rate = Number(usdPerUnit);
        if (!isFinite(rate) || rate <= 0) {
            return '-';
        }
        var localUnits = 1 / rate;
        var usedDecimals = Math.max(2, Math.min(8, parseInt(decimals || 2, 10)));
        return localUnits.toFixed(usedDecimals) + ' ' + code;
    }

    function loadCurrenciesForHistorySelect(rows) {
        var $sel = $('#currencyHistoryCode');
        var html = '';
        rows.forEach(function (r) {
            html += '<option value="' + r.code + '">' + r.code + ' - ' + $('<span>').text(r.name).html() + '</option>';
        });
        $sel.html(html);
    }

    function renderHistory(code) {
        fetch('ajax/currencies.ajax.php?action=history&code=' + encodeURIComponent(code))
            .then(function (res) { return res.json(); })
            .then(function (resp) {
                var $tbody = $('#currencyHistoryTable tbody');
                $tbody.empty();
                if (!resp || !resp.success || !Array.isArray(resp.data) || !resp.data.length) {
                    $tbody.html('<tr><td colspan="5" class="text-center text-muted">Sin historial</td></tr>');
                    return;
                }

                resp.data.forEach(function (r) {
                    var inverseText = oneUsdToCurrencyText(r.currency_code, r.usd_per_unit, r.currency_code === 'CLP' ? 0 : 2);
                    $tbody.append(
                        '<tr>' +
                        '<td>' + r.currency_code + '</td>' +
                        '<td>' + Number(r.usd_per_unit).toFixed(8) + '</td>' +
                        '<td>' + inverseText + '</td>' +
                        '<td>' + r.effective_at + '</td>' +
                        '<td>' + r.created_at + '</td>' +
                        '</tr>'
                    );
                });
            });
    }

    var table = $('#currenciesTable').DataTable({
        ajax: {
            url: 'ajax/currencies.ajax.php?action=list',
            dataSrc: function (json) {
                var rows = (json && json.success && Array.isArray(json.data)) ? json.data : [];
                loadCurrenciesForHistorySelect(rows);
                return rows.map(function (r) {
                    var actions = '' +
                        '<div class="btn-group">' +
                        '<button type="button" class="btn btn-default btn-info btn-addRate" data-code="' + r.code + '" data-name="' + $('<span>').text(r.name).html() + '"><i class="fa fa-plus"></i></button>' +
                        '</div>';
                    return [
                        r.code,
                        r.name,
                        r.symbol,
                        r.decimals,
                        r.current_usd_per_unit !== null ? Number(r.current_usd_per_unit).toFixed(8) : '-',
                        oneUsdToCurrencyText(r.code, r.current_usd_per_unit, r.decimals),
                        r.current_effective_at || '-',
                        r.active ? 'Activa' : 'Inactiva',
                        actions
                    ];
                });
            }
        },
        deferRender: true,
        retrieve: true,
        processing: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
        }
    });

    $('#btnLoadCurrencyHistory').on('click', function () {
        var code = $('#currencyHistoryCode').val();
        if (!code) return;
        renderHistory(code);
    });

    $('#currencyForm').on('submit', function (e) {
        e.preventDefault();

        var fd = new FormData(this);
        fd.append('action', 'save_currency');
        if (!fd.get('effective_at')) {
            fd.set('effective_at', '');
        }
        fd.set('active', this.active.checked ? '1' : '0');

        fetch('ajax/currencies.ajax.php', { method: 'POST', body: fd })
            .then(function (res) { return res.json(); })
            .then(function (resp) {
                if (!resp || !resp.success) {
                    swal({ icon: 'error', title: 'No se pudo guardar', text: (resp && resp.message) ? resp.message : 'Error desconocido' });
                    return;
                }
                $('#addCurrencyModal').modal('hide');
                $('#currencyForm')[0].reset();
                table.ajax.reload(null, false);
                swal({ icon: 'success', title: 'Moneda guardada' });
            });
    });

    $(document).on('click', '.btn-addRate', function () {
        var code = $(this).data('code');
        var name = $(this).data('name');
        var $form = $('#addRateForm');
        $form[0].reset();
        $form.find('input[name="code"]').val(code);
        $form.find('input[name="currency_label"]').val(code + ' - ' + name);
        $('#addRateModal').modal('show');
    });

    $('#addRateForm').on('submit', function (e) {
        e.preventDefault();

        var fd = new FormData(this);
        fd.append('action', 'add_rate');
        if (!fd.get('effective_at')) {
            fd.set('effective_at', '');
        }

        fetch('ajax/currencies.ajax.php', { method: 'POST', body: fd })
            .then(function (res) { return res.json(); })
            .then(function (resp) {
                if (!resp || !resp.success) {
                    swal({ icon: 'error', title: 'No se pudo guardar', text: (resp && resp.message) ? resp.message : 'Error desconocido' });
                    return;
                }
                $('#addRateModal').modal('hide');
                table.ajax.reload(null, false);
                renderHistory(fd.get('code'));
                swal({ icon: 'success', title: 'Tipo de cambio guardado' });
            });
    });
});
