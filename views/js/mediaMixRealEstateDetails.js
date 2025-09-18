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

function renderSegmentaciones(selectId, selectedValue) {
    var options = '<option value="">-- Selecciona segmentación --</option>';
    var segs = segmentaciones.slice();
    var found = false;
    if (selectedValue) {
        segs.forEach(function(seg) {
            if (seg === selectedValue) found = true;
        });
        if (!found) segs.push(selectedValue);
    }
    segs.forEach(function(seg) {
        var selected = (seg === selectedValue) ? ' selected' : '';
        options += '<option value="' + seg + '"' + selected + '>' + seg + '</option>';
    });
    $(selectId).html(options);
    if (selectedValue) {
        $(selectId).val(selectedValue);
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
        renderSegmentaciones('#newDetailSegmentation', null);
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
        var segmentation = $('#newDetailSegmentation').val();
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
                // Otros campos (estos no dependen de AJAX)
                $('#editDetailSegmentation').val(data.segmentation);
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
});