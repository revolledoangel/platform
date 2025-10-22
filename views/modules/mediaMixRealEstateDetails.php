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

// 3. PROCESAR ACTUALIZACIÓN DE CONFIGURACIÓN SOLO SI SE ENVIÓ EL FORMULARIO ESPECÍFICO
if (isset($_POST['configMediaMixId']) && 
    isset($_POST['configName']) && 
    isset($_POST['configCurrency']) && 
    isset($_POST['configFee']) && 
    isset($_POST['configFeeType']) && 
    isset($_POST['configIgv']) &&
    $_POST['configMediaMixId'] == $_GET['mediaMixId']) { // Validación adicional
    
    $updateConfig = new MediaMixRealEstateDetails_Controller();
    $updateConfig->ctrUpdateMediaMixConfig();
}

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

        <!-- Modal Configurar Mix -->
        <div class="modal fade" id="configMixModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form id="configMixForm" method="post" autocomplete="off">
                        <input type="hidden" name="configMediaMixId" value="<?php echo htmlspecialchars($mmre['id']); ?>">
                        <div class="modal-header" style="background:#17a2b8;color:#fff">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title"><i class="fa fa-cog"></i> Configuración del Mix de Medios</h4>
                        </div>
                        <div class="modal-body">
                            <div class="box-body">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> Estos cambios afectarán el cálculo de totales y comisiones.
                                </div>
                                <div class="form-group">
                                    <label>Nombre del Mix:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                                        <input type="text" class="form-control" name="configName" 
                                               value="<?php echo htmlspecialchars($mmre['name']); ?>"
                                               placeholder="Ej: Campaña Enero">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Moneda:</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-money"></i></span>
                                                <select class="form-control" name="configCurrency" required>
                                                    <option value="USD" <?php echo $mmre['currency'] === 'USD' ? 'selected' : ''; ?>>USD</option>
                                                    <option value="PEN" <?php echo $mmre['currency'] === 'PEN' ? 'selected' : ''; ?>>PEN</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>IGV (%):</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                                                <input type="number" step="any" class="form-control" name="configIgv" 
                                                       value="<?php echo htmlspecialchars($mmre['igv']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Fee de Agencia:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="configFeeSymbol">
                                            <?php if (isset($mmre['fee_type']) && $mmre['fee_type'] === 'fixed'): ?>
                                                <i class="fa fa-money"></i>
                                            <?php else: ?>
                                                <i class="fa fa-percent"></i>
                                            <?php endif; ?>
                                        </span>
                                        <input type="number" step="any" class="form-control" name="configFee" 
                                               id="configFeeInput" value="<?php echo htmlspecialchars($mmre['fee']); ?>" required>
                                    </div>

                                    <!-- Input hidden para el tipo de fee -->
                                    <input type="hidden" name="configFeeType" id="configFeeTypeHidden" 
                                           value="<?php echo htmlspecialchars($mmre['fee_type'] ?? 'percentage'); ?>">

                                    <div class="radio-group" style="display: flex; gap: 15px; margin-top: 8px;">
                                        <label class="radio-inline">
                                            <input type="radio" name="configFeeType_ui" value="percentage" 
                                                   <?php echo (!isset($mmre['fee_type']) || $mmre['fee_type'] === 'percentage') ? 'checked' : ''; ?>> 
                                            Porcentaje (%)
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="configFeeType_ui" value="fixed" 
                                                   <?php echo (isset($mmre['fee_type']) && $mmre['fee_type'] === 'fixed') ? 'checked' : ''; ?>> 
                                            Valor Fijo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-info">
                                <i class="fa fa-save"></i> Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabla de detalles -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Detalles registrados</h3>
                <button class="btn btn-success pull-right" id="exportExcelBtn" style="margin-left:10px;">
                    <i class="fa fa-file-excel-o"></i> Exportar a Excel
                </button>
                <button class="btn btn-info pull-right" data-toggle="modal" data-target="#configMixModal" style="margin-left:10px;">
                    <i class="fa fa-cog"></i> Configurar Mix
                </button>
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
                            
                            // CALCULAR CORRECTAMENTE EL TOTAL DEL PROYECTO
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
                        
                        <!-- FILA DE SUBTOTAL POR PROYECTO - FUERA DEL ROWSPAN -->
                        <tr style="background:#f5f5f5;font-weight:bold;">
                            <td colspan="8" style="text-align:right; padding-right: 10px;"></td>
                            <td style="text-align:right;">
                                <strong><?php echo htmlspecialchars($mmre['currency']) . ' ' . number_format($projectTotal, 2); ?></strong>
                            </td>
                            <td style="text-align:right;">
                                <strong>100%</strong>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sección de Totales -->
        <div style="margin-top: 20px;">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-calculator"></i> Resumen de Inversión</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr class="info">
                                    <td class="text-right"><strong>Inversión Neta Total:</strong></td>
                                    <td class="text-right" style="font-size: 16px;">
                                        <strong id="inversionNetaTotal">
                                            <?php 
                                            $totalInversion = 0;
                                            foreach ($details as $d) {
                                                $totalInversion += floatval($d['investment']);
                                            }
                                            echo htmlspecialchars($mmre['currency']) . ' ' . number_format($totalInversion, 2);
                                            ?>
                                        </strong>
                                    </td>
                                </tr>
                                <tr class="warning">
                                    <td class="text-right"><strong>Comisión Agencia:</strong></td>
                                    <td class="text-right" style="font-size: 16px;">
                                        <strong id="comisionAgencia">
                                            <?php
                                            // Calcular comisión según tipo de fee
                                            $comision = 0;
                                            $feeDisplay = '';
                                            
                                            if (isset($mmre['fee_type']) && $mmre['fee_type'] === 'fixed') {
                                                $comision = floatval($mmre['fee']);
                                                $feeDisplay = '(fijo)';
                                            } else {
                                                $comision = $totalInversion * (floatval($mmre['fee']) / 100);
                                                $feeDisplay = '(' . $mmre['fee'] . '%)';
                                            }
                                            
                                            echo htmlspecialchars($mmre['currency']) . ' ' . number_format($comision, 2);
                                            ?>
                                            <small class="text-muted"><?php echo $feeDisplay; ?></small>
                                        </strong>
                                    </td>
                                </tr>
                                <tr class="active">
                                    <td class="text-right"><strong>Pauta + Comisión:</strong></td>
                                    <td class="text-right" style="font-size: 18px;">
                                        <strong id="pautaComision">
                                            <?php
                                            $pautaComision = $totalInversion + $comision;
                                            echo htmlspecialchars($mmre['currency']) . ' ' . number_format($pautaComision, 2);
                                            ?>
                                        </strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr class="warning">
                                    <td class="text-right"><strong>IGV (<?php echo $mmre['igv']; ?>%):</strong></td>
                                    <td class="text-right" style="font-size: 16px;">
                                        <strong id="igvCalculado">
                                            <?php
                                            $igvCalculado = $pautaComision * (floatval($mmre['igv']) / 100);
                                            echo htmlspecialchars($mmre['currency']) . ' ' . number_format($igvCalculado, 2);
                                            ?>
                                        </strong>
                                    </td>
                                </tr>
                                <tr class="success" style="font-size: 20px;">
                                    <td class="text-right"><strong>INVERSIÓN TOTAL + IGV:</strong></td>
                                    <td class="text-right" style="font-size: 20px;">
                                        <strong id="inversionTotalIgv" style="color: #00a65a;">
                                            <?php
                                            $inversionTotalIgv = $pautaComision + $igvCalculado;
                                            echo htmlspecialchars($mmre['currency']) . ' ' . number_format($inversionTotalIgv, 2);
                                            ?>
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        <small>Los totales se actualizan automáticamente</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Botón flotante para ir a totales -->
<div id="floatingTotalsBtn" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; display: none;">
    <button class="btn btn-warning btn-lg" style="border-radius: 50%; width: 60px; height: 60px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);" 
            title="Ver totales">
        <i class="fa fa-calculator" style="font-size: 20px;"></i>
    </button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<style>
    /* Centrar contenido de todas las celdas de la tabla */
    #detailsTable td, 
    #detailsTable th {
        text-align: center !important;
        vertical-align: middle !important;
    }
    
    /* Mantener alineación a la derecha solo para montos y porcentajes */
    #detailsTable td:nth-child(9),  /* Columna de Inversión */
    #detailsTable td:nth-child(10)  /* Columna de Distribución */
    {
        text-align: right !important;
        vertical-align: middle !important;
    }
    
    /* Centrar contenido de filas de subtotales */
    #detailsTable tr[style*="background:#f5f5f5"] td {
        text-align: center !important;
        vertical-align: middle !important;
    }
    
    /* Mantener alineación a la derecha para montos en subtotales */
    #detailsTable tr[style*="background:#f5f5f5"] td:nth-child(9),
    #detailsTable tr[style*="background:#f5f5f5"] td:nth-child(10) {
        text-align: right !important;
        vertical-align: middle !important;
    }
</style>
<script>
    // Solo variables globales - NO FUNCIONES
    window.mmreId = <?php echo (int) $mmre['id']; ?>;
    window.clientName = <?php echo json_encode($mmre['client_name']); ?>;
    window.currency = <?php echo json_encode($mmre['currency']); ?>;
    window.periodName = <?php echo json_encode($mmre['period_name']); ?>;
    window.mmreFee = <?php echo floatval($mmre['fee']); ?>; // Asegurar que es número
    window.mmreFeeType = <?php echo json_encode($mmre['fee_type'] ?? 'percentage'); ?>;
    window.mmreIgv = <?php echo floatval($mmre['igv']); ?>; // Asegurar que es número
</script>