$(document).ready(function () {

    /* Editar Proyecto*/
    $(document).on("click", ".btn-editProject", function () {

        var idProject = $(this).attr("projectId");

        fetch(`https://algoritmo.digital/backend/public/api/projects/${idProject}`)
            .then(response => response.json())
            .then(data => {
                if (data) {

                    // === 🔄 LIMPIAR CAMPOS DEL MODAL ===

                    // Limpiar inputs de texto (menos ocultos)
                    $("#editProjectModal input[type='text']").val("");

                    // Limpiar selects normales
                    $("#editProjectModal select").val("");

                    // Limpiar Select2 múltiple
                    $("#editProjectModal .select2").val(null).trigger("change");

                    // ✅ AHORA llenar los nuevos datos

                    $("input[name='editProjectId']").val(data.id);
                    $("input[name='editProjectName']").val(data.name);
                    $("input[name='editProjectGroup']").val(data.group);
                    $("input[name='editProjectCode']").val(data.code);

                    const $select = $("select[name='editProjectClient']");
                    const existingOption = $select.find("option[value='" + data.client_id + "']");

                    if (existingOption.length) {
                        $select.val(data.client_id);
                    } else {
                        const $option = $("#editProjectClient");
                        $option.val(data.client_id).text(data.client_name).prop("selected", true);
                    }

                    $select.trigger("change");

                } else {
                    alert("No se pudo obtener la información del proyecto.");
                }
            })

            .catch(error => {
                console.error("Error al obtener datos del proyecto:", error);
            });
    });

    $("#editProjectForm").on("submit", function (e) {
        e.preventDefault();

        const projectId = $("input[name='editProjectId']").val();
        const name = $("input[name='editProjectName']").val().trim();
        const code = $("input[name='editProjectCode']").val().trim();
        const group = $("input[name='editProjectGroup']").val().trim();
        const clientId = $("select[name='editProjectClient']").val();

        // Validación básica
        if (!name || !code) {
            swal({
                icon: "warning",
                title: "Campos obligatorios",
                text: "Debes ingresar el nombre y el código del proyecto."
            });
            return;
        }

        let body = {
            name: name,
            code: code
        };

        if (clientId) {
            body.client_id = parseInt(clientId);
        }

        if (group) {
            body.group = group;
        }

        fetch(`https://algoritmo.digital/backend/public/api/projects/${projectId}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(body)
        })
            .then(res => res.json())
            .then(response => {
                if (response && typeof response === "object") {

                    const updatedProject = response;
                    const nombre = updatedProject.name || "—";
                    const codigo = updatedProject.code || "—";
                    const grupo = updatedProject.group || "—";
                    const cliente = updatedProject.client_name || "—";

                    swal({
                        icon: "success",
                        title: "Proyecto actualizado correctamente",
                        html: `
                        <b>Nombre:</b> ${nombre}<br>
                        <b>Cliente:</b> ${cliente}<br>
                        <b>Grupo:</b> ${grupo}<br>
                        <b>Código:</b> ${codigo}
                    `
                    }).then(() => {
                        $("#editProjectModal").modal("hide");
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
    $(document).on("change", ".switch-project input[type=checkbox]", function () {

        const projectId = $(this).data("id");


        const isActive = this.checked ? 1 : 0;

        const formData = new FormData();
        formData.append("id", projectId);
        formData.append("active", isActive);

        fetch("ajax/projects.ajax.php", {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    swal({
                        icon: "success",
                        title: "Cambio exitoso",
                        text: "Se actualizó el estado correctamente"
                    });
                } else {
                    swal({
                        icon: "error",
                        title: "Error",
                        text: response.message
                    });
                }
            })
            .catch(error => {

                swal({
                    icon: "error",
                    title: "Error de conexión",
                    text: "No se pudo conectar con el servidor.",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            });
    });

    /** Eliminar Proyecto */
    $(document).on("click", ".btn-deleteProject", function () {
        var projectId = $(this).attr("projectId");

        swal({
            title: "¿Seguro que desea borrar el Proyecto?",
            text: "si no lo estás, cancela la acción",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar proyecto'
        }).then((result) => {
            if (result.value) {
                window.location = "index.php?route=projects&projectId=" + projectId;
            }
        });
    });

    // Inicializar Select2
    $('#filterClient').select2();

    // Inicializar DataTable
    var table = $('#projectsTable').DataTable({

        ajax: "ajax/projects.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"

        }
    });

    // Aplicar filtro cuando cambie el select
    $('#filterClient').on('change', function () {
        const cliente = $(this).val();
        // Suponiendo que el nombre del cliente está en la 2da columna (index 1)
        table.column(2).search(cliente).draw();
    });

});