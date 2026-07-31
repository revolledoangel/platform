<div class="content-wrapper">

    <section class="content-header">
        <h1>
            Generador de Urls + Utms
            <small>Administrar URLS</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Urls + Utms</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addUrlModal">
                    Nueva URL
                </button>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="urlsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="max-width:250px">URL</th>
                                <th>UTM Source</th>
                                <th>UTM Medium</th>
                                <th>UTM Campaign</th>
                                <th>UTM Term</th>
                                <th>UTM Content</th>
                                <th>Campaña</th>
                                <th style="max-width:100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal agregar URL -->
<div class="modal fade in" id="addUrlModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="addUrlForm" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Generar URL con UTM</h4>
                </div>

                <div class="modal-body">
                    <div class="box-body">

                        <!-- Periodo -->
                        <div class="form-group">
                            <label for="newUrlPeriod">Periodo</label>
                            <select class="form-control select2" id="newUrlPeriod" name="newUrlPeriod" required
                                style="width:100%;">
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


                        <!-- Cliente -->
                        <div class="form-group">
                            <label for="newUrlClient">Cliente</label>
                            <select class="form-control select2" id="newUrlClient" name="newUrlClient" required
                                style="width:100%;">
                                <option value="">-- Selecciona un cliente --</option>
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
                            <label for="newUrlProject">Proyecto</label>
                            <select class="form-control select2" id="newUrlProject" name="newUrlProject" required
                                disabled style="width:100%;">
                                <option value="">-- Selecciona un cliente primero --</option>
                            </select>
                        </div>

                        <!-- Campaña -->
                        <div class="form-group">
                            <label for="newUrlCampaign">Campaña</label>
                            <select class="form-control select2" id="newUrlCampaign" name="newUrlCampaign" required
                                disabled style="width:100%;">
                                <option value="">-- Selecciona un proyecto primero --</option>
                            </select>
                        </div>

                        <!-- URL Final -->
                        <div class="form-group">
                            <label for="newUrlInput">URL Base</label>
                            <input type="url" class="form-control" id="newUrlInput" name="newUrlInput"
                                placeholder="https://tusitio.com" required>
                        </div>

                        <!-- UTM Source -->
                        <div class="form-group">
                            <label for="utmSource">UTM Source</label>
                            <input type="text" class="form-control" id="utmSource" name="utmSource">
                        </div>

                        <!-- UTM Medium -->
                        <div class="form-group">
                            <label for="utmMedium">UTM Medium</label>
                            <input type="text" class="form-control" id="utmMedium" name="utmMedium">
                        </div>

                        <!-- UTM Campaign -->
                        <div class="form-group">
                            <label for="utmCampaign">UTM Campaign</label>
                            <input type="text" class="form-control" id="utmCampaign" name="utmCampaign">
                        </div>

                        <!-- UTM Term -->
                        <div class="form-group">
                            <label for="utmTerm">UTM Term</label>
                            <input type="text" class="form-control" id="utmTerm" name="utmTerm">
                        </div>

                        <!-- UTM Content -->
                        <div class="form-group">
                            <label for="utmContent">UTM Content</label>
                            <input type="text" class="form-control" id="utmContent" name="utmContent">
                        </div>

                        <!-- Vista previa y acciones -->
                        <div id="generatedUrlsContainer" style="margin-top:20px;"></div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info" id="btnGenerateUrl">Generar URL</button>
                    <button type="submit" class="btn btn-primary">Guardar URLs</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$deleteUrl = new Urls_Controller();
$deleteUrl->ctrDeleteUrl();
?>