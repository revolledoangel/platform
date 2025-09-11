$(document).ready(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const mediaMixId = urlParams.get('mediaMixId');

    if (!mediaMixId) return;

    // Inicializar DataTable
    $('#detailsTable').DataTable({
        ajax: `ajax/mediaMixRealEstateDetails.ajax.php?action=list&mediaMixId=${mediaMixId}`,
        // ... resto de tu configuración ...
    });

    // LÓGICA PARA CARGAR DATOS EN EL MODAL
    $('#addDetailModal').on('show.bs.modal', function () {
        
        // CORRECCIÓN: Leemos el clientId desde el atributo data-* del modal
        const clientId = $(this).data('client-id'); 

        // Si no hay clientId, no continuamos
        if (!clientId) {
            console.error("No se pudo obtener el client-id desde el modal.");
            return;
        }

        // Cargar Proyectos (filtrados por cliente)
        const projectSelect = $('#newDetailProject');
        projectSelect.html('<option>Cargando proyectos...</option>');
        $.ajax({
            url: 'ajax/projects.ajax.php',
            type: 'POST',
            data: { clientId: clientId }, // Ahora enviamos el ID correcto
            dataType: 'json',
            success: function(projects) {
                let options = '<option value="">-- Selecciona un proyecto --</option>';
                if (projects && projects.length > 0) {
                    projects.forEach(project => {
                        options += `<option value="${project.id}">${project.name}</option>`;
                    });
                } else {
                    options = '<option value="">No hay proyectos para este cliente</option>';
                }
                projectSelect.html(options);
            },
            error: function() {
                projectSelect.html('<option value="">Error al cargar proyectos</option>');
            }
        });

        // Cargar Plataformas (lista completa)
        const platformSelect = $('#newDetailPlatform');
        platformSelect.html('<option>Cargando plataformas...</option>');
        $.ajax({
            url: 'ajax/platforms.ajax.php',
            type: 'POST',
            data: { action: 'list' },
            dataType: 'json',
            success: function(platforms) {
                let options = '<option value="">-- Selecciona una plataforma --</option>';
                if (platforms && platforms.length > 0) {
                    platforms.forEach(platform => {
                        options += `<option value="${platform.id}">${platform.name}</option>`;
                    });
                }
                platformSelect.html(options);
            },
            error: function() {
                 platformSelect.html('<option value="">Error al cargar plataformas</option>');
            }
        });
        
        // Cargar Canales (lista completa)
        const channelSelect = $('#newDetailChannel');
        channelSelect.html('<option>Cargando canales...</option>');
        $.ajax({
            url: 'ajax/channels.ajax.php',
            type: 'GET',
            data: { action: 'list' },
            dataType: 'json',
            success: function(response) {
                let options = '<option value="">-- Selecciona un canal --</option>';
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(row) {
                        // row[1] es el nombre
                        options += `<option value="${row[1]}">${row[1]}</option>`;
                    });
                } else {
                    options = '<option value="">No hay canales disponibles</option>';
                }
                channelSelect.html(options);
            },
            error: function() {
                channelSelect.html('<option value="">Error al cargar canales</option>');
            }
        });

        // Cargar Tipos de Campaña (lista completa)
        const campaignTypeSelect = $('#newDetailCampaignType');
        campaignTypeSelect.html('<option>Cargando tipos de campaña...</option>');
        $.ajax({
            url: 'ajax/campaignTypes.ajax.php',
            type: 'GET',
            data: { action: 'list' },
            dataType: 'json',
            success: function(response) {
                let options = '<option value="">-- Selecciona un tipo de campaña --</option>';
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(row) {
                        // row[1] es el nombre
                        options += `<option value="${row[1]}">${row[1]}</option>`;
                    });
                } else {
                    options = '<option value="">No hay tipos de campaña disponibles</option>';
                }
                campaignTypeSelect.html(options);
            },
            error: function() {
                campaignTypeSelect.html('<option value="">Error al cargar tipos de campaña</option>');
            }
        });

        // Cargar Formatos según la plataforma seleccionada
        const formatSelect = $('#newDetailFormat');
        $('#newDetailPlatform').on('change', function () {
            const platformId = $(this).val();
            if (!platformId) {
                formatSelect.html('<option value="">-- Selecciona una plataforma primero --</option>').prop('disabled', true);
                return;
            }
            formatSelect.html('<option value="">Cargando formatos...</option>').prop('disabled', true);
            $.ajax({
                url: `https://algoritmo.digital/backend/public/api/platforms/${platformId}/formats`,
                type: 'POST',
                headers: { 'Accept': 'application/json' },
                success: function(response) {
                    let options = '';
                    if (response.success && Array.isArray(response.formats)) {
                        response.formats.forEach(format => {
                            options += `<option value="${format.id}">${format.name} (${format.code})</option>`;
                        });
                        formatSelect.html(options).prop('disabled', false);
                    } else {
                        formatSelect.html('<option value="">No se encontraron formatos</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    formatSelect.html('<option value="">Error al cargar formatos</option>').prop('disabled', true);
                }
            });
        });
        // Al abrir el modal, deshabilitar el select de formatos
        $('#addDetailModal').on('show.bs.modal', function () {
            formatSelect.html('<option value="">-- Selecciona una plataforma primero --</option>').prop('disabled', true);
        });

        // Cargar Objetivos (lista completa)
        const objectiveSelect = $('#newDetailObjective');
        objectiveSelect.html('<option>Cargando objetivos...</option>');
        let objectivesData = [];
        $.ajax({
            url: 'ajax/objectives.ajax.php',
            type: 'GET',
            data: { action: 'list' },
            dataType: 'json',
            success: function(response) {
                let options = '<option value="">-- Selecciona un objetivo --</option>';
                if (response.data && response.data.length > 0) {
                    objectivesData = response.data.map(function(row, idx) {
                        // row[0]=name, row[1]=default_result, row[2]=code
                        return {
                            id: idx, // No tenemos el id real, así que usamos el índice
                            name: row[0],
                            default_result: row[1]
                        };
                    });
                    objectivesData.forEach(function(obj, idx) {
                        options += `<option value="${idx}">${obj.name}</option>`;
                    });
                } else {
                    options = '<option value="">No hay objetivos disponibles</option>';
                }
                objectiveSelect.html(options);
            },
            error: function() {
                objectiveSelect.html('<option value="">Error al cargar objetivos</option>');
            }
        });

        // Actualizar label de Proyección según el objetivo seleccionado
        $('#newDetailObjective').on('change', function() {
            const idx = $(this).val();
            let label = 'Proyección';
            if (objectivesData[idx] && objectivesData[idx].default_result) {
                label += ' (' + objectivesData[idx].default_result + ')';
            }
            $('#projectionLabel').text(label);
        });
        // Aquí harías lo mismo para otros selects que necesites cargar dinámicamente
    });

    // === CARGA DINÁMICA DE PROYECTOS AL ABRIR EL MODAL ===
    let projectsLoaded = false;
    $('#addDetailModal').on('show.bs.modal', function () {
        if (projectsLoaded) return;
        const clientId = $(this).data('client-id');
        const $projectSelect = $('#newDetailProject');
        $projectSelect.html('<option value="">Cargando proyectos...</option>').prop('disabled', true);
        fetch(`https://algoritmo.digital/backend/public/api/clients/${clientId}/projects`, {
            method: "POST",
            headers: { "Accept": "application/json" }
        })
        .then(res => res.json())
        .then(response => {
            if (response.success && Array.isArray(response.projects)) {
                let options = '<option value="">-- Selecciona un proyecto --</option>';
                response.projects.forEach(project => {
                    options += `<option value="${project.id}">${project.name} (${project.code})</option>`;
                });
                $projectSelect.html(options).prop('disabled', false);
                projectsLoaded = true;
            } else {
                $projectSelect.html('<option value="">No se encontraron proyectos</option>').prop('disabled', true);
            }
        })
        .catch(err => {
            $projectSelect.html('<option value="">Error al cargar proyectos</option>').prop('disabled', true);
        });
    });

    // Inicialización de Select2 para el modal
    $('#addDetailModal .select2').select2({
        dropdownParent: $('#addDetailModal')
    });
});