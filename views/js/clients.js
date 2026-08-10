$(document).ready(function () {

    var clientFeeConcepts = [];
    var clientFeeRules = [];
    var clientFeeCharges = [];
    var feeCurrencies = [];
    var isLoadingClientFee = false;
    var clientFeeLoadSeq = 0;
    var currentFeeClientId = null;

    function isAdminProfile() {
        var perfil = ($('#perfilUsuario').val() || '').trim();
        return perfil === 'Super' || perfil === 'Administrador';
    }

    function loadFeeConcepts(selectedConceptId) {
        return fetch('ajax/fees.ajax.php?action=list_concepts')
            .then(res => res.json())
            .then(resp => {
                clientFeeConcepts = (resp && resp.success && Array.isArray(resp.data)) ? resp.data : [];
                var $select = $('#clientFeeConceptSelect');
                if (!$select.length) return;

                var options = '<option value="">-- Selecciona un concepto --</option>';
                clientFeeConcepts.forEach(c => {
                    options += '<option value="' + c.id + '">' + $('<span>').text(c.name).html() + '</option>';
                });
                $select.html(options);

                if (selectedConceptId) {
                    $select.val(String(selectedConceptId));
                    var selected = clientFeeConcepts.find(c => String(c.id) === String(selectedConceptId));
                    $('#clientFeeConceptName').val(selected ? selected.name : '');
                }
            });
    }

    function loadFeeCurrencies() {
        return fetch('ajax/currencies.ajax.php?action=list&only_active=1')
            .then(res => res.json())
            .then(resp => {
                feeCurrencies = (resp && resp.success && Array.isArray(resp.data)) ? resp.data : [];
                var options = '';
                feeCurrencies.forEach(function (m) {
                    options += '<option value="' + m.code + '">' + m.code + '</option>';
                });
                $('#clientFeeChargeCurrency').html(options);
            });
    }

    function renderClientFeeRules() {
        var $tbody = $('#clientFeeRulesBody');
        if (!$tbody.length) return;

        $tbody.empty();
        if (isLoadingClientFee) {
            $tbody.html('<tr><td colspan="8" class="text-center text-muted">Cargando configuración...</td></tr>');
            return;
        }
        if (!clientFeeRules.length) {
            $tbody.html('<tr><td colspan="8" class="text-center text-muted">Sin reglas configuradas</td></tr>');
            return;
        }

        var currencyOptions = '';
        feeCurrencies.forEach(function (m) {
            currencyOptions += '<option value="' + m.code + '">' + m.code + '</option>';
        });

        clientFeeRules.forEach((r, idx) => {
            var maxVal = (r.max_investment === null || r.max_investment === '' || typeof r.max_investment === 'undefined') ? '' : r.max_investment;
            var selectedCurrency = (r.fixed_currency_code || 'USD');
            var mode = r.fee_mode || 'percentage';
            var feeLabel = (r.fee_label || '').replace(/"/g, '&quot;');
            var row = '' +
                '<tr data-index="' + idx + '">' +
                '<td><input type="number" class="form-control input-sm rule-min" step="0.01" value="' + (typeof r.min_investment !== 'undefined' ? r.min_investment : 0) + '"></td>' +
                '<td><input type="number" class="form-control input-sm rule-max" step="0.01" value="' + maxVal + '" placeholder="Sin tope"></td>' +
                '<td>' +
                    '<select class="form-control input-sm rule-mode">' +
                        '<option value="percentage" ' + (mode === 'percentage' ? 'selected' : '') + '>Porcentaje</option>' +
                        '<option value="fixed" ' + (mode === 'fixed' ? 'selected' : '') + '>Fijo</option>' +
                    '</select>' +
                '</td>' +
                '<td><input type="text" class="form-control input-sm rule-label" maxlength="120" value="' + feeLabel + '" placeholder="Ej: Fee Google"></td>' +
                '<td class="rule-percentage-cell"><input type="number" class="form-control input-sm rule-percentage" step="0.0001" value="' + (typeof r.percentage_value !== 'undefined' ? r.percentage_value : 0) + '"></td>' +
                '<td class="rule-fixed-cell"><input type="number" class="form-control input-sm rule-fixed" step="0.01" value="' + (typeof r.fixed_value !== 'undefined' ? r.fixed_value : 0) + '"><span class="text-muted rule-fixed-na" style="display:none;">No aplica</span></td>' +
                '<td class="rule-currency-cell"><select class="form-control input-sm rule-fixed-currency">' + currencyOptions + '</select><span class="text-muted rule-currency-na" style="display:none;">-</span></td>' +
                '<td><button type="button" class="btn btn-danger btn-sm btn-remove-rule"><i class="fa fa-trash"></i></button></td>' +
                '</tr>';
            $tbody.append(row);
            var $row = $tbody.find('tr[data-index="' + idx + '"]');
            $row.find('.rule-fixed-currency').val(selectedCurrency);
            applyClientRuleModeUI($row);
        });
    }

    function applyClientRuleModeUI($row) {
        if (!$row || !$row.length) return;
        var mode = $row.find('.rule-mode').val() || 'percentage';
        var isPercentage = mode === 'percentage';

        $row.find('.rule-percentage').prop('disabled', !isPercentage).toggle(isPercentage);
        $row.find('.rule-fixed').prop('disabled', isPercentage).toggle(!isPercentage);
        $row.find('.rule-fixed-currency').prop('disabled', isPercentage).toggle(!isPercentage);
        $row.find('.rule-fixed-na').toggle(isPercentage);
        $row.find('.rule-currency-na').toggle(isPercentage);
    }

    function renderClientFeeCharges() {
        var $tbody = $('#clientFeeChargesBody');
        if (!$tbody.length) return;

        $tbody.empty();
        if (isLoadingClientFee) {
            $tbody.html('<tr><td colspan="4" class="text-center text-muted">Cargando configuración...</td></tr>');
            return;
        }
        if (!clientFeeCharges.length) {
            $tbody.html('<tr><td colspan="4" class="text-center text-muted">Sin cargos configurados</td></tr>');
            return;
        }

        clientFeeCharges.forEach((c, idx) => {
            var row = '' +
                '<tr data-index="' + idx + '">' +
                '<td>' + $('<span>').text(c.concept_name || '').html() + '</td>' +
                '<td>' + Number(c.amount || 0).toFixed(2) + '</td>' +
                '<td>' + (c.currency_code || 'USD') + '</td>' +
                '<td><button type="button" class="btn btn-danger btn-sm btn-remove-charge"><i class="fa fa-trash"></i></button></td>' +
                '</tr>';
            $tbody.append(row);
        });
    }

    function collectClientFeeRulesFromTable() {
        var rules = [];
        $('#clientFeeRulesBody tr[data-index]').each(function () {
            var $row = $(this);
            var min = parseFloat($row.find('.rule-min').val());
            var maxRaw = $row.find('.rule-max').val();
            var mode = $row.find('.rule-mode').val();
            var feeLabel = ($row.find('.rule-label').val() || '').trim();
            var percentage = parseFloat($row.find('.rule-percentage').val());
            var fixed = parseFloat($row.find('.rule-fixed').val());
            var fixedCurrency = $row.find('.rule-fixed-currency').val() || 'USD';

            var normalizedMode = mode || 'percentage';
            var normalizedPercentage = isNaN(percentage) ? 0 : percentage;
            var normalizedFixed = isNaN(fixed) ? 0 : fixed;

            if (normalizedMode === 'percentage') {
                normalizedFixed = 0;
            } else if (normalizedMode === 'fixed') {
                normalizedPercentage = 0;
            }

            rules.push({
                min_investment: isNaN(min) ? 0 : min,
                max_investment: maxRaw === '' ? null : (isNaN(parseFloat(maxRaw)) ? null : parseFloat(maxRaw)),
                fee_mode: normalizedMode,
                fee_label: feeLabel,
                percentage_value: normalizedPercentage,
                fixed_value: normalizedFixed,
                fixed_currency_code: fixedCurrency
            });
        });
        return rules;
    }

    function loadClientFeeConfig(clientId) {
        if (!$('#clientFeeRulesBody').length) {
            return Promise.resolve();
        }

        currentFeeClientId = String(clientId);
        var reqSeq = ++clientFeeLoadSeq;
        isLoadingClientFee = true;
        clientFeeRules = [];
        clientFeeCharges = [];
        renderClientFeeRules();
        renderClientFeeCharges();

        var url = 'ajax/fees.ajax.php?action=get_client_config&client_id=' + encodeURIComponent(clientId) + '&_ts=' + Date.now();
        return fetch(url, { cache: 'no-store' })
            .then(res => res.json())
            .then(resp => {
                if (reqSeq !== clientFeeLoadSeq || currentFeeClientId !== String(clientId)) {
                    return;
                }
                var data = (resp && resp.success && resp.data) ? resp.data : { rules: [], charges: [] };
                clientFeeRules = Array.isArray(data.rules) ? data.rules : [];
                clientFeeCharges = Array.isArray(data.charges) ? data.charges : [];
                isLoadingClientFee = false;
                renderClientFeeRules();
                renderClientFeeCharges();
            })
            .catch(() => {
                if (reqSeq !== clientFeeLoadSeq || currentFeeClientId !== String(clientId)) {
                    return;
                }
                isLoadingClientFee = false;
                clientFeeRules = [];
                clientFeeCharges = [];
                renderClientFeeRules();
                renderClientFeeCharges();
            });
    }

    /** INICIALIZAR DataTable y guardar referencia en una variable global */
    var table = $('#clientsTable').DataTable({
        ajax: "ajax/clients.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        order: [],
        columnDefs: [
            {
                targets: 4, // columna oculta para estado
                visible: false
            }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });

    // Filtro personalizado por estado
    $('#filtroEstado').on('change', function () {
        let valor = $(this).val();
        table.column(4).search(valor).draw(); // aquí sí existe `table`
    });

    /* Editar Cliente*/
    $(document).on("click", ".btn-editClient", function () {

        var idClient = $(this).attr("clientId");

        // Evita mostrar datos del cliente anterior mientras carga este cliente.
        currentFeeClientId = String(idClient);
        isLoadingClientFee = true;
        clientFeeRules = [];
        clientFeeCharges = [];
        renderClientFeeRules();
        renderClientFeeCharges();
        $('#clientFeeConceptName').val('');
        $('#clientFeeConceptAmount').val('');

        fetch(`https://algoritmo.digital/backend/public/api/clients/${idClient}`)
            .then(response => response.json())
            .then(data => {
                if (data) {

                    // === 🔄 LIMPIAR CAMPOS DEL MODAL ===

                    // Limpiar inputs de texto (menos ocultos)
                    $("#editClientModal input[type='text']").val("");

                    // Limpiar selects normales
                    $("#editClientModal select").val("");

                    // Limpiar Select2 múltiple
                    $("#editClientModal .select2").val(null).trigger("change");

                    // ✅ AHORA llenar los nuevos datos

                    $("input[name='editClientId']").val(idClient);
                    $("input[name='editClientName']").val(data.name);
                    $("input[name='editClientCode']").val(data.code);

                    const $select = $("select[name='editClientUser']");
                    const existingOption = $select.find("option[value='" + data.user_id + "']");

                    if (existingOption.length) {
                        $select.val(data.user_id);
                    } else {
                        const $option = $("#editClientUser");
                        $option.val(data.user_id).text(data.user_name).prop("selected", true);
                    }

                    $select.trigger("change");

                    // Verticales
                    const verticalNames = data.verticals;
                    const $verticalSelect = $("select[name='editClientVerticals[]']");
                    let selectedVerticalIds = [];

                    $verticalSelect.find("option").each(function () {
                        const $option = $(this);
                        const optionText = $option.text().trim();
                        if (verticalNames.includes(optionText)) {
                            selectedVerticalIds.push($option.val());
                        }
                    });

                    $verticalSelect.val(selectedVerticalIds).trigger("change");

                    // Cargar looker_url desde DB local
                    fetch('ajax/clients.ajax.php?action=get_looker_url&client_id=' + idClient)
                        .then(r => r.json())
                        .then(lr => {
                            $("input[name='editClientLookerUrl']").val(lr.looker_url || '');
                        });

                    // Carga el fee de inmediato y catálogos en paralelo para reducir latencia visual.
                    loadClientFeeConfig(idClient);
                    loadFeeConcepts();
                    loadFeeCurrencies().then(() => {
                        if (currentFeeClientId === String(idClient)) {
                            renderClientFeeRules();
                            renderClientFeeCharges();
                        }
                    });

                } else {
                    alert("No se pudo obtener la información del usuario.");
                }
            })

            .catch(error => {
                console.error("Error al obtener datos del usuario:", error);
                isLoadingClientFee = false;
                renderClientFeeRules();
                renderClientFeeCharges();
            });
    });

    $("#editClientForm").on("submit", function (e) {
        e.preventDefault();

        const clientId = $("input[name='editClientId']").val();
        const name = $("input[name='editClientName']").val().trim();
        const code = $("input[name='editClientCode']").val().trim();
        const userId = $("select[name='editClientUser']").val();
        const verticalIds = $("select[name='editClientVerticals[]']").val();

        // Validación básica
        if (!name || !code) {
            swal({
                icon: "warning",
                title: "Campos obligatorios",
                text: "Debes ingresar el nombre y el código del cliente."
            });
            return;
        }

        let body = {
            name: name,
            code: code
        };

        const lookerUrl = $("input[name='editClientLookerUrl']").val().trim();

        if (userId) {
            body.user_id = parseInt(userId);
        }

        if (verticalIds && verticalIds.length > 0) {
            body.vertical_ids = verticalIds.map(id => parseInt(id));
        }

        fetch(`https://algoritmo.digital/backend/public/api/clients/${clientId}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(body)
        })
            .then(async res => {
                const status = res.status;
                const response = await res.json();

                if (status === 200 || status === 201) {
                    // Save looker_url locally
                    const fd = new FormData();
                    fd.append('action', 'save_looker_url');
                    fd.append('client_id', clientId);
                    fd.append('looker_url', lookerUrl);
                    fetch('ajax/clients.ajax.php', { method: 'POST', body: fd }).catch(() => {});

                    if ($('#clientFeeRulesBody').length) {
                        const rules = collectClientFeeRulesFromTable();
                        const charges = clientFeeCharges;
                        const feeFd = new FormData();
                        feeFd.append('action', 'save_client_config');
                        feeFd.append('client_id', clientId);
                        feeFd.append('rules_json', JSON.stringify(rules));
                        feeFd.append('charges_json', JSON.stringify(charges));
                        const feeRes = await fetch('ajax/fees.ajax.php', { method: 'POST', body: feeFd });
                        const feeJson = await feeRes.json();
                        if (!feeJson || !feeJson.success) {
                            throw new Error((feeJson && feeJson.message) ? feeJson.message : 'No se pudo guardar la configuración de fee.');
                        }
                    }

                    const nombre = response.name || "—";
                    const codigo = response.code || "—";
                    const usuario = response.user_name || "—";
                    const verticales = Array.isArray(response.verticals)
                        ? response.verticals.map(v => v.name).join(", ")
                        : "—";

                    swal({
                        icon: "success",
                        title: "Cliente actualizado correctamente",
                        html: `
                            <b>Nombre:</b> ${nombre}<br>
                            <b>Usuario asignado:</b> ${usuario}<br>
                            <b>Vertical(es):</b> ${verticales}<br>
                            <b>Código:</b> ${codigo}
                        `
                    }).then(() => {
                        $("#editClientModal").modal("hide");
                        location.reload();
                    });

                } else {
                    swal({
                        icon: "error",
                        title: "Error al actualizar",
                        text: response.message || "Respuesta inesperada de la API."
                    });
                }
            })
            .catch(error => {
                console.error("❌ Error en fetch:", error);
                swal({
                    icon: "error",
                    title: "Error de red",
                    text: "No se pudo conectar con el servidor."
                });
            });
    });

    /* cambiar el switch de active */
    $(document).on("change", ".switch-client input[type=checkbox]", function () {
        const clientId = $(this).data("id");
        const isActive = this.checked ? 1 : 0;

        const formData = new FormData();
        formData.append("id", clientId);
        formData.append("active", isActive);

        fetch("ajax/clients.ajax.php", {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    swal({
                        icon: "success",
                        title: "Cambio exitoso",
                        text: "Se actualizó el estado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                } else {
                    swal({
                        icon: "error",
                        title: "Error",
                        text: "❌ Error: " + response.message,
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                }
            })
            .catch(error => {
                console.error("❌ Error:", error);
                swal({
                    icon: "error",
                    title: "Error de conexión",
                    text: "No se pudo conectar con el servidor.",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            });
    });

    /** Eliminar Cliente */
    $(document).on("click", ".btn-deleteClient", function () {
        var clientId = $(this).attr("clientId");

        swal({
            title: "¿Seguro que desea borrar el Cliente?",
            text: "si no lo estás, cancela la acción",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar cliente'
        }).then((result) => {
            if (result.value) {
                window.location = "index.php?route=clients&clientId=" + clientId;
            }
        });
    });

    $('#btnAddClientFeeRule').on('click', function () {
        clientFeeRules.push({
            min_investment: 0,
            max_investment: null,
            fee_mode: 'percentage',
            fee_label: '',
            percentage_value: 0,
            fixed_value: 0,
            fixed_currency_code: 'USD'
        });
        renderClientFeeRules();
    });

    $(document).on('click', '.btn-remove-rule', function () {
        var idx = parseInt($(this).closest('tr').attr('data-index'), 10);
        if (!isNaN(idx)) {
            clientFeeRules.splice(idx, 1);
            renderClientFeeRules();
        }
    });

    $(document).on('change', '.rule-mode', function () {
        var $row = $(this).closest('tr');
        applyClientRuleModeUI($row);
    });

    $('#clientFeeConceptSelect').on('change', function () {
        var conceptId = $(this).val();
        var selected = clientFeeConcepts.find(c => String(c.id) === String(conceptId));
        $('#clientFeeConceptName').val(selected ? selected.name : '');
    });

    $('#btnCreateClientFeeConcept').on('click', function () {
        var name = ($('#clientFeeNewConcept').val() || '').trim();
        if (!name) {
            swal({ icon: 'warning', title: 'Concepto requerido', text: 'Ingresa un nombre de concepto.' });
            return;
        }

        var fd = new FormData();
        fd.append('action', 'create_concept');
        fd.append('name', name);

        fetch('ajax/fees.ajax.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(resp => {
                if (!resp || !resp.success) {
                    swal({ icon: 'error', title: 'No se pudo crear', text: (resp && resp.message) ? resp.message : 'Error desconocido' });
                    return;
                }
                $('#clientFeeNewConcept').val('');
                loadFeeConcepts(resp.concept.id);
            });
    });

    $('#btnAddClientFeeCharge').on('click', function () {
        var conceptId = $('#clientFeeConceptSelect').val();
        var concept = clientFeeConcepts.find(c => String(c.id) === String(conceptId));
        var amount = parseFloat($('#clientFeeConceptAmount').val());
        var chargeCurrency = $('#clientFeeChargeCurrency').val() || 'USD';

        if (!concept) {
            swal({ icon: 'warning', title: 'Concepto requerido', text: 'Selecciona un concepto para agregar el cargo.' });
            return;
        }
        if (isNaN(amount)) {
            swal({ icon: 'warning', title: 'Monto inválido', text: 'Ingresa un monto válido.' });
            return;
        }

        clientFeeCharges.push({
            concept_id: concept.id,
            concept_name: concept.name,
            amount: amount,
            currency_code: chargeCurrency
        });
        $('#clientFeeConceptAmount').val('');
        renderClientFeeCharges();
    });

    $(document).on('click', '.btn-remove-charge', function () {
        var idx = parseInt($(this).closest('tr').attr('data-index'), 10);
        if (!isNaN(idx)) {
            clientFeeCharges.splice(idx, 1);
            renderClientFeeCharges();
        }
    });
});