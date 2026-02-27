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

    // Manejar cambio de tipo de fee en modal agregar
    $('input[name="newFeeType"]').on('change', function() {
        var feeType = $(this).val();
        var $symbol = $('#newFeeSymbol');
        var $input = $('#newFeeInput');
        
        if (feeType === 'percentage') {
            $symbol.html('<i class="fa fa-percent"></i>');
            $input.attr('placeholder', 'Ej: 10');
        } else {
            $symbol.html('<i class="fa fa-money"></i>');
            $input.attr('placeholder', 'Ej: 1500');
        }
    });

    // Manejar cambio de tipo de fee en modal editar
    $('input[name="editFeeType"]').on('change', function() {
        var feeType = $(this).val();
        var $symbol = $('#editFeeSymbol');
        var $input = $('#editFeeInput');
        
        if (feeType === 'percentage') {
            $symbol.html('<i class="fa fa-percent"></i>');
            $input.attr('placeholder', 'Ej: 10');
        } else {
            $symbol.html('<i class="fa fa-money"></i>');
            $input.attr('placeholder', 'Ej: 1500');
        }
    });

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
                    
                    // Configurar tipo de fee
                    var feeType = data.fee_type || 'percentage'; // default percentage si no existe
                    $("input[name='editFeeType'][value='" + feeType + "']").prop('checked', true).trigger('change');
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

    /** Clonar Media Mix - NUEVO */
    $('#mediaMixRealEstateTable tbody').on("click", ".btn-cloneMediaMix", function () {
        var mediaMixId = $(this).attr("mediaMixId");
        
        // Obtener el client_id de la fila (está oculto en la columna 2)
        var row = $(this).closest('tr');
        var rowData = mediaMixTable.row(row).data();
        var clientId = rowData[2]; // Columna client_id (índice 2)
        
        console.log('Clonar Mix ID:', mediaMixId, 'Cliente ID:', clientId);
        
        // Guardar los IDs en el modal
        $('#cloneMixId').val(mediaMixId);
        $('#cloneClientId').val(clientId);
        
        // Limpiar campos
        $('#clonePeriodSelect').html('<option value="">Cargando períodos...</option>');
        $('#cloneOnlyAon').prop('checked', false);
        $('#cloneNewName').val(''); // Limpiar el nombre
        
        // Cargar períodos disponibles para este cliente
        $.ajax({
            url: 'ajax/mediaMixRealEstate.ajax.php',
            method: 'POST',
            data: { 
                action: 'getAvailablePeriods',
                client_id: clientId 
            },
            dataType: 'json',
            success: function(periods) {
                var options = '<option value="">-- Seleccione un período --</option>';
                if (periods && periods.length > 0) {
                    periods.forEach(function(period) {
                        options += '<option value="' + period.id + '">' + period.name + '</option>';
                    });
                } else {
                    options = '<option value="">No hay períodos disponibles</option>';
                }
                $('#clonePeriodSelect').html(options);
            },
            error: function() {
                $('#clonePeriodSelect').html('<option value="">Error al cargar períodos</option>');
            }
        });
        
        // Mostrar el modal
        $('#cloneMediaMixModal').modal('show');
    });
    
    /** Confirmar clonación */
    $('#confirmCloneBtn').on('click', function() {
        var mixId = $('#cloneMixId').val();
        var periodId = $('#clonePeriodSelect').val();
        var onlyAon = $('#cloneOnlyAon').is(':checked') ? 1 : 0;
        var newName = $('#cloneNewName').val().trim();
        
        if (!periodId) {
            swal('Error', 'Debe seleccionar un período', 'error');
            return;
        }
        
        // Confirmar acción
        var confirmText = onlyAon ? 'Se copiarán solo las campañas AON' : 'Se copiarán todas las campañas';
        if (newName) {
            confirmText += '\nNombre: ' + newName;
        } else {
            confirmText += '\nEl nombre se generará automáticamente';
        }
        
        swal({
            title: '¿Confirmar clonación?',
            text: confirmText,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, clonar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                // Mostrar loading
                swal({
                    title: 'Clonando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    onOpen: () => {
                        swal.showLoading();
                    }
                });
                
                // Llamada AJAX para clonar
                $.ajax({
                    url: 'ajax/mediaMixRealEstate.ajax.php',
                    method: 'POST',
                    data: {
                        action: 'cloneMix',
                        mix_id: mixId,
                        period_id: periodId,
                        only_aon: onlyAon,
                        new_name: newName
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Cerrar modal de clonación
                            $('#cloneMediaMixModal').modal('hide');
                            
                            // Mostrar éxito con botón para ver el mix generado
                            swal({
                                type: 'success',
                                title: '¡Clonación exitosa!',
                                html: '<p>' + response.message + '</p>' +
                                      '<p><strong>Nuevo Mix:</strong> ' + response.new_mix_name + '</p>',
                                showCancelButton: true,
                                confirmButtonText: '<i class="fa fa-eye"></i> Ver Mix Generado',
                                cancelButtonText: 'Cerrar',
                                confirmButtonColor: '#17a2b8',
                                cancelButtonColor: '#6c757d'
                            }).then((result) => {
                                if (result.value) {
                                    // Redirigir a los detalles del nuevo mix
                                    window.location = 'mediaMixRealEstateDetails?mediaMixId=' + response.new_mix_id;
                                } else {
                                    // Recargar la tabla
                                    mediaMixTable.ajax.reload();
                                }
                            });
                        } else {
                            swal({
                                type: 'error',
                                title: 'Error al clonar',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        swal({
                            type: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo completar la clonación. Error: ' + error
                        });
                    }
                });
            }
        });
    });
