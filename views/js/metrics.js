$(document).ready(function () {
    const API_BASE = 'https://algoritmo.digital/backend/public/api';

    if ($.fn.select2) {
        $('#newMetricPlatforms, #editMetricPlatforms').select2({
            width: '100%'
        });
    }

    const metricsTable = $('#metricsTable').DataTable({
        ajax: 'ajax/metrics.ajax.php?action=list',
        deferRender: true,
        retrieve: true,
        processing: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
        }
    });

    function normalizePlatformIds(platformIds) {
        return (platformIds || [])
            .map(id => parseInt(id, 10))
            .filter(id => Number.isInteger(id) && id > 0);
    }

    async function parseJsonSafe(res) {
        try {
            return await res.json();
        } catch (error) {
            return null;
        }
    }

    async function postJson(url, payload) {
        const options = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };

        if (typeof payload !== 'undefined') {
            options.body = JSON.stringify(payload);
        }

        const res = await fetch(url, options);
        const data = await parseJsonSafe(res);
        return { ok: res.ok, status: res.status, data };
    }

    function extractMetricId(response) {
        return parseInt(
            response?.id
            ?? response?.data?.id
            ?? response?.metric?.id
            ?? response?.data?.metric?.id,
            10
        ) || null;
    }

    async function syncMetricPlatforms(metricId, platformIds) {
        const normalizedPlatforms = normalizePlatformIds(platformIds);
        const normalizedMetricId = parseInt(metricId, 10);

        if (!Number.isInteger(normalizedMetricId) || normalizedMetricId <= 0) {
            throw new Error('No se pudo identificar la métrica para vincular plataformas.');
        }

        const assignUrl = `${API_BASE}/metrics/${normalizedMetricId}/assign-platforms`;
        const syncPayloads = [
            { platforms_ids: normalizedPlatforms },
            { platform_ids: normalizedPlatforms },
            { platforms: normalizedPlatforms },
            { ids: normalizedPlatforms }
        ];

        for (const payload of syncPayloads) {
            const result = await postJson(assignUrl, payload);
            console.log('[Metrics] assign-platforms attempt', {
                url: assignUrl,
                payload,
                status: result.status,
                response: result.data
            });

            if (result.ok) {
                return;
            }
        }

        if (normalizedPlatforms.length === 0) {
            throw new Error('No se pudo limpiar la relación de plataformas para esta métrica.');
        }

        for (const platformId of normalizedPlatforms) {
            const relateUrl = `${API_BASE}/platforms/${platformId}/metrics`;
            const relationPayloads = [
                { metric_id: normalizedMetricId },
                { metrics_ids: [normalizedMetricId] },
                { metrics: [normalizedMetricId] },
                { id: normalizedMetricId }
            ];

            let linked = false;

            for (const payload of relationPayloads) {
                const result = await postJson(relateUrl, payload);
                console.log('[Metrics] platform-metric link attempt', {
                    url: relateUrl,
                    payload,
                    status: result.status,
                    response: result.data
                });

                if (result.ok) {
                    linked = true;
                    break;
                }
            }

            if (!linked) {
                throw new Error(`No se pudo vincular la plataforma ${platformId} a la métrica.`);
            }
        }
    }

    async function fetchMetricPlatforms(metricId) {
        const res = await fetch(`${API_BASE}/metrics/${metricId}/platforms`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ metric_id: parseInt(metricId, 10) })
        });

        let data = [];
        try {
            const decoded = await res.json();
            data = Array.isArray(decoded?.data) ? decoded.data : (Array.isArray(decoded) ? decoded : []);
        } catch (error) {
            data = [];
        }

        if (!res.ok) {
            return [];
        }

        return data;
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

        const selectedPlatforms = normalizePlatformIds($('#newMetricPlatforms').val() || []);

        console.log('[Metrics] create metric REQUEST', {
            url: `${API_BASE}/metrics`,
            payload,
            selectedPlatforms
        });

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
            console.log('[Metrics] create metric RESPONSE', response);

            const metricId = extractMetricId(response);

            if (!metricId) {
                throw new Error(response?.message || 'No se pudo crear la métrica.');
            }

            await syncMetricPlatforms(metricId, selectedPlatforms);

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
            console.error('[Metrics] create ERROR', err);
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

                const currentPlatforms = Array.isArray(data.platforms) ? data.platforms : [];
                const currentPlatformIds = currentPlatforms.map(platform => String(platform.id));

                if (currentPlatformIds.length === 0) {
                    fetchMetricPlatforms(data.id)
                        .then(platforms => {
                            const platformIds = (platforms || []).map(platform => String(platform.id));
                            $('#editMetricPlatforms').val(platformIds).trigger('change');
                        })
                        .catch(() => {
                            $('#editMetricPlatforms').val([]).trigger('change');
                        });
                } else {
                    $('#editMetricPlatforms').val(currentPlatformIds).trigger('change');
                }
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

        const selectedPlatforms = normalizePlatformIds($('#editMetricPlatforms').val() || []);

        console.log('[Metrics] update metric REQUEST', {
            url: `${API_BASE}/metrics/${metricId}`,
            payload,
            selectedPlatforms
        });

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
            console.log('[Metrics] update metric RESPONSE', response);

            await syncMetricPlatforms(metricId, selectedPlatforms);

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
            console.error('[Metrics] update ERROR', err);
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
