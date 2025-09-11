<?php

// 1. VALIDACIÓN INICIAL
if (!isset($_GET['mediaMixId']) || !is_numeric($_GET['mediaMixId'])) {
    echo '<script>window.location = "mediaMixRealEstate";</script>';
    return;
}

// 2. LLAMAR A LA FUNCIÓN CORRECTA QUE DEVUELVE TODO EL OBJETO
$response = MediaMixRealEstateDetails_Controller::ctrShowDetails($_GET['mediaMixId']);

// 3. SEPARAMOS LA RESPUESTA EN DOS VARIABLES
// Usamos '?? null' y '?? []' como medida de seguridad por si la API falla.
$mmre = $response['mmre'] ?? null;
$details = $response['details'] ?? [];

// 4. VERIFICAR QUE EL MEDIA MIX EXISTE USANDO LA NUEVA VARIABLE $mmre
if (!$mmre) {
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

$projects = [];
if (isset($mmre['client_id'])) {
    $projects = MediaMixRealEstateDetails_Controller::getProjectsByClient($mmre['client_id']);
}


// Lógica del CRUD para los detalles
$createDetail = new MediaMixRealEstateDetails_Controller();
$createDetail->ctrCreateDetail();
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
        <div class="box">
            <div class="box-header with-border">
                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addDetailModal">
                    Agregar Detalle
                </button>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="detailsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Proyecto</th>
                                <th>Plataforma</th>
                                <th>Objetivo(s)</th>
                                <th>AON</th>
                                <th>Tipo Campaña</th>
                                <th>Canal</th>
                                <th>Segmentación</th>
                                <th>Formato</th>
                                <th>Moneda</th>
                                <th>Inversión</th>
                                <th>Distribución</th>
                                <th>Meta Proyectada</th>
                                <th>Tipo Resultado</th>
                                <th>CPR</th>
                                <th>Comentarios</th>
                                <th>Estado</th>
                                <th style="width:100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>


<!-- Modal agregar detalle -->
<div class="modal fade in" id="addDetailModal" data-client-id="<?php echo htmlspecialchars($mmre['client_id']); ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form role="form" method="post" autocomplete="off">
                <input type="hidden" name="newMediaMixRealEstateId" value="<?php echo htmlspecialchars($mmre['id']); ?>">

                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">Agregar Detalle a "<?php echo htmlspecialchars($mmre['name']); ?>"</h4>
                </div>

                <div class="modal-body">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Proyecto para <?php echo htmlspecialchars($mmre['client_name']); ?> <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="newDetailProject" name="newProjectId" required style="width:100%;">
                                        <option value="">Cargando proyectos...</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Plataforma <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="newDetailPlatform" name="newPlatformId" required style="width:100%;"></select>
                                </div>
                                <div class="form-group">
                                    <label>Canal <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="newDetailChannel" name="newChannelId" required style="width:100%;"></select>
                                </div>
                                <div class="form-group">
                                    <label>Tipo de Campaña <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="newDetailCampaignType" name="newCampaignTypeId" required style="width:100%;"></select>
                                </div>
                                <div class="form-group">
                                    <label>Segmentación <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="newDetailSegmentation" name="newSegmentation" required style="width:100%;">
                                        <option value="Prospecting (Intereses / Comportamientos)">Prospecting (Intereses / Comportamientos)</option>
                                        <option value="Prospecting (Palabras Clave Genéricas)">Prospecting (Palabras Clave Genéricas)</option>
                                        <option value="Públicos Similares (Lookalikes - LAL)">Públicos Similares (Lookalikes - LAL)</option>
                                        <option value="Prospecting Amplio / Automatizado">Prospecting Amplio / Automatizado</option>
                                        <option value="Remarketing de Interacción">Remarketing de Interacción</option>
                                        <option value="Remarketing de Tráfico Web">Remarketing de Tráfico Web</option>
                                        <option value="Remarketing (Palabras Clave de Marca)">Remarketing (Palabras Clave de Marca)</option>
                                        <option value="Remarketing de Alta Intención">Remarketing de Alta Intención</option>
                                        <option value="Clientes Actuales (Compradores)">Clientes Actuales (Compradores)</option>
                                        <option value="Clientes Potenciales (Leads)">Clientes Potenciales (Leads)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Objetivo <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="newDetailObjective" name="newObjectiveId" required style="width:100%;"></select>
                                </div>
                                <div class="form-group">
                                    <label id="projectionLabel">Proyección <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="newProjection" id="newDetailProjection" min="0" step="1" required placeholder="Ej: 1000">
                                </div>
                                <div class="form-group">
                                    <label>Formato(s) <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="newDetailFormat" name="newFormat[]" multiple="multiple" required style="width:100%;"></select>
                                </div>
                                <div class="form-group">
                                    <label>Inversión (en <?php echo htmlspecialchars($mmre['currency']); ?>): <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="newInvestment" id="newDetailInvestment" min="0" step="0.01" required placeholder="Ej: 1000.00">
                                </div>
                                <div class="form-group">
                                    <label><input type="checkbox" id="newDetailAon" name="newAon" value="1"> Always On (AON)</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Comentarios (opcional)</label>
                                    <textarea class="form-control" name="newComments" id="newDetailComments" rows="3" placeholder="Agrega comentarios adicionales..."></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Estado <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="newDetailStatus" name="newStatus" required style="width:100%;">
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
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Detalle</button>
                </div>
            </form>
        </div>
    </div>
</div>