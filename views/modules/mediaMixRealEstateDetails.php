<?php

// 1. VALIDACIÓN INICIAL
if (!isset($_GET['mediaMixId']) || !is_numeric($_GET['mediaMixId'])) {
    echo '<script>window.location = "mediaMixRealEstate";</script>';
    return;
}

// 2. OBTENER SOLO EL MIX DE MEDIOS (NO DETALLES NI PROYECTOS)
$mmreData = MediaMixRealEstateDetails_Controller::ctrGetMediaMixById($_GET['mediaMixId']);
if (!$mmreData) {
    echo '<script>
        swal({
            type: "error",
            title: "Error",
            text: "¡Mix de Medios no encontrado!",
        }).then(() => {
            window.location = "mediaMixRealEstate";
        });
    </script>';
    return;
}
$mmre = $mmreData['mmre'];
$details = $mmreData['details'];

// Lógica del CRUD para los detalles (asegúrate de que esto esté antes del HTML)
//$createDetail = new MediaMixRealEstateDetails_Controller();
//$createDetail->ctrCreateDetail();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <?php echo htmlspecialchars($mmre['name']); ?>
            <small>Detalles del Mix de Medios</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="mediaMixRealEstate">Mix de Medios Inmobiliario</a></li>
            <li class="active">Detalles</li>
        </ol>
    </section>
    <section class="content">
        <!-- Modal agregar detalle -->
        <div class="modal fade in" id="addDetailModal"
            data-client-id="<?php echo htmlspecialchars($mmre['client_id']); ?>">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form role="form" method="post" autocomplete="off">
                        <input type="hidden" name="newMediaMixRealEstateId"
                            value="<?php echo htmlspecialchars($mmre['id']); ?>">
                        <input type="hidden" name="result_type" id="resultTypeHidden">
                        <div class="modal-header" style="background:#00013b;color:#fff">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title">Agregar Detalle a "<?php echo htmlspecialchars($mmre['name']); ?>"
                            </h4>
                        </div>
                        <div class="modal-body">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Proyecto para <?php echo htmlspecialchars($mmre['client_name']); ?>
                                                <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="newDetailProject"
                                                name="newProjectId" required style="width:100%;">
                                                <option value="">Cargando proyectos...</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Plataforma <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="newDetailPlatform"
                                                name="newPlatformId" required style="width:100%;"></select>
                                        </div>
                                        <div class="form-group">
                                            <label>Canal <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="newDetailChannel"
                                                name="newChannelId" required style="width:100%;"></select>
                                        </div>
                                        <div class="form-group">
                                            <label>Tipo de Campaña <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="newDetailCampaignType"
                                                name="newCampaignTypeId" required style="width:100%;"></select>
                                        </div>
                                        <div class="form-group">
                                            <label>Segmentación <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="newDetailSegmentation"
                                                name="newSegmentation[]" multiple="multiple" required style="width:100%;">
                                                <option value="Prospecting (Intereses / Comportamientos)">Prospecting
                                                    (Intereses / Comportamientos)</option>
                                                <option value="Prospecting (Palabras Clave Genéricas)">Prospecting
                                                    (Palabras Clave Genéricas)</option>
                                                <option value="Públicos Similares (Lookalikes - LAL)">Públicos Similares
                                                    (Lookalikes - LAL)</option>
                                                <option value="Prospecting Amplio / Automatizado">Prospecting Amplio /
                                                    Automatizado</option>
                                                <option value="Remarketing de Interacción">Remarketing de Interacción
                                                </option>
                                                <option value="Remarketing de Tráfico Web">Remarketing de Tráfico Web
                                                </option>
                                                <option value="Remarketing (Palabras Clave de Marca)">Remarketing
                                                    (Palabras Clave de Marca)</option>
                                                <option value="Remarketing de Alta Intención">Remarketing de Alta
                                                    Intención</option>
                                                <option value="Clientes Actuales (Compradores)">Clientes Actuales
                                                    (Compradores)</option>
                                                <option value="Clientes Potenciales (Leads)">Clientes Potenciales
                                                    (Leads)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Objetivo <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="newDetailObjective"
                                                name="newObjectiveId" required style="width:100%;"></select>
                                        </div>
                                        <div class="form-group">
                                            <label>Tipo Resultado</label>
                                            <input type="text" class="form-control" id="newDetailResultType"
                                                name="newResultType" placeholder="Tipo de resultado" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label id="projectionLabel">Proyección <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="newProjection"
                                                id="newDetailProjection" min="0" step="1" required
                                                placeholder="Ej: 1000">
                                        </div>
                                        <div class="form-group">
                                            <label>Formato(s) <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="newDetailFormat" name="newFormat[]"
                                                multiple="multiple" required style="width:100%;"></select>
                                        </div>
                                        <div class="form-group">
                                            <label>Inversión (en <?php echo htmlspecialchars($mmre['currency']); ?>):
                                                <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="newInvestment"
                                                id="newDetailInvestment" min="0" step="0.01" required
                                                placeholder="Ej: 1000.00">
                                        </div>
                                        <div class="form-group">
                                            <label><input type="checkbox" id="newDetailAon" name="newAon" value="1">
                                                Always On (AON)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Comentarios (opcional)</label>
                                            <textarea class="form-control" name="newComments" id="newDetailComments"
                                                rows="3" placeholder="Agrega comentarios adicionales..."></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>Estado <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="newDetailStatus" name="newStatus"
                                                required style="width:100%;">
                                                <option value="Activa" selected>Activa</option>
                                                <option value="Por confirmar">Por confirmar</option>
                                                <option value="Suspendida">Suspendida</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left"
                                data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Detalle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal editar detalle -->
        <div class="modal fade" id="editDetailModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form id="editDetailForm" role="form" method="post" autocomplete="off">
                        <input type="hidden" name="editDetailId" id="editDetailId">
                        <input type="hidden" name="editMediaMixRealEstateId"
                            value="<?php echo htmlspecialchars($mmre['id']); ?>">
                        <input type="hidden" name="editResultType" id="editResultTypeHidden">
                        <div class="modal-header" style="background:#00013b;color:#fff">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title">Editar Detalle</h4>
                        </div>
                        <div class="modal-body">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Proyecto <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="editDetailProject"
                                                name="editProjectId" required style="width:100%;"></select>
                                        </div>
                                        <div class="form-group">
                                            <label>Plataforma <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="editDetailPlatform"
                                                name="editPlatformId" required style="width:100%;"></select>
                                        </div>
                                        <div class="form-group">
                                            <label>Canal <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="editDetailChannel"
                                                name="editChannelId" required style="width:100%;"></select>
                                        </div>
                                        <div class="form-group">
                                            <label>Tipo de Campaña <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="editDetailCampaignType"
                                                name="editCampaignTypeId" required style="width:100%;"></select>
                                        </div>
                                        <div class="form-group">
                                            <label>Segmentación <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="editDetailSegmentation"
                                                name="editSegmentation[]" multiple="multiple" required style="width:100%;"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Objetivo <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="editDetailObjective"
                                                name="editObjectiveId" required style="width:100%;"></select>
                                        </div>
                                        <div class="form-group">
                                            <label>Tipo Resultado</label>
                                            <input type="text" class="form-control" id="editDetailResultType"
                                                name="editResultType" placeholder="Tipo de resultado" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label id="editProjectionLabel">Proyección <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="editProjection"
                                                id="editDetailProjection" min="0" step="1" required
                                                placeholder="Ej: 1000">
                                        </div>
                                        <div class="form-group">
                                            <label>Formato(s) <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="editDetailFormat"
                                                name="editFormat[]" multiple="multiple" required
                                                style="width:100%;"></select>
                                        </div>
                                        <div class="form-group">
                                            <label>Inversión (en <?php echo htmlspecialchars($mmre['currency']); ?>):
                                                <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="editInvestment"
                                                id="editDetailInvestment" min="0" step="0.01" required
                                                placeholder="Ej: 1000.00">
                                        </div>
                                        <div class="form-group">
                                            <label><input type="checkbox" id="editDetailAon" name="editAon" value="1">
                                                Always On (AON)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Comentarios (opcional)</label>
                                            <textarea class="form-control" name="editComments" id="editDetailComments"
                                                rows="3" placeholder="Agrega comentarios adicionales..."></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>Estado <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="editDetailStatus" name="editStatus"
                                                required style="width:100%;">
                                                <option value="Activa">Activa</option>
                                                <option value="Por confirmar">Por confirmar</option>
                                                <option value="Suspendida">Suspendida</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left"
                                data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabla de detalles -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Detalles registrados</h3>
                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addDetailModal"
                    style="margin-left:10px;">Agregar Detalle</button>
            </div>
            <div class="box-body">
                <table id="detailsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Proyecto</th>
                            <th>Plataforma</th>
                            <th>Objetivo</th>
                            <th>AON</th>
                            <th>Tipo Campaña</th>
                            <th>Canal</th>
                            <th>Segmentación</th>
                            <th>Formatos</th>
                            <th>Inversión</th>
                            <th>Distribución</th>
                            <th>Estado</th>
                            <th>Proyección</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Agrupar detalles por proyecto y plataforma
                        $grouped = [];
                        foreach ($details as $d) {
                            $grouped[$d['project_name']][$d['platform_name']][] = $d;
                        }
                        foreach ($grouped as $project => $platforms):
                            $projectRowspan = 0;
                            $projectTotal = 0;
                            foreach ($platforms as $rows) {
                                $projectRowspan += count($rows);
                                foreach ($rows as $d) {
                                    $projectTotal += floatval($d['investment']);
                                }
                            }
                            $firstProjectRow = true;
                            foreach ($platforms as $platform => $rows):
                                $platformRowspan = count($rows);
                                $firstPlatformRow = true;
                                foreach ($rows as $d):
                                    $distribution = $projectTotal > 0 ? round(floatval($d['investment']) * 100 / $projectTotal, 2) : 0;
                                    $projectionResult = htmlspecialchars($d['projection']) . ' ' . htmlspecialchars($d['result_type']);
                        ?>
                        <tr>
                            <?php if ($firstProjectRow): ?>
                            <td rowspan="<?php echo $projectRowspan; ?>" style="vertical-align: middle; text-align: center;"><strong><?php echo htmlspecialchars($project); ?></strong></td>
                            <?php $firstProjectRow = false; endif; ?>
                            <?php if ($firstPlatformRow): ?>
                            <td rowspan="<?php echo $platformRowspan; ?>" style="vertical-align: middle; text-align: center;"><?php echo htmlspecialchars($platform); ?></td>
                            <?php $firstPlatformRow = false; endif; ?>
                            <td><?php echo isset($d['objectives_names'][0]) ? htmlspecialchars($d['objectives_names'][0]) : ''; ?></td>
                            <td><?php echo $d['aon'] ? 'Sí' : 'No'; ?></td>
                            <td><?php echo htmlspecialchars($d['campaign_type_name']); ?></td>
                            <td><?php echo htmlspecialchars($d['channel_name']); ?></td>
                            <td><?php echo htmlspecialchars($d['segmentation']); ?></td>
                            <td><?php echo is_array($d['formats_names']) ? implode(', ', $d['formats_names']) : ''; ?></td>
                            <td><?php echo htmlspecialchars($mmre['currency']) . ' ' . number_format(floatval($d['investment']), 2); ?></td>
                            <td><?php echo $distribution . '%'; ?></td>
                            <td><?php echo htmlspecialchars($d['state']); ?></td>
                            <td><?php echo $projectionResult; ?></td>
                            <td style="white-space: nowrap;">
                                <button class="btn btn-xs btn-warning btn-editDetail" title="Editar" data-detail-id="<?php echo $d['id']; ?>"><i class="fa fa-pencil"></i></button>
                                <button class="btn btn-xs btn-info btn-copyCode" 
                                        title="Código: <?php echo htmlspecialchars($d['platform_code'] . $mmre['client_code'] . $d['project_code']); ?>, Plataforma: <?php echo htmlspecialchars($d['platform_name']); ?> (<?php echo htmlspecialchars($d['platform_code']); ?>) + Cliente: <?php echo htmlspecialchars($mmre['client_name']); ?> (<?php echo htmlspecialchars($mmre['client_code']); ?>) + Proyecto: <?php echo htmlspecialchars($d['project_name']); ?> (<?php echo htmlspecialchars($d['project_code']); ?>)"
                                        data-platform-code="<?php echo htmlspecialchars($d['platform_code']); ?>"
                                        data-client-code="<?php echo htmlspecialchars($mmre['client_code']); ?>"
                                        data-project-code="<?php echo htmlspecialchars($d['project_code']); ?>">
                                    <i class="fa fa-copy"></i>
                                </button>
                                <button class="btn btn-xs btn-danger" title="Eliminar"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; endforeach; ?>
                        <tr style="background:#f5f5f5;font-weight:bold;">
                            <td colspan="8"></td>
                            <td><?php echo htmlspecialchars($mmre['currency']) . ' ' . number_format($projectTotal, 2); ?></td>
                            <td>100%</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
<script>
    window.mmreId = <?php echo (int) $mmre['id']; ?>;
</script>
</script>
</script>