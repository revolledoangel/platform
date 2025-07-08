$(document).ready(function () {

    // Inicializar Select2
    $('#filterClient').select2();
    $('#filterPeriod').select2();

    // Inicializar DataTable con botón de Excel
    var table = $('#campaignsTable').DataTable({
        ajax: "ajax/campaigns.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Exportar Excel',
                className: 'btn btn-success',
                title: '',
                filename: function () {
                    const today = new Date();
                    const yyyy = today.getFullYear();
                    const mm = String(today.getMonth() + 1).padStart(2, '0');
                    const dd = String(today.getDate()).padStart(2, '0');
                    return `Campañas_Export_${yyyy}-${mm}-${dd}`;
                },
                exportOptions: {
                    columns: ':not(:last-child)',
                    format: {
                        body: function (data, row, column, node) {
                            // Columnas verticales (2) y objetivos (8)
                            if (column === 2 || column === 8) {
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = data;
                                const texts = Array.from(tempDiv.querySelectorAll('span')).map(el => el.textContent.trim());
                                return texts.join(', ');
                            }

                            // Inversión (9) y meta (10): solo número
                            if (column === 9 || column === 10) {
                                return parseInt(data.replace(/[^0-9]/g, '')) || 0;
                            }

                            return data;
                        }
                    }
                }
            }
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });

    // Filtro por cliente
    $('#filterClient').on('change', function () {
        table.column(5).search(this.value).draw(); // cliente está en la columna 5
    });

    // Filtro por periodo (por nombre)
    $('#filterPeriod').on('change', function () {
        table.column(0).search(this.value).draw(); // periodo_name está en la columna 0
    });

    // Carga dinámica de proyectos por cliente
    $('#newCampaignClient').on('change', function () {
        const clientId = $(this).val();
        const $projectSelect = $('#newCampaignProject');

        if (!clientId) {
            $projectSelect.html('<option value="">-- Selecciona un cliente primero --</option>').prop('disabled', true);
            return;
        }

        $projectSelect.html('<option value="">Cargando proyectos...</option>').prop('disabled', true);

        fetch(`https://algoritmo.digital/backend/public/api/clients/${clientId}/projects`, {
            method: "POST",
            headers: {
                "Accept": "application/json"
            }
        })
            .then(res => res.json())
            .then(response => {
                if (response.success && Array.isArray(response.projects)) {
                    let options = '<option value="">-- Selecciona un proyecto --</option>';
                    response.projects.forEach(project => {
                        options += `<option value="${project.id}">${project.name} (${project.code})</option>`;
                    });
                    $projectSelect.html(options).prop('disabled', false);
                } else {
                    $projectSelect.html('<option value="">No se encontraron proyectos</option>').prop('disabled', true);
                }
            })
            .catch(err => {
                console.error("Error cargando proyectos:", err);
                $projectSelect.html('<option value="">Error al cargar proyectos</option>').prop('disabled', true);
            });
    });

    // Carga dinámica de formatos por plataforma
    $('#newCampaignPlatform').on('change', function () {
        const platformId = $(this).val();
        const $formatSelect = $('select[name="newCampaignFormats[]"]');

        if (!platformId) {
            $formatSelect.html('<option value="">-- Selecciona una plataforma primero --</option>').prop('disabled', true);
            return;
        }

        $formatSelect.html('<option value="">Cargando formatos...</option>').prop('disabled', true);

        fetch(`https://algoritmo.digital/backend/public/api/platforms/${platformId}/formats`, {
            method: "POST",
            headers: {
                "Accept": "application/json"
            }
        })
            .then(res => res.json())
            .then(response => {
                if (response.success && Array.isArray(response.formats)) {
                    let options = '';
                    response.formats.forEach(format => {
                        options += `<option value="${format.id}">${format.name} (${format.code})</option>`;
                    });
                    $formatSelect.html(options).prop('disabled', false);
                } else {
                    $formatSelect.html('<option value="">No se encontraron formatos</option>').prop('disabled', true);
                }
            })
            .catch(err => {
                console.error("Error cargando formatos:", err);
                $formatSelect.html('<option value="">Error al cargar formatos</option>').prop('disabled', true);
            });
    });

    // Activar Select2 con dropdown dentro del modal
    $('#newCampaignClient, #newCampaignProject, #newCampaignPlatform').select2({
        dropdownParent: $('#addCampaignModal')
    });

    $('select[name="newCampaignFormats[]"]').select2({
        dropdownParent: $('#addCampaignModal')
    });

    $('select[name="newCampaignObjectives[]"]').select2({
        dropdownParent: $('#addCampaignModal')
    });

    //trae los datos prellenados al modal de editar cliente
    $(document).on("click", ".btn-editCampaign", function () {
        const idCampaign = $(this).attr("campaignId");

        fetch(`https://algoritmo.digital/backend/public/api/campaigns/${idCampaign}`)
            .then(response => response.json())
            .then(async (data) => {
                if (!data || !data.id) {
                    alert("No se pudo obtener la información de la campaña.");
                    return;
                }

                // === 🔄 LIMPIAR CAMPOS DEL MODAL ===
                $("#editCampaignModal input[type='text']").val("");
                $("#editCampaignModal input[type='number']").val("");
                $("#editCampaignModal textarea").val("");
                $("#editCampaignModal select").val("");
                $("#editCampaignModal .select2").val(null).trigger("change");

                // === ✅ LLENAR CAMPOS CON DATOS ===
                $("input[name='editCampaignId']").val(data.id);
                $("input[name='editCampaignName']").val(data.name);
                $("input[name='editCampaignInvestment']").val(data.investment);
                $("input[name='editCampaignGoal']").val(data.goal);
                $("textarea[name='editCampaignComments']").val(data.comments);

                $("select[name='editCampaignPeriod']").val(data.period_id).trigger("change");

                // Normalizar estado
                const validStates = ["Sin determinar", "Activa", "Por confirmar", "Suspendida"];
                if (validStates.includes(data.state)) {
                    $("select[name='editCampaignStatus']").val(data.state).trigger("change");
                }

                // Cliente (y cargar proyectos del cliente)
                $("select[name='editCampaignClient']").val(data.client_id).trigger("change");

                // Cargar proyectos del cliente directamente
                const $projectSelect = $("select[name='editCampaignProject']");
                $projectSelect.html('<option value="">Cargando proyectos...</option>').prop('disabled', true);

                const projectRes = await fetch(`https://algoritmo.digital/backend/public/api/clients/${data.client_id}/projects`, {
                    method: "POST",
                    headers: { "Accept": "application/json" }
                });

                const projectData = await projectRes.json();

                if (projectData.success && Array.isArray(projectData.projects)) {
                    let options = '<option value="">-- Selecciona un proyecto --</option>';
                    projectData.projects.forEach(project => {
                        options += `<option value="${project.id}">${project.name} (${project.code})</option>`;
                    });
                    $projectSelect.html(options).prop('disabled', false);
                    $projectSelect.val(data.project_id).trigger("change");
                } else {
                    $projectSelect.html('<option value="">No se encontraron proyectos</option>').prop('disabled', true);
                }

                // Plataforma (y cargar formatos directamente)
                $("select[name='editCampaignPlatform']").val(data.platform_id).trigger("change");

                const $formatSelect = $("select[name='editCampaignFormats[]']");
                $formatSelect.html('<option value="">Cargando formatos...</option>').prop('disabled', true);

                const formatRes = await fetch(`https://algoritmo.digital/backend/public/api/platforms/${data.platform_id}/formats`, {
                    method: "POST",
                    headers: { "Accept": "application/json" }
                });

                const formatData = await formatRes.json();

                if (formatData.success && Array.isArray(formatData.formats)) {
                    let options = '';
                    formatData.formats.forEach(format => {
                        options += `<option value="${format.id}">${format.name} (${format.code})</option>`;
                    });
                    $formatSelect.html(options).prop('disabled', false);
                    $formatSelect.val(data.formats_ids).trigger("change");
                } else {
                    $formatSelect.html('<option value="">No se encontraron formatos</option>').prop('disabled', true);
                }

                // Objetivos
                $("select[name='editCampaignObjectives[]']").val(data.objectives_ids).trigger("change");
            })
            .catch(error => {
                console.error("Error al obtener datos de la campaña:", error);
            });
    });

    //editar cliente
    $("#editCampaignForm").on("submit", function (e) {
        e.preventDefault();

        const campaignId = $("input[name='editCampaignId']").val();
        const name = $("input[name='editCampaignName']").val().trim();
        const comments = $("textarea[name='editCampaignComments']").val().trim();
        const investment = $("input[name='editCampaignInvestment']").val().trim();
        const goal = $("input[name='editCampaignGoal']").val().trim();

        const periodId = $("select[name='editCampaignPeriod']").val();
        const projectId = $("select[name='editCampaignProject']").val();
        const status = $("select[name='editCampaignStatus']").val();
        const formatIds = $("select[name='editCampaignFormats[]']").val();
        const objectiveIds = $("select[name='editCampaignObjectives[]']").val();

        // Validación básica
        if (!periodId || !projectId || !status || !investment || !goal || !formatIds.length || !objectiveIds.length) {
            swal({
                icon: "warning",
                title: "Campos incompletos",
                text: "Por favor, completa todos los campos obligatorios."
            });
            return;
        }

        const body = {
            period_id: parseInt(periodId),
            project_id: parseInt(projectId),
            state: status,
            investment: parseInt(investment),
            goal: parseInt(goal),
            formats_ids: formatIds.map(id => parseInt(id)),
            objectives_ids: objectiveIds.map(id => parseInt(id)),
        };

        if (name) body.name = name;
        if (comments) body.comments = comments;

        fetch(`https://algoritmo.digital/backend/public/api/campaigns/${campaignId}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(body)
        })
            .then(async res => {
                const statusCode = res.status;
                const response = await res.json();

                if (statusCode === 200 || statusCode === 201) {
                    swal({
                        icon: "success",
                        title: "Campaña actualizada correctamente",
                        html: `
                        <b>Proyecto:</b> ${response.project_name || "—"}<br>
                        <b>Plataforma:</b> ${response.platform_name || "—"}<br>
                        <b>Inversión:</b> $${response.investment || "0"}<br>
                        <b>Meta:</b> ${response.goal || "—"}
                    `
                    }).then(() => {
                        $("#editCampaignModal").modal("hide");
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

    $(document).on("click", ".btn-deleteCampaign", function () {
        const campaignId = $(this).attr("campaignId");

        swal({
            title: "¿Estás seguro de eliminar la campaña?",
            text: "Esta acción no se puede deshacer.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "Cancelar",
            confirmButtonText: "Sí, eliminar"
        }).then((result) => {
            if (result.value) {
                window.location = "index.php?route=campaigns&campaignId=" + campaignId;
            }
        });
    });




});
