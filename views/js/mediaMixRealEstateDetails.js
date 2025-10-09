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
    // Función para exportar tabla a Excel con estilos modernos
    function exportTableToExcel() {
        // Verificar si la librería ExcelJS está disponible
        if (typeof ExcelJS === 'undefined') {
            swal({
                icon: 'error',
                title: 'Librería no disponible',
                text: 'La librería de Excel no está cargada. Por favor, contacta al administrador.'
            });
            return;
        }
        
        exportTableToExcel();
    }

    function exportTableToExcel() {
        // Obtener datos del mix de medios
        var mixName = $('h1').first().text().trim();
        var clientName = window.clientName || 'Cliente';
        var currency = window.currency || 'USD';
        var periodName = window.periodName || 'Período';
        
        // Crear workbook con ExcelJS
        var workbook = new ExcelJS.Workbook();
        var worksheet = workbook.addWorksheet('Detalles Mix');
        
        // Agregar información general
        worksheet.addRow(['Mix de Medios:', mixName]);
        worksheet.addRow(['Cliente:', clientName]);
        worksheet.addRow(['Período:', periodName]);
        worksheet.addRow(['Moneda:', currency]);
        worksheet.addRow([]); // Fila vacía
        
        // Headers de la tabla
        var headers = [
            'Proyecto', 'Plataforma', 'Objetivo', 'AON', 'Tipo Campaña',
            'Canal', 'Segmentación', 'Formatos', 'Inversión (' + currency + ')',
            'Distribución (%)', 'Estado', 'Proyección'
        ];
        var headerRow = worksheet.addRow(headers);
        
        // Crear matriz más robusta para mapear la estructura de la tabla
        var tableMatrix = [];
        var mergeInfo = [];
        var subtotalRows = []; // Para marcar filas de subtotales
        var currentRowIndex = 0;
        
        // Procesar cada fila de la tabla HTML
        $('#detailsTable tbody tr').each(function() {
            var $row = $(this);
            
            // Identificar fila de totales/subtotales
            if ($row.css('background-color') === 'rgb(245, 245, 245)' || $row.find('td').first().attr('colspan')) {
                // Esta es una fila de subtotal
                var subtotalRowData = new Array(12).fill('');
                var colIndex = 0;
                
                $row.find('td').each(function() {
                    var $cell = $(this);
                    var colspan = parseInt($cell.attr('colspan')) || 1;
                    var cellText = $cell.text().trim();
                    
                    // Llenar las celdas según el colspan (solo primeras 12 columnas)
                    for (var i = 0; i < colspan && colIndex < 12; i++) {
                        if (i === 0) {
                            subtotalRowData[colIndex] = cellText;
                        } else {
                            subtotalRowData[colIndex] = '';
                        }
                        colIndex++;
                    }
                });
                
                tableMatrix[currentRowIndex] = subtotalRowData;
                
                // Marcar esta fila como subtotal para aplicar estilos especiales
                subtotalRows.push(currentRowIndex + 7); // +7 por las filas de info general y header
                
                currentRowIndex++;
                return;
            }
            
            // Inicializar fila en la matriz si no existe
            if (!tableMatrix[currentRowIndex]) {
                tableMatrix[currentRowIndex] = new Array(12).fill(null);
            }
            
            var actualColumn = 0;
            
            $row.find('td').each(function() {
                var $cell = $(this);
                var colspan = parseInt($cell.attr('colspan')) || 1;
                var rowspan = parseInt($cell.attr('rowspan')) || 1;
                var cellText = $cell.text().trim();
                
                // Encontrar la próxima celda disponible en esta fila
                while (actualColumn < 12 && tableMatrix[currentRowIndex][actualColumn] !== null) {
                    actualColumn++;
                }
                
                // Saltar la columna de acciones (última columna de la tabla HTML)
                if (actualColumn >= 12 || $cell.hasClass('actions-column') || $cell.find('.btn').length > 0) {
                    return; // No procesar esta celda
                }
                
                // Marcar todas las celdas que ocupa esta celda (incluidas las combinadas)
                for (var r = 0; r < rowspan; r++) {
                    for (var c = 0; c < colspan; c++) {
                        var targetRow = currentRowIndex + r;
                        var targetCol = actualColumn + c;
                        
                        // Asegurar que la fila existe en la matriz
                        while (tableMatrix.length <= targetRow) {
                            tableMatrix.push(new Array(12).fill(null));
                        }
                        
                        if (targetCol < 12) {
                            // Solo poner el texto en la primera celda de la combinación
                            if (r === 0 && c === 0) {
                                tableMatrix[targetRow][targetCol] = cellText;
                            } else {
                                tableMatrix[targetRow][targetCol] = ''; // Marcar como ocupada pero vacía
                            }
                        }
                    }
                }
                
                // Registrar información de combinación si es necesario
                if ((rowspan > 1 || colspan > 1) && actualColumn < 12) {
                    mergeInfo.push({
                        startRow: currentRowIndex + 7, // +7 porque empezamos desde la fila 7 en Excel
                        endRow: currentRowIndex + rowspan - 1 + 7,
                        startCol: actualColumn + 1, // Excel usa índices basados en 1
                        endCol: actualColumn + colspan,
                        value: cellText
                    });
                }
                
                actualColumn += colspan;
            });
            
            currentRowIndex++;
        });
        
        // Convertir matriz a formato Excel (reemplazar null con '')
        var excelData = [];
        tableMatrix.forEach(function(row) {
            var cleanRow = [];
            for (var i = 0; i < 12; i++) {
                cleanRow.push(row[i] === null ? '' : row[i]);
            }
            excelData.push(cleanRow);
        });
        
        // Agregar todas las filas a Excel
        excelData.forEach(function(row) {
            worksheet.addRow(row);
        });
        
        // Aplicar las combinaciones de celdas
        mergeInfo.forEach(function(merge) {
            try {
                // Solo combinar si realmente hay más de una celda
                if (merge.startRow !== merge.endRow || merge.startCol !== merge.endCol) {
                    worksheet.mergeCells(merge.startRow, merge.startCol, merge.endRow, merge.endCol);
                    
                    // Asegurar que el valor esté en la celda combinada
                    var cell = worksheet.getCell(merge.startRow, merge.startCol);
                    if (merge.value && merge.value.trim() !== '') {
                        cell.value = merge.value;
                        cell.alignment = { vertical: 'middle', horizontal: 'center' };
                    }
                }
            } catch (e) {
                console.log('Error al combinar celdas:', merge, e);
            }
        });
        
        // APLICAR ESTILOS MODERNOS
        
        // Estilo para información general (filas 1-4)
        for (var i = 1; i <= 4; i++) {
            var row = worksheet.getRow(i);
            row.getCell(1).font = { name: 'Segoe UI', size: 9, bold: true, color: { argb: 'FF1F4E79' } };
            row.getCell(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE7F3FF' } };
            row.getCell(1).alignment = { vertical: 'middle', horizontal: 'left', wrapText: true };
            row.getCell(1).border = {
                top: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                left: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                bottom: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                right: { style: 'thin', color: { argb: 'FFB4C7E7' } }
            };
            
            row.getCell(2).font = { name: 'Segoe UI', size: 9, color: { argb: 'FF2F5F8F' } };
            row.getCell(2).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FBFF' } };
            row.getCell(2).alignment = { vertical: 'middle', horizontal: 'left', wrapText: true };
            row.getCell(2).border = {
                top: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                left: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                bottom: { style: 'thin', color: { argb: 'FFB4C7E7' } },
                right: { style: 'thin', color: { argb: 'FFB4C7E7' } }
            };
            
            // Altura automática para información general
            row.height = 25; // Un poco más de altura base para wrap text
        }
        
        // Estilo para headers (fila 6)
        headerRow.eachCell(function(cell, colNumber) {
            cell.font = { name: 'Segoe UI', size: 8, bold: true, color: { argb: 'FFFFFFFF' } };
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1F4E79' } };
            cell.border = {
                top: { style: 'medium', color: { argb: 'FF1F4E79' } },
                left: { style: 'thin', color: { argb: 'FFFFFFFF' } },
                bottom: { style: 'medium', color: { argb: 'FF1F4E79' } },
                right: { style: 'thin', color: { argb: 'FFFFFFFF' } }
            };
            cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
        });
        headerRow.height = 30; // Más altura para headers con wrap text
        
        // Función para calcular altura de fila basada en contenido
        function calculateRowHeight(rowData, columnWidths, fontSize) {
            // Validación de entrada
            if (!rowData || !Array.isArray(rowData) || !columnWidths || !Array.isArray(columnWidths)) {
                return 20; // Altura base por defecto
            }
            
            var maxLines = 1;
            var avgCharWidth = fontSize * 0.6; // Aproximación del ancho de carácter
            
            for (var i = 0; i < rowData.length && i < columnWidths.length; i++) {
                var cellValue = rowData[i];
                if (cellValue && typeof cellValue === 'string' && cellValue.length > 0) {
                    var cellWidth = columnWidths[i] * 7; // Convertir ancho de columna a píxeles aproximados
                    var textLength = cellValue.length;
                    var estimatedWidth = textLength * avgCharWidth;
                    var linesNeeded = Math.ceil(estimatedWidth / cellWidth);
                    maxLines = Math.max(maxLines, linesNeeded);
                }
            }
            
            // Altura base + altura adicional por línea extra
            var baseHeight = 18;
            var lineHeight = 12;
            return Math.max(baseHeight, baseHeight + ((maxLines - 1) * lineHeight));
        }
        
        // Establecer ancho de columnas ANTES de calcular alturas
        var columnWidths = [15, 15, 14, 4, 15, 15, 19, 15, 14, 12, 7, 14];
        columnWidths.forEach(function(width, index) {
            worksheet.getColumn(index + 1).width = width;
        });
        
        // Estilo para datos (desde fila 7) con altura automática
        for (var i = 7; i <= worksheet.rowCount; i++) {
            var row = worksheet.getRow(i);
            var isEvenRow = (i - 7) % 2 === 0;
            var isSubtotalRow = subtotalRows.includes(i);
            
            // Obtener datos de la fila para calcular altura - MEJORADO
            var rowDataForHeight = [];
            for (var col = 1; col <= 12; col++) {
                var cell = row.getCell(col);
                rowDataForHeight.push(cell.value ? String(cell.value) : '');
            }
            
            row.eachCell(function(cell, colNumber) {
                var bgColor, textColor, font, border;
                
                if (isSubtotalRow) {
                    // ESTILO ESPECIAL PARA SUBTOTALES
                    bgColor = 'FF2C3E50'; // Azul oscuro corporativo
                    textColor = 'FFFFFFFF'; // Texto blanco
                    font = { name: 'Segoe UI', size: 9, bold: true, color: { argb: textColor } };
                    
                    // Bordes más gruesos para "cortar" visualmente
                    border = {
                        top: { style: 'medium', color: { argb: 'FF1F4E79' } },
                        bottom: { style: 'medium', color: { argb: 'FF1F4E79' } },
                        left: { style: 'thin', color: { argb: 'FF34495E' } },
                        right: { style: 'thin', color: { argb: 'FF34495E' } }
                    };
                    
                    // Alineación especial para subtotales
                    if (colNumber === 9 || colNumber === 10) { // Inversión y Distribución
                        cell.alignment = { vertical: 'middle', horizontal: 'right', wrapText: true };
                    } else {
                        cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                    }
                    
                } else {
                    // ESTILO NORMAL PARA DATOS
                    bgColor = isEvenRow ? 'FFF8F9FA' : 'FFFFFFFF';
                    textColor = 'FF333333';
                    
                    // Colores especiales por columna
                    if (colNumber === 4 && cell.value === 'Sí') { // AON
                        bgColor = 'FFE8F5E8';
                        textColor = 'FF2E7D2E';
                    } else if (colNumber === 11) { // Estado
                        if (cell.value === 'Activa') {
                            bgColor = 'FFE8F5E8';
                            textColor = 'FF2E7D2E';
                        } else if (cell.value === 'Suspendida') {
                            bgColor = 'FFFFE8E8';
                            textColor = 'FFD63384';
                        } else if (cell.value === 'Por confirmar') {
                            bgColor = 'FFFFF3CD';
                            textColor = 'FF856404';
                        }
                    }
                    
                    // Verificar si esta celda está combinada y tiene contenido
                    var isMergedWithContent = mergeInfo.some(function(merge) {
                        return i === merge.startRow && colNumber === merge.startCol && merge.value && merge.value.trim() !== '';
                    });
                    
                    // Estilo especial para celdas combinadas de proyecto y plataforma
                    if ((colNumber === 1 || colNumber === 2) && isMergedWithContent) {
                        font = { name: 'Segoe UI', size: 8, bold: true, color: { argb: textColor } };
                        cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                    } else {
                        font = { name: 'Segoe UI', size: 8, color: { argb: textColor } };
                        
                        // Alineación según el tipo de columna - TODAS CON WRAP TEXT
                        if (colNumber === 1 || colNumber === 2 || colNumber === 4 || colNumber === 11) { // Proyecto, Plataforma, AON, Estado - centradas
                            cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                        } else if (colNumber === 9 || colNumber === 10) { // Inversión y Distribución
                            cell.alignment = { vertical: 'middle', horizontal: 'right', wrapText: true };
                        } else {
                            cell.alignment = { vertical: 'middle', horizontal: 'left', wrapText: true };
                        }
                    }
                    
                    // Bordes normales
                    border = {
                        top: { style: 'thin', color: { argb: 'FFE1E5E9' } },
                        left: { style: 'thin', color: { argb: 'FFE1E5E9' } },
                        bottom: { style: 'thin', color: { argb: 'FFE1E5E9' } },
                        right: { style: 'thin', color: { argb: 'FFE1E5E9' } }
                    };
                }
                
                // Aplicar estilos
                cell.font = font;
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: bgColor } };
                cell.border = border;
                
                // Formato numérico
                if (colNumber === 9) { // Inversión
                    cell.numFmt = '#,##0.00';
                } else if (colNumber === 10) { // Distribución
                    cell.numFmt = '0.00"%"';
                }
            });
            
            // Calcular y aplicar altura automática - CON VALIDACIÓN
            try {
                if (isSubtotalRow) {
                    // Para subtotales, un poco más de altura base
                    var subtotalHeight = calculateRowHeight(rowDataForHeight, columnWidths, 9);
                    row.height = Math.max(subtotalHeight, 25); // Mínimo 25 para subtotales
                } else {
                    // Para datos normales, calcular según contenido
                    var dataHeight = calculateRowHeight(rowDataForHeight, columnWidths, 8);
                    row.height = Math.max(dataHeight, 20); // Mínimo 20 para datos
                }
            } catch (error) {
                console.log('Error calculating row height:', error);
                // Altura por defecto en caso de error
                row.height = isSubtotalRow ? 25 : 20;
            }
        }

        // **AQUÍ AGREGAMOS LA SECCIÓN DE TOTALES SIN MODIFICAR LO ANTERIOR**
        
        // SECCIÓN DE TOTALES PROFESIONALES
        var totalsStartRow = worksheet.rowCount + 3;
        
        // Título de totales
        worksheet.addRow([]);
        worksheet.addRow([]);
        worksheet.addRow(['RESUMEN FINANCIERO']);
        var totalsTitle = worksheet.getRow(totalsStartRow);
        worksheet.mergeCells(totalsStartRow, 1, totalsStartRow, 12);
        totalsTitle.getCell(1).font = { name: 'Segoe UI', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
        totalsTitle.getCell(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } };
        totalsTitle.getCell(1).alignment = { vertical: 'middle', horizontal: 'center' };
        totalsTitle.height = 30;
        
        worksheet.addRow([]);
        
        // Obtener totales calculados del HTML
        var inversionNeta = $('#inversionNetaTotal').text().replace(/[^\d.,]/g, '') || '0';
        var comisionText = $('#comisionAgencia').text();
        var comisionValue = comisionText.replace(/[^\d.,]/g, '') || '0';
        var comisionType = comisionText.includes('(fijo)') ? '(Valor Fijo)' : '(' + window.mmreFee + '%)';
        var pautaComision = $('#pautaComision').text().replace(/[^\d.,]/g, '') || '0';
        var igvValue = $('#igvCalculado').text().replace(/[^\d.,]/g, '') || '0';
        var totalFinal = $('#inversionTotalIgv').text().replace(/[^\d.,]/g, '') || '0';
        
        // Estructura de totales en dos columnas
        var totalsData = [
            ['', '', 'CONCEPTOS', '', '', 'IMPORTES', '', ''],
            ['', '', 'Inversión Neta Total', '', '', inversionNeta, '', ''],
            ['', '', 'Comisión de Agencia ' + comisionType, '', '', comisionValue, '', ''],
            ['', '', 'Subtotal (Pauta + Comisión)', '', '', pautaComision, '', ''],
            ['', '', 'IGV (' + window.mmreIgv + '%)', '', '', igvValue, '', ''],
            ['', '', '', '', '', '', '', ''],
            ['', '', 'TOTAL INVERSIÓN + IGV', '', '', totalFinal, '', '']
        ];
        
        totalsData.forEach(function(rowData, index) {
            var row = worksheet.addRow(rowData);
            var rowNum = totalsStartRow + 2 + index;
            
            if (index === 0) { // Header de totales
                worksheet.mergeCells(rowNum, 3, rowNum, 5);
                worksheet.mergeCells(rowNum, 6, rowNum, 8);
                row.getCell(3).font = { name: 'Segoe UI', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
                row.getCell(6).font = { name: 'Segoe UI', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
                row.getCell(3).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF17A2B8' } };
                row.getCell(6).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF17A2B8' } };
                row.getCell(3).alignment = { vertical: 'middle', horizontal: 'center' };
                row.getCell(6).alignment = { vertical: 'middle', horizontal: 'center' };
                row.height = 25;
            } else if (index === totalsData.length - 2) { // Línea separadora
                // Fila vacía con borde superior
                for (var col = 3; col <= 8; col++) {
                    row.getCell(col).border = { top: { style: 'medium', color: { argb: 'FF28A745' } } };
                }
            } else if (index === totalsData.length - 1) { // Total final
                worksheet.mergeCells(rowNum, 3, rowNum, 5);
                worksheet.mergeCells(rowNum, 6, rowNum, 8);
                row.getCell(3).font = { name: 'Segoe UI', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
                row.getCell(6).font = { name: 'Segoe UI', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
                row.getCell(3).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } };
                row.getCell(6).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } };
                row.getCell(3).alignment = { vertical: 'middle', horizontal: 'center' };
                row.getCell(6).alignment = { vertical: 'middle', horizontal: 'right' };
                row.height = 30;
                
                // Formato de moneda para el total
                if (totalFinal) {
                    row.getCell(6).value = parseFloat(totalFinal.replace(/,/g, ''));
                    row.getCell(6).numFmt = currency + ' #,##0.00';
                }
            } else if (rowData[2] && rowData[5]) { // Filas de datos
                worksheet.mergeCells(rowNum, 3, rowNum, 5);
                worksheet.mergeCells(rowNum, 6, rowNum, 8);
                
                // Estilos alternos para mejor legibilidad
                var bgColor = index % 2 === 1 ? 'FFF8F9FA' : 'FFFFFFFF';
                var isSubtotal = rowData[2].includes('Subtotal');
                
                if (isSubtotal) {
                    bgColor = 'FFECF0F1';
                    row.getCell(3).font = { name: 'Segoe UI', size: 11, bold: true, color: { argb: 'FF2C3E50' } };
                    row.getCell(6).font = { name: 'Segoe UI', size: 11, bold: true, color: { argb: 'FF2C3E50' } };
                } else {
                    row.getCell(3).font = { name: 'Segoe UI', size: 10, color: { argb: 'FF495057' } };
                    row.getCell(6).font = { name: 'Segoe UI', size: 10, color: { argb: 'FF495057' } };
                }
                
                row.getCell(3).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: bgColor } };
                row.getCell(6).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: bgColor } };
                row.getCell(3).alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };
                row.getCell(6).alignment = { vertical: 'middle', horizontal: 'right' };
                
                // Bordes suaves
                for (var col = 3; col <= 8; col++) {
                    row.getCell(col).border = {
                        top: { style: 'thin', color: { argb: 'FFDEE2E6' } },
                        bottom: { style: 'thin', color: { argb: 'FFDEE2E6' } },
                        left: col === 3 ? { style: 'thin', color: { argb: 'FFDEE2E6' } } : undefined,
                        right: col === 8 ? { style: 'thin', color: { argb: 'FFDEE2E6' } } : undefined
                    };
                }
                
                // Formato numérico
                if (rowData[5] && !isNaN(parseFloat(rowData[5].replace(/,/g, '')))) {
                    row.getCell(6).value = parseFloat(rowData[5].replace(/,/g, ''));
                    row.getCell(6).numFmt = currency + ' #,##0.00';
                }
                
                row.height = 22;
            }
        });
        
        // Generar nombre de archivo
        var fileName = 'Mix_Medios_' + mixName.replace(/[^a-zA-Z0-9]/g, '_') + '_' + 
                      new Date().toISOString().slice(0,10) + '.xlsx';
        
        // Descargar archivo
        workbook.xlsx.writeBuffer().then(function(buffer) {
            var blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            a.click();
            window.URL.revokeObjectURL(url);
            
            swal({
                icon: 'success',
                title: '¡Excel exportado exitosamente!',
                text: 'El archivo incluye detalles completos y resumen financiero profesional.',
                timer: 3000
            });
        }).catch(function(error) {
            console.error('Error al generar Excel:', error);
            swal({
                icon: 'error',
                title: 'Error al generar Excel',
                text: 'Hubo un problema al crear el archivo Excel.'
            });
        });
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

    // Recalcular totales cuando se carga la página
    setTimeout(function() {
        if (typeof recalcularTotales === 'function') {
            recalcularTotales();
        }
    }, 500);
});