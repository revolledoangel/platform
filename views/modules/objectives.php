<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Objetivos
            <small>Administrar Objetivos</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Objetivos</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="box">

            <div class="box-header with-border">

                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addObjectiveModal">
                    Agregar Objetivo
                </button>

            </div>

            <!-- /.box-header -->

            <div class="box-body">
                <div class="table-responsive">

                    <table id="objectivesTable" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th style="max-width:200px">Nombre</th>
                                <th style="max-width:40px">Código</th>
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

<!-- Modal agregar objetivo -->
<div class="modal fade in" id="addObjectiveModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <form role="form" method="post" enctype="multipart/form-data" autocomplete="off">

                <div class="modal-header" style="background:#00013b;color:#fff">

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                        <span aria-hidden="true">×</span></button>

                    <h4 class="modal-title">Agregar Objetivo</h4>

                </div>

                <div class="modal-body">

                    <div class="box-body">

                        <!-- Nombre -->
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="ion ion-person-add"></i></span>
                                <input type="text" class="form-control" placeholder="Nombre del Objetivo"
                                    name="newObjectiveName" required autocomplete="off">
                            </div>
                        </div>

                        <!-- Código -->
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="glyphicon glyphicon-qrcode"></i></span>
                                <input type="text" class="form-control"
                                    placeholder="Código del objetivo (3 dígitos numéricos, ej. 001)"
                                    name="newObjectiveCode" pattern="\d{3}"
                                    title="Debe contener exactamente 3 dígitos (ej. 001, 123)" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <?php

                $newObjective = new Objectives_Controller();
                $newObjective->ctrCreateObjective();

                ?>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- Modal editar objetivo -->
<div class="modal fade in" id="editObjectiveModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="editObjectiveForm" autocomplete="off">

                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Editar Objetivo</h4>
                </div>

                <input type="hidden" name="editObjectiveId">

                <div class="modal-body">
                    <div class="box-body">

                        <!-- Editar Nombre -->
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i style="width: 25px;" class="ion ion-person-add"></i>
                                </span>
                                <input type="text" class="form-control" name="editObjectiveName"
                                    placeholder="Nombre del objetivo" required autocomplete="off">
                            </div>
                        </div>

                        <!-- Código (solo lectura) -->
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i style="width: 25px;" class="glyphicon glyphicon-qrcode"></i>
                                </span>
                                <input type="text" class="form-control" name="editObjectiveCode"
                                    placeholder="Código (no editable)" pattern="\d{3}" readonly
                                    title="El código no se puede modificar" autocomplete="off">
                            </div>
                            <small class="text-muted">El código no puede ser modificado</small>
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
$deleteObjective = new Objectives_Controller();
$deleteObjective->ctrDeleteObjective();
?>