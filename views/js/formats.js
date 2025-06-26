$(document).ready(function () {

    /* Editar Formato*/
    $(document).on("click", ".btn-editFormat", function () {

        var idFormat = $(this).attr("formatId");

        fetch(`https://algoritmo.digital/backend/public/api/formats/${idFormat}`)
            .then(response => response.json())
            .then(data => {
                if (data) {

                    // === 🔄 LIMPIAR CAMPOS DEL MODAL ===

                    // Limpiar inputs de texto (menos ocultos)
                    $("#editFormatModal input[type='text']").val("");

                    // Limpiar selects normales
                    $("#editFormatModal select").val("");

                    // Limpiar Select2 múltiple
                    $("#editFormatModal .select2").val(null).trigger("change");

                    // ✅ AHORA llenar los nuevos datos

                    $("input[name='editFormatId']").val(data.id);
                    $("input[name='editFormatName']").val(data.name);
                    $("input[name='editFormatCode']").val(data.code);

                    const $select = $("select[name='editFormatPlatform']");
                    const existingOption = $select.find("option[value='" + data.platform_id + "']");

                    if (existingOption.length) {
                        $select.val(data.platform_id);
                    } else {
                        const $option = $("#editFormatPlatform");
                        $option.val(data.platform_id).text(data.platform_name).prop("selected", true);
                    }

                    $select.trigger("change");

                } else {
                    alert("No se pudo obtener la información del formato.");
                }
            })

            .catch(error => {
                console.error("Error al obtener datos del formato:", error);
            });
    });

    $("#editFormatForm").on("submit", function (e) {

        e.preventDefault();

        const formatId = $("input[name='editFormatId']").val();
        const name = $("input[name='editFormatName']").val().trim();
        const code = $("input[name='editFormatCode']").val().trim();
        const platformId = $("select[name='editFormatPlatform']").val();

        // Validación básica
        if (!name || !code) {
            swal({
                icon: "warning",
                title: "Campos obligatorios",
                text: "Debes ingresar el nombre y el código del formato."
            });
            return;
        }

        let body = {
            name: name,
            code: code
        };

        if (platformId) {
            body.platform_id = parseInt(platformId);
        }

        fetch(`https://algoritmo.digital/backend/public/api/formats/${formatId}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(body)
        })
            .then(async res => {
                const response = await res.json();

                if (!res.ok) {
                    // Lanza el error para que lo capture el .catch
                    const msg = response.message || "Error inesperado del servidor.";
                    const detalle = response.error ? `\n\nDetalles técnicos:\n${response.error}` : "";
                    throw new Error(msg + detalle);
                }

                return response;
            })
            .then(response => {
                const nombre = response.name || "—";
                const codigo = response.code || "—";
                const plataforma = response.platform_name || "—";

                swal({
                    icon: "success",
                    title: "Formato actualizado correctamente",
                    html: `
            <b>Nombre:</b> ${nombre}<br>
            <b>Plataforma:</b> ${plataforma}<br>
            <b>Código:</b> ${codigo}
        `
                }).then(() => {
                    $("#editFormatModal").modal("hide");
                    location.reload();
                });
            })
            .catch(error => {
                console.error("❌ Error en fetch:", error);
                swal({
                    icon: "error",
                    title: "Error al actualizar",
                    text: error.message || "No se pudo conectar con el servidor."
                });
            });

    });

    /* cambiar el switch de active */
    $(document).on("change", ".switch-format input[type=checkbox]", function () {

        const formatId = $(this).data("id");


        const isActive = this.checked ? 1 : 0;

        const formData = new FormData();
        formData.append("id", formatId);
        formData.append("active", isActive);

        fetch("ajax/formats.ajax.php", {
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

    /** Eliminar Formato */
    $(document).on("click", ".btn-deleteFormat", function () {
        var formatId = $(this).attr("formatId");

        swal({
            title: "¿Seguro que desea borrar el Formato?",
            text: "si no lo estás, cancela la acción",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar formato'
        }).then((result) => {
            if (result.value) {
                window.location = "index.php?route=formats&formatId=" + formatId;
            }
        });
    });

    // Inicializar Select2
    $('#filterPlatform').select2();

    // Inicializar DataTable
    var table = $('#formatsTable').DataTable({

        ajax: "ajax/formats.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"

        }
    });

    // Aplicar filtro cuando cambie el select
    $('#filterPlatform').on('change', function () {
        const plataforma = $(this).val();
        // Suponiendo que el nombre del cliente está en la 2da columna (index 1)
        table.column(2).search(plataforma).draw();
    });

});