$(document).ready(function () {

    // MEJORA: Se define la tabla en una variable para poder recargarla después vía AJAX.
    var campaignTypesTable = $('#campaignTypesTable').DataTable({
        "ajax": "ajax/campaignTypes.ajax.php?action=list",
        "deferRender": true,
        "retrieve": true,
        "processing": true,
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });

    /* =============================================
     EDITAR TIPO DE CAMPAÑA
    ============================================= */
    // Se asigna el evento al cuerpo de la tabla para que funcione con la paginación de DataTable
    $("#campaignTypesTable tbody").on("click", ".btn-editCampaignType", function () {
        // MEJORA: Se usa .data() para leer atributos data-* que es más estándar
        var campaignTypeId = $(this).data("campaigntypeid");

        // Se usa el AJAX handler de PHP para traer los datos del registro a editar
        $.ajax({
            url: "ajax/campaignTypes.ajax.php",
            method: "GET",
            data: { "campaignTypeId": campaignTypeId },
            dataType: "json",
            success: function(data) {
                if (data) {
                    // Cargar datos en el modal usando los IDs de los inputs
                    $("#campaignTypeId").val(data.id);
                    $("#editCampaignTypeName").val(data.name);
                } else {
                    swal({ icon: "error", title: "Error", text: "No se pudo obtener la información." });
                }
            }
        });
    });

    /* =============================================
     GUARDAR CAMBIOS (EDITAR)
    ============================================= */
    $("#editCampaignTypeForm").on("submit", function (e) {
        e.preventDefault();

        // Se usa el API para actualizar el registro (como en tu código original)
        const campaignTypeId = $("#campaignTypeId").val();
        const name = $("#editCampaignTypeName").val().trim();

        if (!name) {
            swal({ icon: "warning", title: "Campo obligatorio", text: "Debes ingresar el nombre." });
            return;
        }

        let body = {
            name: name,
        };

        fetch(`https://algoritmo.digital/backend/public/api/campaign_types/${campaignTypeId}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(body)
        })
        .then(res => res.json())
        .then(response => {
            if (response.id) { // Un ID en la respuesta usualmente significa éxito
                swal({
                    icon: "success",
                    title: "¡Actualizado!",
                    text: "El tipo de campaña se actualizó correctamente."
                }).then(() => {
                    $("#editCampaignTypeModal").modal("hide");
                    // MEJORA: Se recargan solo los datos de la tabla, no toda la página.
                    campaignTypesTable.ajax.reload();
                });
            } else {
                swal({
                    icon: "error",
                    title: "Error",
                    text: response.message || "No se pudo actualizar."
                });
            }
        })
        .catch(error => {
            console.error("Error en fetch:", error);
            swal({ icon: "error", title: "Error de conexión", text: "No se pudo conectar con el servidor." });
        });
    });

    /* =============================================
     ELIMINAR TIPO DE CAMPAÑA
    ============================================= */
    $("#campaignTypesTable tbody").on("click", ".btn-deleteCampaignType", function () {
        var campaignTypeId = $(this).data("campaigntypeid");

        swal({
            title: "¿Estás seguro de borrar el tipo de campaña?",
            text: "¡Si no lo estás, puedes cancelar la acción!",
            icon: "warning",
            buttons: ["Cancelar", "Sí, borrar"],
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                // Se redirige a la página con los parámetros para que el controlador PHP haga el borrado
                window.location = `index.php?route=campaignTypes&deleteCampaignTypeId=${campaignTypeId}`;
            }
        });
    });

});