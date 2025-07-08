<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Vertical
            <small>Administrar Vertical</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Vertical</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="box">

            <div class="box-header with-border">

                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addVerticalModal">
                    Agregar Vertical
                </button>

            </div>

            <!-- /.box-header -->
            <div class="box-body">
                <div class="table-responsive">
                    <table id="verticalsTable" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
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

<!-- Modal agregar vertical -->
<div class="modal fade in" id="addVerticalModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <form role="form" method="post" enctype="multipart/form-data" autocomplete="off">

                <div class="modal-header" style="background:#00013b;color:#fff">

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                        <span aria-hidden="true">×</span></button>

                    <h4 class="modal-title">Agregar Vertical</h4>

                </div>

                <div class="modal-body">

                    <div class="box-body">

                        <div class="form-group">

                            <div class="input-group">

                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-circle-o"></i></span>

                                <input type="text" class="form-control" placeholder="Nombre de Vertical"
                                    name="newVerticalName" required autocomplete="off">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <?php
                $createVertical = new Verticals_controller();
                $createVertical -> ctrCreateVertical();
                ?>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- Modal editar vertical -->
<div class="modal fade in" id="editVerticalModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="editVerticalForm" autocomplete="off">

                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Editar Vertical</h4>
                </div>

                <input type="hidden" name="editVerticalId">

                <div class="modal-body">

                    <div class="box-body">

                        <!-- Editar Nombre -->
                        <div class="form-group">

                            <div class="input-group">

                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="ion ion-person-add"></i></span>
                                <input type="text" class="form-control" value=""
                                    name="editVerticalName" required autocomplete="off">
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
$deletePlatform = new Verticals_controller();
$deletePlatform -> ctrDeleteVertical();
?>