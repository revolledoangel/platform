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