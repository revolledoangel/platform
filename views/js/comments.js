$(document).ready(function () {

    /** Editar Comentario */
    $(document).on("click", ".btn-editComment", function () {
        const commentId = $(this).attr("commentId");

        fetch(`https://algoritmo.digital/backend/public/api/comments/${commentId}`)
            .then(res => res.json())
            .then(data => {
                if (data) {
                    // Limpiar
                    $("#editCommentModal textarea").val("");

                    // Cargar datos
                    $("input[name='editCommentId']").val(data.id);
                    $("#editCommentClient").val(data.client_id).trigger('change');
                    $("#editCommentPlatform").val(data.platform_id).trigger('change');
                    $("#editCommentPeriod").val(data.period_id).trigger('change');
                    $("#editCommentConclusion").val(data.conclusion);
                    $("#editCommentRecommendation").val(data.recommendation);
                } else {
                    alert("No se pudo cargar la información del comentario.");
                }
            })
            .catch(err => {
                console.error("Error al obtener el comentario:", err);
            });
    });

    /** Guardar edición */
    $("#editCommentForm").on("submit", function (e) {
        e.preventDefault();

        const id = $("input[name='editCommentId']").val();
        const body = {
            client_id: $("#editCommentClient").val(),
            platform_id: $("#editCommentPlatform").val(),
            period_id: $("#editCommentPeriod").val(),
            conclusion: $("#editCommentConclusion").val().trim(),
            recommendation: $("#editCommentRecommendation").val().trim()
        };

        fetch(`https://algoritmo.digital/backend/public/api/comments/${id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(body)
        })
            .then(res => res.json())
            .then(response => {
                if (response && response.id) {
                    swal({
                        icon: "success",
                        title: "Comentario actualizado",
                        text: "Los cambios se guardaron correctamente."
                    }).then(() => {
                        $("#editCommentModal").modal("hide");
                        location.reload();
                    });
                } else {
                    swal({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo actualizar el comentario."
                    });
                }
            })
            .catch(error => {
                console.error("Error en actualización:", error);
                swal({
                    icon: "error",
                    title: "Error de red",
                    text: "No se pudo conectar con el servidor."
                });
            });
    });

    /** Eliminar Comentario */
    $(document).on("click", ".btn-deleteComment", function () {
        const commentId = $(this).attr("commentId");

        swal({
            title: "¿Deseas eliminar este comentario?",
            text: "Esta acción no se puede deshacer.",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.value) {
                window.location = `index.php?route=comments&commentId=${commentId}`;
            }
        });
    });

    /** Cargar tabla */
    $('#commentsTable').DataTable({
        ajax: "ajax/comments.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });

});
