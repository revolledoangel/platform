$(document).ready(function () {

    /* Editar Proyecto*/
    $(document).on("click", ".btn-editPlatform", function () {

        var idPlatform = $(this).attr("platformId");

        fetch(`https://algoritmo.digital/backend/public/api/platforms/${idPlatform}`)
            .then(response => response.json())
            .then(data => {
                if (data) {

                    // === 🔄 LIMPIAR CAMPOS DEL MODAL ===

                    // Limpiar inputs de texto (menos ocultos)
                    $("#editPlatformModal input[type='text']").val("");

                    // Limpiar selects normales
                    $("#editPlatformModal select").val("");

                    // Limpiar Select2 múltiple
                    $("#editPlatformModal .select2").val(null).trigger("change");

                    // ✅ AHORA llenar los nuevos datos

                    $("input[name='editPlatformId']").val(data.id);
                    $("input[name='editPlatformName']").val(data.name);
                    $("input[name='editPlatformCode']").val(data.code);

                    $select.trigger("change");

                } else {
                    alert("No se pudo obtener la información de la plataforma.");
                }
            })

            .catch(error => {
                console.error("Error al obtener datos de la plataforma: ", error);
            });
    });

    $("#editPlatformForm").on("submit", function (e) {
        e.preventDefault();

        const platformId = $("input[name='editPlatformId']").val();
        const name = $("input[name='editPlatformName']").val().trim();
        const code = $("input[name='editPlatformCode']").val().trim();

        // Validación básica
        if (!name || !code) {
            swal({
                icon: "warning",
                title: "Campos obligatorios",
                text: "Debes ingresar el nombre y el código de la plataforma."
            });
            return;
        }

        let body = {
            name: name,
            code: code
        };

        fetch(`https://algoritmo.digital/backend/public/api/platforms/${platformId}`, {
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
                
                const updatedPlatform = response;
                const nombre = updatedPlatform.name || "—";
                const codigo = updatedPlatform.code || "—";

                swal({
                    icon: "success",
                    title: "Plataforma actualizada correctamente",
                    html: `
                        <b>Nombre:</b> ${nombre}<br>
                        <b>Código:</b> ${codigo}
                    `
                }).then(() => {
                    $("#editPlatformModal").modal("hide");
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
    $(document).on("change", ".switch-platform input[type=checkbox]", function () {

        const platformId = $(this).data("id");

        
        const isActive = this.checked ? 1 : 0;

        const formData = new FormData();
        formData.append("id", platformId);
        formData.append("active", isActive);

        fetch("ajax/platforms.ajax.php", {
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
    $(document).on("click", ".btn-deletePlatform", function () {
        var platformId = $(this).attr("platformId");

        swal({
            title: "¿Seguro que desea borrar la Plataforma?",
            text: "si no lo estás, cancela la acción",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar plataforma'
        }).then((result) => {
            if (result.value) {
                window.location = "index.php?route=platforms&platformId=" + platformId;
            }
        });
    });

    // Inicializar DataTable
    var table = $('#platformsTable').DataTable({

        ajax: "ajax/platforms.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"

        }
    });

});