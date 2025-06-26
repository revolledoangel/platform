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
                    <table class="table table-bordered table-striped tablas">

                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $verticals = Verticals_Controller::ctrShowVerticals();

                            foreach ($verticals as $vertical) {
                                echo '<tr>';

                                // Nombre
                                echo '<td>' . $vertical["name"] . '</td>';

                                // Fecha de Creación
                                $fecha = new DateTime($vertical["created_at"]);
                                $fechaFormateada = $fecha->format('d-m-Y');

                                echo '<td>' . ($fechaFormateada ?? 'Sin registro') . '</td>';

                                // Botones de acción
                                echo '<td>
                                    <div class="btn-group">

                                        <button type="button" class="btn btn-default btn-warning btn-editVertical" verticalId="' . $vertical["id"] . '" data-toggle="modal" data-target="#editVerticalModal">
                                            <span class="glyphicon glyphicon-pencil"></span>
                                        </button>

                                       <button type="button" class="btn btn-default btn-danger btn-deleteVertical" verticalId="' . $vertical["id"] . '">
                                            <span class="glyphicon glyphicon-remove"></span>
                                        </button>

                                    </div>
                                </td>';

                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Nombre</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </tfoot>
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
                $createVertical = new Verticals_Controller();
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

            <form role="form" method="post" enctype="multipart/form-data" autocomplete="off">

                <div class="modal-header" style="background:#00013b;color:#fff">

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                        <span aria-hidden="true">×</span></button>

                    <h4 class="modal-title">Editar Vertical</h4>

                </div>

                <input type="hidden" name="editVerticalId">

                <div class="modal-body">

                    <div class="box-body">

                        <div class="form-group">

                            <div class="input-group">

                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-circle-o"></i></span>

                                <input type="text" class="form-control" value="" name="editVerticalName" required autocomplete="off">
                    
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <?php
                $editVertical = new Verticals_Controller();
                $editVertical->ctrEditVertical();
                ?>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<?php
$deleteVertical = new Verticals_controller();
$deleteVertical -> ctrDeleteVertical();
?>