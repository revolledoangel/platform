<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Formatos
            <small>Administrar Formatos</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Formatos</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="box">

            <div class="box-header with-border">

                <div class="form-group pull-left" style="width:300px; margin-bottom:20px;">
                    <label for="filterPlatform">Filtrar por Plataforma:</label>
                    <select id="filterPlatform" class="form-control select2">
                        <option value="">-- Todas las Plataformas --</option>
                        <?php
                        $plataformas = Platforms_controller::ctrShowPlatforms();
                        foreach ($plataformas as $plataforma) {
                            echo '<option value="' . htmlspecialchars($plataforma["name"]) . '">' . htmlspecialchars($plataforma["name"]) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addFormatModal">
                    Agregar Formato
                </button>

            </div>

            <!-- /.box-header -->
            
            <div class="box-body">
                <div class="table-responsive">

                    <table id="formatsTable" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th style="max-width:200px">Nombre</th>
                                <th style="max-width:40px">Código</th>
                                <th style="max-width:200px">Plataforma</th>
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

<!-- Modal agregar formato -->
<div class="modal fade in" id="addFormatModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form role="form" method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Agregar Formato</h4>
                </div>
                <div class="modal-body">

                    <div class="box-body">

                        <!-- Asignar Plataforma -->
                        <div class="form-group">
                            <div class="input-group" style="higth:34px;">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-user"></i></span>
                                <select class="form-control select2" style="width: 100%;" name="newFormatPlatform" required>
                                    <option value="" selected="selected">Asignar Plataforma</option>
                                    <?php
                                    $plataformas = Platforms_controller::ctrShowPlatforms();

                                    if (isset($plataformas) && is_array($plataformas)) {
                                        foreach ($plataformas as $plataforma) {
                                            echo '<option value="' . htmlspecialchars($plataforma["id"]) . '">' . htmlspecialchars($plataforma["name"]) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="ion ion-person-add"></i></span>
                                <input type="text" class="form-control" placeholder="Nombre Formato"
                                    name="newFormatName" required autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="glyphicon glyphicon-qrcode"></i></span>
                                <input type="text" class="form-control"
                                    placeholder="Código de formato (dejar en blanco para autogenerarlo)"
                                    name="newFormatCode" autocomplete="off">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <?php
                
                $newFormat = new Formats_Controller();
                $newFormat->ctrCreateFormat();
                
                ?>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- Modal editar formato -->
<div class="modal fade in" id="editFormatModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="editFormatForm" autocomplete="off">

                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Editar Formato</h4>
                </div>

                <input type="hidden" name="editFormatId">

                <div class="modal-body">

                    <div class="box-body">

                        <!-- Editar Plataforma -->

                        <div class="form-group">
                            <div class="input-group" style="higth:34px;">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-user"></i></span>
                                <select class="form-control select2" style="width: 100%;" name="editFormatPlatform" required>
                                    <option value="" id="editFormatPlatform"></option>
                                    <?php
                                    $plataformas = Platforms_controller::ctrShowPlatforms();

                                    if (isset($plataformas) && is_array($plataformas)) {
                                        foreach ($plataformas as $plataforma) {
                                            echo '<option value="' . htmlspecialchars($plataforma["id"]) . '">' . htmlspecialchars($plataforma["name"]) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Editar Nombre -->
                        <div class="form-group">

                            <div class="input-group">

                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="ion ion-person-add"></i></span>
                                <input type="text" class="form-control" value=""
                                    name="editFormatName" required autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="glyphicon glyphicon-qrcode"></i></span>
                                <input type="text" class="form-control"
                                    value=""
                                    name="editFormatCode" required autocomplete="off">
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
$deleteFormat = new Formats_controller();
$deleteFormat -> ctrDeleteFormat();
?>