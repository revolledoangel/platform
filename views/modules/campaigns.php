<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Campañas
            <small>Administrar Campañas</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Campañas</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="box">

            <div class="box-header with-border">

                <div class="form-group pull-left" style="width:300px; margin-bottom:20px;">
                    <label for="filterClient">Filtrar por Cliente:</label>
                    <select id="filterClient" class="form-control select2">
                        <option value="">-- Todos los clientes --</option>
                        <?php
                        $clientes = Clients_controller::ctrShowClients();
                        foreach ($clientes as $cliente) {
                            echo '<option value="' . htmlspecialchars($cliente["name"]) . '">' . htmlspecialchars($cliente["name"]) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group pull-left" style="width:300px; margin-left:20px; margin-bottom:20px;">
                    <label for="filterPeriod">Filtrar por Período:</label>
                    <select id="filterPeriod" class="form-control select2">
                        <option value="">-- Todos los períodos --</option>
                        <?php
                        $periodos = Periods_controller::ctrShowPeriods();
                        foreach ($periodos as $periodo) {
                            echo '<option value="' . htmlspecialchars($periodo["name"]) . '">' . htmlspecialchars($periodo["name"]) . '</option>';
                        }
                        ?>
                    </select>
                </div>


                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addCampaignModal">
                    Agregar Campaña
                </button>

            </div>

            <!-- /.box-header -->

            <div class="box-body">
                <div class="table-responsive">

                    <table id="campaignsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Periodo</th>
                                <th>Plataforma</th>
                                <th>Verticales</th>
                                <th>Analista</th>
                                <th>Grupo</th>
                                <th>Cliente</th>
                                <th>Proyecto</th>
                                <th>Código</th>
                                <th>Objetivo(s)</th>
                                <th>Inversión</th>
                                <th>Meta</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
            <!-- /.box-body -->
        </div>

    </section>
    <!-- /.content -->
</div>

<!-- Modal agregar campaña -->
<div class="modal fade in" id="addCampaignModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form role="form" method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">Agregar Campaña</h4>
                </div>

                <div class="modal-body">
                    <div class="box-body">

                        <!-- Seleccionar Periodo -->
                        <div class="form-group">
                            <label for="newCampaignPeriod">Periodo</label>
                            <select class="form-control select2" id="newCampaignPeriod" name="newCampaignPeriod"
                                required style="width:100%;">
                                <?php
                                $periodsJson = @file_get_contents('https://algoritmo.digital/backend/public/api/periods');
                                $periods = json_decode($periodsJson, true);

                                if (!is_array($periods)) {
                                    echo '<option value="">Error al cargar periodos</option>';
                                } else {
                                    $currentMonth = (int) date("n");
                                    $currentYear = (int) date("Y");
                                    $selectedFound = false;

                                    echo '<option value="">-- Selecciona un periodo --</option>';

                                    foreach ($periods as $period) {
                                        $selected = '';
                                        if ((int) $period["month_number"] === $currentMonth && (int) $period["year"] === $currentYear) {
                                            $selected = ' selected';
                                            $selectedFound = true;
                                        }
                                        echo '<option value="' . htmlspecialchars($period["id"]) . '"' . $selected . '>' . htmlspecialchars($period["name"]) . '</option>';
                                    }

                                    if (!$selectedFound) {
                                        echo '<!-- ⚠ No se encontró periodo para el mes actual -->';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Seleccionar Cliente -->
                        <div class="form-group">
                            <label for="newCampaignClient">Cliente</label>
                            <select class="form-control select2" id="newCampaignClient" name="newCampaignClient"
                                required style="width:100%;">
                                <option value="">-- Selecciona un cliente --</option>
                                <?php
                                $clientes = Clients_controller::ctrShowClients();
                                foreach ($clientes as $cliente) {
                                    echo '<option value="' . $cliente["id"] . '">' . htmlspecialchars($cliente["name"]) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Seleccionar Proyecto (depende del cliente) -->
                        <div class="form-group">
                            <label for="newCampaignProject">Proyecto</label>
                            <select class="form-control select2" id="newCampaignProject" name="newCampaignProject"
                                required disabled style="width:100%;">
                                <option value="">-- Selecciona un cliente primero --</option>
                            </select>
                        </div>

                        <!-- Seleccionar Plataforma -->
                        <div class="form-group">
                            <label for="newCampaignPlatform">Plataforma</label>
                            <select class="form-control select2" id="newCampaignPlatform" name="newCampaignPlatform"
                                required style="width:100%;">
                                <option value="">-- Selecciona una plataforma --</option>
                                <?php
                                $platforms = Platforms_controller::ctrShowPlatforms();
                                foreach ($platforms as $platform) {
                                    echo '<option value="' . $platform["id"] . '">' . htmlspecialchars($platform["name"]) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Seleccionar Formato(s) -->
                        <div class="form-group">
                            <label for="newCampaignFormat">Formato(s)</label>
                            <select class="form-control select2" id="newCampaignFormat" name="newCampaignFormats[]"
                                multiple="multiple" required disabled style="width:100%;">
                                <option value="">-- Selecciona una plataforma primero --</option>
                            </select>
                        </div>

                        <!-- Seleccionar Objetivo(s) -->
                        <div class="form-group">
                            <label for="newCampaignObjectives">Objetivo(s)</label>
                            <select class="form-control select2" id="newCampaignObjectives"
                                name="newCampaignObjectives[]" multiple="multiple"
                                data-placeholder="Selecciona uno o varios objetivos" required style="width:100%;">
                                <?php
                                $objetivos = Objectives_controller::ctrShowObjectives();

                                if (isset($objetivos) && is_array($objetivos)) {
                                    foreach ($objetivos as $obj) {
                                        echo '<option value="' . htmlspecialchars($obj["id"]) . '">' . htmlspecialchars($obj["name"]) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label for="newCampaignStatus">Estado</label>
                            <select class="form-control select2" id="newCampaignStatus" name="newCampaignStatus"
                                required style="width:100%;">
                                <option value="">-- Selecciona un estado --</option>
                                <option value="Activa">Activa</option>
                                <option value="Por confirmar">Por confirmar</option>
                                <option value="Suspendida">Suspendida</option>
                            </select>
                        </div>

                        <!-- Inversión propuesta -->
                        <div class="form-group">
                            <label for="newCampaignInvestment">Inversión propuesta</label>
                            <input type="number" class="form-control" name="newCampaignInvestment"
                                id="newCampaignInvestment" required min="0" step="1">
                        </div>

                        <!-- Meta propuesta -->
                        <div class="form-group">
                            <label for="newCampaignGoal">Meta propuesta</label>
                            <input type="number" class="form-control" name="newCampaignGoal" id="newCampaignGoal"
                                required min="0" step="1">
                        </div>

                        <!-- Nombre (opcional) -->
                        <div class="form-group">
                            <label for="newCampaignName">Nombre (opcional)</label>
                            <input type="text" class="form-control" name="newCampaignName" id="newCampaignName">
                        </div>

                        <!-- Comentarios (opcional) -->
                        <div class="form-group">
                            <label for="newCampaignComments">Comentarios (opcional)</label>
                            <textarea class="form-control" name="newCampaignComments" id="newCampaignComments"
                                rows="3"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Campaña</button>
                </div>

                <?php

                $createCampaign = new Campaigns_controller();
                $createCampaign->ctrCreateCampaign();

                ?>

            </form>
        </div>
    </div>
</div>

<!-- Modal editar campaña -->
<div class="modal fade" id="editCampaignModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="editCampaignForm" autocomplete="off">
                <div class="modal-header" style="background:#ff851b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Editar Campaña</h4>
                </div>

                <div class="modal-body">
                    <div class="box-body">

                        <!-- ID oculto -->
                        <input type="hidden" name="editCampaignId" id="editCampaignId">

                        <!-- Periodo -->
                        <div class="form-group">
                            <label for="editCampaignPeriod">Periodo</label>
                            <select class="form-control select2" name="editCampaignPeriod" id="editCampaignPeriod" required style="width:100%;">
                                <?php
                                $periodsJson = @file_get_contents('https://algoritmo.digital/backend/public/api/periods');
                                $periods = json_decode($periodsJson, true);
                                if (is_array($periods)) {
                                    foreach ($periods as $period) {
                                        echo '<option value="' . htmlspecialchars($period["id"]) . '">' . htmlspecialchars($period["name"]) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">Error al cargar periodos</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Cliente -->
                        <div class="form-group">
                            <label for="editCampaignClient">Cliente</label>
                            <select class="form-control select2" name="editCampaignClient" id="editCampaignClient" required style="width:100%;">
                                <?php
                                $clientes = Clients_controller::ctrShowClients();
                                foreach ($clientes as $cliente) {
                                    echo '<option value="' . $cliente["id"] . '">' . htmlspecialchars($cliente["name"]) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Proyecto -->
                        <div class="form-group">
                            <label for="editCampaignProject">Proyecto</label>
                            <select class="form-control select2" name="editCampaignProject" id="editCampaignProject" required style="width:100%;">
                                <option value="">-- Selecciona un cliente primero --</option>
                            </select>
                        </div>

                        <!-- Plataforma -->
                        <div class="form-group">
                            <label for="editCampaignPlatform">Plataforma</label>
                            <select class="form-control select2" name="editCampaignPlatform" id="editCampaignPlatform" required style="width:100%;">
                                <?php
                                $platforms = Platforms_controller::ctrShowPlatforms();
                                foreach ($platforms as $platform) {
                                    echo '<option value="' . $platform["id"] . '">' . htmlspecialchars($platform["name"]) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Formatos -->
                        <div class="form-group">
                            <label for="editCampaignFormats">Formato(s)</label>
                            <select class="form-control select2" name="editCampaignFormats[]" id="editCampaignFormats" multiple required style="width:100%;">
                                <option value="">-- Selecciona una plataforma primero --</option>
                            </select>
                        </div>

                        <!-- Objetivos -->
                        <div class="form-group">
                            <label for="editCampaignObjectives">Objetivo(s)</label>
                            <select class="form-control select2" name="editCampaignObjectives[]" id="editCampaignObjectives" multiple required style="width:100%;">
                                <?php
                                $objetivos = Objectives_controller::ctrShowObjectives();
                                if (is_array($objetivos)) {
                                    foreach ($objetivos as $obj) {
                                        echo '<option value="' . htmlspecialchars($obj["id"]) . '">' . htmlspecialchars($obj["name"]) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label for="editCampaignStatus">Estado</label>
                            <select class="form-control select2" name="editCampaignStatus" id="editCampaignStatus" required style="width:100%;">
                                <option value="Activa">Activa</option>
                                <option value="Por confirmar">Por confirmar</option>
                                <option value="Suspendida">Suspendida</option>
                                <option value="Sin determinar">Sin determinar</option>
                            </select>
                        </div>

                        <!-- Inversión -->
                        <div class="form-group">
                            <label for="editCampaignInvestment">Inversión propuesta</label>
                            <input type="number" class="form-control" name="editCampaignInvestment" id="editCampaignInvestment" min="0" step="1" required>
                        </div>

                        <!-- Meta -->
                        <div class="form-group">
                            <label for="editCampaignGoal">Meta propuesta</label>
                            <input type="number" class="form-control" name="editCampaignGoal" id="editCampaignGoal" min="0" step="1" required>
                        </div>

                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="editCampaignName">Nombre (opcional)</label>
                            <input type="text" class="form-control" name="editCampaignName" id="editCampaignName">
                        </div>

                        <!-- Comentarios -->
                        <div class="form-group">
                            <label for="editCampaignComments">Comentarios (opcional)</label>
                            <textarea class="form-control" name="editCampaignComments" id="editCampaignComments" rows="3"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Campaña</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php
$deleteCampaign = new Campaigns_controller();
$deleteCampaign->ctrDeleteCampaign();
?>