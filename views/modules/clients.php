<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Clientes
            <small>Administrar Clientes</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Clientes</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="box">

            <div class="box-header with-border">

                <div class="form-group pull-left">
                    <label for="filtroEstado">Filtrar por estado:</label>
                    <select id="filtroEstado" class="form-control" style="width: 200px; display: inline-block;">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>

                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addClientModal">
                    Agregar Cliente
                </button>

            </div>

            <!-- /.box-header -->
            <div class="box-body">
                <div class="table-responsive">
                    <table id="clientsTable" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th style="max-width:200px">Nombre</th>
                                <th style="max-width:40px">Código</th>
                                <th style="max-width:200px">Analista</th>
                                <th style="max-width:100px">Vertical</th>
                                <th style="display:none;">Estado (filtro)</th>
                                <th style="max-width:40px">Estado</th>
                                <th style="max-width:150px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>

                    </table>
                </div>
            </div>
            <!-- /.box-body -->
        </div>

    </section>
    <!-- /.content -->
</div>

<?php
$perfil = $_SESSION['perfil'] ?? '';
$isAdmin = in_array($perfil, ['Super', 'Administrador'], true);
?>

<!-- Modal agregar cliente -->
<div class="modal fade in" id="addClientModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form role="form" method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Agregar Cliente</h4>
                </div>
                <div class="modal-body">

                    <div class="box-body">

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="ion ion-person-add"></i></span>
                                <input type="text" class="form-control" placeholder="Nombre Cliente"
                                    name="newClientName" required autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group" style="higth:34px;">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-user"></i></span>
                                <select class="form-control select2" style="width: 100%;" name="newClientUser">
                                    <option value="" selected="selected">Asignar Analista</option>
                                    <?php
                                    $usuarios = Users_controller::ctrShowUsers();

                                    if (isset($usuarios) && is_array($usuarios)) {
                                        foreach ($usuarios as $usuario) {
                                            echo '<option value="' . htmlspecialchars($usuario["id"]) . '">' . htmlspecialchars($usuario["name"]) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group" style="higth:34px;">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-tasks"></i></span>
                                <select name="newClientVerticals[]" class="form-control select2" multiple="multiple"
                                    data-placeholder="Vertical(es)" style="width: 100%;">
                                    <?php
                                    $verticales = Verticals_controller::ctrShowVerticals();

                                    if (isset($verticales) && is_array($verticales)) {
                                        foreach ($verticales as $vertical) {
                                            echo '<option value="' . htmlspecialchars($vertical["id"]) . '">' . htmlspecialchars($vertical["name"]) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="glyphicon glyphicon-qrcode"></i></span>
                                <input type="text" class="form-control"
                                    placeholder="Código de cliente (dejar en blanco para autogenerarlo)"
                                    name="newClientCode" autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-bar-chart"></i></span>
                                <input type="url" class="form-control"
                                    placeholder="URL de Looker Studio (opcional)"
                                    name="newClientLookerUrl" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <?php

                $createClient = new Clients_Controller();
                $createClient->ctrCreateClient();

                ?>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- Modal editar cliente -->
<div class="modal fade in" id="editClientModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="editClientForm" autocomplete="off">

                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Editar Cliente</h4>
                </div>

                <input type="hidden" name="editClientId">

                <div class="modal-body">

                    <div class="box-body">

                        <div class="form-group">

                            <div class="input-group">

                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="ion ion-person-add"></i></span>
                                <input type="text" class="form-control" value="" name="editClientName" required
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group" style="higth:34px;">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-user"></i></span>
                                <select class="form-control select2" style="width: 100%;" name="editClientUser">

                                    <option value="" id="editClientUser"></option>

                                    <?php
                                    $usuarios = Users_controller::ctrShowUsers();

                                    if (isset($usuarios) && is_array($usuarios)) {
                                        foreach ($usuarios as $usuario) {
                                            echo '<option value="' . htmlspecialchars($usuario["id"]) . '">' . htmlspecialchars($usuario["name"]) . '</option>';
                                        }
                                    }
                                    ?>

                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group" style="higth:34px;">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-tasks"></i></span>
                                <select name="editClientVerticals[]" class="form-control select2" multiple="multiple"
                                    data-placeholder="Vertical(es)" style="width: 100%;">
                                    <?php
                                    $verticales = Verticals_controller::ctrShowVerticals();

                                    if (isset($verticales) && is_array($verticales)) {
                                        foreach ($verticales as $vertical) {
                                            echo '<option value="' . htmlspecialchars($vertical["id"]) . '">' . htmlspecialchars($vertical["name"]) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="glyphicon glyphicon-qrcode"></i></span>
                                <input type="text" class="form-control" value="" name="editClientCode"
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-bar-chart"></i></span>
                                <input type="url" class="form-control" value="" name="editClientLookerUrl"
                                    placeholder="URL de Looker Studio (opcional)" autocomplete="off">
                            </div>
                        </div>

                        <?php if ($isAdmin): ?>
                        <hr>
                        <div class="box box-default" style="padding:12px; border:1px solid #e5e5e5;">
                            <h4 style="margin-top:0;"><i class="fa fa-calculator"></i> Configuración de Fees del Cliente</h4>
                            <p class="text-muted" style="margin-bottom:10px;">
                                Esta configuración se usa para nuevos cálculos del mix y puede sincronizarse después con un clic.
                            </p>

                            <h5><strong>Reglas por tramo de inversión (USD)</strong></h5>
                            <table class="table table-condensed table-bordered" style="margin-bottom:8px;">
                                <thead>
                                    <tr>
                                        <th>Desde</th>
                                        <th>Hasta</th>
                                        <th>Tipo</th>
                                        <th>Etiqueta (opcional)</th>
                                        <th>%</th>
                                        <th>Fijo USD</th>
                                        <th>Moneda fijo</th>
                                        <th style="width:50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="clientFeeRulesBody">
                                    <tr><td colspan="8" class="text-center text-muted">Sin reglas configuradas</td></tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-default btn-sm" id="btnAddClientFeeRule">
                                <i class="fa fa-plus"></i> Agregar regla
                            </button>

                            <hr>
                            <h5><strong>Cargos fijos adicionales por concepto (USD)</strong></h5>
                            <table class="table table-condensed table-bordered" style="margin-bottom:8px;">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Monto USD</th>
                                                    <th>Moneda</th>
                                        <th style="width:50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="clientFeeChargesBody">
                                                <tr><td colspan="4" class="text-center text-muted">Sin cargos configurados</td></tr>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-md-7">
                                    <select class="form-control input-sm" id="clientFeeConceptSelect">
                                        <option value="">-- Selecciona un concepto --</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="clientFeeNewConcept" placeholder="Nuevo concepto (ej: Zapier)">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-info" id="btnCreateClientFeeConcept">Crear</button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:8px;">
                                <div class="col-md-7">
                                    <input type="text" class="form-control input-sm" id="clientFeeConceptName" placeholder="Concepto seleccionado" readonly>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control input-sm" id="clientFeeConceptAmount" step="0.01" placeholder="Monto USD">
                                </div>
                                <div class="col-md-2" style="padding-right:0;">
                                    <select class="form-control input-sm" id="clientFeeChargeCurrency"></select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success btn-sm btn-block" id="btnAddClientFeeCharge">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<?php
$deleteClient = new Clients_controller();
$deleteClient->ctrDeleteClient();
?>