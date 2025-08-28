$(document).ready(function () {
    // 1. Obtener el ID del Media Mix desde la URL
    const urlParams = new URLSearchParams(window.location.search);
    const mediaMixId = urlParams.get('mediaMixId');

    // 2. Si no hay ID, no hacer nada para evitar errores
    if (!mediaMixId) {
        console.error("No se encontró el mediaMixId en la URL. La tabla no se puede inicializar.");
        return;
    }

    // 3. Inicializar DataTable con un manejador AJAX manual para depuración
    console.log("Inicializando DataTable para mediaMixId:", mediaMixId);

    $('#detailsTable').DataTable({
        processing: true, // Mantenemos el indicador de "Procesando..."
        ajax: function (data, callback, settings) {
            
            console.log("DataTable está solicitando datos...");

            $.ajax({
                url: `ajax/mediaMixRealEstateDetails.ajax.php?action=list&mediaMixId=${mediaMixId}`,
                type: 'GET',
                dataType: 'json'
            })
            .done(function (response) {
                console.log("✅ Petición AJAX exitosa. Respuesta recibida:", response);
                // 'callback' es la función de DataTables que procesa el JSON
                callback(response);
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.error("❌ Petición AJAX falló.");
                console.error("Status:", textStatus);
                console.error("Error:", errorThrown);
                console.error("Respuesta del servidor:", jqXHR.responseText);
                
                // Le decimos a DataTables que falle para que no se quede "Procesando..."
                // y mostramos un error en la tabla.
                $('#detailsTable').DataTable().clear().draw();
                alert("Error al cargar los datos. Revisa la consola para más detalles.");
            })
            .always(function() {
                console.log("Petición AJAX completada (éxito o fallo).");
            });
        },
        deferRender: true,
        retrieve: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });
});