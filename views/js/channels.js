$(document).ready(function () {

    /* Editar Canal */
    $(document).on("click", ".btn-editChannel", function () {
        var idChannel = $(this).attr("channelId");

        fetch(`https://algoritmo.digital/backend/public/api/channels/${idChannel}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    // Limpiar campos
                    $("#editChannelModal input[type='text']").val("");

                    // Cargar datos
                    $("input[name='editChannelId']").val(data.id);
                    $("input[name='editChannelName']").val(data.name);

                    // Cargar plataformas asociadas
                    $.ajax({
                        url: 'ajax/channels.ajax.php?action=get_channel_platforms&channelId=' + idChannel,
                        method: 'GET',
                        dataType: 'json',
                        success: function(platformIds) {
                            $('#editChannelPlatforms').val(platformIds).trigger('change');
                        }
                    });
                } else {
                    alert("No se pudo obtener la información del canal.");
                }
            })
            .catch(error => {
                console.error("Error al obtener datos del canal: ", error);
            });
    });

    // Guardar edición de canal
    $("#editChannelForm").on("submit", function (e) {
        e.preventDefault();

        const channelId = $("input[name='editChannelId']").val();
        const name = $("input[name='editChannelName']").val().trim();

        if (!name) {
            swal({
                icon: "warning",
                title: "Campo obligatorio",
                text: "Debes ingresar el nombre del canal."
            });
            return;
        }

        let body = {
            name: name,
        };

        fetch(`https://algoritmo.digital/backend/public/api/channels/${channelId}`, {
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

                    // Guardar plataformas asociadas
                    var platformIds = $('#editChannelPlatforms').val() || [];
                    $.ajax({
                        url: 'ajax/channels.ajax.php',
                        method: 'POST',
                        data: { action: 'save_channel_platforms', channel_id: channelId, 'platform_ids[]': platformIds },
                        dataType: 'json',
                        complete: function() {
                            swal({
                                icon: "success",
                                title: "Canal actualizado correctamente",
                                html: `<b>Nombre:</b> ${nombre}<br>`
                            }).then(() => {
                                $("#editChannelModal").modal("hide");
                                location.reload();
                            });
                        }
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

    /** Eliminar Canal */
    $(document).on("click", ".btn-deleteChannel", function () {
        var channelId = $(this).attr("channelId");

        swal({
            title: "¿Seguro que desea borrar el Canal?",
            text: "si no lo estás, cancela la acción",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar canal'
        }).then((result) => {
            if (result.value) {
                window.location = "index.php?route=channels&channelId=" + channelId;
            }
        });
    });

    // Inicializar DataTable
    $('#channelsTable').DataTable({
        ajax: "ajax/channels.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });

    // Cargar plataformas en los selectores de ambos modales
    $.ajax({
        url: 'ajax/channels.ajax.php?action=get_platforms',
        method: 'GET',
        dataType: 'json',
        success: function(platforms) {
            var opts = '';
            platforms.forEach(function(p) {
                opts += '<option value="' + p.id + '">' + p.name + '</option>';
            });
            $('#newChannelPlatforms, #editChannelPlatforms').html(opts);
            if ($.fn.select2) {
                $('#newChannelPlatforms').select2({ placeholder: 'Selecciona plataformas...', allowClear: true });
                $('#editChannelPlatforms').select2({ placeholder: 'Selecciona plataformas...', allowClear: true, dropdownParent: $('#editChannelModal') });
            }
        }
    });

});