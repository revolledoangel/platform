$(document).ready(function () {

    /* Editar Vertical*/
    $(document).on("click", ".btn-editVertical", function () {

        var idVertical = $(this).attr("verticalId");

        fetch(`https://algoritmo.digital/backend/public/api/verticals/${idVertical}`)
            .then(response => response.json())
            .then(data => {
                if (data) {

                    // === 🔄 LIMPIAR CAMPOS DEL MODAL ===

                    // Limpiar inputs de texto (menos ocultos)
                    $("#editVerticalModal input[type='text']").val("");

                    // ✅ AHORA llenar los nuevos datos

                    $("input[name='editVerticalId']").val(data.id);
                    $("input[name='editVerticalName']").val(data.name);

                    $select.trigger("change");

                } else {
                    alert("No se pudo obtener la información del vertical.");
                }
            })

            .catch(error => {
                console.error("Error al obtener datos de la vertical: ", error);
            });
    });

    $("#editVerticalForm").on("submit", function (e) {
        e.preventDefault();

        const verticalId = $("input[name='editVerticalId']").val();
        const name = $("input[name='editVerticalName']").val().trim();

        // Validación básica
        if (!name) {
            swal({
                icon: "warning",
                title: "Campo obligatorio",
                text: "Debes ingresar el nombre del vertical"
            });
            return;
        }

        let body = {
            name: name
        };

        fetch(`https://algoritmo.digital/backend/public/api/verticals/${verticalId}`, {
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
                
                const updatedVertical = response;
                const nombre = updatedVertical.name || "—";

                swal({
                    icon: "success",
                    title: "Vertical actualizada correctamente",
                    html: `
                        <b>Nombre:</b> ${nombre}
                    `
                }).then(() => {
                    $("#editVerticalModal").modal("hide");
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

    /** Eliminar vertical */
    $(document).on("click", ".btn-deleteVertical", function () {
        var verticalId = $(this).attr("verticalId");

        swal({
            title: "¿Seguro que desea borrar el Vertical?",
            text: "si no lo estás, cancela la acción",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar vertical'
        }).then((result) => {
            if (result.value) {
                window.location = "index.php?route=verticals&verticalId=" + verticalId;
            }
        });
    });

    // Inicializar DataTable
    var table = $('#verticalsTable').DataTable({

        ajax: "ajax/verticals.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        order: [],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"

        }
    });

});