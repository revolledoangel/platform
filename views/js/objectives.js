$(document).ready(function () {

    /* Editar Objetivo */
    $(document).on("click", ".btn-editObjective", function () {

        var idObjective = $(this).attr("objectiveId");

        fetch(`https://algoritmo.digital/backend/public/api/objectives/${idObjective}`)
            .then(response => response.json())
            .then(data => {
                if (data) {

                    // Limpiar campos
                    $("#editObjectiveModal input[type='text']").val("");

                    // Cargar datos
                    $("input[name='editObjectiveId']").val(data.id);
                    $("input[name='editObjectiveName']").val(data.name);
                    $("input[name='editObjectiveCode']").val(data.code);

                } else {
                    alert("No se pudo obtener la información del objetivo.");
                }
            })
            .catch(error => {
                console.error("Error al obtener datos del objetivo: ", error);
            });
    });

    // Guardar edición de objetivo
    $("#editObjectiveForm").on("submit", function (e) {
        e.preventDefault();

        const objectiveId = $("input[name='editObjectiveId']").val();
        const name = $("input[name='editObjectiveName']").val().trim();
        const code = $("input[name='editObjectiveCode']").val().trim();

        if (!name) {
            swal({
                icon: "warning",
                title: "Campo obligatorio",
                text: "Debes ingresar el nombre del objetivo."
            });
            return;
        }

        let body = {
            name: name
        };

        fetch(`https://algoritmo.digital/backend/public/api/objectives/${objectiveId}`, {
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
                    const updated = response;
                    const nombre = updated.name || "—";
                    const codigo = updated.code || "—";

                    swal({
                        icon: "success",
                        title: "Objetivo actualizado correctamente",
                        html: `
                        <b>Nombre:</b> ${nombre}<br>
                        <b>Código:</b> ${codigo}
                    `
                    }).then(() => {
                        $("#editObjectiveModal").modal("hide");
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

    /** Eliminar Objetivo */
    $(document).on("click", ".btn-deleteObjective", function () {
        var objectiveId = $(this).attr("objectiveId");

        swal({
            title: "¿Seguro que desea borrar el Objetivo?",
            text: "si no lo estás, cancela la acción",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar objetivo'
        }).then((result) => {
            if (result.value) {
                window.location = "index.php?route=objectives&objectiveId=" + objectiveId;
            }
        });
    });

    // Inicializar DataTable
    $('#objectivesTable').DataTable({
        ajax: "ajax/objectives.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });

});
