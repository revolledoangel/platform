$(document).ready(function () {
    const $modal = $('#addUrlModal');

    const $periodSelect = $modal.find("select[name='newUrlPeriod']");
    const $clientSelect = $modal.find("select[name='newUrlClient']");
    const $projectSelect = $modal.find("select[name='newUrlProject']");
    const $campaignSelect = $modal.find("select[name='newUrlCampaign']");
    const $utmFields = $('#utmFields');
    const $generatedUrlList = $('#generatedUrlsContainer');

    let campaignData = {};
    let urlsGeneradas = [];

    $periodSelect.select2({ dropdownParent: $modal });
    $clientSelect.select2({ dropdownParent: $modal });
    $projectSelect.select2({ dropdownParent: $modal });
    $campaignSelect.select2({ dropdownParent: $modal });

    // === CARGAR PERIODOS ===
    fetch("https://algoritmo.digital/backend/public/api/periods")
        .then(res => res.json())
        .then(data => {
            const now = new Date();
            const currentMonth = now.getMonth() + 1;
            const currentYear = now.getFullYear();

            $periodSelect.html('<option disabled>Seleccione un periodo</option>');
            data.forEach(p => {
                const selected = (parseInt(p.month_number) === currentMonth && parseInt(p.year) === currentYear) ? 'selected' : '';
                $periodSelect.append(`<option value="${p.id}" ${selected}>${p.name}</option>`);
            });

            // Trigger campaigns refresh if period pre-selected
            loadCampaigns();
        });

    // === CARGAR CLIENTES ===
    fetch("https://algoritmo.digital/backend/public/api/clients")
        .then(res => res.json())
        .then(data => {
            $clientSelect.html('<option disabled selected>Seleccione un cliente</option>');
            data.forEach(c => {
                $clientSelect.append(`<option value="${c.id}">${c.name}</option>`);
            });
        });

    // === Eventos dependientes ===
    $periodSelect.on("change", loadCampaigns);
    $projectSelect.on("change", loadCampaigns);

    $clientSelect.on('change', function () {
        const clientId = $(this).val();
        $projectSelect.prop('disabled', true).html('<option>Cargando...</option>');

        fetch(`https://algoritmo.digital/backend/public/api/clients/${clientId}/projects`, {
            method: "POST",
            headers: { "Accept": "application/json" }
        })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    $projectSelect.html('<option disabled selected>Seleccione un proyecto</option>');
                    response.projects.forEach(p => {
                        $projectSelect.append(`<option value="${p.id}">${p.name} (${p.code})</option>`);
                    });
                    $projectSelect.prop('disabled', false);
                } else {
                    $projectSelect.html('<option>No se encontraron proyectos</option>').prop('disabled', true);
                }

                // 🔁 refrescar campañas después de actualizar proyectos
                loadCampaigns();
            })
            .catch(err => {
                console.error("Error cargando proyectos:", err);
                $projectSelect.html('<option>Error al cargar</option>').prop('disabled', true);
            });
    });

    // === Función reutilizable: cargar campañas filtradas ===
    function loadCampaigns() {
        const projectId = parseInt($projectSelect.val());
        const selectedPeriodId = parseInt($periodSelect.val());

        if (!projectId || !selectedPeriodId) {
            $campaignSelect.html('<option>Seleccione proyecto y periodo</option>').prop('disabled', true);
            return;
        }

        $campaignSelect.prop('disabled', true).html('<option>Cargando campañas...</option>');

        fetch("https://algoritmo.digital/backend/public/api/campaigns", {
            method: "GET",
            headers: { "Accept": "application/json" }
        })
            .then(res => res.json())
            .then(data => {
                const campañasFiltradas = data.filter(c =>
                    parseInt(c.project_id) === projectId &&
                    parseInt(c.period_id) === selectedPeriodId
                );

                if (campañasFiltradas.length > 0) {
                    $campaignSelect.html('<option disabled selected>Seleccione una campaña</option>');
                    campañasFiltradas.forEach(c => {
                        const label = `${c.name} - ${c.platform_name} - ${c.period_name}`;
                        $campaignSelect.append(`<option value="${c.id}">${label}</option>`);
                    });
                    $campaignSelect.prop('disabled', false);
                } else {
                    $campaignSelect.html('<option>No se encontraron campañas</option>').prop('disabled', true);
                }
            })
            .catch(err => {
                console.error("Error al cargar campañas:", err);
                $campaignSelect.html('<option>Error al cargar campañas</option>').prop('disabled', true);
            });
    }

    // === Selección de campaña y carga de datos UTM
    $campaignSelect.on('change', function () {
        const campaignId = $(this).val();
        fetch(`https://algoritmo.digital/backend/public/api/campaigns/${campaignId}`)
            .then(res => res.json())
            .then(data => {
                campaignData = data;

                $("input[name='utmSource']").val(data.platform_name);
                $("input[name='utmMedium']").val(data.period_name);
                $("input[name='utmCampaign']").val(`${data.period_name} ${data.platform_name}`);
                $("input[name='utmTerm']").val(data.platform_code === "GG" ? "{keyword}" : data.project_group);
                $("input[name='utmContent']").val(data.client_name);
            });
    });

    // === Generar URL
    $('#btnGenerateUrl').on('click', function () {
        const baseUrl = $("input[name='newUrlInput']").val();
        if (!baseUrl) {
            alert("Ingrese una URL base válida.");
            return;
        }

        const utms = {
            utm_source: $("input[name='utmSource']").val(),
            utm_medium: $("input[name='utmMedium']").val(),
            utm_campaign: $("input[name='utmCampaign']").val(),
            utm_term: $("input[name='utmTerm']").val(),
            utm_content: $("input[name='utmContent']").val()
        };

        const fullUrl = `${baseUrl}?${$.param(utms)}`;
        urlsGeneradas.push({ ...utms, url: baseUrl, campaign_id: campaignData.id });

        $generatedUrlList.append(`
            <div class="well well-sm generated-url">
                <code>${fullUrl}</code>
                <button type="button" class="btn btn-xs btn-default copyUrlBtn" data-url="${fullUrl}">
                    <i class="fa fa-copy"></i>
                </button>
            </div>
        `);
    });

    // === Copiar al portapapeles
    $(document).on("click", ".copyUrlBtn", function () {
        const url = $(this).data("url");
        navigator.clipboard.writeText(url).then(() => {
            $(this).html('<i class="fa fa-check"></i>');
        });
    });

    // === Guardar URLs en backend
    $('#addUrlForm').on('submit', function (e) {
        e.preventDefault();
        if (urlsGeneradas.length === 0) {
            alert("Primero genera al menos una URL");
            return;
        }

        let promesas = urlsGeneradas.map(u => {
            return fetch("https://algoritmo.digital/backend/public/api/urls", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(u)
            });
        });

        Promise.all(promesas)
            .then(() => {
                swal({
                    icon: "success",
                    title: "URLs guardadas correctamente"
                }).then(() => {
                    $('#addUrlModal').modal('hide');
                    location.reload();
                });
            })
            .catch(err => {
                console.error("Error al guardar URLs:", err);
                swal({ icon: "error", title: "Error", text: "No se pudieron guardar las URLs" });
            });
    });

    //mostrar url
    $(document).on("click", ".btn-showUrl", function () {
        const fullUrl = $(this).data("url");

        swal({
            title: "URL Generada",
            html: `
            <code style="word-break: break-all;">${fullUrl}</code>
            <br><br>
            <button class="btn btn-default copyGeneratedUrl" data-url="${fullUrl}">
                <i class="fa fa-copy"></i> Copiar
            </button>
        `,
            showConfirmButton: false
        });

        // Delegar el click al botón generado dentro de swal
        $(document).off("click", ".copyGeneratedUrl").on("click", ".copyGeneratedUrl", function () {
            const urlToCopy = $(this).data("url");
            navigator.clipboard.writeText(urlToCopy).then(() => {
                $(this).html('<i class="fa fa-check"></i> Copiado');
            });
        });
    });


    //eliminar
    $(document).on("click", ".btn-deleteUrl", function () {
        const urlId = $(this).attr("urlId");

        swal({
            title: "¿Seguro que deseas eliminar esta URL?",
            text: "Esta acción no se puede deshacer",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar URL'
        }).then((result) => {
            if (result.value) {
                fetch(`index.php?route=urls&urlToDelete=${urlId}`, {
                    method: "GET"
                })
                    .then(res => res.text())
                    .then(html => {
                        // Mostrar alerta basada en la respuesta (usamos DOMParser)
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const scripts = doc.querySelectorAll('script');
                        scripts.forEach(script => eval(script.innerText));

                        // Recargar tabla sin refrescar toda la página
                        $('#urlsTable').DataTable().ajax.reload(null, false);
                    })
                    .catch(err => {
                        console.error("Error eliminando URL:", err);
                        swal({
                            icon: "error",
                            title: "Error de red",
                            text: "No se pudo conectar con el servidor."
                        });
                    });
            }
        });
    });


    //Inicializar Datatable
    $('#urlsTable').DataTable({
        ajax: "ajax/urls.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });
});
