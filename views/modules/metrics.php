<?php
$platforms = Platforms_controller::ctrShowPlatforms();
if (!is_array($platforms)) {
    $platforms = [];
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Métricas
            <small>Administrar métricas</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Métricas</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addMetricModal">
                    Agregar Métrica
                </button>
            </div>

            <div class="box-body">
                <table id="metricsTable" class="table table-bordered table-striped dt-responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>Estado</th>
                            <th>Plataforma(s)</th>
                            <th>Req. Evento/Conv.</th>
                            <th style="width:100px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="addMetricModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" id="addMetricForm" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Agregar Métrica</h4>
                </div>

                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="newMetricName">Nombre</label>
                            <input type="text" class="form-control" id="newMetricName" required>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="newMetricActive" checked> Activo
                            </label>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="newMetricRequiresEvent"> Requiere especificar evento o conversión
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="newMetricPlatforms">Plataformas</label>
                            <select id="newMetricPlatforms" class="form-control select2" multiple style="width:100%;">
                                <?php foreach ($platforms as $platform): ?>
                                    <?php if (!empty($platform['id']) && isset($platform['name'])): ?>
                                        <option value="<?= htmlspecialchars($platform['id']) ?>"><?= htmlspecialchars($platform['name']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
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

<div class="modal fade" id="editMetricModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" id="editMetricForm" autocomplete="off">
                <input type="hidden" id="editMetricId">

                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Editar Métrica</h4>
                </div>

                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="editMetricName">Nombre</label>
                            <input type="text" class="form-control" id="editMetricName" required>
                        </div>

                        <div class="form-group">
                            <label for="editMetricCode">Código</label>
                               <input type="text" class="form-control" id="editMetricCode" readonly>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="editMetricActive"> Activo
                            </label>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="editMetricRequiresEvent"> Requiere especificar evento o conversión
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="editMetricPlatforms">Plataformas</label>
                            <select id="editMetricPlatforms" class="form-control select2" multiple style="width:100%;">
                                <?php foreach ($platforms as $platform): ?>
                                    <?php if (!empty($platform['id']) && isset($platform['name'])): ?>
                                        <option value="<?= htmlspecialchars($platform['id']) ?>"><?= htmlspecialchars($platform['name']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
