// Archivo limpio para comenzar desde cero
var cachedProjects = null;
var lastSelectedProject = null;
var cachedMetricsByPlatform = {};
var lastSelectedMetric = null;
var cachedPlatforms = null;
var lastSelectedPlatform = null;
var cachedChannels = null;
var lastSelectedChannel = null;
var cachedFormatsByPlatform = {};
var lastSelectedFormats = [];
var lastPlatformForFormats = null;
var feeCurrenciesCatalog = [];

// Opciones de segmentación centralizadas
var segmentaciones = [
    "Prospecting (Intereses / Comportamientos)",
    "Prospecting (Palabras Clave Genéricas)",
    "Públicos Similares (Lookalikes - LAL)",
    "Prospecting Amplio / Automatizado",
    "Remarketing de Interacción",
    "Remarketing de Tráfico Web",
    "Remarketing (Palabras Clave de Marca)",
    "Remarketing de Alta Intención",
    "Clientes Actuales (Compradores)",
    "Clientes Potenciales (Leads)"
];

function renderSegmentaciones(selectId, selectedValues) {
    var options = '';
    var segs = segmentaciones.slice();
    if (selectedValues && Array.isArray(selectedValues)) {
        selectedValues.forEach(function(val) {
            if (segs.indexOf(val) === -1) segs.push(val);
        });
    }
    segs.forEach(function(seg) {
        var selected = (selectedValues && selectedValues.includes(seg)) ? ' selected' : '';
        options += '<option value="' + seg + '"' + selected + '>' + seg + '</option>';
    });
    $(selectId).html(options);
    if (selectedValues && selectedValues.length > 0) {
        $(selectId).val(selectedValues);
    } else {
        $(selectId).val('');
    }
    if ($(selectId).hasClass('select2')) {
        $(selectId).trigger('change.select2');
    }
}

$(document).ready(function () {
    // Helper: reinicializar Select2 en los selects de métrica para habilitar búsqueda
    function reinitMetricSelect($sel, $modal) {
        if ($sel.hasClass('select2-hidden-accessible')) {
            $sel.select2('destroy');
        }
        $sel.select2({ width: '100%', dropdownParent: $modal, minimumResultsForSearch: 0 });
    }

    function normalizeMetricText(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .trim();
    }

    function baseMetricText(value) {
        return String(value || '').replace(/\s*\([^)]*\)\s*$/, '').trim();
    }

    function resolveMetricId(metrics, detailData) {
        if (!Array.isArray(metrics) || !detailData) return null;

        var byResolvedId = parseInt(detailData.resolved_metric_id, 10);
        if (!isNaN(byResolvedId) && metrics.some(function(m) { return parseInt(m.id, 10) === byResolvedId; })) {
            return byResolvedId;
        }

        var targetCode = normalizeMetricText(detailData.resolved_metric_code || detailData.metric_code || '');
        if (targetCode) {
            var foundByCode = metrics.find(function(m) {
                return normalizeMetricText(m.code || '') === targetCode;
            });
            if (foundByCode) return parseInt(foundByCode.id, 10);
        }

        var targetRaw = normalizeMetricText(detailData.result_type || '');
        var targetBase = normalizeMetricText(baseMetricText(detailData.result_type || ''));

        var found = metrics.find(function(m) {
            var metricName = normalizeMetricText(m.name || '');
            return metricName === targetRaw || metricName === targetBase;
        });
        if (found) return parseInt(found.id, 10);

        found = metrics.find(function(m) {
            var metricName = normalizeMetricText(m.name || '');
            return targetRaw.indexOf(metricName + ' (') === 0 || targetBase.indexOf(metricName + ' (') === 0;
        });
        return found ? parseInt(found.id, 10) : null;
    }

    function normalizeMixFeeRules() {
        return Array.isArray(window.mixFeeRules) ? window.mixFeeRules : [];
    }

    function normalizeMixFeeCharges() {
        return Array.isArray(window.mixFeeCharges) ? window.mixFeeCharges : [];
    }

    function calculateAgencyFeeDetails(investmentTotal) {
        var total = parseFloat(investmentTotal) || 0;
        var rules = normalizeMixFeeRules();
        var charges = normalizeMixFeeCharges();
        var targetUsdPerUnit = parseFloat(window.mmreCurrencyUsdPerUnitSnapshot) || 1;
        var matchingRules = [];

        rules.forEach(function(rule) {
            var min = parseFloat(rule.min_investment) || 0;
            var max = (rule.max_investment === null || rule.max_investment === '' || typeof rule.max_investment === 'undefined')
                ? null
                : parseFloat(rule.max_investment);
            var maxOk = max === null || total <= max;
            if (total >= min && maxOk) {
                matchingRules.push(rule);
            }
        });

        var baseFee = 0;
        var fixedComponentConverted = 0;
        var fixedComponentOriginal = null;
        var ruleComponents = [];
        var label = 'Sin regla';

        if (matchingRules.length === 0 && rules.length > 0) {
            matchingRules.push(rules[rules.length - 1]);
        }

        if (matchingRules.length) {
            label = matchingRules.length > 1 ? 'Reglas combinadas' : 'Regla aplicada';
            matchingRules.forEach(function(rule, idx) {
                var mode = rule.fee_mode || 'percentage';
                var percent = parseFloat(rule.percentage_value) || 0;
                var fixed = parseFloat(rule.fixed_value) || 0;
                var converted = 0;

                if (mode === 'fixed') {
                    var fixedRateSnapshot = parseFloat(rule.fixed_usd_per_unit_snapshot || rule.fixed_rate_snapshot || 0) || 0;
                    if (fixedRateSnapshot <= 0) {
                        fixedRateSnapshot = targetUsdPerUnit;
                    }
                    converted = (fixed * fixedRateSnapshot) / targetUsdPerUnit;
                    fixedComponentConverted += converted;
                    if (!fixedComponentOriginal) {
                        fixedComponentOriginal = {
                            amount: fixed,
                            currency_code: rule.fixed_currency_code || window.currency || 'USD'
                        };
                    }
                } else {
                    converted = total * (percent / 100);
                }

                baseFee += converted;
                ruleComponents.push({
                    index: idx + 1,
                    fee_mode: mode,
                    fee_label: (rule.fee_label || '').trim(),
                    percentage_value: percent,
                    fixed_value: fixed,
                    fixed_currency_code: rule.fixed_currency_code || window.currency || 'USD',
                    converted_amount: converted
                });
            });
        } else {
            var legacyFee = parseFloat(window.mmreFee) || 0;
            var legacyType = window.mmreFeeType || 'percentage';
            if (legacyType === 'fixed') {
                baseFee = legacyFee;
                label = 'Fee histórico fijo';
                ruleComponents.push({ index: 1, fee_mode: 'fixed', fee_label: '', converted_amount: legacyFee, percentage_value: 0, fixed_value: legacyFee, fixed_currency_code: window.currency || 'USD' });
            } else {
                baseFee = total * (legacyFee / 100);
                label = 'Fee histórico porcentual';
                ruleComponents.push({ index: 1, fee_mode: 'percentage', fee_label: '', converted_amount: baseFee, percentage_value: legacyFee, fixed_value: 0, fixed_currency_code: window.currency || 'USD' });
            }
        }

        var chargesTotal = 0;
        var convertedCharges = [];
        charges.forEach(function(charge) {
            var amount = parseFloat(charge.amount) || 0;
            var usdSnapshot = parseFloat(charge.usd_per_unit_snapshot || charge.usd_rate_snapshot || 0) || 0;
            if (usdSnapshot <= 0) {
                usdSnapshot = targetUsdPerUnit;
            }
            var converted = (amount * usdSnapshot) / targetUsdPerUnit;
            chargesTotal += converted;
            convertedCharges.push($.extend({}, charge, { converted_amount: converted }));
        });

        return {
            baseFee: baseFee,
            fixedComponentConverted: fixedComponentConverted,
            fixedComponentOriginal: fixedComponentOriginal,
            chargesTotal: chargesTotal,
            totalFee: baseFee + chargesTotal,
            label: label,
            selectedRule: matchingRules.length ? matchingRules[0] : null,
            appliedRules: matchingRules,
            ruleComponents: ruleComponents,
            charges: convertedCharges
        };
    }

    // Inicializar DataTable para la tabla de detalles
    // $('#detailsTable').DataTable({
    //     language: {
    //         url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
    //     },
    //     order: [[0, 'desc']]
    // });

    $('#addDetailModal').on('show.bs.modal', function () {
        var clientId = $(this).data('client-id');
        var $projectSelect = $('#newDetailProject');
        var $metricSelect = $('#newDetailMetric');
        var $platformSelect = $('#newDetailPlatform');
        var $channelSelect = $('#newDetailChannel');
        var $formatSelect = $('#newDetailFormat');
        // Proyectos (persistencia)
        if (cachedProjects && Array.isArray(cachedProjects) && cachedProjects.length > 0) {
            var options = '<option value="">-- Selecciona un proyecto --</option>';
            cachedProjects.forEach(function(project) {
                var selected = (lastSelectedProject == project.id) ? ' selected' : '';
                options += '<option value="' + project.id + '"' + selected + '>' + project.name + '</option>';
            });
            $projectSelect.html(options).prop('disabled', false);
            if (lastSelectedProject) $projectSelect.val(lastSelectedProject).trigger('change');
        } else {
            $projectSelect.html('<option value="">Cargando proyectos...</option>').prop('disabled', true);
            $.ajax({
                url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                method: 'POST',
                data: { client_id: clientId },
                dataType: 'json',
                success: function(projects) {
                    cachedProjects = projects;
                    var options = '<option value="">-- Selecciona un proyecto --</option>';
                    if (Array.isArray(projects) && projects.length > 0) {
                        projects.forEach(function(project) {
                            var selected = (lastSelectedProject == project.id) ? ' selected' : '';
                            options += '<option value="' + project.id + '"' + selected + '>' + project.name + '</option>';
                        });
                        $projectSelect.html(options).prop('disabled', false);
                        if (lastSelectedProject) $projectSelect.val(lastSelectedProject).trigger('change');
                    } else {
                        $projectSelect.html('<option value="">No hay proyectos para este cliente</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    $projectSelect.html('<option value="">Error al cargar proyectos</option>').prop('disabled', true);
                }
            });
        }
        // Objetivo medible (Métricas filtradas por plataforma) - inicia deshabilitado
        $metricSelect.html('<option value="">Selecciona una plataforma primero</option>').prop('disabled', true);
        if (lastSelectedPlatform && cachedMetricsByPlatform[lastSelectedPlatform]) {
            var mOpts = '<option value="">-- Selecciona una métrica --</option>';
            cachedMetricsByPlatform[lastSelectedPlatform].forEach(function(m) {
                var sel = (lastSelectedMetric == m.id) ? ' selected' : '';
                mOpts += '<option value="' + m.id + '"' + sel + '>' + m.name + (m.code ? ' (' + m.code + ')' : '') + '</option>';
            });
            $metricSelect.html(mOpts).prop('disabled', false);
            reinitMetricSelect($metricSelect, $('#addDetailModal'));
            if (lastSelectedMetric) $metricSelect.val(lastSelectedMetric).trigger('change');
        }
        // Plataformas (persistencia)
        if (cachedPlatforms && Array.isArray(cachedPlatforms) && cachedPlatforms.length > 0) {
            var options = '<option value="">-- Selecciona una plataforma --</option>';
            cachedPlatforms.forEach(function(plat) {
                var selected = (lastSelectedPlatform == plat.id) ? ' selected' : '';
                options += '<option value="' + plat.id + '"' + selected + '>' + plat.name + '</option>';
            });
            $platformSelect.html(options).prop('disabled', false);
            if (lastSelectedPlatform) $platformSelect.val(lastSelectedPlatform).trigger('change');
        } else {
            $platformSelect.html('<option value="">Cargando plataformas...</option>').prop('disabled', true);
            $.ajax({
                url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                method: 'POST',
                data: { get_platforms: 1 },
                dataType: 'json',
                success: function(platforms) {
                    cachedPlatforms = platforms;
                    var options = '<option value="">-- Selecciona una plataforma --</option>';
                    if (Array.isArray(platforms) && platforms.length > 0) {
                        platforms.forEach(function(plat) {
                            var selected = (lastSelectedPlatform == plat.id) ? ' selected' : '';
                            options += '<option value="' + plat.id + '"' + selected + '>' + plat.name + '</option>';
                        });
                        $platformSelect.html(options).prop('disabled', false);
                        if (lastSelectedPlatform) $platformSelect.val(lastSelectedPlatform).trigger('change');
                    } else {
                        $platformSelect.html('<option value="">No hay plataformas</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    $platformSelect.html('<option value="">Error al cargar plataformas</option>').prop('disabled', true);
                }
            });
        }
        // Canales (persistencia)
        if (cachedChannels && Array.isArray(cachedChannels) && cachedChannels.length > 0) {
            var options = '<option value="">-- Selecciona un canal --</option>';
            cachedChannels.forEach(function(chan) {
                var selected = (lastSelectedChannel == chan.id) ? ' selected' : '';
                options += '<option value="' + chan.id + '"' + selected + '>' + chan.name + '</option>';
            });
            $channelSelect.html(options).prop('disabled', false);
            if (lastSelectedChannel) $channelSelect.val(lastSelectedChannel).trigger('change');
        } else {
            $channelSelect.html('<option value="">Cargando canales...</option>').prop('disabled', true);
            $.ajax({
                url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                method: 'POST',
                data: { get_channels: 1 },
                dataType: 'json',
                success: function(channels) {
                    cachedChannels = channels;
                    var options = '<option value="">-- Selecciona un canal --</option>';
                    if (Array.isArray(channels) && channels.length > 0) {
                        channels.forEach(function(chan) {
                            var selected = (lastSelectedChannel == chan.id) ? ' selected' : '';
                            options += '<option value="' + chan.id + '"' + selected + '>' + chan.name + '</option>';
                        });
                        $channelSelect.html(options).prop('disabled', false);
                        if (lastSelectedChannel) $channelSelect.val(lastSelectedChannel).trigger('change');
                    } else {
                        $channelSelect.html('<option value="">No hay canales</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    $channelSelect.html('<option value="">Error al cargar canales</option>').prop('disabled', true);
                }
            });
        }
        // Formatos (persistencia y dependiente de plataforma)
        $formatSelect.prop('disabled', true);
        $formatSelect.html('<option value="">Selecciona una plataforma primero</option>');
        if (lastPlatformForFormats && cachedFormatsByPlatform[lastPlatformForFormats]) {
            var options = '';
            cachedFormatsByPlatform[lastPlatformForFormats].forEach(function(fmt) {
                var selected = (lastSelectedFormats && lastSelectedFormats.includes(String(fmt.id))) ? ' selected' : '';
                options += '<option value="' + fmt.id + '"' + selected + '>' + fmt.name + (fmt.code ? ' ('+fmt.code+')' : '') + '</option>';
            });
            $formatSelect.html(options).prop('disabled', false);
            if (lastSelectedFormats && lastSelectedFormats.length > 0) $formatSelect.val(lastSelectedFormats).trigger('change');
        }
        // Segmentaciones
        renderSegmentaciones('#newDetailSegmentation', []);
    });
    // Cuando cambia la plataforma, carga formatos, métricas y canales correspondientes
    $('#newDetailPlatform').on('change', function () {
        var platformId = $(this).val();
        var $formatSelect = $('#newDetailFormat');
        var $metricSelect = $('#newDetailMetric');
        var $channelSelectP = $('#newDetailChannel');
        lastPlatformForFormats = platformId;
        lastSelectedPlatform = platformId;
        if (!platformId) {
            $formatSelect.html('<option value="">Selecciona una plataforma primero</option>').prop('disabled', true);
            $metricSelect.html('<option value="">Selecciona una plataforma primero</option>').prop('disabled', true);
            $channelSelectP.html('<option value="">Selecciona una plataforma primero</option>').prop('disabled', true);
            return;
        }
        // Cargar formatos
        if (cachedFormatsByPlatform[platformId]) {
            var options = '';
            cachedFormatsByPlatform[platformId].forEach(function(fmt) {
                var selected = (lastSelectedFormats && lastSelectedFormats.includes(String(fmt.id))) ? ' selected' : '';
                options += '<option value="' + fmt.id + '"' + selected + '>' + fmt.name + (fmt.code ? ' ('+fmt.code+')' : '') + '</option>';
            });
            $formatSelect.html(options).prop('disabled', false);
            if (lastSelectedFormats && lastSelectedFormats.length > 0) $formatSelect.val(lastSelectedFormats).trigger('change');
        } else {
            $formatSelect.html('<option value="">Cargando formatos...</option>').prop('disabled', true);
            $.ajax({
                url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                method: 'POST',
                data: { platform_id: platformId },
                dataType: 'json',
                success: function(formats) {
                    cachedFormatsByPlatform[platformId] = formats;
                    var options = '';
                    if (Array.isArray(formats) && formats.length > 0) {
                        formats.forEach(function(fmt) {
                            var selected = (lastSelectedFormats && lastSelectedFormats.includes(String(fmt.id))) ? ' selected' : '';
                            options += '<option value="' + fmt.id + '"' + selected + '>' + fmt.name + (fmt.code ? ' ('+fmt.code+')' : '') + '</option>';
                        });
                        $formatSelect.html(options).prop('disabled', false);
                        if (lastSelectedFormats && lastSelectedFormats.length > 0) $formatSelect.val(lastSelectedFormats).trigger('change');
                    } else {
                        $formatSelect.html('<option value="">No hay formatos para esta plataforma</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    $formatSelect.html('<option value="">Error al cargar formatos</option>').prop('disabled', true);
                }
            });
        }
        // Cargar métricas por plataforma
        $metricSelect.html('<option value="">Cargando métricas...</option>').prop('disabled', true);
        if (cachedMetricsByPlatform[platformId]) {
            var mOpts = '<option value="">-- Selecciona una métrica --</option>';
            cachedMetricsByPlatform[platformId].forEach(function(m) {
                mOpts += '<option value="' + m.id + '" data-requires-event="' + (m.requires_event || 0) + '">' + m.name + (m.code ? ' (' + m.code + ')' : '') + '</option>';
            });
            $metricSelect.html(mOpts).prop('disabled', false);
            reinitMetricSelect($metricSelect, $('#addDetailModal'));
        } else {
            $.ajax({
                url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                method: 'POST',
                data: { get_metrics_by_platform: platformId },
                dataType: 'json',
                success: function(metrics) {
                    cachedMetricsByPlatform[platformId] = metrics;
                    if (Array.isArray(metrics) && metrics.length > 0) {
                        var mOpts = '<option value="">-- Selecciona una métrica --</option>';
                        metrics.forEach(function(m) {
                            mOpts += '<option value="' + m.id + '" data-requires-event="' + (m.requires_event || 0) + '">' + m.name + (m.code ? ' (' + m.code + ')' : '') + '</option>';
                        });
                        $metricSelect.html(mOpts).prop('disabled', false);
                        reinitMetricSelect($metricSelect, $('#addDetailModal'));
                    } else {
                        $metricSelect.html('<option value="">No hay métricas para esta plataforma</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    $metricSelect.html('<option value="">Error al cargar métricas</option>').prop('disabled', true);
                }
            });
        }
        // Cargar canales por plataforma
        $channelSelectP.html('<option value="">Cargando canales...</option>').prop('disabled', true);
        $.ajax({
            url: 'ajax/mediaMixRealEstateDetails.ajax.php',
            method: 'POST',
            data: { get_channels_by_platform: platformId },
            dataType: 'json',
            success: function(channels) {
                if (Array.isArray(channels) && channels.length > 0) {
                    var cOpts = '<option value="">-- Selecciona un canal --</option>';
                    channels.forEach(function(chan) {
                        var selected = (lastSelectedChannel == chan.id) ? ' selected' : '';
                        cOpts += '<option value="' + chan.id + '"' + selected + '>' + chan.name + '</option>';
                    });
                    $channelSelectP.html(cOpts).prop('disabled', false);
                    if (lastSelectedChannel) $channelSelectP.val(lastSelectedChannel).trigger('change');
                } else {
                    $channelSelectP.html('<option value="">No hay canales para esta plataforma</option>').prop('disabled', true);
                }
            },
            error: function() {
                $channelSelectP.html('<option value="">Error al cargar canales</option>').prop('disabled', true);
            }
        });
    });
    // Cuando cambia la métrica en add modal, muestra campo de evento si requiere
    $('#newDetailMetric').on('change', function() {
        var selectedOpt = $(this).find('option:selected');
        var requiresEvent = parseInt(selectedOpt.data('requires-event')) || 0;
        if (requiresEvent) {
            $('#newEventNameGroup').show();
        } else {
            $('#newEventNameGroup').hide();
            $('#newDetailEventName').val('');
        }
    });
    // Guarda la selección previa al cerrar el modal
    $('#addDetailModal').on('hidden.bs.modal', function () {
        lastSelectedProject = $('#newDetailProject').val();
        lastSelectedMetric = $('#newDetailMetric').val();
        lastSelectedPlatform = $('#newDetailPlatform').val();
        lastSelectedChannel = $('#newDetailChannel').val();
        lastSelectedFormats = $('#newDetailFormat').val() || [];
        lastPlatformForFormats = $('#newDetailPlatform').val();
    });
    // Guardar detalle
    $('#addDetailModal form').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        // Obtención robusta del ID del mix de medios desde variable global
        var mediamixrealestate_id = typeof window.mmreId !== 'undefined' ? parseInt(window.mmreId) : null;
        var project_id = parseInt($('#newDetailProject').val());
        var channel_id = parseInt($('#newDetailChannel').val());
        var segmentationArr = $('#newDetailSegmentation').val() || [];
        var segmentation = segmentationArr.join(', ');
        var selectedMetricId = $('#newDetailMetric').val();
        var selectedMetricText = $('#newDetailMetric option:selected').text();
        var result_type = selectedMetricText && selectedMetricId ? selectedMetricText.split(' (')[0] : '';
        var event_name = $('#newDetailEventName').val().trim();
        var projection = parseInt($('#newDetailProjection').val());
        var formats_ids = $('#newDetailFormat').val() ? $('#newDetailFormat').val().map(function(x){return parseInt(x);}) : [];
        var isCLP = (window.currency === 'CLP');
        var investment = isCLP ? parseInt($('#newDetailInvestment').val()) : parseFloat($('#newDetailInvestment').val());
        var aon = $('#newDetailAon').is(':checked') ? 1 : 0;
        var comments = $('#newDetailComments').val();
        var state = $('#newDetailStatus').val();
        var campaign_name = $('#newDetailCampaignName').val().trim().substring(0, 100);

        // Validación robusta con mensaje de campos faltantes
        var missingFields = [];
        if (isNaN(mediamixrealestate_id)) missingFields.push('Mix de Medios');
        if (isNaN(project_id)) missingFields.push('Proyecto');
        if (isNaN(channel_id)) missingFields.push('Canal');
        if (!segmentation) missingFields.push('Segmentación');
        if (!selectedMetricId) missingFields.push('Objetivo medible (Métrica)');
        var selectedMetricRequiresEvent = parseInt($('#newDetailMetric option:selected').data('requires-event')) || 0;
        if (selectedMetricRequiresEvent && !event_name) missingFields.push('Nombre del evento o conversión');
        if (isNaN(projection)) missingFields.push('Proyección');
        if (!Array.isArray(formats_ids) || formats_ids.length === 0 || formats_ids.some(isNaN)) missingFields.push('Formato(s)');
        if (isNaN(investment)) missingFields.push('Inversión');
        if (!state) missingFields.push('Estado');
        if (missingFields.length > 0) {
            swal({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor, completa los siguientes campos obligatorios:\n' + missingFields.join(', ')
            });
            return;
        }

        var body = {
            mediamixrealestate_id: mediamixrealestate_id,
            project_id: project_id,
            channel_id: channel_id,
            segmentation: segmentation,
            metric_id: parseInt(selectedMetricId),
            result_type: event_name ? result_type + ' (' + event_name + ')' : result_type,
            projection: projection,
            formats_ids: formats_ids,
            investment: investment,
            aon: aon,
            comments: comments,
            state: state,
            campaign_name: campaign_name
        };

        $.ajax({
            url: 'ajax/mediaMixRealEstateDetails.ajax.php',
            method: 'POST',
            data: { local_create_detail: JSON.stringify(body) },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    swal({ icon: 'success', title: 'Detalle guardado', text: 'El detalle se guardó correctamente.' })
                        .then(function() { location.reload(); });
                } else {
                    swal({ icon: 'error', title: 'Error al guardar', text: response.message || 'No se pudo guardar el detalle.' });
                }
            },
            error: function() {
                swal({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
            }
        });
    });
    // Evento para abrir el modal de edición y prellenar los campos con AJAX
    $(document).on('click', '.btn-editDetail', function (e) {
        e.preventDefault();
        var detailId = $(this).data('detail-id');
        $.ajax({
            url: 'ajax/mediaMixRealEstateDetails.ajax.php',
            method: 'POST',
            data: { get_detail_id: detailId },
            dataType: 'json',
            success: function(data) {
                var effectivePlatformId = data.platform_id;
                if (data.channel_platform_id && String(data.channel_platform_id) !== String(data.platform_id || '')) {
                    effectivePlatformId = data.channel_platform_id;
                }

                var ajaxCount = 0;
                var totalAjax = 5;
                function showModalIfReady() {
                    ajaxCount++;
                    if (ajaxCount === totalAjax) {
                        $('#editDetailModal').modal('show');
                    }
                }
                // Proyectos
                $.ajax({
                    url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                    method: 'POST',
                    data: { client_id: data.client_id },
                    dataType: 'json',
                    success: function(projects) {
                        var options = '<option value="">-- Selecciona un proyecto --</option>';
                        projects.forEach(function(project) {
                            var selected = (data.project_id == project.id) ? ' selected' : '';
                            options += '<option value="' + project.id + '"' + selected + '>' + project.name + '</option>';
                        });
                        $('#editDetailProject').html(options).prop('disabled', false);
                        showModalIfReady();
                    }
                });
                // Plataformas
                $.ajax({
                    url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                    method: 'POST',
                    data: { get_platforms: 1 },
                    dataType: 'json',
                    success: function(platforms) {
                        var options = '<option value="">-- Selecciona una plataforma --</option>';
                        platforms.forEach(function(plat) {
                            var selected = (String(effectivePlatformId) === String(plat.id)) ? ' selected' : '';
                            options += '<option value="' + plat.id + '"' + selected + '>' + plat.name + '</option>';
                        });
                        $('#editDetailPlatform').html(options).prop('disabled', false);
                        if (effectivePlatformId) {
                            $('#editDetailPlatform').val(String(effectivePlatformId));
                        }
                        showModalIfReady();
                    }
                });
                // Canales (dependiente de plataforma)
                $.ajax({
                    url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                    method: 'POST',
                    data: { get_channels_by_platform: effectivePlatformId, selected_channel_id: data.channel_id },
                    dataType: 'json',
                    success: function(channels) {
                        var options = '<option value="">-- Selecciona un canal --</option>';
                        channels.forEach(function(chan) {
                            var selected = (data.channel_id == chan.id) ? ' selected' : '';
                            options += '<option value="' + chan.id + '"' + selected + '>' + chan.name + '</option>';
                        });
                        $('#editDetailChannel').html(options).prop('disabled', false).attr('data-selected-id', data.channel_id || '');
                        showModalIfReady();
                    }
                });
                // Métricas por plataforma (reemplaza objetivos)
                $.ajax({
                    url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                    method: 'POST',
                    data: { get_metrics_by_platform: effectivePlatformId },
                    dataType: 'json',
                    success: function(metrics) {
                        cachedMetricsByPlatform[effectivePlatformId] = metrics;
                        var matchedMetricId = resolveMetricId(metrics, data);
                        var mOpts = '<option value="">-- Selecciona una métrica --</option>';
                        metrics.forEach(function(m) {
                            mOpts += '<option value="' + m.id + '" data-requires-event="' + (m.requires_event || 0) + '">' + m.name + (m.code ? ' (' + m.code + ')' : '') + '</option>';
                        });
                        $('#editDetailMetric').html(mOpts).prop('disabled', false);
                        reinitMetricSelect($('#editDetailMetric'), $('#editDetailModal'));
                        if (matchedMetricId) {
                            $('#editDetailMetric').val(matchedMetricId).trigger('change');
                        }
                        // Show event name field and pre-fill it if the stored result_type has an event in parentheses
                        var preSelected = $('#editDetailMetric option:selected');
                        if (parseInt(preSelected.data('requires-event'))) {
                            $('#editEventNameGroup').show();
                            var eventMatch = data.result_type ? data.result_type.match(/\(([^)]+)\)\s*$/) : null;
                            if (eventMatch) {
                                $('#editDetailEventName').val(eventMatch[1]);
                            }
                        }
                        showModalIfReady();
                    },
                    error: function() {
                        $('#editDetailMetric').html('<option value="">Error al cargar métricas</option>').prop('disabled', true);
                        showModalIfReady();
                    }
                });
                // Formatos (dependiente de plataforma)
                $.ajax({
                    url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                    method: 'POST',
                    data: { platform_id: effectivePlatformId },
                    dataType: 'json',
                    success: function(formats) {
                        cachedFormatsByPlatform[effectivePlatformId] = formats;
                        var options = '';
                        formats.forEach(function(fmt) {
                            var selected = (data.formats_ids && data.formats_ids.map(String).includes(String(fmt.id))) ? ' selected' : '';
                            options += '<option value="' + fmt.id + '"' + selected + '>' + fmt.name + (fmt.code ? ' ('+fmt.code+')' : '') + '</option>';
                        });
                        $('#editDetailFormat').html(options).prop('disabled', false);
                        // Preselecciona los formatos existentes
                        if (data.formats_ids && data.formats_ids.length > 0) {
                            $('#editDetailFormat').val(data.formats_ids.map(String)).trigger('change');
                        }
                        showModalIfReady();
                    }
                });
                // Segmentación (procesar como array)
                var segs = (data.segmentation && typeof data.segmentation === 'string') ? data.segmentation.split(',').map(function(s){return s.trim();}) : [];
                renderSegmentaciones('#editDetailSegmentation', segs);
                // Asegúrate de que select2 se actualice con los valores correctos
                setTimeout(function() {
                    $('#editDetailSegmentation').select2('destroy').select2();
                    if (segs.length > 0) {
                        $('#editDetailSegmentation').val(segs).trigger('change');
                    }
                }, 100);
                
                $('#editDetailProjection').val(data.projection);
                $('#editDetailInvestment').val(data.investment);
                $('#editDetailAon').prop('checked', data.aon == 1);
                $('#editDetailComments').val(data.comments);
                $('#editDetailStatus').val(data.state);
                $('#editDetailCampaignName').val(data.campaign_name || '');
                $('#editDetailId').val(data.id);
            }
        });
    });
    // Cuando cambia la métrica en edit modal, muestra campo de evento si requiere
    $('#editDetailMetric').on('change', function() {
        var selectedOpt = $(this).find('option:selected');
        var requiresEvent = parseInt(selectedOpt.data('requires-event')) || 0;
        if (requiresEvent) {
            $('#editEventNameGroup').show();
        } else {
            $('#editEventNameGroup').hide();
            $('#editDetailEventName').val('');
        }
    });
    // Cuando cambia la plataforma en el modal editar, recarga las métricas y canales
    $('#editDetailPlatform').on('change', function () {
        var platformId = $(this).val();
        var $metricSelect = $('#editDetailMetric');
        var $formatSelect = $('#editDetailFormat');
        var selectedChannelId = $('#editDetailChannel').attr('data-selected-id') || $('#editDetailChannel').val() || '';
        if (!platformId) {
            $metricSelect.html('<option value="">Selecciona una plataforma primero</option>').prop('disabled', true);
            $('#editDetailChannel').html('<option value="">Selecciona una plataforma primero</option>').prop('disabled', true);
            $formatSelect.html('<option value="">Selecciona una plataforma primero</option>').prop('disabled', true);
            return;
        }

        // Al cambiar de plataforma, se limpia la métrica para evitar mantener una que no pertenezca.
        $metricSelect.val('');

        // Recargar formatos por plataforma para evitar conservar formatos de otra plataforma.
        $formatSelect.html('<option value="">Cargando formatos...</option>').prop('disabled', true);
        if (cachedFormatsByPlatform[platformId]) {
            var fOptsCached = '';
            cachedFormatsByPlatform[platformId].forEach(function(fmt) {
                fOptsCached += '<option value="' + fmt.id + '">' + fmt.name + (fmt.code ? ' (' + fmt.code + ')' : '') + '</option>';
            });
            $formatSelect.html(fOptsCached).prop('disabled', false).val([]).trigger('change');
        } else {
            $.ajax({
                url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                method: 'POST',
                data: { platform_id: platformId },
                dataType: 'json',
                success: function(formats) {
                    cachedFormatsByPlatform[platformId] = formats;
                    if (Array.isArray(formats) && formats.length > 0) {
                        var fOpts = '';
                        formats.forEach(function(fmt) {
                            fOpts += '<option value="' + fmt.id + '">' + fmt.name + (fmt.code ? ' (' + fmt.code + ')' : '') + '</option>';
                        });
                        $formatSelect.html(fOpts).prop('disabled', false).val([]).trigger('change');
                    } else {
                        $formatSelect.html('<option value="">No hay formatos para esta plataforma</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    $formatSelect.html('<option value="">Error al cargar formatos</option>').prop('disabled', true);
                }
            });
        }

        $metricSelect.html('<option value="">Cargando métricas...</option>').prop('disabled', true);
        if (cachedMetricsByPlatform[platformId]) {
            var mOpts = '<option value="">-- Selecciona una métrica --</option>';
            cachedMetricsByPlatform[platformId].forEach(function(m) {
                mOpts += '<option value="' + m.id + '" data-requires-event="' + (m.requires_event || 0) + '">' + m.name + (m.code ? ' (' + m.code + ')' : '') + '</option>';
            });
            $metricSelect.html(mOpts).prop('disabled', false);
            reinitMetricSelect($metricSelect, $('#editDetailModal'));
        } else {
            $.ajax({
                url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                method: 'POST',
                data: { get_metrics_by_platform: platformId },
                dataType: 'json',
                success: function(metrics) {
                    cachedMetricsByPlatform[platformId] = metrics;
                    if (Array.isArray(metrics) && metrics.length > 0) {
                        var mOpts = '<option value="">-- Selecciona una métrica --</option>';
                        metrics.forEach(function(m) {
                            mOpts += '<option value="' + m.id + '" data-requires-event="' + (m.requires_event || 0) + '">' + m.name + (m.code ? ' (' + m.code + ')' : '') + '</option>';
                        });
                        $metricSelect.html(mOpts).prop('disabled', false);
                        reinitMetricSelect($metricSelect, $('#editDetailModal'));
                    } else {
                        $metricSelect.html('<option value="">No hay métricas para esta plataforma</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    $metricSelect.html('<option value="">Error al cargar métricas</option>').prop('disabled', true);
                }
            });
        }
        // Recargar canales por plataforma
        $('#editDetailChannel').html('<option value="">Cargando canales...</option>').prop('disabled', true);
        $.ajax({
            url: 'ajax/mediaMixRealEstateDetails.ajax.php',
            method: 'POST',
            data: { get_channels_by_platform: platformId, selected_channel_id: selectedChannelId },
            dataType: 'json',
            success: function(channels) {
                if (Array.isArray(channels) && channels.length > 0) {
                    var cOpts = '<option value="">-- Selecciona un canal --</option>';
                    var foundSelected = false;
                    channels.forEach(function(chan) {
                        var selected = String(selectedChannelId) !== '' && String(chan.id) === String(selectedChannelId);
                        if (selected) foundSelected = true;
                        cOpts += '<option value="' + chan.id + '"' + (selected ? ' selected' : '') + '>' + chan.name + '</option>';
                    });
                    $('#editDetailChannel').html(cOpts).prop('disabled', false);
                    if (foundSelected) {
                        $('#editDetailChannel').val(String(selectedChannelId)).trigger('change');
                    }
                } else {
                    $('#editDetailChannel').html('<option value="">No hay canales para esta plataforma</option>').prop('disabled', true);
                }
            },
            error: function() {
                $('#editDetailChannel').html('<option value="">Error al cargar canales</option>').prop('disabled', true);
            }
        });
    });
    // Guardar cambios en el detalle (modal editar)
    $('#editDetailForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        var detail_id = parseInt($('#editDetailId').val());
        var mediamixrealestate_id = typeof window.mmreId !== 'undefined' ? parseInt(window.mmreId) : null;
        var project_id = parseInt($('#editDetailProject').val());
        var channel_id = parseInt($('#editDetailChannel').val());
        var segmentationArr = $('#editDetailSegmentation').val() || [];
        var segmentation = segmentationArr.join(', ');
        var selectedEditMetricId = $('#editDetailMetric').val();
        var selectedEditMetricText = $('#editDetailMetric option:selected').text();
        var result_type = selectedEditMetricText && selectedEditMetricId ? selectedEditMetricText.split(' (')[0] : '';
        var event_name = $('#editDetailEventName').val().trim();
        var projection = parseInt($('#editDetailProjection').val());
        var formats_ids = $('#editDetailFormat').val() ? $('#editDetailFormat').val().map(function(x){return parseInt(x);}) : [];
        var isCLP = (window.currency === 'CLP');
        var investment = isCLP ? parseInt($('#editDetailInvestment').val()) : parseFloat($('#editDetailInvestment').val());
        var aon = $('#editDetailAon').is(':checked') ? 1 : 0;
        var comments = $('#editDetailComments').val();
        var state = $('#editDetailStatus').val();
        var campaign_name = $('#editDetailCampaignName').val().trim().substring(0, 100);

        // Validación robusta con mensaje de campos faltantes
        var missingFields = [];
        if (isNaN(detail_id)) missingFields.push('ID del detalle');
        if (isNaN(mediamixrealestate_id)) missingFields.push('Mix de Medios');
        if (isNaN(project_id)) missingFields.push('Proyecto');
        if (isNaN(channel_id)) missingFields.push('Canal');
        if (!segmentation) missingFields.push('Segmentación');
        if (!selectedEditMetricId) missingFields.push('Objetivo medible (Métrica)');
        var editMetricRequiresEvent = parseInt($('#editDetailMetric option:selected').data('requires-event')) || 0;
        if (editMetricRequiresEvent && !event_name) missingFields.push('Nombre del evento o conversión');
        if (isNaN(projection)) missingFields.push('Proyección');
        if (!Array.isArray(formats_ids) || formats_ids.length === 0 || formats_ids.some(isNaN)) missingFields.push('Formato(s)');
        if (isNaN(investment)) missingFields.push('Inversión');
        if (!state) missingFields.push('Estado');
        if (missingFields.length > 0) {
            swal({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor, completa los siguientes campos obligatorios:\n' + missingFields.join(', ')
            });
            return;
        }

        var body = {
            id: detail_id,
            mediamixrealestate_id: mediamixrealestate_id,
            project_id: project_id,
            channel_id: channel_id,
            segmentation: segmentation,
            metric_id: parseInt(selectedEditMetricId),
            result_type: event_name ? result_type + ' (' + event_name + ')' : result_type,
            projection: projection,
            formats_ids: formats_ids,
            investment: investment,
            aon: aon,
            comments: comments,
            state: state,
            campaign_name: campaign_name
        };

        $.ajax({
            url: 'ajax/mediaMixRealEstateDetails.ajax.php',
            method: 'POST',
            data: { local_update_detail: JSON.stringify(body), detail_id: detail_id },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    swal({ icon: 'success', title: 'Detalle actualizado', text: 'Los cambios se guardaron correctamente.' })
                        .then(function() { location.reload(); });
                } else {
                    swal({ icon: 'error', title: 'Error al guardar', text: response.message || 'No se pudo actualizar el detalle.' });
                }
            },
            error: function() {
                swal({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
            }
        });
    });
    $(document).on('click', '.btn-danger', function () {
        var detailId = $(this).closest('tr').find('.btn-editDetail').data('detail-id');
        if (!detailId) return;
        swal({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará el detalle permanentemente.',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                    method: 'POST',
                    data: { delete_detail_id: detailId },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success) {
                            swal({
                                title: 'Eliminado correctamente',
                                icon: 'success'
                            }).then(function () { location.reload(); });
                        } else {
                            swal('Error al eliminar', { icon: 'error' });
                        }
                    },
                    error: function () {
                        swal('Error de red', { icon: 'error' });
                    }
                });
            }
        });
    });
    // Nueva función para generar y copiar código
    function generateAndCopyCode(platformCode, clientCode, projectCode, metricCode) {
        var fullCode = (platformCode || '') + (clientCode || '') + (projectCode || '') + (metricCode || '');
        
        // Copiar al portapapeles
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(fullCode).then(function() {
                swal({
                    icon: 'success',
                    title: 'Código copiado',
                    text: 'El código "' + fullCode + '" se copió al portapapeles.',
                    timer: 2000
                });
            }).catch(function() {
                fallbackCopyTextToClipboard(fullCode);
            });
        } else {
            fallbackCopyTextToClipboard(fullCode);
        }
    }

    function sanitizeNomenclaturePart(text) {
        return String(text || '').replace(/\|/g, '/').replace(/\s+/g, ' ').trim();
    }

    function truncateTo80(text) {
        var clean = sanitizeNomenclaturePart(text);
        return clean.length > 80 ? clean.substring(0, 80) : clean;
    }

    var _monthMap = {
        'enero': '01', 'febrero': '02', 'marzo': '03', 'abril': '04',
        'mayo': '05', 'junio': '06', 'julio': '07', 'agosto': '08',
        'septiembre': '09', 'octubre': '10', 'noviembre': '11', 'diciembre': '12'
    };

    function periodToYYYYMM(periodName) {
        // Expects "Mayo 2026" → "2026-05"
        var parts = String(periodName || '').trim().split(/\s+/);
        if (parts.length < 2) { return sanitizeNomenclaturePart(periodName); }
        var month = _monthMap[parts[0].toLowerCase()];
        var year = parts[1];
        if (month && year) { return year + '-' + month; }
        return sanitizeNomenclaturePart(periodName);
    }

    function buildExtendedNomenclature(platformCode, clientCode, projectCode, metricCode, clientName, metricName, projectName, campaignName, periodName, investment, channelName) {
        var code = (platformCode || '') + (clientCode || '') + (projectCode || '') + (metricCode || '');
        var metricRaw = sanitizeNomenclaturePart(metricName);
        var metricPrincipal = metricRaw;
        var eventDetail = '';

        var match = metricRaw.match(/^(.*)\(([^)]+)\)\s*$/);
        if (match) {
            metricPrincipal = sanitizeNomenclaturePart(match[1]);
            eventDetail = sanitizeNomenclaturePart(match[2]);
        }

        var metricDisplay = sanitizeNomenclaturePart(metricPrincipal);
        if (eventDetail) {
            metricDisplay = metricDisplay + ': ' + truncateTo80(eventDetail);
        }

        // Nombre descriptivo: campaign_name si existe, sino project_name
        var descriptiveName = sanitizeNomenclaturePart(campaignName);
        if (!descriptiveName) {
            descriptiveName = sanitizeNomenclaturePart(projectName);
        }
        if (descriptiveName.length > 100) { descriptiveName = descriptiveName.substring(0, 100); }

        var fecha = periodToYYYYMM(periodName);

        var investmentClean = sanitizeNomenclaturePart(String(investment || ''));

        var parts = [
            sanitizeNomenclaturePart(code),
            sanitizeNomenclaturePart(clientName),
            descriptiveName,
            metricDisplay,
            fecha,
            investmentClean,
            sanitizeNomenclaturePart(channelName)
        ];

        return parts.filter(Boolean).join(' | ');
    }

    function copyTextWithFeedback(text, titleText) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function() {
                swal({
                    icon: 'success',
                    title: titleText || 'Texto copiado',
                    text: text,
                    timer: 2500
                });
            }).catch(function() {
                fallbackCopyTextToClipboard(text);
            });
        } else {
            fallbackCopyTextToClipboard(text);
        }
    }

    // Función de respaldo para copiar texto
    function fallbackCopyTextToClipboard(text) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                swal({
                    icon: 'success',
                    title: 'Código copiado',
                    text: 'El código "' + text + '" se copió al portapapeles.',
                    timer: 2000
                });
            } else {
                swal({
                    icon: 'error',
                    title: 'Error al copiar',
                    text: 'No se pudo copiar el código. Código: ' + text
                });
            }
        } catch (err) {
            swal({
                icon: 'error',
                title: 'Error al copiar',
                text: 'No se pudo copiar el código. Código: ' + text
            });
        }
        
        document.body.removeChild(textArea);
    }

    // Evento para copiar código
    $(document).on('click', '.btn-copyCode', function (e) {
        e.preventDefault();
        var platformCode = $(this).data('platform-code') || '';
        var clientCode = $(this).data('client-code') || '';
        var projectCode = $(this).data('project-code') || '';
        var metricCode = $(this).data('metric-code') || '';
        
        generateAndCopyCode(platformCode, clientCode, projectCode, metricCode);
    });

    $(document).on('click', '.btn-copyCodeExtended', function (e) {
        e.preventDefault();
        var platformCode = $(this).data('platform-code') || '';
        var clientCode = $(this).data('client-code') || '';
        var projectCode = $(this).data('project-code') || '';
        var metricCode = $(this).data('metric-code') || '';
        var clientName = $(this).data('client-name') || '';
        var metricName = $(this).data('metric-name') || '';
        var projectName = $(this).data('project-name') || '';
        var campaignName = $(this).data('campaign-name') || '';
        var investment = $(this).data('investment') || '';
        var channelName = $(this).data('channel-name') || '';
        var periodName = window.periodName || '';

        var nomenclature = buildExtendedNomenclature(
            platformCode,
            clientCode,
            projectCode,
            metricCode,
            clientName,
            metricName,
            projectName,
            campaignName,
            periodName,
            investment,
            channelName
        );

        copyTextWithFeedback(nomenclature, 'Nomenclatura copiada');
    });
    // Función para exportar tabla a Excel con estilos modernos - ÚNICA VERSIÓN
    function exportTableToExcel() {
        // Verificar si ExcelJS está disponible
        if (typeof ExcelJS === 'undefined') {
            swal({
                icon: 'error',
                title: 'Librería no disponible',
                text: 'La librería ExcelJS no está cargada correctamente.'
            });
            return;
        }

        try {
            // Obtener datos del mix de medios
            var mixName = $('h1').first().text().trim();
            var clientName = window.clientName || 'Cliente';
            var currency = window.currency || 'USD';
            var periodName = window.periodName || 'Período';
            
            // Crear workbook con ExcelJS
            var workbook = new ExcelJS.Workbook();
            var worksheet = workbook.addWorksheet('Detalles Mix', {
                properties: { defaultColWidth: 15 }
            });
            
            // Configurar propiedades básicas del documento
            workbook.creator = 'Algoritmo Digital Platform';
            workbook.lastModifiedBy = 'Sistema';
            workbook.created = new Date();
            workbook.modified = new Date();
            
            // Establecer anchos de columna PRIMERO
            // Columnas: A,B,C,D,E,F,G,H,I,J,K,L
            // Proyecto, Plataforma, Campaña, AON, Canal, Segmentación, Formatos, Inversión, Distribución, Estado, Proyección, CPR
            var columnWidths = [15, 15, 15, 18, 22, 18, 16, 16, 14, 10, 22, 12];
            columnWidths.forEach(function(width, index) {
                worksheet.getColumn(index + 1).width = width;
            });
            
            // SECCIÓN 1: TÍTULO PRINCIPAL
            worksheet.addRow(['INFORMACIÓN DEL MIX DE MEDIOS']);
            worksheet.mergeCells('A1:L1');
            var titleRow = worksheet.getRow(1);
            titleRow.getCell(1).font = { name: 'Arial', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
            titleRow.getCell(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF366092' } };
            titleRow.getCell(1).alignment = { vertical: 'middle', horizontal: 'center' };
            titleRow.height = 30;
            
            worksheet.addRow([]);
            
            // SECCIÓN 2: INFORMACIÓN DETALLADA OCUPANDO TODO EL ANCHO
            var infoRows = [
                ['Mix de Medios:', '', mixName, '', '', '', '', '', '', '', '', ''],
                ['Cliente:', '', clientName, '', '', '', '', '', '', '', '', ''], 
                ['Período:', '', periodName, '', '', '', '', '', '', '', '', ''],
                ['Moneda:', '', currency, '', '', '', '', '', '', '', '', ''],
                ['Fecha de Exportación:', '', new Date().toLocaleDateString('es-PE', { timeZone: 'America/Lima' }), '', '', '', '', '', '', '', '', '']
            ];
            
            infoRows.forEach(function(rowData, index) {
                var row = worksheet.addRow(rowData);
                var rowNumber = index + 3;
                
                // Merge de la etiqueta (columnas A-B)
                worksheet.mergeCells(rowNumber, 1, rowNumber, 2);
                var labelCell = row.getCell(1);
                labelCell.font = { name: 'Arial', size: 11, bold: true, color: { argb: 'FF1F4E79' } };
                labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE7F3FF' } };
                labelCell.alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };
                labelCell.border = {
                    top: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                    left: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                    bottom: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                    right: { style: 'thin', color: { argb: 'FFB4C7E7' } }
                };
                
                // Merge del valor (columnas C-L)
                worksheet.mergeCells(rowNumber, 3, rowNumber, 12);
                var valueCell = row.getCell(3);
                valueCell.font = { name: 'Arial', size: 11, color: { argb: 'FF2F5F8F' } };
                valueCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                valueCell.alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };
                valueCell.border = {
                    top: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                    left: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                    bottom: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                    right: { style: 'thin', color: { argb: 'FFB4C7E7' } }
                };
                
                // Altura fija con alineación vertical centrada
                row.height = 25;
            });
            
            worksheet.addRow([]);
            
            // Función para calcular altura automática
            function calculateAutoHeight(rowData, columnWidths) {
                if (!rowData || !Array.isArray(rowData) || !columnWidths || !Array.isArray(columnWidths)) {
                    return 22;
                }
                
                var maxLines = 1;
                var avgCharWidth = 7;
                
                for (var i = 0; i < rowData.length && i < columnWidths.length; i++) {
                    var cellValue = String(rowData[i] || '');
                    if (cellValue.length > 0) {
                        var columnWidthInPixels = columnWidths[i] * 7.5;
                        var estimatedTextWidth = cellValue.length * avgCharWidth;
                        var linesNeeded = Math.ceil(estimatedTextWidth / columnWidthInPixels);
                        
                        var naturalBreaks = (cellValue.match(/[,;\/\-\s]/g) || []).length;
                        if (naturalBreaks > 0 && cellValue.length > 50) {
                            linesNeeded = Math.max(linesNeeded, Math.ceil(cellValue.length / 40));
                        }
                        
                        maxLines = Math.max(maxLines, linesNeeded);
                    }
                }
                
                var baseHeight = 18;
                var lineHeight = 14;
                var calculatedHeight = baseHeight + ((maxLines - 1) * lineHeight);
                
                return Math.max(20, Math.min(calculatedHeight, 100));
            }
            
            // Headers de la tabla
            var headers = [
                'Proyecto', 'Plataforma', 'Campaña', 'AON',
                'Canal', 'Segmentación', 'Formatos', 'Inversión (' + currency + ')',
                'Distribución (%)', 'Estado', 'Proyección', 'CPR'
            ];
            var headerRow = worksheet.addRow(headers);
            
            // Estilo para headers
            headerRow.eachCell(function(cell, colNumber) {
                cell.font = { name: 'Arial', size: 9, bold: true, color: { argb: 'FFFFFFFF' } };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF366092' } };
                cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                cell.border = {
                    top: { style: 'thin', color: { argb: 'FF000000' } },
                    left: { style: 'thin', color: { argb: 'FF000000' } },
                    bottom: { style: 'thin', color: { argb: 'FF000000' } },
                    right: { style: 'thin', color: { argb: 'FF000000' } }
                };
            });
            headerRow.height = 35;
            
            // PROCESAR DATOS DE LA TABLA RESPETANDO LA ESTRUCTURA REAL E INCLUYENDO SUBTOTALES POR PROYECTO
            var excelRows = [];
            var mergeRanges = [];
            var rowIndex = 0;
            
            // Crear matriz para rastrear celdas ocupadas
            var occupiedCells = {};
            
            $('#detailsTable tbody tr').each(function() {
                var $row = $(this);
                
                // Crear fila para cada detalle
                var excelRowData = new Array(12).fill('');
                var colIndex = 0;
                
                $row.find('td').each(function() {
                    var $cell = $(this);
                    
                    // Saltar columna de acciones (la que tiene botones)
                    if ($cell.find('.btn').length > 0) {
                        return;
                    }
                    
                    // Encontrar la próxima posición disponible en la fila
                    while (occupiedCells[rowIndex + '_' + colIndex]) {
                        colIndex++;
                    }
                    
                    // Si ya llegamos al límite de columnas, salir
                    if (colIndex >= 12) {
                        return;
                    }
                    
                    var cellText = $cell.text().trim();
                    var colspan = parseInt($cell.attr('colspan')) || 1;
                    var rowspan = parseInt($cell.attr('rowspan')) || 1;
                    
                    // Colocar el valor en la celda actual
                    excelRowData[colIndex] = cellText;
                    
                    // Marcar todas las celdas ocupadas por este elemento
                    for (var r = 0; r < rowspan; r++) {
                        for (var c = 0; c < colspan; c++) {
                            if (colIndex + c < 12) {
                                occupiedCells[(rowIndex + r) + '_' + (colIndex + c)] = true;
                            }
                        }
                    }
                    
                    // Si hay merge, registrarlo (ajustar número de fila por las nuevas filas de info)
                    if (rowspan > 1 || colspan > 1) {
                        mergeRanges.push({
                            startRow: rowIndex + 10, // +10 por info general y headers
                            endRow: rowIndex + rowspan - 1 + 10,
                            startCol: colIndex + 1, // Excel es 1-based
                            endCol: colIndex + colspan,
                            value: cellText
                        });
                    }
                    
                    colIndex += colspan;
                });
                
                excelRows.push(excelRowData);
                rowIndex++;
            });
            
            // Agregar todas las filas procesadas a Excel (incluyendo subtotales)
            excelRows.forEach(function(rowData, index) {
                var excelRow = worksheet.addRow(rowData);
                
                // DETECTAR SUBTOTALES DE FORMA MÁS ESPECÍFICA Y CORRECTA
                var isSubtotalRow = false;
                
                // Verificar si es fila de subtotal:
                // - Las primeras 7 columnas vacías (debido a colspan="7")
                // - Columna 8 (index 7) con formato de moneda
                // - Columna 9 (index 8) con "100%"
                var emptyColumns = 0;
                for (var i = 0; i < 7; i++) {
                    if (!rowData[i] || rowData[i] === '' || rowData[i] === null) {
                        emptyColumns++;
                    }
                }
                
                if (emptyColumns === 7 && 
                    rowData[7] && 
                    rowData[7].match(/^[A-Z]{3}\s[\d,]+\.?\d*$/) && 
                    rowData[8] === '100%') {
                    
                    isSubtotalRow = true;
                    console.log('✅ SUBTOTAL detectado en fila ' + index + ': ' + rowData[7]);
                }
                
                if (isSubtotalRow) {
                    // Estilo especial para filas de subtotal
                    excelRow.eachCell(function(cell, colNumber) {
                        if (colNumber <= 12) {
                            if (colNumber === 8) { // Columna H - Inversión subtotal
                                cell.font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FF2C3E50' } };
                                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFEEF2F3' } };
                                cell.alignment = { vertical: 'middle', horizontal: 'right', wrapText: true };
                                cell.border = {
                                    top: { style: 'thin', color: { argb: 'FF85929E' } },
                                    left: { style: 'thin', color: { argb: 'FF85929E' } },
                                    bottom: { style: 'thin', color: { argb: 'FF85929E' } },
                                    right: { style: 'thin', color: { argb: 'FF85929E' } }
                                };
                                
                                // Formato numérico para subtotal
                                var subtotalValue = parseFloat(rowData[7].replace(/,/g, ''));
                                if (!isNaN(subtotalValue)) {
                                    cell.value = subtotalValue;
                                    cell.numFmt = '"' + currency + '" #,##0.00';
                                }
                            } else if (colNumber === 9) { // Columna I - Distribución 100%
                                cell.font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FF2C3E50' } };
                                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFEEF2F3' } };
                                cell.alignment = { vertical: 'middle', horizontal: 'right', wrapText: true };
                                cell.border = {
                                    top: { style: 'thin', color: { argb: 'FF85929E' } },
                                    left: { style: 'thin', color: { argb: 'FF85929E' } },
                                    bottom: { style: 'thin', color: { argb: 'FF85929E' } },
                                    right: { style: 'thin', color: { argb: 'FF85929E' } }
                                };
                                cell.value = '100%';
                            } else {
                                // Todas las demás columnas en blanco con fondo sutil
                                cell.value = '';
                                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFEEF2F3' } };
                                cell.border = {
                                    top: { style: 'thin', color: { argb: 'FF85929E' } },
                                    left: { style: 'thin', color: { argb: 'FF85929E' } },
                                    bottom: { style: 'thin', color: { argb: 'FF85929E' } },
                                    right: { style: 'thin', color: { argb: 'FF85929E' } }
                                };
                            }
                        }
                    });
                    
                    excelRow.height = 22;
                } else {
                    // Calcular altura automática para filas normales
                    var autoHeight = calculateAutoHeight(rowData, columnWidths);
                    excelRow.height = autoHeight;
                    
                    excelRow.eachCell(function(cell, colNumber) {
                        // Estilo básico para datos normales
                        cell.font = { name: 'Arial', size: 9 };
                        cell.alignment = { 
                            vertical: 'middle',
                            wrapText: true
                        };
                        
                        // Alineación especial para columnas numéricas
                        if (colNumber === 7 || colNumber === 8 || colNumber === 12) {
                            cell.alignment.horizontal = 'right';
                        } else {
                            cell.alignment.horizontal = 'center';
                        }

                        cell.border = {
                            top: { style: 'thin', color: { argb: 'FFCCCCCC' } },
                            left: { style: 'thin', color: { argb: 'FFCCCCCC' } },
                            bottom: { style: 'thin', color: { argb: 'FFCCCCCC' } },
                            right: { style: 'thin', color: { argb: 'FFCCCCCC' } }
                        };
                        
                        // Colores alternados
                        var normalRowIndex = index - Math.floor(index / 4);
                        if (normalRowIndex % 2 === 0) {
                            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8F9FA' } };
                        }

                        // CPR en columna 12 (L)
                        if (colNumber === 12) {
                            var investment = parseFloat(String(rowData[7]).replace(/[^0-9.-]/g, ''));
                            var projection = parseFloat(String(rowData[10]).replace(/[^0-9.-]/g, ''));
                            
                            if (!isNaN(investment) && !isNaN(projection) && projection > 0) {
                                var cpr = investment / projection;
                                cell.value = parseFloat(cpr.toFixed(4));
                                cell.numFmt = '#,##0.0###" CPR"';
                            } else {
                                cell.value = 'N/A';
                            }
                            cell.alignment = { vertical: 'middle', horizontal: 'right', wrapText: true };
                        }
                    });
                }
            });
            
            // Aplicar merges
            mergeRanges.forEach(function(merge) {
                try {
                    if (merge.startRow !== merge.endRow || merge.startCol !== merge.endCol) {
                        worksheet.mergeCells(merge.startRow, merge.startCol, merge.endRow, merge.endCol);
                        var cell = worksheet.getCell(merge.startRow, merge.startCol);
                        cell.value = merge.value;
                        cell.font = { name: 'Arial', size: 9, bold: true };
                        cell.alignment = { 
                            vertical: 'middle', 
                            horizontal: 'center', 
                            wrapText: true 
                        };
                        
                        // Ajustar altura de celdas combinadas
                        if (merge.value) {
                            var mergedCellHeight = calculateAutoHeight([merge.value], [columnWidths[merge.startCol - 1]]);
                            for (var r = merge.startRow; r <= merge.endRow; r++) {
                                var targetRow = worksheet.getRow(r);
                                if (targetRow.height < mergedCellHeight) {
                                    targetRow.height = mergedCellHeight;
                                }
                            }
                        }
                    }
                } catch (e) {
                    console.log('Error al combinar celdas:', merge, e);
                }
            });
            
            // INTEGRAR SOLO TOTALES GENERALES (SIN SUBTOTALES POR PROYECTO)
            // Agregar fila vacía para separar
            worksheet.addRow([]);
            
            // Obtener totales del HTML
            var inversionNeta = '0';
            var nacionalizacionLinkedin = '0';
            var hasNacionalizacion = false;
            var comisionValue = '0';
            var pautaComision = '0';
            var igvValue = '0';
            var totalFinal = '0';
            
            // Leer directamente de la celda HTML que muestra "Inversión Neta Total"
            var inversionText = $('#inversionNetaTotal').text() || '';
            // Extraer número (el texto contiene moneda + número, ej. "USD 1,234.00")
            var inversionNumber = parseFloat(String(inversionText).replace(/[^0-9\.\-\,]/g, '').replace(/,/g, '')) || 0;
            
            // Verificar si existe nacionalización y obtener su valor
            if ($('#nacionalizacionLinkedin').length > 0) {
                var nacionalizacionText = $('#nacionalizacionLinkedin').text() || '';
                var nacionalizacionNumber = parseFloat(String(nacionalizacionText).replace(/[^0-9\.\-\,]/g, '').replace(/,/g, '')) || 0;
                if (nacionalizacionNumber > 0) {
                    hasNacionalizacion = true;
                    nacionalizacionLinkedin = nacionalizacionNumber.toString();
                }
            }
            
            // Variables globales de configuración
            var igvPercent = parseFloat(window.mmreIgv) || 18;
            var nationalizationFeePercent = parseFloat(window.mmreNationalizationFee) || 30;
            
            // Calcular comisión desde reglas/cargos snapshot del mix
            var feeCalc = calculateAgencyFeeDetails(inversionNumber);
            var calculatedComision = feeCalc.totalFee;
            // Calcular pauta, igv y total final (incluir nacionalización si existe)
            var calculatedPauta = inversionNumber + (hasNacionalizacion ? parseFloat(nacionalizacionLinkedin) : 0) + calculatedComision;
            var calculatedIgv = calculatedPauta * (igvPercent / 100);
            var calculatedFinal = calculatedPauta + calculatedIgv;
            
            // Asignar variables en formato adecuado (strings para la estructura previa)
            inversionNeta = inversionNumber.toString();
            comisionValue = calculatedComision.toString();
            pautaComision = calculatedPauta.toString();
            igvValue = calculatedIgv.toString();
            totalFinal = calculatedFinal.toString();
            
            var feeDetailRows = [];
            var exportComponents = Array.isArray(feeCalc.ruleComponents) ? feeCalc.ruleComponents.slice() : [];
            exportComponents.sort(function(a, b) {
                var aFixed = (a.fee_mode || 'percentage') === 'fixed' ? 0 : 1;
                var bFixed = (b.fee_mode || 'percentage') === 'fixed' ? 0 : 1;
                return aFixed - bFixed;
            });
            exportComponents.forEach(function(comp, idx) {
                var customLabel = String(comp.fee_label || '').trim();
                var title = customLabel !== '' ? customLabel : ('Fee ' + (idx + 1));
                feeDetailRows.push({
                    label: title + ':',
                    amount: parseFloat(comp.converted_amount || 0)
                });
            });
            
            // Debug mínimo (opcional)
            console.log('Totales Excel (con nacionalización):', {
                inversionNumber: inversionNumber,
                hasNacionalizacion: hasNacionalizacion,
                nacionalizacionLinkedin: nacionalizacionLinkedin,
                calculatedComision: calculatedComision,
                calculatedPauta: calculatedPauta,
                calculatedIgv: calculatedIgv,
                calculatedFinal: calculatedFinal,
                nationalizationFeePercent: nationalizationFeePercent
            });
            
            // Estructura de totales generales (incluir nacionalización si aplica)
            var totalsData = [
                ['', '', '', 'Inversión Neta Total:', '', '', '', inversionNeta, '', '', '', '']
            ];
            
            // Agregar fila de nacionalización solo si existe
            if (hasNacionalizacion) {
                totalsData.push(['', '', '', 'Nacionalización LinkedIn (' + nationalizationFeePercent + '%):', '', '', '', nacionalizacionLinkedin, '', '', '', '']);
            }
            
            // Continuar con resto de totales
            totalsData.push(['', '', '', 'Comisión de Agencia:', '', '', '', comisionValue, '', '', '', '']);
            var feeDetailStartIndex = totalsData.length;
            feeDetailRows.forEach(function(item) {
                totalsData.push(['', '', '', item.label, '', '', '', String(item.amount), '', '', '', '']);
            });
            var feeDetailEndIndex = totalsData.length;
            
            var subtotalLabel = hasNacionalizacion ? 'Subtotal (Pauta + Nacionalización + Comisión):' : 'Subtotal (Pauta + Comisión):';
            totalsData.push(['', '', '', subtotalLabel, '', '', '', pautaComision, '', '', '', '']);
            
            totalsData.push(['', '', '', 'IGV (' + igvPercent + '%):', '', '', '', igvValue, '', '', '', '']);
            totalsData.push(['', '', '', '', '', '', '', '', '', '', '', '']); // Fila vacía
            totalsData.push(['', '', '', 'TOTAL INVERSIÓN + IGV:', '', '', '', totalFinal, '', '', '', '']);
            
            totalsData.forEach(function(rowData, index) {
                var row = worksheet.addRow(rowData);
                var isFinalTotal = rowData[3] && rowData[3].includes('TOTAL INVERSIÓN + IGV');
                var isGeneralTotal = rowData[3] && (rowData[3].includes('Inversión Neta') || rowData[3].includes('Nacionalización') || rowData[3].includes('Comisión') || rowData[3].includes('Subtotal (Pauta') || rowData[3].includes('IGV') || (index >= feeDetailStartIndex && index < feeDetailEndIndex));
                var isNacionalizacion = rowData[3] && rowData[3].includes('Nacionalización LinkedIn');
                var isFeeDetail = index >= feeDetailStartIndex && index < feeDetailEndIndex;
                
                if (isFinalTotal) { // Total final
                    // Merge etiqueta (columnas D-G)
                    worksheet.mergeCells(row.number, 4, row.number, 7);
                    var labelCell = row.getCell(4);
                    labelCell.font = { name: 'Arial', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
                    labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } };
                    labelCell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                    labelCell.border = {
                        top: { style: 'medium', color: { argb: 'FF28A745' } },
                        left: { style: 'medium', color: { argb: 'FF28A745' } },
                        bottom: { style: 'medium', color: { argb: 'FF28A745' } },
                        right: { style: 'medium', color: { argb: 'FF28A745' } }
                    };
                    
                    // Valor en columna H (inversión)
                    var numericTotal = parseFloat(totalFinal) || 0;
                    row.getCell(8).value = numericTotal;
                    row.getCell(8).numFmt = '"' + currency + '" #,##0.00';
                    row.getCell(8).font = { name: 'Arial', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
                    row.getCell(8).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } };
                    row.getCell(8).alignment = { vertical: 'middle', horizontal: 'right', wrapText: true };
                    row.getCell(8).border = {
                        top: { style: 'medium', color: { argb: 'FF28A745' } },
                        left: { style: 'medium', color: { argb: 'FF28A745' } },
                        bottom: { style: 'medium', color: { argb: 'FF28A745' } },
                        right: { style: 'medium', color: { argb: 'FF28A745' } }
                    };
                    
                    row.height = 35;
                    
                } else if (isGeneralTotal) { // Totales generales
                    var isSubtotal = rowData[3].includes('Subtotal (Pauta');
                    
                    // Merge etiqueta (columnas D-G)
                    worksheet.mergeCells(row.number, 4, row.number, 7);
                    var labelCell = row.getCell(4);
                    
                    // Estilo para nacionalización (mismo estilo que otros totales, sin color destacado)
                    if (isNacionalizacion) {
                        labelCell.font = { name: 'Arial', size: 9, color: { argb: 'FF2F5F8F' } };
                        labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                    } else if (isFeeDetail) {
                        labelCell.font = { name: 'Arial', size: 9, color: { argb: 'FF2F5F8F' } };
                        labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                    } else if (isSubtotal) {
                        labelCell.font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FF2F5F8F' } };
                        labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFECF0F1' } };
                    } else {
                        labelCell.font = { name: 'Arial', size: 9, color: { argb: 'FF2F5F8F' } };
                        labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                    }
                    labelCell.alignment = { vertical: 'middle', horizontal: 'left', wrapText: true, indent: isFeeDetail ? 2 : 1 };
                    labelCell.border = {
                        top: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                        left: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                        bottom: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                        right: { style: 'thin', color: { argb: 'FFB4C7E7' } }
                    };
                    
                    // Valor en columna H (inversión)
                    var numericVal = parseFloat(rowData[7]) || 0;
                    row.getCell(8).value = numericVal;
                    row.getCell(8).numFmt = '"' + currency + '" #,##0.00';
                    
                    if (isNacionalizacion) {
                        row.getCell(8).font = { name: 'Arial', size: 9, color: { argb: 'FF2F5F8F' } };
                        row.getCell(8).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                    } else if (isFeeDetail) {
                        row.getCell(8).font = { name: 'Arial', size: 9, color: { argb: 'FF2F5F8F' } };
                        row.getCell(8).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                    } else if (isSubtotal) {
                        row.getCell(8).font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FF2F5F8F' } };
                        row.getCell(8).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFECF0F1' } };
                    } else {
                        row.getCell(8).font = { name: 'Arial', size: 9, color: { argb: 'FF2F5F8F' } };
                        row.getCell(8).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                    }
                    row.getCell(8).alignment = { vertical: 'middle', horizontal: 'right', wrapText: true };
                    row.getCell(8).border = {
                        top: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                        left: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                        bottom: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                        right: { style: 'thin', color: { argb: 'FFB4C7E7' } }
                    };
                    
                    row.height = isFeeDetail ? 24 : 28;
                    
                } else {
                    // Fila vacía o separadora
                    row.height = 10;
                }
            });
            
            // Generar nombre con fecha y hora de Lima, Perú
            var now = new Date();
            var limaDate = new Date(now.toLocaleString("en-US", {timeZone: "America/Lima"}));
            
            var year = limaDate.getFullYear();
            var month = String(limaDate.getMonth() + 1).padStart(2, '0');
            var day = String(limaDate.getDate()).padStart(2, '0');
            var hours = String(limaDate.getHours()).padStart(2, '0');
            var minutes = String(limaDate.getMinutes()).padStart(2, '0');
            
            var dateTimeString = year + month + day + '_' + hours + minutes;
            var cleanMixName = mixName.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_');
            var fileName = cleanMixName + '_' + dateTimeString + '.xlsx';
            
            // Descargar archivo
            workbook.xlsx.writeBuffer().then(function(buffer) {
                var blob = new Blob([buffer], { 
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
                });
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
                
                swal({
                    icon: 'success',
                    title: '¡Excel exportado exitosamente!',
                    text: 'Archivo: ' + fileName,
                    timer: 3000
                });
            }).catch(function(error) {
                console.error('Error al generar Excel:', error);
                swal({
                    icon: 'error',
                    title: 'Error al generar Excel',
                    text: 'Problema en la generación: ' + error.message
                });
            });

        } catch (error) {
            console.error('Error en exportTableToExcel:', error);
            swal({
                icon: 'error',
                title: 'Error al exportar',
                text: 'Error específico: ' + error.message
            });
        }
    }

    // Evento para el botón de exportar Excel
    $('#exportExcelBtn').on('click', function() {
        exportTableToExcel();
    });

    // Función para scroll suave a totales
    window.scrollToTotals = function() {
        $('html, body').animate({
            scrollTop: $('.box-info').offset().top - 20
        }, 800);
    };
    
    // Evento click para botón flotante
    $('#floatingTotalsBtn').on('click', function() {
        window.scrollToTotals();
    });
    
    // Mostrar/ocultar botón flotante según scroll
    $(window).scroll(function() {
        var totalsOffset = $('.box-info').length > 0 ? $('.box-info').offset().top : 0;
        var scrollTop = $(window).scrollTop();
        
        if (scrollTop > 300 && scrollTop < (totalsOffset - 100)) {
            $('#floatingTotalsBtn').fadeIn();
        } else {
            $('#floatingTotalsBtn').fadeOut();
        }
    });

    // ── Configuración de fee por reglas/cargos (snapshot del mix) ──────────
    var mixFeeRulesState = Array.isArray(window.mixFeeRules) ? window.mixFeeRules.slice() : [];
    var mixFeeChargesState = Array.isArray(window.mixFeeCharges) ? window.mixFeeCharges.slice() : [];
    var feeConceptsCatalog = [];
    var isLoadingMixFeeConfig = false;
    var mixFeeLoadSeq = 0;

    function formatMoney(value) {
        return (window.currency || 'USD') + ' ' + Number(value || 0).toFixed(2);
    }

    function buildFeeBreakdownLines(feeCalc, inversionNeta) {
        var lines = [];
        var components = (feeCalc && Array.isArray(feeCalc.ruleComponents)) ? feeCalc.ruleComponents : [];
        if (!components.length) return lines;

        var orderedComponents = components.slice().sort(function(a, b) {
            var aFixed = (a.fee_mode || 'percentage') === 'fixed' ? 0 : 1;
            var bFixed = (b.fee_mode || 'percentage') === 'fixed' ? 0 : 1;
            return aFixed - bFixed;
        });

        orderedComponents.forEach(function (comp, idx) {
            var amountText = Number(comp.converted_amount || 0).toFixed(2) + ' ' + String(window.currency || 'USD').toLowerCase();
            var customLabel = String(comp.fee_label || '').trim();
            var title = customLabel !== '' ? customLabel : ('Fee ' + (idx + 1));
            lines.push(title + ': ' + amountText);
        });

        if (!lines.length && inversionNeta > 0 && feeCalc && feeCalc.baseFee) {
            lines.push('Fee 1: ' + Number(feeCalc.baseFee || 0).toFixed(2) + ' ' + String(window.currency || 'USD').toLowerCase());
        }

        return lines;
    }

    function refreshSummaryFromFeeState() {
        var inversionText = $('#inversionNetaTotal').text() || '';
        var inversionNeta = parseFloat(String(inversionText).replace(/[^0-9\.\-,]/g, '').replace(/,/g, '')) || 0;

        var nacionalizacion = 0;
        if ($('#nacionalizacionLinkedin').length > 0) {
            var nacionalizacionText = $('#nacionalizacionLinkedin').text() || '';
            nacionalizacion = parseFloat(String(nacionalizacionText).replace(/[^0-9\.\-,]/g, '').replace(/,/g, '')) || 0;
        }

        var feeCalc = calculateAgencyFeeDetails(inversionNeta);
        var comision = parseFloat(feeCalc.baseFee || 0);
        var extra = parseFloat(feeCalc.chargesTotal || 0);
        var subtotal = inversionNeta + nacionalizacion + comision + extra;
        var igvPct = parseFloat(window.mmreIgv) || 0;
        var igv = subtotal * (igvPct / 100);
        var total = subtotal + igv;

        $('#comisionAgencia').text(formatMoney(comision));

        var lines = buildFeeBreakdownLines(feeCalc, inversionNeta);
        var breakdownHtml = lines.map(function (line) { return '<div>' + line + '</div>'; }).join('');
        $('#comisionDesglose').html(breakdownHtml);

        var $chargesBody = $('#mixFeeExtraChargesRows');
        if ($chargesBody.length) {
            $chargesBody.empty();
            (feeCalc.charges || []).forEach(function (ch) {
                var converted = parseFloat(ch.converted_amount || ch.amount || 0);
                var origCode = ch.currency_code || (window.currency || 'USD');
                var origAmount = parseFloat(ch.amount || 0);
                $chargesBody.append(
                    '<tr class="warning">' +
                        '<td class="text-right"><strong>' + $('<span>').text(ch.concept_name || '').html() + ':</strong></td>' +
                        '<td class="text-right" style="font-size:16px;"><strong>' +
                            formatMoney(converted) +
                            ' <small class="text-muted">(' + origCode + ' ' + origAmount.toFixed(2) + ')</small>' +
                        '</strong></td>' +
                    '</tr>'
                );
            });
        }

        $('#pautaComision').text(formatMoney(subtotal));
        $('#igvCalculado').text(formatMoney(igv));
        $('#inversionTotalIgv').text(formatMoney(total));
    }

    function loadMixFeeConfigFromServer() {
        var seq = ++mixFeeLoadSeq;
        isLoadingMixFeeConfig = true;
        renderMixFeeRules();
        renderMixFeeCharges();

        var url = 'ajax/fees.ajax.php?action=get_mix_config&mix_id=' + encodeURIComponent(window.mmreId) + '&_ts=' + Date.now();
        return fetch(url, { cache: 'no-store' })
            .then(function(res) { return res.json(); })
            .then(function(cfg) {
                if (seq !== mixFeeLoadSeq) return;
                var data = (cfg && cfg.success && cfg.data) ? cfg.data : { rules: [], charges: [] };
                mixFeeRulesState = Array.isArray(data.rules) ? data.rules : [];
                mixFeeChargesState = Array.isArray(data.charges) ? data.charges : [];
                window.mixFeeRules = mixFeeRulesState.slice();
                window.mixFeeCharges = mixFeeChargesState.slice();
                if (data.mix_meta && data.mix_meta.currency_usd_per_unit_snapshot) {
                    window.mmreCurrencyUsdPerUnitSnapshot = parseFloat(data.mix_meta.currency_usd_per_unit_snapshot) || window.mmreCurrencyUsdPerUnitSnapshot;
                }
                isLoadingMixFeeConfig = false;
                renderMixFeeRules();
                renderMixFeeCharges();
                refreshSummaryFromFeeState();
            })
            .catch(function() {
                if (seq !== mixFeeLoadSeq) return;
                isLoadingMixFeeConfig = false;
                renderMixFeeRules();
                renderMixFeeCharges();
            });
    }

    function loadFeeCurrenciesCatalog(selectedCode) {
        return fetch('ajax/currencies.ajax.php?action=list&only_active=1')
            .then(function(res) { return res.json(); })
            .then(function(resp) {
                feeCurrenciesCatalog = (resp && resp.success && Array.isArray(resp.data)) ? resp.data : [];
                var options = '';
                feeCurrenciesCatalog.forEach(function(c) {
                    options += '<option value="' + c.code + '">' + c.code + '</option>';
                });
                $('#mixFeeChargeCurrency').html(options);

                var currentCurrency = selectedCode || window.currency || 'USD';
                if ($('#mixFeeChargeCurrency option[value="' + currentCurrency + '"]').length) {
                    $('#mixFeeChargeCurrency').val(currentCurrency);
                }

                var configCurrencyOptions = '';
                feeCurrenciesCatalog.forEach(function(c) {
                    configCurrencyOptions += '<option value="' + c.code + '">' + c.code + ' - ' + $('<span>').text(c.name).html() + '</option>';
                });
                if ($('#configCurrencySelect').length && configCurrencyOptions) {
                    var selected = $('#configCurrencySelect').val() || window.currency || 'USD';
                    $('#configCurrencySelect').html(configCurrencyOptions);
                    $('#configCurrencySelect').val(selected);
                }
            });
    }

    function renderMixFeeRules() {
        var $tbody = $('#mixFeeRulesBody');
        if (!$tbody.length) return;
        $tbody.empty();
        if (isLoadingMixFeeConfig) {
            $tbody.html('<tr><td colspan="8" class="text-center text-muted">Cargando configuración...</td></tr>');
            return;
        }
        if (!mixFeeRulesState.length) {
            $tbody.html('<tr><td colspan="8" class="text-center text-muted">Sin reglas configuradas</td></tr>');
            return;
        }

        var currencyOptions = '';
        feeCurrenciesCatalog.forEach(function(c) {
            currencyOptions += '<option value="' + c.code + '">' + c.code + '</option>';
        });

        mixFeeRulesState.forEach(function(rule, idx) {
            var maxVal = (rule.max_investment === null || rule.max_investment === '' || typeof rule.max_investment === 'undefined')
                ? '' : rule.max_investment;
            var selectedCurrency = rule.fixed_currency_code || window.currency || 'USD';
            var mode = rule.fee_mode || 'percentage';
            var feeLabel = String(rule.fee_label || '').replace(/"/g, '&quot;');
            $tbody.append(
                '<tr data-index="' + idx + '">' +
                    '<td><input type="number" class="form-control input-sm mix-rule-min" step="0.01" value="' + (typeof rule.min_investment !== 'undefined' ? rule.min_investment : 0) + '"></td>' +
                    '<td><input type="number" class="form-control input-sm mix-rule-max" step="0.01" value="' + maxVal + '" placeholder="Sin tope"></td>' +
                    '<td>' +
                        '<select class="form-control input-sm mix-rule-mode">' +
                            '<option value="percentage" ' + (mode === 'percentage' ? 'selected' : '') + '>Porcentaje</option>' +
                            '<option value="fixed" ' + (mode === 'fixed' ? 'selected' : '') + '>Fijo</option>' +
                        '</select>' +
                    '</td>' +
                    '<td><input type="text" class="form-control input-sm mix-rule-label" maxlength="120" value="' + feeLabel + '" placeholder="Ej: Fee Google"></td>' +
                    '<td class="mix-rule-percentage-cell"><input type="number" class="form-control input-sm mix-rule-percentage" step="0.0001" value="' + (typeof rule.percentage_value !== 'undefined' ? rule.percentage_value : 0) + '"></td>' +
                    '<td class="mix-rule-fixed-cell"><input type="number" class="form-control input-sm mix-rule-fixed" step="0.01" value="' + (typeof rule.fixed_value !== 'undefined' ? rule.fixed_value : 0) + '"><span class="text-muted mix-rule-fixed-na" style="display:none;">No aplica</span></td>' +
                    '<td class="mix-rule-currency-cell"><select class="form-control input-sm mix-rule-fixed-currency">' + currencyOptions + '</select><span class="text-muted mix-rule-currency-na" style="display:none;">-</span></td>' +
                    '<td><button type="button" class="btn btn-danger btn-sm btn-remove-mix-rule"><i class="fa fa-trash"></i></button></td>' +
                '</tr>'
            );
            var $row = $tbody.find('tr[data-index="' + idx + '"]');
            $row.find('.mix-rule-fixed-currency').val(selectedCurrency);
            applyMixRuleModeUI($row);
        });
    }

    function applyMixRuleModeUI($row) {
        if (!$row || !$row.length) return;
        var mode = $row.find('.mix-rule-mode').val() || 'percentage';
        var isPercentage = mode === 'percentage';

        $row.find('.mix-rule-percentage').prop('disabled', !isPercentage).toggle(isPercentage);
        $row.find('.mix-rule-fixed').prop('disabled', isPercentage).toggle(!isPercentage);
        $row.find('.mix-rule-fixed-currency').prop('disabled', isPercentage).toggle(!isPercentage);
        $row.find('.mix-rule-fixed-na').toggle(isPercentage);
        $row.find('.mix-rule-currency-na').toggle(isPercentage);
    }

    function renderMixFeeCharges() {
        var $tbody = $('#mixFeeChargesBody');
        if (!$tbody.length) return;
        $tbody.empty();
        if (isLoadingMixFeeConfig) {
            $tbody.html('<tr><td colspan="4" class="text-center text-muted">Cargando configuración...</td></tr>');
            return;
        }
        if (!mixFeeChargesState.length) {
            $tbody.html('<tr><td colspan="4" class="text-center text-muted">Sin cargos configurados</td></tr>');
            return;
        }

        mixFeeChargesState.forEach(function(charge, idx) {
            $tbody.append(
                '<tr data-index="' + idx + '">' +
                    '<td>' + $('<span>').text(charge.concept_name || '').html() + '</td>' +
                    '<td>' + (parseFloat(charge.amount) || 0).toFixed(2) + '</td>' +
                    '<td>' + (charge.currency_code || window.currency || 'USD') + '</td>' +
                    '<td><button type="button" class="btn btn-danger btn-sm btn-remove-mix-charge"><i class="fa fa-trash"></i></button></td>' +
                '</tr>'
            );
        });
    }

    function collectMixRulesFromTable() {
        var list = [];
        $('#mixFeeRulesBody tr[data-index]').each(function() {
            var $row = $(this);
            var min = parseFloat($row.find('.mix-rule-min').val());
            var maxRaw = $row.find('.mix-rule-max').val();
            var mode = $row.find('.mix-rule-mode').val();
            var feeLabel = ($row.find('.mix-rule-label').val() || '').trim();
            var pct = parseFloat($row.find('.mix-rule-percentage').val());
            var fixed = parseFloat($row.find('.mix-rule-fixed').val());
            var fixedCurrency = $row.find('.mix-rule-fixed-currency').val() || window.currency || 'USD';

            var normalizedMode = mode || 'percentage';
            var normalizedPct = isNaN(pct) ? 0 : pct;
            var normalizedFixed = isNaN(fixed) ? 0 : fixed;

            if (normalizedMode === 'percentage') {
                normalizedFixed = 0;
            } else if (normalizedMode === 'fixed') {
                normalizedPct = 0;
            }

            list.push({
                min_investment: isNaN(min) ? 0 : min,
                max_investment: maxRaw === '' ? null : (isNaN(parseFloat(maxRaw)) ? null : parseFloat(maxRaw)),
                fee_mode: normalizedMode,
                fee_label: feeLabel,
                percentage_value: normalizedPct,
                fixed_value: normalizedFixed,
                fixed_currency_code: fixedCurrency
            });
        });
        return list;
    }

    function loadFeeConceptCatalog(selectedId) {
        if (!$('#mixFeeConceptSelect').length) return Promise.resolve();
        return fetch('ajax/fees.ajax.php?action=list_concepts')
            .then(function(res) { return res.json(); })
            .then(function(resp) {
                feeConceptsCatalog = (resp && resp.success && Array.isArray(resp.data)) ? resp.data : [];
                var html = '<option value="">-- Selecciona un concepto --</option>';
                feeConceptsCatalog.forEach(function(c) {
                    html += '<option value="' + c.id + '">' + $('<span>').text(c.name).html() + '</option>';
                });
                $('#mixFeeConceptSelect').html(html);
                if (selectedId) {
                    $('#mixFeeConceptSelect').val(String(selectedId));
                }
            });
    }

    $('#configMixModal').on('show.bs.modal', function() {
        isLoadingMixFeeConfig = true;
        renderMixFeeRules();
        renderMixFeeCharges();
        Promise.all([loadFeeCurrenciesCatalog(window.currency || 'USD'), loadFeeConceptCatalog(), loadMixFeeConfigFromServer()]);
    });

    $('#btnAddMixFeeRule').on('click', function() {
        mixFeeRulesState.push({
            min_investment: 0,
            max_investment: null,
            fee_mode: 'percentage',
            fee_label: '',
            percentage_value: 0,
            fixed_value: 0,
            fixed_currency_code: window.currency || 'USD'
        });
        renderMixFeeRules();
    });

    $(document).on('click', '.btn-remove-mix-rule', function() {
        var idx = parseInt($(this).closest('tr').attr('data-index'), 10);
        if (!isNaN(idx)) {
            mixFeeRulesState.splice(idx, 1);
            renderMixFeeRules();
        }
    });

    $(document).on('change', '.mix-rule-mode', function() {
        var $row = $(this).closest('tr');
        applyMixRuleModeUI($row);
    });

    $('#btnAddMixFeeCharge').on('click', function() {
        var conceptId = $('#mixFeeConceptSelect').val();
        var concept = feeConceptsCatalog.find(function(c) { return String(c.id) === String(conceptId); });
        var amount = parseFloat($('#mixFeeChargeAmount').val());
        var chargeCurrency = $('#mixFeeChargeCurrency').val() || window.currency || 'USD';
        if (!concept) {
            swal({ icon: 'warning', title: 'Concepto requerido', text: 'Selecciona un concepto.' });
            return;
        }
        if (isNaN(amount)) {
            swal({ icon: 'warning', title: 'Monto inválido', text: 'Ingresa un monto válido.' });
            return;
        }

        mixFeeChargesState.push({
            concept_id: concept.id,
            concept_name: concept.name,
            amount: amount,
            currency_code: chargeCurrency
        });
        $('#mixFeeChargeAmount').val('');
        renderMixFeeCharges();
    });

    $(document).on('click', '.btn-remove-mix-charge', function() {
        var idx = parseInt($(this).closest('tr').attr('data-index'), 10);
        if (!isNaN(idx)) {
            mixFeeChargesState.splice(idx, 1);
            renderMixFeeCharges();
        }
    });

    $('#btnSyncFeesFromClient').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Cargando fee...');
        isLoadingMixFeeConfig = true;
        renderMixFeeRules();
        renderMixFeeCharges();

        var fd = new FormData();
        fd.append('action', 'sync_mix_from_client');
        fd.append('mix_id', window.mmreId);
        fd.append('client_id', window.clientId);

        fetch('ajax/fees.ajax.php', { method: 'POST', body: fd })
            .then(function(res) { return res.json(); })
            .then(function(resp) {
                if (!resp || !resp.success) {
                    isLoadingMixFeeConfig = false;
                    renderMixFeeRules();
                    renderMixFeeCharges();
                    swal({ icon: 'error', title: 'No se pudo actualizar', text: (resp && resp.message) ? resp.message : 'Error desconocido' });
                    return;
                }
                return loadMixFeeConfigFromServer().then(function() {
                    swal({ icon: 'success', title: 'Configuración actualizada', text: 'Se aplicó la configuración actual del cliente.' });
                });
            })
            .finally(function() {
                $btn.prop('disabled', false).html(originalText);
            });
    });

    $('#configMixForm').on('submit', function() {
        if (!window.isAdmin) {
            return true;
        }

        mixFeeRulesState = collectMixRulesFromTable();
        $('#configRulesJson').val(JSON.stringify(mixFeeRulesState));
        $('#configChargesJson').val(JSON.stringify(mixFeeChargesState));
        window.mixFeeRules = mixFeeRulesState.slice();
        window.mixFeeCharges = mixFeeChargesState.slice();
        return true;
    });
    // ─────────────────────────────────────────────────────────────────────────
});