<?php
// LLAMADAS A LOS MÉTODOS DEL CONTROLADOR
$createMediaMix = new MediaMixEcommerce_Controller();
$createMediaMix->ctrCreatemediaMixEcommerce();

$editMediaMix = new MediaMixEcommerce_Controller();
$editMediaMix->ctrEditmediaMixEcommerce();

$deleteMediaMix = new MediaMixEcommerce_Controller();
$deleteMediaMix->ctrDeletemediaMixEcommerce();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Mix de Medios Ecommerce
            <small>Administrar</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Mix de Medios Ecommerce</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">

                <?php
                // Capturamos los IDs de la URL, si existen.
                $selectedPeriodId = $_GET['period_id'] ?? null;
                $selectedClientId = $_GET['client_id'] ?? null;
                ?>

                

                <div class="form-group pull-left" style="width:300px; margin-bottom:20px;">
                    <label for="filterClient">Filtrar por Cliente:</label>
                    <select id="filterClient" class="form-control select2" style="width:100%;">
                        <option value="">-- Todos los clientes --</option>
                        <?php
                        $clientes = Clients_controller::ctrShowClients();
                        foreach ($clientes as $cliente) {
                            // Lógica de preselección:
                            // Si hay un client_id en la URL, se selecciona. Si no, no se hace nada.
                            $isSelected = ($selectedClientId && $cliente['id'] == $selectedClientId);

                            echo '<option value="' . htmlspecialchars($cliente["id"]) . '"' . ($isSelected ? ' selected' : '') . '>' . htmlspecialchars($cliente["name"]) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group pull-left" style="width:300px; margin-left:20px; margin-bottom:20px;">
                    <label for="filterPeriod">Filtrar por Período:</label>
                    <select id="filterPeriod" class="form-control select2" style="width:100%;">
                        <option value="">-- Todos los períodos --</option>
                        <?php
                        $periodos = Periods_controller::ctrShowPeriods();
                        $currentMonth = (int) date("n");
                        $currentYear = (int) date("Y");

                        foreach ($periodos as $periodo) {
                            $isSelected = false;

                            // Lógica de preselección:
                            // 1. Si hay un period_id en la URL, se prioriza esa selección.
                            if ($selectedPeriodId && $periodo['id'] == $selectedPeriodId) {
                                $isSelected = true;

                                // 2. Si no hay ID en la URL, se selecciona el periodo actual por defecto.
                            } elseif (!$selectedPeriodId && (int) $periodo['month_number'] === $currentMonth && (int) $periodo['year'] === $currentYear) {
                                $isSelected = true;
                            }

                            echo '<option value="' . htmlspecialchars($periodo["id"]) . '"' . ($isSelected ? ' selected' : '') . '>' . htmlspecialchars($periodo["name"]) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <button class="btn btn-primary pull-right" data-toggle="modal"
                    data-target="#addmediaMixEcommerceModal">
                    Agregar Mix de Medios
                </button>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="mediaMixEcommerceTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>Nombre</th>
                                <th>Cliente</th>
                                <th>Periodo</th>
                                <th>Moneda</th>
                                <th>Fee (%)</th>
                                <th>IGV (%)</th>
                                <th style="width:150px">Acciones</th> <!-- Aumentado de 120px a 150px -->
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

<div class="modal fade in" id="addmediaMixEcommerceModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form role="form" method="post" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Agregar Mix de Medios Inmobiliario</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <label>Nombre:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                                <input type="text" class="form-control" name="newName"
                                    placeholder="Ej: Campaña Enero (Opcional)">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Periodo:</label>
                                    <select class="form-control select2" name="newPeriodId" required
                                        style="width:100%;">
                                        <option value="">-- Selecciona un periodo --</option>
                                        <?php
                                        $periods = Periods_Controller::ctrShowPeriods();
                                        if (is_array($periods)) {
                                            $currentMonth = (int) date("n");
                                            $currentYear = (int) date("Y");
                                            foreach ($periods as $period) {
                                                $selected = '';
                                                if ((int) $period["month_number"] === $currentMonth && (int) $period["year"] === $currentYear) {
                                                    $selected = ' selected';
                                                }
                                                echo '<option value="' . htmlspecialchars($period["id"]) . '"' . $selected . '>' . htmlspecialchars($period["name"]) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cliente:</label>
                                    <select class="form-control select2" name="newClientId" required
                                        style="width:100%;">
                                        <option value="">-- Selecciona un cliente --</option>
                                        <?php
                                        $clients = Clients_Controller::ctrShowClients();
                                        if (is_array($clients)) {
                                            foreach ($clients as $client) {
                                                echo '<option value="' . htmlspecialchars($client["id"]) . '">' . htmlspecialchars($client["name"]) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Moneda:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-money"></i></span>
                                        <select class="form-control" name="newCurrency" required>
                                            <option value="USD">USD</option>
                                            <option value="PEN">PEN</option>
                                            <option value="CLP">CLP</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fee:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="newFeeSymbol"><i class="fa fa-percent"></i></span>
                                        <input type="number" step="any" class="form-control" name="newFee"
                                            id="newFeeInput" placeholder="Ej: 10" required>
                                    </div>
                                    <div class="radio-group" style="display: flex; gap: 15px; margin-top: 8px;">
                                        <label class="radio-inline">
                                            <input type="radio" name="newFeeType" value="percentage" checked> Porcentaje (%)
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="newFeeType" value="fixed"> Valor Fijo
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>IGV (%):</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                                        <input type="number" step="any" class="form-control" name="newIgv" value="18"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade in" id="editMediaMixEcommerceModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editMediaMixEcommerceForm" method="post" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Editar Mix de Medios Inmobiliario</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="editMediaMixId">
                    <div class="box-body">
                        <div class="form-group">
                            <label>Nombre:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                                <input type="text" class="form-control" name="editName"
                                    placeholder="Ej: Campaña Enero (Opcional)">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Periodo:</label>
                                    <select class="form-control select2" name="editPeriodId" required
                                        style="width:100%;">
                                        <?php
                                        $periods = Periods_Controller::ctrShowPeriods();
                                        if (is_array($periods)) {
                                            foreach ($periods as $period) {
                                                echo '<option value="' . htmlspecialchars($period["id"]) . '">' . htmlspecialchars($period["name"]) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cliente:</label>
                                    <select class="form-control select2" name="editClientId" required
                                        style="width:100%;">
                                        <?php
                                        $clients = Clients_Controller::ctrShowClients();
                                        if (is_array($clients)) {
                                            foreach ($clients as $client) {
                                                echo '<option value="' . htmlspecialchars($client["id"]) . '">' . htmlspecialchars($client["name"]) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Moneda:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-money"></i></span>
                                        <select class="form-control" name="editCurrency" required>
                                            <option value="USD">USD</option>
                                            <option value="PEN">PEN</option>
                                            <option value="CLP">CLP</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fee:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="editFeeSymbol"><i class="fa fa-percent"></i></span>
                                        <input type="number" step="any" class="form-control" name="editFee" 
                                            id="editFeeInput" required>
                                    </div>
                                    <div class="radio-group" style="display: flex; gap: 15px; margin-top: 8px;">
                                        <label class="radio-inline">
                                            <input type="radio" name="editFeeType" value="percentage"> Porcentaje (%)
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="editFeeType" value="fixed"> Valor Fijo
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>IGV (%):</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                                        <input type="number" step="any" class="form-control" name="editIgv" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Clonar Media Mix -->
<div class="modal fade" id="cloneMediaMixModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#28a745;color:#fff">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title"><i class="fa fa-clone"></i> Clonar Mix de Medios</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cloneMixId" value="">
                <input type="hidden" id="cloneClientId" value="">
                
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> Se creará una copia del Mix de Medios con sus campañas en el período seleccionado.
                </div>
                
                <div class="form-group">
                    <label>Nombre del nuevo Mix: <small class="text-muted">(Opcional)</small></label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                        <input type="text" class="form-control" id="cloneNewName" placeholder="Dejar vacío para generar automáticamente">
                    </div>
                    <small class="text-muted">Si no se especifica, se generará como: [Cliente] - [Período]</small>
                </div>
                
                <div class="form-group">
                    <label>Período destino: <span class="text-danger">*</span></label>
                    <select class="form-control" id="clonePeriodSelect" required>
                        <option value="">-- Seleccione un período --</option>
                    </select>
                    <small class="text-muted">Solo se muestran períodos sin Mix asignado para este cliente</small>
                </div>
                
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="cloneOnlyAon" value="1">
                            <strong>Copiar solo campañas Always On (AON)</strong>
                        </label>
                    </div>
                    <small class="text-muted">Si no se marca, se copiarán todas las campañas</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmCloneBtn">
                    <i class="fa fa-clone"></i> Clonar Mix
                </button>
            </div>
        </div>
    </div>
</div>
