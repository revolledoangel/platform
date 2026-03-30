// Archivo limpio para comenzar desde cero
var cachedProjects = null;
var lastSelectedProject = null;
var cachedMetricsByPlatform = {};
var lastSelectedMetric = null;
var defaultObjectiveId = null;
var cachedPlatforms = null;
var lastSelectedPlatform = null;
var cachedChannels = null;
var lastSelectedChannel = null;
var cachedFormatsByPlatform = {};
var lastSelectedFormats = [];
var lastPlatformForFormats = null;
var cachedCampaignTypes = null;
var lastSelectedCampaignType = null;

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
    // Cargar un objetivo por defecto (requerido por la API aunque no se muestre al usuario)
    $.ajax({
        url: 'ajax/mediaMixRealEstateDetails.ajax.php',
        method: 'POST',
        data: { get_objectives: 1 },
        dataType: 'json',
        success: function(data) {
            if (Array.isArray(data) && data.length > 0) {
                defaultObjectiveId = data[0].id;
            }
        }
    });

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
        var $campaignTypeSelect = $('#newDetailCampaignType');
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
        var investment = parseFloat($('#newDetailInvestment').val());
        var aon = $('#newDetailAon').is(':checked') ? 1 : 0;
        var comments = $('#newDetailComments').val();
        var state = $('#newDetailStatus').val();

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
            campaign_type_id: 1,
            segmentation: segmentation,
            objectives_ids: defaultObjectiveId ? [defaultObjectiveId] : [1],
            metric_id: parseInt(selectedMetricId),
            result_type: event_name ? result_type + ' (' + event_name + ')' : result_type,
            projection: projection,
            formats_ids: formats_ids,
            investment: investment,
            aon: aon,
            comments: comments,
            state: state
        };

        fetch('https://algoritmo.digital/backend/public/api/mmre_details', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        })
        .then(async res => {
            const statusCode = res.status;
            const response = await res.json();
            if ((statusCode === 200 || statusCode === 201) && (response.success || (response.id && !isNaN(response.id)))) {
                swal({
                    icon: 'success',
                    title: 'Detalle guardado',
                    text: 'El detalle se guardó correctamente.'
                }).then(() => { location.reload(); });
            } else {
                swal({
                    icon: 'error',
                    title: 'Error al guardar',
                    text: (response.message || 'Respuesta inesperada de la API.') + '\n' + JSON.stringify(response)
                });
            }
        })
        .catch(error => {
            swal({
                icon: 'error',
                title: 'Error de red',
                text: 'No se pudo conectar con el servidor.'
            });
        });
    });
    // Evento para abrir el modal de edición y prellenar los campos con AJAX
    $(document).on('click', '.btn-editDetail', function (e) {
        var detailId = $(this).data('detail-id');
        e.preventDefault();
        var detailId = $(this).data('detail-id');
        $.ajax({
            url: 'ajax/mediaMixRealEstateDetails.ajax.php',
            method: 'POST',
            data: { get_detail_id: detailId },
            dataType: 'json',
            success: function(data) {
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
                            var selected = (data.platform_id == plat.id) ? ' selected' : '';
                            options += '<option value="' + plat.id + '"' + selected + '>' + plat.name + '</option>';
                        });
                        $('#editDetailPlatform').html(options).prop('disabled', false);
                        showModalIfReady();
                    }
                });
                // Canales (dependiente de plataforma)
                $.ajax({
                    url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                    method: 'POST',
                    data: { get_channels_by_platform: data.platform_id },
                    dataType: 'json',
                    success: function(channels) {
                        var options = '<option value="">-- Selecciona un canal --</option>';
                        channels.forEach(function(chan) {
                            var selected = (data.channel_id == chan.id) ? ' selected' : '';
                            options += '<option value="' + chan.id + '"' + selected + '>' + chan.name + '</option>';
                        });
                        $('#editDetailChannel').html(options).prop('disabled', false);
                        showModalIfReady();
                    }
                });
                // Métricas por plataforma (reemplaza objetivos)
                $.ajax({
                    url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                    method: 'POST',
                    data: { get_metrics_by_platform: data.platform_id },
                    dataType: 'json',
                    success: function(metrics) {
                        cachedMetricsByPlatform[data.platform_id] = metrics;
                        var mOpts = '<option value="">-- Selecciona una métrica --</option>';
                        metrics.forEach(function(m) {
                            var selected = (data.result_type && m.name === data.result_type) ? ' selected' : '';
                            mOpts += '<option value="' + m.id + '" data-requires-event="' + (m.requires_event || 0) + '"' + selected + '>' + m.name + (m.code ? ' (' + m.code + ')' : '') + '</option>';
                        });
                        $('#editDetailMetric').html(mOpts).prop('disabled', false);
                        // Show event name field if pre-selected metric requires it
                        var preSelected = $('#editDetailMetric option:selected');
                        if (parseInt(preSelected.data('requires-event'))) {
                            $('#editEventNameGroup').show();
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
                    data: { platform_id: data.platform_id },
                    dataType: 'json',
                    success: function(formats) {
                        var options = '';
                        formats.forEach(function(fmt) {
                            var selected = (data.formats_ids && data.formats_ids.includes(fmt.id)) ? ' selected' : '';
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
        if (!platformId) {
            $metricSelect.html('<option value="">Selecciona una plataforma primero</option>').prop('disabled', true);
            $('#editDetailChannel').html('<option value="">Selecciona una plataforma primero</option>').prop('disabled', true);
            return;
        }
        $metricSelect.html('<option value="">Cargando métricas...</option>').prop('disabled', true);
        if (cachedMetricsByPlatform[platformId]) {
            var mOpts = '<option value="">-- Selecciona una métrica --</option>';
            cachedMetricsByPlatform[platformId].forEach(function(m) {
                mOpts += '<option value="' + m.id + '" data-requires-event="' + (m.requires_event || 0) + '">' + m.name + (m.code ? ' (' + m.code + ')' : '') + '</option>';
            });
            $metricSelect.html(mOpts).prop('disabled', false);
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
            data: { get_channels_by_platform: platformId },
            dataType: 'json',
            success: function(channels) {
                if (Array.isArray(channels) && channels.length > 0) {
                    var cOpts = '<option value="">-- Selecciona un canal --</option>';
                    channels.forEach(function(chan) {
                        cOpts += '<option value="' + chan.id + '">' + chan.name + '</option>';
                    });
                    $('#editDetailChannel').html(cOpts).prop('disabled', false);
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
        var investment = parseFloat($('#editDetailInvestment').val());
        var aon = $('#editDetailAon').is(':checked') ? 1 : 0;
        var comments = $('#editDetailComments').val();
        var state = $('#editDetailStatus').val();

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
            campaign_type_id: 1,
            segmentation: segmentation,
            objectives_ids: defaultObjectiveId ? [defaultObjectiveId] : [1],
            metric_id: parseInt(selectedEditMetricId),
            result_type: event_name ? result_type + ' (' + event_name + ')' : result_type,
            projection: projection,
            formats_ids: formats_ids,
            investment: investment,
            aon: aon,
            comments: comments,
            state: state
        };

        fetch('https://algoritmo.digital/backend/public/api/mmre_details/' + detail_id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        })
        .then(async res => {
            const statusCode = res.status;
            const response = await res.json();
            if ((statusCode === 200 || statusCode === 201) && (response.success || (response.id && !isNaN(response.id)))) {
                swal({
                    icon: 'success',
                    title: 'Detalle actualizado',
                    text: 'Los cambios se guardaron correctamente.'
                }).then(() => { location.reload(); });
            } else {
                swal({
                    icon: 'error',
                    title: 'Error al guardar',
                    text: (response.message || 'Respuesta inesperada de la API.') + '\n' + JSON.stringify(response)
                });
            }
        })
        .catch(error => {
            swal({
                icon: 'error',
                title: 'Error de red',
                text: 'No se pudo conectar con el servidor.'
            });
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
            // Proyecto, Plataforma, AON, Canal, Segmentación, Formatos, Inversión, Distribución, Estado, Proyección, Métrica, CPR
            var columnWidths = [15, 15, 6, 18, 22, 18, 16, 12, 14, 10, 22, 12];
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
                'Proyecto', 'Plataforma', 'AON', 'Canal',
                'Segmentación', 'Formatos', 'Inversión (' + currency + ')',
                'Distribución (%)', 'Estado', 'Proyección', 'Métrica', 'CPR'
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
                            var investment = parseFloat(String(rowData[6]).replace(/[^0-9.-]/g, ''));
                            var projection = parseFloat(String(rowData[9]).replace(/[^0-9.-]/g, ''));
                            var metricName = rowData[10];
                            
                            if (!isNaN(investment) && !isNaN(projection) && projection > 0) {
                                var cpr = investment / projection;
                                if (metricName && metricName.toLowerCase().indexOf('alcance') !== -1) {
                                    cpr *= 1000;
                                    cell.value = parseFloat(cpr.toFixed(2));
                                    cell.numFmt = '#,##0.00" CPM"';
                                } else {
                                    cell.value = parseFloat(cpr.toFixed(4));
                                    cell.numFmt = '#,##0.0###" CPR"';
                                }
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
            var fee = parseFloat(window.mmreFee) || 0;
            var feeType = window.mmreFeeType || 'percentage';
            var igvPercent = parseFloat(window.mmreIgv) || 18;
            var nationalizationFeePercent = parseFloat(window.mmreNationalizationFee) || 30;
            
            // Calcular comision según tipo
            var calculatedComision = (feeType === 'fixed') ? parseFloat(fee) : (inversionNumber * fee / 100);
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
            
            var comisionType = (feeType === 'fixed') ? '(Valor Fijo)' : '(' + fee + '%)';
            
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
                ['', '', '', 'Inversión Neta Total:', '', '', inversionNeta, '', '', '', '', '']
            ];
            
            // Agregar fila de nacionalización solo si existe
            if (hasNacionalizacion) {
                totalsData.push(['', '', '', 'Nacionalización LinkedIn (' + nationalizationFeePercent + '%):', '', '', nacionalizacionLinkedin, '', '', '', '', '']);
            }
            
            // Continuar con resto de totales
            totalsData.push(['', '', '', 'Comisión de Agencia ' + comisionType + ':', '', '', comisionValue, '', '', '', '', '']);
            
            var subtotalLabel = hasNacionalizacion ? 'Subtotal (Pauta + Nacionalización + Comisión):' : 'Subtotal (Pauta + Comisión):';
            totalsData.push(['', '', '', subtotalLabel, '', '', pautaComision, '', '', '', '', '']);
            
            totalsData.push(['', '', '', 'IGV (' + igvPercent + '%):', '', '', igvValue, '', '', '', '', '']);
            totalsData.push(['', '', '', '', '', '', '', '', '', '', '', '']); // Fila vacía
            totalsData.push(['', '', '', 'TOTAL INVERSIÓN + IGV:', '', '', totalFinal, '', '', '', '', '']);
            
            totalsData.forEach(function(rowData, index) {
                var row = worksheet.addRow(rowData);
                var isFinalTotal = rowData[3] && rowData[3].includes('TOTAL INVERSIÓN + IGV');
                var isGeneralTotal = rowData[3] && (rowData[3].includes('Inversión Neta') || rowData[3].includes('Nacionalización') || rowData[3].includes('Comisión') || rowData[3].includes('Subtotal (Pauta') || rowData[3].includes('IGV'));
                var isNacionalizacion = rowData[3] && rowData[3].includes('Nacionalización LinkedIn');
                
                if (isFinalTotal) { // Total final
                    // Merge etiqueta (columnas D-F)
                    worksheet.mergeCells(row.number, 4, row.number, 6);
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
                    
                    // Valor en columna G (inversión)
                    var numericTotal = parseFloat(totalFinal) || 0;
                    row.getCell(7).value = numericTotal;
                    row.getCell(7).numFmt = '"' + currency + '" #,##0.00';
                    row.getCell(7).font = { name: 'Arial', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
                    row.getCell(7).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } };
                    row.getCell(7).alignment = { vertical: 'middle', horizontal: 'right', wrapText: true };
                    row.getCell(7).border = {
                        top: { style: 'medium', color: { argb: 'FF28A745' } },
                        left: { style: 'medium', color: { argb: 'FF28A745' } },
                        bottom: { style: 'medium', color: { argb: 'FF28A745' } },
                        right: { style: 'medium', color: { argb: 'FF28A745' } }
                    };
                    
                    row.height = 35;
                    
                } else if (isGeneralTotal) { // Totales generales
                    var isSubtotal = rowData[3].includes('Subtotal (Pauta');
                    
                    // Merge etiqueta (columnas D-F)
                    worksheet.mergeCells(row.number, 4, row.number, 6);
                    var labelCell = row.getCell(4);
                    
                    // Estilo para nacionalización (mismo estilo que otros totales, sin color destacado)
                    if (isNacionalizacion) {
                        labelCell.font = { name: 'Arial', size: 9, color: { argb: 'FF2F5F8F' } };
                        labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                    } else if (isSubtotal) {
                        labelCell.font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FF2F5F8F' } };
                        labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFECF0F1' } };
                    } else {
                        labelCell.font = { name: 'Arial', size: 9, color: { argb: 'FF2F5F8F' } };
                        labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                    }
                    labelCell.alignment = { vertical: 'middle', horizontal: 'left', wrapText: true, indent: 1 };
                    labelCell.border = {
                        top: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                        left: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                        bottom: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                        right: { style: 'thin', color: { argb: 'FFB4C7E7' } }
                    };
                    
                    // Valor en columna G (inversión)
                    var numericVal = parseFloat(rowData[6]) || 0;
                    row.getCell(7).value = numericVal;
                    row.getCell(7).numFmt = '"' + currency + '" #,##0.00';
                    
                    if (isNacionalizacion) {
                        row.getCell(7).font = { name: 'Arial', size: 9, color: { argb: 'FF2F5F8F' } };
                        row.getCell(7).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                    } else if (isSubtotal) {
                        row.getCell(7).font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FF2F5F8F' } };
                        row.getCell(7).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFECF0F1' } };
                    } else {
                        row.getCell(7).font = { name: 'Arial', size: 9, color: { argb: 'FF2F5F8F' } };
                        row.getCell(7).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                    }
                    row.getCell(7).alignment = { vertical: 'middle', horizontal: 'right', wrapText: true };
                    row.getCell(7).border = {
                        top: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                        left: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                        bottom: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                        right: { style: 'thin', color: { argb: 'FFB4C7E7' } }
                    };
                    
                    row.height = 28;
                    
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

    // Manejar cambio de tipo de fee en modal configuración
    $('input[name="configFeeType"]').on('change', function() {
        var feeType = $(this).val();
        console.log('Changing fee type to:', feeType); // Debug
        
        var $symbol = $('#configFeeSymbol');
        var $input = $('#configFeeInput');
        
        if (feeType === 'percentage') {
            $symbol.html('<i class="fa fa-percent"></i>');
            $input.attr('placeholder', 'Ej: 10');
        } else {
            $symbol.html('<i class="fa fa-money"></i>');
            $input.attr('placeholder', 'Ej: 1500');
        }
    });

    // Actualizar el form submit para usar POST normal
    $('#configMixForm').on('submit', function(e) {
        e.preventDefault();
        
        // Debug para verificar los valores antes del envío
        console.log('Form data:', {
            feeType: $('input[name="configFeeType"]:checked').val(),
            fee: $('#configFeeInput').val(),
            name: $('input[name="configName"]').val(),
            currency: $('select[name="configCurrency"]').val(),
            igv: $('input[name="configIgv"]').val()
        });
        
        // Enviar el formulario normalmente
        this.submit();
    });

    // Nueva función para recalcular totales
    function recalcularTotales() {
        var currency = window.currency || 'USD';
        var fee = parseFloat(window.mmreFee) || 0;
        var feeType = window.mmreFeeType || 'percentage';
        var igvPorcentaje = parseFloat(window.mmreIgv) || 18;
        
        // Calcular inversión neta total
        var totalInversion = 0;
        $('#detailsTable tbody tr').each(function() {
            var inversionText = $(this).find('td:nth-child(9)').text();
            if (inversionText && !$(this).css('background-color').includes('245, 245, 245')) {
                var inversionValue = parseFloat(inversionText.replace(/[^0-9.-]/g, ''));
                if (!isNaN(inversionValue)) {
                    totalInversion += inversionValue;
                }
            }
        });
        
        // Calcular comisión según tipo de fee
        var comision = 0;
        var feeDisplay = '';
        
        console.log('Calculando comisión:', {
            feeType: feeType,
            fee: fee,
            totalInversion: totalInversion
        });
        
        if (feeType === 'fixed') {
            comision = fee; // Usar el valor directamente si es fijo
            feeDisplay = '(fijo)';
        } else {
            comision = totalInversion * (fee / 100); // Calcular porcentaje
            feeDisplay = '(' + fee + '%)';
        }
        
        // Actualizar display de comisión
        $('#comisionAgencia').html(
            currency + ' ' + comision.toFixed(2) + 
            ' <small class="text-muted">' + feeDisplay + '</small>'
        );
        
        // Resto de cálculos...
        // Calcular pauta (inversión + comisión)
        var pauta = totalInversion + comision;
        
        // Calcular IGV y total final
        var igv = pauta * (igvPorcentaje / 100);
        var total = pauta + igv;
        
        // Actualizar campos en el modal de configuración
        $('#configInversionNeta').val(totalInversion.toFixed(2));
        $('#configComision').val(comision.toFixed(2));
        $('#configPauta').val(pauta.toFixed(2));
        $('#configIgv').val(igv.toFixed(2));
        $('#configTotal').val(total.toFixed(2));
        
        // Mostrar mensaje de éxito
        swal({
            icon: 'success',
            title: 'Totales recalculados',
            text: 'Los totales se recalcularon correctamente.'
        });
    }
    
    // Evento para botón de recalcular totales
    $('#recalcularTotalesBtn').on('click', function() {
        recalcularTotales();
    });
});