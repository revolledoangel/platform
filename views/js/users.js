$(document).ready(function () {

    $('#addUserModal').on('show.bs.modal', function () {
        $(this).find('input[name="newUsername"]').val('');
        $(this).find('input[name="newPassword"]').val('');
    });

    $(".newPhoto").change(function () {

        const file = this.files[0];

        const validTypes = ["image/jpeg", "image/png"];
        const maxSize = 2 * 1024 * 1024; // 2MB en bytes

        // Validar tipo
        if (!validTypes.includes(file.type)) {
            $(this).val(""); // Limpia el input
            swal({
                type: "error",
                title: "Error al subir imagen",
                text: "Solo se permite formato de imagen JPG y PNG",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
            return;
        }

        // Validar tamaño
        else if (file.size > maxSize) {
            $(this).val(""); // Limpia el input
            swal({
                type: "error",
                title: "Imagen demasiado grande",
                text: "El tamaño máximo permitido es 2MB",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
            return;
        } else {
            var imageData = new FileReader;
            imageData.readAsDataURL(file);

            $(imageData).on("load", function (event) {

                var imageRoute = event.target.result;

                $(".preview").attr("src", imageRoute);
            })
        }
    });


    /* cambiar el switch de active */
    document.querySelectorAll(".switch-user input[type=checkbox]").forEach((checkbox) => {
        checkbox.addEventListener("change", function () {

            const userId = this.getAttribute("data-id");
            const isActive = this.checked ? 1 : 0;

            const formData = new FormData();
            formData.append("id", userId);
            formData.append("active", isActive);

            fetch("ajax/users.ajax.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(response => {
                    console.log("Respuesta del backend:", response);
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
    });


    /* Editar Usuario*/
    $(document).on("click", ".btn-editUser", function () {

        var idUser = $(this).attr("userId");

        fetch(`https://algoritmo.digital/backend/public/api/users/${idUser}`)
            .then(response => response.json())
            .then(data => {
                if (data.id) {
                    const user = data;

                    // Llenar los campos del modal
                    $("input[name='editId']").val(user.id); // Llenamos el ID
                    $("input[name='editName']").val(user.name);
                    $("input[name='editEmail']").val(user.email);
                    $("input[name='editUsername']").val(user.username);
                    $("select[name='editProfile']").val(user.profile).trigger("change");

                    // Llenar la vista previa de la imagen
                    $("img.preview").attr("src", user.photo || "views/img/template/usuario-sin-foto.png");

                    // === LÍNEA AÑADIDA ===
                    // Guardar la ruta de la foto actual en el input oculto
                    $("input[name='currentPhoto']").val(user.photo);

                    // Tu código para el 'editId' es un poco complejo, lo simplifiqué arriba.
                    // Tu versión original también funciona, pero asegúrate de que el ID se establezca.

                } else {
                    alert("No se pudo obtener la información del usuario.");
                }
            })
            .catch(error => {
                console.error("Error al obtener datos del usuario:", error);
            });
    });

    /** Eliminar Usuario */
    $(".btn-deleteUser").click(function () {

        var userId = $(this).attr("userId");
        var userPhoto = $(this).attr("userPhoto");
        var userUsername = $(this).attr("userUsername");

        swal({
            title: "¿Seguro que desea borrar el usuario?",
            text: "si no lo estás, cancela la acción",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar usuario'
        }).then((result)=>{
            if(result.value){
                window.location ="index.php?route=users&userId="+userId+"&userUsername="+userUsername+"&userPhoto="+userPhoto;
            }
        })

    })


});
