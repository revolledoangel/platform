// Archivo limpio para comenzar desde cero
var cachedProjects = null;
var lastSelectedProject = null;
var cachedObjectives = null;
var lastSelectedObjective = null;
var cachedPlatforms = null;
var lastSelectedPlatform = null;
var cachedChannels = null;
var lastSelectedChannel = null;
var cachedFormatsByPlatform = {};
var lastSelectedFormats = [];
var lastPlatformForFormats = null;
var cachedCampaignTypes = null;
var lastSelectedCampaignType = null;
var lastResultTypeValue = '';
var lastResultTypeAuto = true;

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
        var $objectiveSelect = $('#newDetailObjective');
        var $platformSelect = $('#newDetailPlatform');
        var $channelSelect = $('#newDetailChannel');
        var $formatSelect = $('#newDetailFormat');
        var $campaignTypeSelect = $('#newDetailCampaignType');
        var $resultTypeInput = $('#newDetailResultType');
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
        // Objetivos (persistencia)
        if (cachedObjectives && Array.isArray(cachedObjectives) && cachedObjectives.length > 0) {
            var options = '<option value="">-- Selecciona un objetivo --</option>';
            cachedObjectives.forEach(function(obj) {
                var selected = (lastSelectedObjective == obj.id) ? ' selected' : '';
                options += '<option value="' + obj.id + '"' + selected + '>' + obj.name + '</option>';
            });
            $objectiveSelect.html(options).prop('disabled', false);
            if (lastSelectedObjective) $objectiveSelect.val(lastSelectedObjective).trigger('change');
        } else {
            $objectiveSelect.html('<option value="">Cargando objetivos...</option>').prop('disabled', true);
            $.ajax({
                url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                method: 'POST',
                data: { get_objectives: 1 },
                dataType: 'json',
                success: function(objectives) {
                    cachedObjectives = objectives;
                    var options = '<option value="">-- Selecciona un objetivo --</option>';
                    if (Array.isArray(objectives) && objectives.length > 0) {
                        objectives.forEach(function(obj) {
                            var selected = (lastSelectedObjective == obj.id) ? ' selected' : '';
                            options += '<option value="' + obj.id + '"' + selected + '>' + obj.name + '</option>';
                        });
                        $objectiveSelect.html(options).prop('disabled', false);
                        if (lastSelectedObjective) $objectiveSelect.val(lastSelectedObjective).trigger('change');
                    } else {
                        $objectiveSelect.html('<option value="">No hay objetivos</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    $objectiveSelect.html('<option value="">Error al cargar objetivos</option>').prop('disabled', true);
                }
            });
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
        // Tipos de campaña (persistencia)
        if (cachedCampaignTypes && Array.isArray(cachedCampaignTypes) && cachedCampaignTypes.length > 0) {
            var options = '<option value="">-- Selecciona un tipo de campaña --</option>';
            cachedCampaignTypes.forEach(function(type) {
                var selected = (lastSelectedCampaignType == type.id) ? ' selected' : '';
                options += '<option value="' + type.id + '"' + selected + '>' + type.name + '</option>';
            });
            $campaignTypeSelect.html(options).prop('disabled', false);
            if (lastSelectedCampaignType) $campaignTypeSelect.val(lastSelectedCampaignType).trigger('change');
        } else {
            $campaignTypeSelect.html('<option value="">Cargando tipos de campaña...</option>').prop('disabled', true);
            $.ajax({
                url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                method: 'POST',
                data: { get_campaign_types: 1 },
                dataType: 'json',
                success: function(types) {
                    cachedCampaignTypes = types;
                    var options = '<option value="">-- Selecciona un tipo de campaña --</option>';
                    if (Array.isArray(types) && types.length > 0) {
                        types.forEach(function(type) {
                            var selected = (lastSelectedCampaignType == type.id) ? ' selected' : '';
                            options += '<option value="' + type.id + '"' + selected + '>' + type.name + '</option>';
                        });
                        $campaignTypeSelect.html(options).prop('disabled', false);
                        if (lastSelectedCampaignType) $campaignTypeSelect.val(lastSelectedCampaignType).trigger('change');
                    } else {
                        $campaignTypeSelect.html('<option value="">No hay tipos de campaña</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    $campaignTypeSelect.html('<option value="">Error al cargar tipos de campaña</option>').prop('disabled', true);
                }
            });
        }
        // Tipo Resultado (persistencia y lógica)
        if (lastResultTypeValue) {
            $resultTypeInput.val(lastResultTypeValue);
            $resultTypeInput.prop('readonly', !lastResultTypeAuto);
        } else {
            $resultTypeInput.val('');
            $resultTypeInput.prop('readonly', true);
        }
        // Segmentaciones
        renderSegmentaciones('#newDetailSegmentation', []);
    });
    // Cuando cambia la plataforma, carga los formatos correspondientes
    $('#newDetailPlatform').on('change', function () {
        var platformId = $(this).val();
        var $formatSelect = $('#newDetailFormat');
        lastPlatformForFormats = platformId;
        if (!platformId) {
            $formatSelect.html('<option value="">Selecciona una plataforma primero</option>').prop('disabled', true);
            return;
        }
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
    });
    // Cuando cambia el objetivo, llena el campo de tipo resultado con default_result
    $('#newDetailObjective').on('change', function () {
        var selectedId = $(this).val();
        var $resultTypeInput = $('#newDetailResultType');
        var found = null;
        if (cachedObjectives && Array.isArray(cachedObjectives)) {
            found = cachedObjectives.find(function(obj) { return String(obj.id) === String(selectedId); });
        }
        if (found && found.default_result) {
            $resultTypeInput.val(found.default_result);
            $resultTypeInput.prop('readonly', false);
            lastResultTypeAuto = true;
            lastResultTypeValue = found.default_result;
        } else {
            $resultTypeInput.val('');
            $resultTypeInput.prop('readonly', true);
            lastResultTypeAuto = true;
            lastResultTypeValue = '';
        }
    });
    // Si el usuario edita el campo manualmente, deja de ser automático
    $('#newDetailResultType').on('input', function () {
        lastResultTypeValue = $(this).val();
        lastResultTypeAuto = false;
    });
    // Guarda la selección previa al cerrar el modal
    $('#addDetailModal').on('hidden.bs.modal', function () {
        lastSelectedProject = $('#newDetailProject').val();
        lastSelectedObjective = $('#newDetailObjective').val();
        lastSelectedPlatform = $('#newDetailPlatform').val();
        lastSelectedChannel = $('#newDetailChannel').val();
        lastSelectedFormats = $('#newDetailFormat').val() || [];
        lastPlatformForFormats = $('#newDetailPlatform').val();
        lastSelectedCampaignType = $('#newDetailCampaignType').val();
        lastResultTypeValue = $('#newDetailResultType').val();
        lastResultTypeAuto = $('#newDetailResultType').prop('readonly') ? true : false;
    });
    // Guardar detalle
    $('#addDetailModal form').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        // Obtención robusta del ID del mix de medios desde variable global
        var mediamixrealestate_id = typeof window.mmreId !== 'undefined' ? parseInt(window.mmreId) : null;
        var project_id = parseInt($('#newDetailProject').val());
        var channel_id = parseInt($('#newDetailChannel').val());
        var campaign_type_id = parseInt($('#newDetailCampaignType').val());
        var segmentationArr = $('#newDetailSegmentation').val() || [];
        var segmentation = segmentationArr.join(', ');
        var objectives_ids = $('#newDetailObjective').val() ? [parseInt($('#newDetailObjective').val())] : [];
        var result_type = $('#newDetailResultType').val();
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
        if (isNaN(campaign_type_id)) missingFields.push('Tipo de Campaña');
        if (!segmentation) missingFields.push('Segmentación');
        if (!Array.isArray(objectives_ids) || objectives_ids.length === 0 || isNaN(objectives_ids[0])) missingFields.push('Objetivo');
        if (result_type === undefined || result_type === null || result_type === '') missingFields.push('Tipo Resultado');
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
            campaign_type_id: campaign_type_id,
            segmentation: segmentation,
            objectives_ids: objectives_ids,
            result_type: result_type,
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
                var totalAjax = 6;
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
                // Canales
                $.ajax({
                    url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                    method: 'POST',
                    data: { get_channels: 1 },
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
                // Tipos de campaña
                $.ajax({
                    url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                    method: 'POST',
                    data: { get_campaign_types: 1 },
                    dataType: 'json',
                    success: function(types) {
                        var options = '<option value="">-- Selecciona un tipo de campaña --</option>';
                        types.forEach(function(type) {
                            var selected = (data.campaign_type_id == type.id) ? ' selected' : '';
                            options += '<option value="' + type.id + '"' + selected + '>' + type.name + '</option>';
                        });
                        $('#editDetailCampaignType').html(options).prop('disabled', false);
                        showModalIfReady();
                    }
                });
                // Objetivos
                $.ajax({
                    url: 'ajax/mediaMixRealEstateDetails.ajax.php',
                    method: 'POST',
                    data: { get_objectives: 1 },
                    dataType: 'json',
                    success: function(objectives) {
                        var options = '<option value="">-- Selecciona un objetivo --</option>';
                        objectives.forEach(function(obj) {
                            var selected = (data.objectives_ids && data.objectives_ids.includes(obj.id)) ? ' selected' : '';
                            options += '<option value="' + obj.id + '"' + selected + '>' + obj.name + '</option>';
                        });
                        $('#editDetailObjective').html(options).prop('disabled', false);
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
                
                $('#editDetailResultType').val(data.result_type).prop('readonly', false);
                $('#editDetailProjection').val(data.projection);
                $('#editDetailInvestment').val(data.investment);
                $('#editDetailAon').prop('checked', data.aon == 1);
                $('#editDetailComments').val(data.comments);
                $('#editDetailStatus').val(data.state);
                $('#editDetailId').val(data.id);
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
        var campaign_type_id = parseInt($('#editDetailCampaignType').val());
        var segmentationArr = $('#editDetailSegmentation').val() || [];
        var segmentation = segmentationArr.join(', ');
        var objectives_ids = $('#editDetailObjective').val() ? [parseInt($('#editDetailObjective').val())] : [];
        var result_type = $('#editDetailResultType').val();
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
        if (isNaN(campaign_type_id)) missingFields.push('Tipo de Campaña');
        if (!segmentation) missingFields.push('Segmentación');
        if (!Array.isArray(objectives_ids) || objectives_ids.length === 0 || isNaN(objectives_ids[0])) missingFields.push('Objetivo');
        if (result_type === undefined || result_type === null || result_type === '') missingFields.push('Tipo Resultado');
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
            campaign_type_id: campaign_type_id,
            segmentation: segmentation,
            objectives_ids: objectives_ids,
            result_type: result_type,
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
    function generateAndCopyCode(platformCode, clientCode, projectCode) {
        var fullCode = (platformCode || '') + (clientCode || '') + (projectCode || '');
        
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
        
        generateAndCopyCode(platformCode, clientCode, projectCode);
    });
    // Función para exportar tabla a Excel con estilos modernos - VERSIÓN CORREGIDA
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
            var worksheet = workbook.addWorksheet('Detalles Mix');
            
            // Configurar propiedades básicas del documento
            workbook.creator = 'Algoritmo Digital Platform';
            workbook.lastModifiedBy = 'Sistema';
            workbook.created = new Date();
            workbook.modified = new Date();
            
            // Establecer anchos de columna
            var columnWidths = [15, 15, 14, 6, 15, 15, 20, 15, 12, 10, 10, 12];
            columnWidths.forEach(function(width, index) {
                worksheet.getColumn(index + 1).width = width;
            });
            
            // TÍTULO PRINCIPAL
            worksheet.addRow(['INFORMACIÓN DEL MIX DE MEDIOS']);
            worksheet.mergeCells('A1:L1');
            var titleRow = worksheet.getRow(1);
            titleRow.getCell(1).font = { name: 'Arial', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
            titleRow.getCell(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF366092' } };
            titleRow.getCell(1).alignment = { vertical: 'middle', horizontal: 'center' };
            titleRow.height = 30;
            
            worksheet.addRow([]);
            
            // INFORMACIÓN DETALLADA
            var infoRows = [
                ['Mix de Medios:', '', mixName],
                ['Cliente:', '', clientName], 
                ['Período:', '', periodName],
                ['Moneda:', '', currency],
                ['Fecha de Exportación:', '', new Date().toLocaleDateString('es-PE', { timeZone: 'America/Lima' })]
            ];
            
            infoRows.forEach(function(rowData, index) {
                var fullRowData = rowData.concat(new Array(9).fill('')); // Completar hasta 12 columnas
                var row = worksheet.addRow(fullRowData);
                var rowNumber = index + 3;
                
                // Merge etiqueta (columnas A-B)
                worksheet.mergeCells(rowNumber, 1, rowNumber, 2);
                var labelCell = row.getCell(1);
                labelCell.font = { name: 'Arial', size: 11, bold: true };
                labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE7F3FF' } };
                labelCell.alignment = { vertical: 'middle', horizontal: 'left' };
                
                // Merge valor (columnas C-L)
                worksheet.mergeCells(rowNumber, 3, rowNumber, 12);
                var valueCell = row.getCell(3);
                valueCell.font = { name: 'Arial', size: 11 };
                valueCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                
                row.height = 25;
            });
            
            worksheet.addRow([]);
            
            // HEADERS DE LA TABLA
            var headers = [
                'Proyecto', 'Plataforma', 'Objetivo', 'AON', 'Tipo Campaña',
                'Canal', 'Segmentación', 'Formatos', 'Inversión (' + currency + ')',
                'Distribución (%)', 'Estado', 'Proyección'
            ];
            var headerRow = worksheet.addRow(headers);
            
            headerRow.eachCell(function(cell, colNumber) {
                cell.font = { name: 'Arial', size: 9, bold: true, color: { argb: 'FFFFFFFF' } };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF366092' } };
                cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                cell.border = {
                    top: { style: 'thin' }, left: { style: 'thin' },
                    bottom: { style: 'thin' }, right: { style: 'thin' }
                };
            });
            headerRow.height = 35;
            
            // PROCESAR DATOS DE LA TABLA SIMPLIFICADO
            var currentRow = 10; // Empezar después de headers
            
            $('#detailsTable tbody tr').each(function() {
                var $row = $(this);
                var rowData = new Array(12).fill('');
                var isSubtotal = $row.css('background-color') === 'rgb(245, 245, 245)';
                
                if (isSubtotal) {
                    // Fila de subtotal
                    var subtotalValue = $row.find('td:nth-child(9)').text().replace(/[^\d.,]/g, '');
                    rowData[8] = subtotalValue; // Columna I
                    rowData[9] = '100%'; // Columna J
                    
                    var subtotalRow = worksheet.addRow(rowData);
                    
                    // Estilo para subtotal
                    subtotalRow.getCell(9).font = { bold: true };
                    subtotalRow.getCell(9).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF5F5F5' } };
                    subtotalRow.getCell(10).font = { bold: true };
                    subtotalRow.getCell(10).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF5F5F5' } };
                    
                    if (subtotalValue && !isNaN(parseFloat(subtotalValue))) {
                        subtotalRow.getCell(9).value = parseFloat(subtotalValue);
                        subtotalRow.getCell(9).numFmt = currency + ' #,##0.00';
                    }
                    
                } else {
                    // Fila normal de datos
                    var colIndex = 0;
                    $row.find('td').each(function() {
                        var $cell = $(this);
                        
                        // Saltar botones
                        if ($cell.find('.btn').length > 0) return;
                        
                        if (colIndex < 12) {
                            rowData[colIndex] = $cell.text().trim();
                            colIndex++;
                        }
                    });
                    
                    var dataRow = worksheet.addRow(rowData);
                    
                    // Aplicar estilos básicos
                    dataRow.eachCell(function(cell, colNumber) {
                        cell.font = { name: 'Arial', size: 9 };
                        cell.alignment = { vertical: 'middle', wrapText: true };
                        cell.border = {
                            top: { style: 'thin' }, left: { style: 'thin' },
                            bottom: { style: 'thin' }, right: { style: 'thin' }
                        };
                        
                        // Alineación específica por columna
                        if (colNumber === 4) { // AON
                            cell.alignment = { vertical: 'middle', horizontal: 'center' };
                        } else if (colNumber === 9 || colNumber === 10) { // Inversión y %
                            cell.alignment = { vertical: 'middle', horizontal: 'right' };
                        }
                    });
                }
                currentRow++;
            });
            
            // TOTALES - MÉTODO DIRECTO Y SIMPLIFICADO
            worksheet.addRow([]); // Separador
            
            // Extraer totales del HTML visible - MÉTODO MÁS DIRECTO
            var inversionNeta = '0';
            var comisionValue = '0';
            var pautaComision = '0';
            var igvValue = '0';
            var totalFinal = '0';
            
            // Intentar extraer desde elementos específicos primero
            $('.table tr').each(function() {
                var $row = $(this);
                var $cells = $row.find('td');
                
                if ($cells.length >= 2) {
                    var firstText = $cells.first().text().toLowerCase().trim();
                    var lastText = $cells.last().text().trim();
                    var numValue = lastText.replace(/[^\d.,]/g, '');
                    
                    if (firstText.includes('inversión neta total')) {
                        inversionNeta = numValue;
                        console.log('Inversión Neta encontrada:', inversionNeta);
                    } else if (firstText.includes('comisión')) {
                        comisionValue = numValue;
                        console.log('Comisión encontrada:', comisionValue);
                    } else if (firstText.includes('pauta') && firstText.includes('comisión')) {
                        pautaComision = numValue;
                        console.log('Pauta+Comisión encontrada:', pautaComision);
                    } else if (firstText.includes('igv') && !firstText.includes('total')) {
                        igvValue = numValue;
                        console.log('IGV encontrado:', igvValue);
                    } else if (firstText.includes('total') && firstText.includes('igv')) {
                        totalFinal = numValue;
                        console.log('Total Final encontrado:', totalFinal);
                    }
                }
            });
            
            // Si no encontramos valores, calcular manualmente
            if (inversionNeta === '0') {
                console.log('No se encontraron totales en HTML, calculando manualmente...');
                
                var calculatedTotal = 0;
                $('#detailsTable tbody tr').each(function() {
                    var $row = $(this);
                    if ($row.css('background-color') !== 'rgb(245, 245, 245)') {
                        var invCell = $row.find('td:nth-child(9)');
                        if (invCell.length > 0) {
                            var value = parseFloat(invCell.text().replace(/[^\d.,]/g, '')) || 0;
                            calculatedTotal += value;
                        }
                    }
                });
                
                inversionNeta = calculatedTotal.toString();
                
                // Usar variables globales para calcular el resto
                var fee = parseFloat(window.mmreFee) || 0;
                var feeType = window.mmreFeeType || 'percentage';
                var igvPercent = parseFloat(window.mmreIgv) || 18;
                
                var comisionCalc = (feeType === 'fixed') ? fee : (calculatedTotal * fee / 100);
                var pautaCalc = calculatedTotal + comisionCalc;
                var igvCalc = pautaCalc * (igvPercent / 100);
                var totalCalc = pautaCalc + igvCalc;
                
                comisionValue = comisionCalc.toString();
                pautaComision = pautaCalc.toString();
                igvValue = igvCalc.toString();
                totalFinal = totalCalc.toString();
                
                console.log('Totales calculados:', {
                    inversionNeta, comisionValue, pautaComision, igvValue, totalFinal
                });
            }
            
            var comisionType = (window.mmreFeeType === 'fixed') ? '(Valor Fijo)' : '(' + (window.mmreFee || '0') + '%)';
            
            // Agregar totales al Excel
            var totalsData = [
                ['', '', '', '', '', 'Inversión Neta Total:', '', '', inversionNeta],
                ['', '', '', '', '', 'Comisión de Agencia ' + comisionType + ':', '', '', comisionValue],
                ['', '', '', '', '', 'Subtotal (Pauta + Comisión):', '', '', pautaComision],
                ['', '', '', '', '', 'IGV (' + (window.mmreIgv || '18') + '%):', '', '', igvValue],
                ['', '', '', '', '', '', '', '', ''], // Separador
                ['', '', '', '', '', 'TOTAL INVERSIÓN + IGV:', '', '', totalFinal]
            ];
            
            totalsData.forEach(function(rowData, index) {
                var fullRowData = rowData.concat(new Array(3).fill('')); // Completar hasta 12 columnas
                var row = worksheet.addRow(fullRowData);
                var isFinal = rowData[5] && rowData[5].includes('TOTAL INVERSIÓN');
                var hasValue = rowData[5] && rowData[8];
                
                if (hasValue) {
                    // Merge etiqueta (F-H)
                    worksheet.mergeCells(row.number, 6, row.number, 8);
                    var labelCell = row.getCell(6);
                    
                    if (isFinal) {
                        labelCell.font = { name: 'Arial', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
                        labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } };
                        
                        var valueCell = row.getCell(9);
                        valueCell.font = { name: 'Arial', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
                        valueCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } };
                        
                        row.height = 35;
                    } else {
                        labelCell.font = { name: 'Arial', size: 10, bold: true };
                        labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                        
                        var valueCell = row.getCell(9);
                        valueCell.font = { name: 'Arial', size: 10 };
                        valueCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                        
                        row.height = 28;
                    }
                    
                    labelCell.alignment = { vertical: 'middle', horizontal: 'left' };
                    valueCell.alignment = { vertical: 'middle', horizontal: 'right' };
                    
                    // Formato numérico
                    if (rowData[8] && !isNaN(parseFloat(rowData[8]))) {
                        valueCell.value = parseFloat(rowData[8]);
                        valueCell.numFmt = currency + ' #,##0.00';
                    }
                }
            });
            
            // Generar nombre del archivo
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
                    text: 'Error: ' + error.message
                });
            });

        } catch (error) {
            console.error('Error en exportTableToExcel:', error);
            swal({
                icon: 'error',
                title: 'Error al exportar',
                text: 'Error: ' + error.message
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
    
    // REMOVER COMPLETAMENTE el manejo del submit para que funcione tradicionalmente
    // $('#configMixForm').on('submit', function (e) { ... });

    // Nueva función para recalcular totales
    function recalcularTotales() {
        // Obtener información del mix desde las variables globales
        var currency = window.currency || 'USD';
        var fee = window.mmreFee || 0;
        var feeType = window.mmreFeeType || 'percentage';
        var igvPorcentaje = window.mmreIgv || 18;
        
        // Calcular inversión neta total desde la tabla
        var totalInversion = 0;
        $('#detailsTable tbody tr').each(function() {
            var inversionText = $(this).find('td:nth-child(9)').text(); // Columna de inversión
            if (inversionText && !$(this).css('background-color').includes('245, 245, 245')) { // No contar filas de subtotal
                var inversionValue = parseFloat(inversionText.replace(/[^0-9.-]/g, ''));
                if (!isNaN(inversionValue)) {
                    totalInversion += inversionValue;
                }
            }
        });
        
        // Calcular comisión según tipo de fee
        var comision = 0;
        var feeDisplay = '';
        
        if (feeType === 'fixed') {
            comision = parseFloat(fee);
            feeDisplay = '(fijo)';
        } else {
            comision = totalInversion * (parseFloat(fee) / 100);
            feeDisplay = '(' + fee + '%)';
        }
        
        // Calcular pauta + comisión
        var pautaComision = totalInversion + comision;
        
        // Calcular IGV
        var igvCalculado = pautaComision * (parseFloat(igvPorcentaje) / 100);
        
        // Calcular total final
        var inversionTotalIgv = pautaComision + igvCalculado;
        
        // Actualizar los elementos en pantalla
        $('#inversionNetaTotal').html('<strong>' + currency + ' ' + number_format(totalInversion, 2) + '</strong>');
        $('#comisionAgencia').html('<strong>' + currency + ' ' + number_format(comision, 2) + ' <small class="text-muted">' + feeDisplay + '</small></strong>');
        $('#pautaComision').html('<strong>' + currency + ' ' + number_format(pautaComision, 2) + '</strong>');
        $('#igvCalculado').html('<strong>' + currency + ' ' + number_format(igvCalculado, 2) + '</strong>');
        $('#inversionTotalIgv').html('<strong style="color: #00a65a;">' + currency + ' ' + number_format(inversionTotalIgv, 2) + '</strong>');
    }
    
    // Función auxiliar para formatear números
    function number_format(number, decimals) {
        return parseFloat(number).toLocaleString('en-US', {
            minimumFractionDigits: decimals || 2,
            maximumFractionDigits: decimals || 2
        });
    }
    // Recalcular totales cuando se carga la página
    setTimeout(function() {

        if (typeof recalcularTotales === 'function') {
            recalcularTotales();
        }
    }, 500);
    
    // Guardar configuración del mix - SIMPLIFICADO
    $('#configMixForm').on('submit', function (e) {
        // NO preventDefault - dejar que se envíe normalmente
        // El PHP se encarga de todo
        return true;
    });
});