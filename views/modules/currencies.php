<?php
$perfil = $_SESSION['perfil'] ?? '';
if (!in_array($perfil, ['Super', 'Administrador'], true)) {
    echo '<script>window.location = "home";</script>';
    return;
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Monedas
            <small>Administrar monedas y tipos de cambio</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Monedas</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addCurrencyModal">
                    Agregar / Actualizar Moneda
                </button>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="currenciesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Símbolo</th>
                                <th>Decimales</th>
                                <th>USD por unidad</th>
                                <th>1 USD equivale a</th>
                                <th>Vigencia</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Historial de tipo de cambio</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-control" id="currencyHistoryCode"></select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-default" id="btnLoadCurrencyHistory">Ver historial</button>
                    </div>
                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered" id="currencyHistoryTable">
                        <thead>
                            <tr>
                                <th>Moneda</th>
                                <th>USD por unidad</th>
                                <th>1 USD equivale a</th>
                                <th>Vigente desde</th>
                                <th>Registrado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="4" class="text-center text-muted">Selecciona una moneda</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="addCurrencyModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="currencyForm" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">Guardar Moneda y Tipo de Cambio</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Código (ej: USD, PEN, CLP)</label>
                        <input type="text" class="form-control" name="code" maxlength="10" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" name="name" maxlength="80" required>
                    </div>
                    <div class="form-group">
                        <label>Símbolo</label>
                        <input type="text" class="form-control" name="symbol" maxlength="10" required>
                    </div>
                    <div class="form-group">
                        <label>Decimales</label>
                        <input type="number" class="form-control" name="decimals" min="0" max="8" value="2" required>
                    </div>
                    <div class="form-group">
                        <label>USD por unidad (tipo de cambio base)</label>
                        <input type="number" class="form-control" name="usd_per_unit" step="0.00000001" min="0.00000001" required>
                    </div>
                    <div class="form-group">
                        <label>Vigente desde</label>
                        <input type="datetime-local" class="form-control" name="effective_at">
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="active" value="1" checked> Activa
                        </label>
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

<div class="modal fade" id="addRateModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addRateForm" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">Agregar Tipo de Cambio</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="code">
                    <div class="form-group">
                        <label>Moneda</label>
                        <input type="text" class="form-control" name="currency_label" readonly>
                    </div>
                    <div class="form-group">
                        <label>USD por unidad</label>
                        <input type="number" class="form-control" name="usd_per_unit" step="0.00000001" min="0.00000001" required>
                    </div>
                    <div class="form-group">
                        <label>Vigente desde</label>
                        <input type="datetime-local" class="form-control" name="effective_at">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar tipo de cambio</button>
                </div>
            </form>
        </div>
    </div>
</div>
