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
            // Incrementé I (índice 8) y J (índice 9) para mejor visualización de montos y %
            var columnWidths = [15, 15, 14, 6, 15, 15, 20, 15, 18, 14, 10, 12, 13];
            columnWidths.forEach(function(width, index) {
                worksheet.getColumn(index + 1).width = width;
            });
            
            // SECCIÓN 1: TÍTULO PRINCIPAL
            worksheet.addRow(['INFORMACIÓN DEL MIX DE MEDIOS']);
            worksheet.mergeCells('A1:M1'); // Cambiado de L1 a M1
            var titleRow = worksheet.getRow(1);
            titleRow.getCell(1).font = { name: 'Arial', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
            titleRow.getCell(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF366092' } };
            titleRow.getCell(1).alignment = { vertical: 'middle', horizontal: 'center' };
            titleRow.height = 30;
            
            worksheet.addRow([]);
            
            // SECCIÓN 2: INFORMACIÓN DETALLADA OCUPANDO TODO EL ANCHO
            // Crear filas que ocupen las 13 columnas (antes 12)
            var infoRows = [
                ['Mix de Medios:', '', mixName, '', '', '', '', '', '', '', '', '', ''],
                ['Cliente:', '', clientName, '', '', '', '', '', '', '', '', '', ''], 
                ['Período:', '', periodName, '', '', '', '', '', '', '', '', '', ''],
                ['Moneda:', '', currency, '', '', '', '', '', '', '', '', '', ''],
                ['Fecha de Exportación:', '', new Date().toLocaleDateString('es-PE', { timeZone: 'America/Lima' }), '', '', '', '', '', '', '', '', '', '']
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
                
                // Merge del valor (columnas C-M)
                worksheet.mergeCells(rowNumber, 3, rowNumber, 13); // Cambiado de 12 a 13
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
                'Proyecto', 'Plataforma', 'Objetivo', 'AON', 'Tipo Campaña',
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
                var excelRowData = new Array(13).fill('');
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
                    if (colIndex >= 13) {
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
                            if (colIndex + c < 13) {
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
                
                // Verificar si es fila de subtotal por el patrón exacto:
                // - Las primeras 8 columnas vacías (debido a colspan="8")
                // - Columna 9 con formato de moneda USD/PEN
                // - Columna 10 con "100%"
                var emptyColumns = 0;
                for (var i = 0; i < 8; i++) {
                    if (!rowData[i] || rowData[i] === '' || rowData[i] === null) {
                        emptyColumns++;
                    }
                }
                
                if (emptyColumns === 8 && 
                    rowData[8] && 
                    rowData[8].match(/^[A-Z]{3}\s[\d,]+\.?\d*$/) && 
                    rowData[9] === '100%') {
                    
                    isSubtotalRow = true;
                    console.log('✅ SUBTOTAL CORRECTO detectado en fila ' + index + ': ' + rowData[8]);
                }
                
                if (isSubtotalRow) {
                    // Estilo especial para filas de subtotal
                    excelRow.eachCell(function(cell, colNumber) {
                        if (colNumber <= 13) { // Asegurar que procese hasta la columna M
                            if (colNumber === 9) { // Columna I - Solo el monto del subtotal
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
                                var subtotalValue = parseFloat(rowData[8].replace(/,/g, ''));
                                if (!isNaN(subtotalValue)) {
                                    cell.value = subtotalValue;
                                    cell.numFmt = '"' + currency + '" #,##0.00';
                                }
                            } else if (colNumber === 10) { // Columna J - Distribución 100%
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
                        if (colNumber === 9 || colNumber === 10 || colNumber === 13) {
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

                        // Agregar CPR en columna 13 
                        if (colNumber === 13) {
                            var investment = parseFloat(String(rowData[8]).replace(/[^0-9.-]/g, ''));
                            var projection = parseFloat(String(rowData[11]).replace(/[^0-9.-]/g, ''));
                            var objective = rowData[2];
                            
                            if (!isNaN(investment) && !isNaN(projection) && projection > 0) {
                                var cpr = investment / projection;
                                if (objective && objective.toLowerCase() === 'alcance') {
                                    cpr *= 1000;
                                    cell.value = parseFloat(cpr.toFixed(2));
                                    cell.numFmt = '#,##0.00" CPM"';
                                } else {
                                    cell.value = parseFloat(cpr.toFixed(2));
                                    cell.numFmt = '#,##0.00" CPR"';
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
            var separatorRow = worksheet.addRow(['', '', '', '', '', '', '', '', '', '', '', '']);
            separatorRow.height = 15;
            
            // Obtener totales del HTML - USAR EL VALOR QUE YA CALCULÓ PHP EN LA PÁGINA
            var inversionNeta = '0';
            var comisionValue = '0';
            var pautaComision = '0';
            var igvValue = '0';
            var totalFinal = '0';
            
            // Leer directamente de la celda HTML que muestra "Inversión Neta Total"
            var inversionText = $('#inversionNetaTotal').text() || '';
            // Extraer número (el texto contiene moneda + número, ej. "USD 1,234.00")
            var inversionNumber = parseFloat(String(inversionText).replace(/[^0-9\.\-\,]/g, '').replace(/,/g, '')) || 0;
            
            // Variables globales de configuración
            var fee = parseFloat(window.mmreFee) || 0;
            var feeType = window.mmreFeeType || 'percentage';
            var igvPercent = parseFloat(window.mmreIgv) || 18;
            
            // Calcular comision según tipo
            var calculatedComision = (feeType === 'fixed') ? parseFloat(fee) : (inversionNumber * fee / 100);
            // Calcular pauta, igv y total final
            var calculatedPauta = inversionNumber + calculatedComision;
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
            console.log('Totales (desde HTML):', {
                inversionNumber: inversionNumber,
                calculatedComision: calculatedComision,
                calculatedPauta: calculatedPauta,
                calculatedIgv: calculatedIgv,
                calculatedFinal: calculatedFinal,
                fee: fee,
                feeType: feeType,
                igvPercent: igvPercent
            });
            
            // Estructura de totales generales únicamente (columnas F-H para etiquetas)
            var totalsData = [
                ['', '', '', '', '', 'Inversión Neta Total:', '', '', inversionNeta, '', '', ''],
                ['', '', '', '', '', 'Comisión de Agencia ' + comisionType + ':', '', '', comisionValue, '', '', ''],
                ['', '', '', '', '', 'Subtotal (Pauta + Comisión):', '', '', pautaComision, '', '', ''],
                ['', '', '', '', '', 'IGV (' + igvPercent + '%):', '', '', igvValue, '', '', ''],
                ['', '', '', '', '', '', '', '', '', '', '', ''], // Fila vacía
                ['', '', '', '', '', 'TOTAL INVERSIÓN + IGV:', '', '', totalFinal, '', '', '']
            ];
            
            totalsData.forEach(function(rowData, index) {
                var row = worksheet.addRow(rowData);
                var isFinalTotal = rowData[5] && rowData[5].includes('TOTAL INVERSIÓN + IGV');
                var isGeneralTotal = rowData[5] && (rowData[5].includes('Inversión Neta') || rowData[5].includes('Comisión') || rowData[5].includes('Subtotal (Pauta') || rowData[5].includes('IGV'));
                
                if (isFinalTotal) { // Total final
                    // Merge etiqueta (columnas F-H)
                    worksheet.mergeCells(row.number, 6, row.number, 8);
                    var labelCell = row.getCell(6);
                    labelCell.font = { name: 'Arial', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
                    labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } };
                    labelCell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                    labelCell.border = {
                        top: { style: 'medium', color: { argb: 'FF28A745' } },
                        left: { style: 'medium', color: { argb: 'FF28A745' } },
                        bottom: { style: 'medium', color: { argb: 'FF28A745' } },
                        right: { style: 'medium', color: { argb: 'FF28A745' } }
                    };
                    
                    // Valor en columna I (numérico con formato)
                    var numericTotal = parseFloat(totalFinal) || 0;
                    row.getCell(9).value = numericTotal;
                    row.getCell(9).numFmt = '"' + currency + '" #,##0.00';
                    row.getCell(9).font = { name: 'Arial', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
                    row.getCell(9).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } };
                    row.getCell(9).alignment = { vertical: 'middle', horizontal: 'right', wrapText: true };
                    row.getCell(9).border = {
                        top: { style: 'medium', color: { argb: 'FF28A745' } },
                        left: { style: 'medium', color: { argb: 'FF28A745' } },
                        bottom: { style: 'medium', color: { argb: 'FF28A745' } },
                        right: { style: 'medium', color: { argb: 'FF28A745' } }
                    };
                    
                    row.height = 35;
                    
                } else if (isGeneralTotal) { // Totales generales
                    var isSubtotal = rowData[5].includes('Subtotal (Pauta');
                    
                    // Merge etiqueta (columnas F-H)
                    worksheet.mergeCells(row.number, 6, row.number, 8);
                    var labelCell = row.getCell(6);
                    if (isSubtotal) {
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
                    
                    // Valor en columna I (numérico)
                    var numericVal = parseFloat(rowData[8]) || 0;
                    row.getCell(9).value = numericVal;
                    row.getCell(9).numFmt = '"' + currency + '" #,##0.00';
                    if (isSubtotal) {
                        row.getCell(9).font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FF2F5F8F' } };
                        row.getCell(9).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFECF0F1' } };
                    } else {
                        row.getCell(9).font = { name: 'Arial', size: 9, color: { argb: 'FF2F5F8F' } };
                        row.getCell(9).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
                    }
                    row.getCell(9).alignment = { vertical: 'middle', horizontal: 'right', wrapText: true };
                    row.getCell(9).border = {
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