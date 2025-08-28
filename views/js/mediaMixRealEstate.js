$(document).ready(function () {
    // Inicializar Select2
    $('.select2').select2();

    var mediaMixTable = $('#mediaMixRealEstateTable').DataTable({
        ajax: "ajax/mediaMixRealEstate.ajax.php?action=list",
        deferRender: true,
        retrieve: true,
        processing: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },
        "columnDefs": [
            { "targets": [2, 3], "visible": false, "searchable": true }
        ]
    });

    // --- LÓGICA DE FILTRADO (INICIAL Y AL CAMBIAR) ---

    $('#filterClient').on('change', function () {
        var selectedValue = $(this).val();
        mediaMixTable.column(2).search(selectedValue ? '^' + selectedValue + '$' : '', true, false);

        // Redibujar la tabla solo si ambos filtros han sido procesados
        if ($('#filterPeriod').data('isFiltering') !== true) {
            mediaMixTable.draw();
        }
    });

    $('#filterPeriod').on('change', function () {
        // Marcamos que este filtro está en proceso para evitar doble dibujado
        $(this).data('isFiltering', true);
        var selectedValue = $(this).val();
        mediaMixTable.column(3).search(selectedValue ? '^' + selectedValue + '$' : '', true, false).draw();
        $(this).data('isFiltering', false);
    });

    // --- NUEVO: APLICAR FILTROS AL CARGAR LA PÁGINA ---
    // Disparamos el evento 'change' para que se apliquen los valores preseleccionados.
    // Se dispara primero el de cliente, y el de período es el que finalmente redibuja la tabla.
    $('#filterClient').trigger('change');
    $('#filterPeriod').trigger('change');

    /* Editar Media Mix */
    $('#mediaMixRealEstateTable tbody').on("click", ".btn-editMediaMix", function () {
        var mediaMixId = $(this).attr("mediaMixId");

        fetch(`https://algoritmo.digital/backend/public/api/mmres/${mediaMixId}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    $("input[name='editMediaMixId']").val(data.id);
                    $("input[name='editName']").val(data.name);

                    // Para Select2, se establece el valor y se dispara un evento 'change'
                    $("select[name='editPeriodId']").val(data.period_id).trigger('change');
                    $("select[name='editClientId']").val(data.client_id).trigger('change');

                    $("select[name='editCurrency']").val(data.currency);
                    $("input[name='editFee']").val(data.fee);
                    $("input[name='editIgv']").val(data.igv);
                } else {
                    alert("No se pudo obtener la información del registro.");
                }
            })
            .catch(error => console.error("Error al obtener datos: ", error));
    });

    /** Eliminar Media Mix */
    $('#mediaMixRealEstateTable tbody').on("click", ".btn-deleteMediaMix", function () {
        var mediaMixId = $(this).attr("mediaMixId");

        swal({
            title: "¿Seguro que desea borrar el registro?",
            text: "Esta acción no se puede deshacer.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar registro'
        }).then((result) => {
            if (result.value) {
                window.location = "index.php?route=mediaMixRealEstate&mediaMixId=" + mediaMixId;
            }
        });
    });
});