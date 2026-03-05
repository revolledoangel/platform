$(document).ready(function () {
    const API_BASE = 'https://algoritmo.digital/backend/public/api';

    const metricsTable = $('#metricsTable').DataTable({
        ajax: 'ajax/metrics.ajax.php?action=list',
        deferRender: true,
        retrieve: true,
        processing: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
        }
    });

    function assignPlatforms(metricId, platformIds) {
        return fetch(`${API_BASE}/metrics/${metricId}/assign-platforms`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ platform_ids: platformIds || [] })
        });
    }

    $('#addMetricModal').on('shown.bs.modal', function () {
        $('#newMetricPlatforms').val(null).trigger('change');
    });

    $('#addMetricForm').on('submit', function (e) {
        e.preventDefault();

        const payload = {
            name: $('#newMetricName').val().trim(),
            code: $('#newMetricCode').val().trim(),
            active: $('#newMetricActive').is(':checked') ? 1 : 0
        };

        const selectedPlatforms = $('#newMetricPlatforms').val() || [];

        fetch(`${API_BASE}/metrics`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(async response => {
            if (!response || !response.id) {
                throw new Error(response?.message || 'No se pudo crear la métrica.');
            }

            await assignPlatforms(response.id, selectedPlatforms);

            swal({
                icon: 'success',
                title: 'Métrica creada',
                text: 'La métrica se creó correctamente.'
            }).then(() => {
                $('#addMetricModal').modal('hide');
                $('#addMetricForm')[0].reset();
                $('#newMetricPlatforms').val(null).trigger('change');
                metricsTable.ajax.reload();
            });
        })
        .catch(err => {
            swal({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo crear la métrica.'
            });
        });
    });

    $('#metricsTable tbody').on('click', '.btn-editMetric', function () {
        const metricId = $(this).data('metricid');

        $.ajax({
            url: 'ajax/metrics.ajax.php',
            method: 'GET',
            data: { metricId },
            dataType: 'json',
            success: function (data) {
                if (!data || !data.id) {
                    swal({ icon: 'error', title: 'Error', text: 'No se pudo obtener la métrica.' });
                    return;
                }

                $('#editMetricId').val(data.id);
                $('#editMetricName').val(data.name || '');
                $('#editMetricCode').val(data.code || '');
                $('#editMetricActive').prop('checked', !!data.active);

                const platformIds = (data.platforms || []).map(platform => String(platform.id));
                $('#editMetricPlatforms').val(platformIds).trigger('change');
            }
        });
    });

    $('#editMetricForm').on('submit', function (e) {
        e.preventDefault();

        const metricId = $('#editMetricId').val();
        const payload = {
            name: $('#editMetricName').val().trim(),
            code: $('#editMetricCode').val().trim(),
            active: $('#editMetricActive').is(':checked') ? 1 : 0
        };

        const selectedPlatforms = $('#editMetricPlatforms').val() || [];

        fetch(`${API_BASE}/metrics/${metricId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(async response => {
            if (!response || !response.id) {
                throw new Error(response?.message || 'No se pudo actualizar la métrica.');
            }

            await assignPlatforms(metricId, selectedPlatforms);

            swal({
                icon: 'success',
                title: 'Métrica actualizada',
                text: 'Los cambios se guardaron correctamente.'
            }).then(() => {
                $('#editMetricModal').modal('hide');
                metricsTable.ajax.reload();
            });
        })
        .catch(err => {
            swal({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo actualizar la métrica.'
            });
        });
    });

    $('#metricsTable tbody').on('click', '.btn-deleteMetric', function () {
        const metricId = $(this).data('metricid');

        swal({
            title: '¿Deseas eliminar esta métrica?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            buttons: ['Cancelar', 'Sí, eliminar'],
            dangerMode: true
        }).then((willDelete) => {
            if (!willDelete) {
                return;
            }

            fetch(`${API_BASE}/metrics/${metricId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(response => {
                if (response?.success || response?.message || response?.id) {
                    swal({
                        icon: 'success',
                        title: 'Eliminado',
                        text: 'La métrica fue eliminada.'
                    }).then(() => {
                        metricsTable.ajax.reload();
                    });
                    return;
                }

                throw new Error(response?.message || 'No se pudo eliminar la métrica.');
            })
            .catch(err => {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: err.message || 'No se pudo eliminar la métrica.'
                });
            });
        });
    });
});
