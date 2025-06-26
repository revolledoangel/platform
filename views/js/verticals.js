$(document).ready(function () {

    /* Editar Vertical*/
    $(document).on("click", ".btn-editVertical", function () {

        var idVertical = $(this).attr("verticalId");

        fetch(`https://algoritmo.digital/backend/public/api/verticals/${idVertical}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    const vertical = data;

                    // Llenar los campos del modal
                    $("input[name='editVerticalId']").val(vertical.id); // Llenamos el ID
                    $("input[name='editVerticalName']").val(vertical.name);

                } else {
                    alert("No se pudo obtener la información del usuario.");
                }
            })
            .catch(error => {
                console.error("Error al obtener datos del usuario:", error);
            });
    });

    /** Eliminar Vertical */
    $(".btn-deleteVertical").click(function () {

        var verticalId = $(this).attr("verticalId");

        swal({
            title: "¿Seguro que desea borrar el Vertical?",
            text: "si no lo estás, cancela la acción",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar el Vertical'
        }).then((result)=>{
            if(result.value){
                window.location ="index.php?route=verticals&verticalId="+verticalId;
            }
        })

    })

});