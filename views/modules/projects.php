<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Proyectos
            <small>Administrar Proyectos</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Proyectos</li>
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

                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addProjectModal">
                    Agregar Proyecto
                </button>

            </div>

            <!-- /.box-header -->
            
            <div class="box-body">
                <div class="table-responsive">

                    <table id="projectsTable" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th style="max-width:200px">Nombre</th>
                                <th style="max-width:40px">Código</th>
                                <th style="max-width:200px">Cliente</th>
                                <th style="max-width:100px">Grupo</th>
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

<!-- Modal agregar proyecto -->
<div class="modal fade in" id="addProjectModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form role="form" method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Agregar Proyecto</h4>
                </div>
                <div class="modal-body">

                    <div class="box-body">

                        <!-- Asignar Cliente -->

                        <div class="form-group">
                            <div class="input-group" style="higth:34px;">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-user"></i></span>
                                <select class="form-control select2" style="width: 100%;" name="newProjectClient" required>
                                    <option value="" selected="selected">Asignar Cliente</option>
                                    <?php
                                    $clientes = Clients_controller::ctrShowClients();

                                    if (isset($clientes) && is_array($clientes)) {
                                        foreach ($clientes as $cliente) {
                                            echo '<option value="' . htmlspecialchars($cliente["id"]) . '">' . htmlspecialchars($cliente["name"]) . '</option>';
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
                                <input type="text" class="form-control" placeholder="Nombre Proyecto"
                                    name="newProjectName" required autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="fa fa-map"></i></span>
                                <input type="text" class="form-control" placeholder="Grupo (zona geográfica)"
                                    name="newProjectGroup" autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="glyphicon glyphicon-qrcode"></i></span>
                                <input type="text" class="form-control"
                                    placeholder="Código de proyecto (dejar en blanco para autogenerarlo)"
                                    name="newProjectCode" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <?php
                
                $newProject = new Projects_Controller();
                $newProject->ctrCreateProject();
                
                ?>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- Modal editar proyecto -->
<div class="modal fade in" id="editProjectModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="editProjectForm" autocomplete="off">

                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Editar Cliente</h4>
                </div>

                <input type="hidden" name="editProjectId">

                <div class="modal-body">

                    <div class="box-body">

                        <!-- Editar Cliente -->

                        <div class="form-group">
                            <div class="input-group" style="higth:34px;">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-user"></i></span>
                                <select class="form-control select2" style="width: 100%;" name="editProjectClient" required>
                                    <option value="" id="editProjectClient"></option>
                                    <?php
                                    $clientes = Clients_controller::ctrShowClients();

                                    if (isset($clientes) && is_array($clientes)) {
                                        foreach ($clientes as $cliente) {
                                            echo '<option value="' . htmlspecialchars($cliente["id"]) . '">' . htmlspecialchars($cliente["name"]) . '</option>';
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
                                    name="editProjectName" required autocomplete="off">
                            </div>
                        </div>

                        <!-- Editar Grupo -->

                        <div class="form-group">

                            <div class="input-group">

                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="fa fa-map"></i></span>
                                <input type="text" class="form-control" value=""
                                    name="editProjectGroup" autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;"
                                        class="glyphicon glyphicon-qrcode"></i></span>
                                <input type="text" class="form-control"
                                    value=""
                                    name="editProjectCode" required autocomplete="off">
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
$deleteProject = new Projects_controller();
$deleteProject -> ctrDeleteProject();
?>