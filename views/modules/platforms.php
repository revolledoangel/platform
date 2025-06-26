<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Plataformas
            <small>Administrar Plataformas</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Plataformas</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="box">

            <div class="box-header with-border">

                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addPlatformModal">
                    Agregar Plataforma
                </button>

            </div>

            <!-- /.box-header -->
            
            <div class="box-body">
                <div class="table-responsive">

                    <table id="platformsTable" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th style="max-width:200px">Nombre</th>
                                <th style="max-width:40px">Código</th>
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

<!-- Modal agregar plataforma -->
<div class="modal fade in" id="addPlatformModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <form role="form" method="post" enctype="multipart/form-data" autocomplete="off">

                <div class="modal-header" style="background:#00013b;color:#fff">

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                        <span aria-hidden="true">×</span></button>

                    <h4 class="modal-title">Agregar Plataforma</h4>

                </div>

                <div class="modal-body">

                    <div class="box-body">

                        <!-- Nombre -->
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="ion ion-person-add"></i></span>
                                <input type="text" class="form-control" placeholder="Nombre Plataforma"
                                    name="newPlatformName" required autocomplete="off">
                            </div>
                        </div>

                        <!-- Código -->
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="glyphicon glyphicon-qrcode"></i></span>
                                <input type="text" class="form-control"
                                    placeholder="Código de plataforma (dejar en blanco para autogenerarlo)"
                                    name="newPlatformCode" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <?php
                
                $newPlatform = new Platforms_Controller();
                $newPlatform->ctrCreatePlatform();
                
                ?>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- Modal editar plataforma -->
<div class="modal fade in" id="editPlatformModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="editPlatformForm" autocomplete="off">

                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Editar Plataforma</h4>
                </div>

                <input type="hidden" name="editPlatformId">

                <div class="modal-body">

                    <div class="box-body">

                        <!-- Editar Nombre -->
                        <div class="form-group">

                            <div class="input-group">

                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="ion ion-person-add"></i></span>
                                <input type="text" class="form-control" value=""
                                    name="editPlatformName" required autocomplete="off">
                            </div>
                        </div>

                        <!-- Editar Codigo -->
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="glyphicon glyphicon-qrcode"></i></span>
                                <input type="text" class="form-control"
                                    value=""
                                    name="editPlatformCode" required autocomplete="off">
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
$deletePlatform = new Platforms_controller();
$deletePlatform -> ctrDeletePlatform();
?>