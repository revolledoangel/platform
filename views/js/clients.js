$(document).ready(function () {

    /** INICIALIZAR DataTable y guardar referencia en una variable global */
    var table = $('#clientsTable').DataTable({
        ajax: "ajax/clients.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        order: [],
        columnDefs: [
            {
                targets: 4, // columna oculta para estado
                visible: false
            }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });

    // Filtro personalizado por estado
    $('#filtroEstado').on('change', function () {
        let valor = $(this).val();
        table.column(4).search(valor).draw(); // aquí sí existe `table`
    });

    /* Editar Cliente*/
    $(document).on("click", ".btn-editClient", function () {

        var idClient = $(this).attr("clientId");

        fetch(`https://algoritmo.digital/backend/public/api/clients/${idClient}`)
            .then(response => response.json())
            .then(data => {
                if (data) {

                    // === 🔄 LIMPIAR CAMPOS DEL MODAL ===

                    // Limpiar inputs de texto (menos ocultos)
                    $("#editClientModal input[type='text']").val("");

                    // Limpiar selects normales
                    $("#editClientModal select").val("");

                    // Limpiar Select2 múltiple
                    $("#editClientModal .select2").val(null).trigger("change");

                    // ✅ AHORA llenar los nuevos datos

                    $("input[name='editClientId']").val(data.id);
                    $("input[name='editClientName']").val(data.name);
                    $("input[name='editClientCode']").val(data.code);

                    const $select = $("select[name='editClientUser']");
                    const existingOption = $select.find("option[value='" + data.user_id + "']");

                    if (existingOption.length) {
                        $select.val(data.user_id);
                    } else {
                        const $option = $("#editClientUser");
                        $option.val(data.user_id).text(data.user_name).prop("selected", true);
                    }

                    $select.trigger("change");

                    // Verticales
                    const verticalNames = data.verticals;
                    const $verticalSelect = $("select[name='editClientVerticals[]']");
                    let selectedVerticalIds = [];

                    $verticalSelect.find("option").each(function () {
                        const $option = $(this);
                        const optionText = $option.text().trim();
                        if (verticalNames.includes(optionText)) {
                            selectedVerticalIds.push($option.val());
                        }
                    });

                    $verticalSelect.val(selectedVerticalIds).trigger("change");


                } else {
                    alert("No se pudo obtener la información del usuario.");
                }
            })

            .catch(error => {
                console.error("Error al obtener datos del usuario:", error);
            });
    });

    $("#editClientForm").on("submit", function (e) {
        e.preventDefault();

        const clientId = $("input[name='editClientId']").val();
        const name = $("input[name='editClientName']").val().trim();
        const code = $("input[name='editClientCode']").val().trim();
        const userId = $("select[name='editClientUser']").val();
        const verticalIds = $("select[name='editClientVerticals[]']").val();

        // Validación básica
        if (!name || !code) {
            swal({
                icon: "warning",
                title: "Campos obligatorios",
                text: "Debes ingresar el nombre y el código del cliente."
            });
            return;
        }

        let body = {
            name: name,
            code: code
        };

        if (userId) {
            body.user_id = parseInt(userId);
        }

        if (verticalIds && verticalIds.length > 0) {
            body.vertical_ids = verticalIds.map(id => parseInt(id));
        }

        fetch(`https://algoritmo.digital/backend/public/api/clients/${clientId}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(body)
        })
            .then(async res => {
                const status = res.status;
                const response = await res.json();

                if (status === 200 || status === 201) {
                    const nombre = response.name || "—";
                    const codigo = response.code || "—";
                    const usuario = response.user_name || "—";
                    const verticales = Array.isArray(response.verticals)
                        ? response.verticals.map(v => v.name).join(", ")
                        : "—";

                    swal({
                        icon: "success",
                        title: "Cliente actualizado correctamente",
                        html: `
                            <b>Nombre:</b> ${nombre}<br>
                            <b>Usuario asignado:</b> ${usuario}<br>
                            <b>Vertical(es):</b> ${verticales}<br>
                            <b>Código:</b> ${codigo}
                        `
                    }).then(() => {
                        $("#editClientModal").modal("hide");
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
    $(document).on("change", ".switch-client input[type=checkbox]", function () {
        const clientId = $(this).data("id");
        const isActive = this.checked ? 1 : 0;

        const formData = new FormData();
        formData.append("id", clientId);
        formData.append("active", isActive);

        fetch("ajax/clients.ajax.php", {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    swal({
                        icon: "success",
                        title: "Cambio exitoso",
                        text: "Se actualizó el estado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                } else {
                    swal({
                        icon: "error",
                        title: "Error",
                        text: "❌ Error: " + response.message,
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                }
            })
            .catch(error => {
                console.error("❌ Error:", error);
                swal({
                    icon: "error",
                    title: "Error de conexión",
                    text: "No se pudo conectar con el servidor.",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            });
    });

    /** Eliminar Cliente */
    $(document).on("click", ".btn-deleteClient", function () {
        var clientId = $(this).attr("clientId");

        swal({
            title: "¿Seguro que desea borrar el Cliente?",
            text: "si no lo estás, cancela la acción",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar cliente'
        }).then((result) => {
            if (result.value) {
                window.location = "index.php?route=clients&clientId=" + clientId;
            }
        });
    });
});