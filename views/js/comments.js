$(document).ready(function () {

    /** Cargar tabla */
    var commentsTable = $('#commentsTable').DataTable({
        ajax: "ajax/comments.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },
        "columnDefs": [
            { "targets": [1, 3, 5], "visible": false, "searchable": true } // Ocultar columnas de IDs
        ]
    });

    // Inicializar Select2 después de cargar la tabla
    $('.select2').select2();

    // Filtrado por Cliente (columna 1 - client_id oculta)
    $('#filterClient').on('change', function () {
        var selectedValue = $(this).val();
        commentsTable.column(1).search(selectedValue ? '^' + selectedValue + '$' : '', true, false);

        // Redibujar la tabla solo si el filtro de plataforma no está procesándose
        if ($('#filterPlatform').data('isFiltering') !== true) {
            commentsTable.draw();
        }
    });

    // Filtrado por Plataforma (columna 3 - platform_id oculta)
    $('#filterPlatform').on('change', function () {
        var selectedValue = $(this).val();
        commentsTable.column(3).search(selectedValue ? '^' + selectedValue + '$' : '', true, false);

        // Redibujar solo si no hay otros filtros procesándose
        if ($('#filterClient').data('isFiltering') !== true && $('#filterPeriod').data('isFiltering') !== true) {
            commentsTable.draw();
        }
    });

    // Filtrado por Periodo (columna 5 - period_id oculta)
    $('#filterPeriod').on('change', function () {
        // Marcamos que este filtro está en proceso para evitar doble dibujado
        $(this).data('isFiltering', true);
        var selectedValue = $(this).val();
        commentsTable.column(5).search(selectedValue ? '^' + selectedValue + '$' : '', true, false).draw();
        $(this).data('isFiltering', false);
    });

    // Aplicar filtros al cargar la página
    $('#filterClient').trigger('change');
    $('#filterPlatform').trigger('change');
    $('#filterPeriod').trigger('change');

    /** Crear Comentario */
    $("#addCommentForm").on("submit", function (e) {
        e.preventDefault();

        const body = {
            client_id: $("#newCommentClient").val(),
            platform_id: $("#newCommentPlatform").val(),
            period_id: $("#newCommentPeriod").val(),
            conclusion: CKEDITOR.instances.commentConclusion.getData(),
            recommendation: CKEDITOR.instances.commentRecommendation.getData()
        };

        fetch('https://algoritmo.digital/backend/public/api/comments', {
            method: "POST",
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
                        title: "Comentario creado",
                        text: "El comentario se creó correctamente."
                    }).then(() => {
                        $("#addCommentModal").modal("hide");
                        location.reload();
                    });
                } else {
                    swal({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo crear el comentario."
                    });
                }
            })
            .catch(error => {
                console.error("Error en creación:", error);
                swal({
                    icon: "error",
                    title: "Error de red",
                    text: "No se pudo conectar con el servidor."
                });
            });
    });

    /** Ver Comentario */
    $(document).on("click", ".btn-viewComment", function () {
        const commentId = $(this).attr("commentId");

        fetch(`https://algoritmo.digital/backend/public/api/comments/${commentId}`)
            .then(res => res.json())
            .then(data => {
                if (data) {
                    // Obtener nombres de los selects en la página
                    const clientName = data.client_name || data.client?.name || $('#filterClient option[value="' + data.client_id + '"]').text();
                    const platformName = data.platform_name || data.platform?.name || $('#filterPlatform option[value="' + data.platform_id + '"]').text();
                    const periodName = data.period_name || data.period?.name || $('#filterPeriod option[value="' + data.period_id + '"]').text();
                    
                    // Mostrar datos completos en el modal
                    $("#viewCommentClient").text(clientName);
                    $("#viewCommentPlatform").text(platformName);
                    $("#viewCommentPeriod").text(periodName);
                    $("#viewCommentRecommendation").html(data.recommendation || '');
                    $("#viewCommentConclusion").html(data.conclusion || '');
                } else {
                    alert("No se pudo cargar la información del comentario.");
                }
            })
            .catch(err => {
                console.error("Error al obtener el comentario:", err);
            });
    });

    /** Editar Comentario */
    $(document).on("click", ".btn-editComment", function () {
        const commentId = $(this).attr("commentId");

        fetch(`https://algoritmo.digital/backend/public/api/comments/${commentId}`)
            .then(res => res.json())
            .then(data => {
                if (data) {
                    // Cargar datos
                    $("input[name='editCommentId']").val(data.id);
                    $("#editCommentClient").val(data.client_id).trigger('change');
                    $("#editCommentPlatform").val(data.platform_id).trigger('change');
                    $("#editCommentPeriod").val(data.period_id).trigger('change');
                    
                    // Cargar contenido en CKEditor
                    if (CKEDITOR.instances.editCommentConclusion) {
                        CKEDITOR.instances.editCommentConclusion.setData(data.conclusion || '');
                    }
                    if (CKEDITOR.instances.editCommentRecommendation) {
                        CKEDITOR.instances.editCommentRecommendation.setData(data.recommendation || '');
                    }
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
            conclusion: CKEDITOR.instances.editCommentConclusion.getData(),
            recommendation: CKEDITOR.instances.editCommentRecommendation.getData()
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
                fetch(`https://algoritmo.digital/backend/public/api/comments/${commentId}`, {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json"
                    }
                })
                    .then(res => res.json())
                    .then(response => {
                        if (response && (response.success || response.message)) {
                            swal({
                                icon: "success",
                                title: "¡Comentario eliminado!",
                                text: "El comentario ha sido eliminado correctamente."
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            swal({
                                icon: "error",
                                title: "Error",
                                text: "No se pudo eliminar el comentario."
                            });
                        }
                    })
                    .catch(error => {
                        console.error("Error al eliminar:", error);
                        swal({
                            icon: "error",
                            title: "Error de red",
                            text: "No se pudo conectar con el servidor."
                        });
                    });
            }
        });
    });

    /** Inicializar CKEditor cuando se abra el modal de creación */
    $('#addCommentModal').on('shown.bs.modal', function () {
        if (typeof CKEDITOR !== 'undefined') {
            if (!CKEDITOR.instances.commentRecommendation) {
                CKEDITOR.replace('commentRecommendation', {
                    height: 150,
                    toolbar: [
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline'] },
                        { name: 'paragraph', items: ['BulletedList'] }
                    ],
                    removePlugins: 'elementspath',
                    resize_enabled: false
                });
            }
            if (!CKEDITOR.instances.commentConclusion) {
                CKEDITOR.replace('commentConclusion', {
                    height: 150,
                    toolbar: [
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline'] },
                        { name: 'paragraph', items: ['BulletedList'] }
                    ],
                    removePlugins: 'elementspath',
                    resize_enabled: false
                });
            }
        }
    });

    /** Generar URL de reporte */
    $('#generateReportBtn').on('click', function() {
        const clientId = $('#reportClient').val();
        const periodId = $('#reportPeriod').val();

        if (!clientId || !periodId) {
            swal({
                icon: "warning",
                title: "Campos incompletos",
                text: "Por favor selecciona un cliente y un periodo."
            });
            return;
        }

        // Mostrar loading
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generando...');

        // Obtener comentarios para buscar el hashed_code
        fetch('https://algoritmo.digital/backend/public/api/comments')
            .then(res => res.json())
            .then(comments => {
                console.log('Comentarios recibidos:', comments);
                console.log('Buscando cliente ID:', clientId, 'periodo ID:', periodId);
                
                // Buscar un comentario que coincida con el cliente Y el periodo para obtener su hashed_code
                const clientComment = comments.find(comment => {
                    const matchClient = comment.client_id == clientId;
                    const matchPeriod = comment.period_id == periodId;
                    console.log(`Comentario ID ${comment.id}: cliente match=${matchClient}, periodo match=${matchPeriod}`);
                    return matchClient && matchPeriod;
                });

                console.log('Comentario encontrado:', clientComment);

                if (clientComment && clientComment.hashed_code) {
                    // Generar la URL
                    const pathArray = window.location.pathname.split('/');
                    const basePath = pathArray.slice(0, pathArray.indexOf('index.php') > -1 ? pathArray.indexOf('index.php') : pathArray.length - 1).join('/');
                    const baseUrl = window.location.origin + basePath;
                    const reportUrl = `${baseUrl}/comment_view/?code=${clientComment.hashed_code}&period=${periodId}`;
                    
                    console.log('URL generada:', reportUrl);
                    
                    // Mostrar la URL
                    $('#reportUrl').val(reportUrl);
                    $('#openUrlBtn').attr('href', reportUrl);
                    $('#reportUrlContainer').slideDown();
                } else {
                    console.error('No se encontró hashed_code. Cliente encontrado:', clientComment);
                    swal({
                        icon: "error",
                        title: "Error",
                        text: "No se encontraron comentarios para este cliente en el periodo seleccionado."
                    });
                }

                // Restaurar botón
                $('#generateReportBtn').prop('disabled', false).html('<i class="fa fa-cog"></i> Generar Reporte');
            })
            .catch(error => {
                console.error('Error:', error);
                swal({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo generar el reporte."
                });
                $('#generateReportBtn').prop('disabled', false).html('<i class="fa fa-cog"></i> Generar Reporte');
            });
    });

    /** Copiar URL al portapapeles */
    $('#copyUrlBtn').on('click', function() {
        const urlInput = document.getElementById('reportUrl');
        urlInput.select();
        urlInput.setSelectionRange(0, 99999); // Para móviles

        try {
            document.execCommand('copy');
            swal({
                icon: "success",
                title: "¡Copiado!",
                text: "La URL ha sido copiada al portapapeles.",
                timer: 2000,
                showConfirmButton: false
            });
        } catch (err) {
            swal({
                icon: "error",
                title: "Error",
                text: "No se pudo copiar la URL. Por favor cópiala manualmente."
            });
        }
    });

    /** Limpiar URL al cerrar el modal */
    $('#reportModal').on('hidden.bs.modal', function() {
        $('#reportClient').val('').trigger('change');
        $('#reportPeriod').val('').trigger('change');
        $('#reportUrlContainer').hide();
        $('#reportUrl').val('');
    });

    /** Inicializar CKEditor cuando se abra el modal de edición */
    $('#editCommentModal').on('shown.bs.modal', function () {
        if (typeof CKEDITOR !== 'undefined') {
            if (!CKEDITOR.instances.editCommentRecommendation) {
                CKEDITOR.replace('editCommentRecommendation', {
                    height: 150,
                    toolbar: [
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline'] },
                        { name: 'paragraph', items: ['BulletedList'] }
                    ],
                    removePlugins: 'elementspath',
                    resize_enabled: false
                });
            }
            if (!CKEDITOR.instances.editCommentConclusion) {
                CKEDITOR.replace('editCommentConclusion', {
                    height: 150,
                    toolbar: [
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline'] },
                        { name: 'paragraph', items: ['BulletedList'] }
                    ],
                    removePlugins: 'elementspath',
                    resize_enabled: false
                });
            }
        }
    });

});
