<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Usuarios
            <small>Administrar Usuarios</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Usuarios</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="box">

            <div class="box-header with-border">

                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addUserModal">
                    Agregar Usuario
                </button>

            </div>

            <!-- /.box-header -->
            <div class="box-body">

                <table class="table table-bordered table-striped dt-responsive tablas">

                    <thead>
                        <tr>
                            <th style="max-width:150px">Nombre</th>
                            <th style="max-width:100px">Usuario</th>
                            <th style="max-width:200px">E-mail</th>
                            <th style="max-width:100px">Perfil</th>
                            <th style="max-width:40px">Estado</th>
                            <th style="max-width:150px">Último Login</th>
                            <th style="max-width:150px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td>Angel Revolledo</td>
                            <td>revolledoangel</td>
                            <td>angel.revolledo@algoritmo.digital</td>
                            <td>Administrador</td>
                            <td>
                                <label class="switch text-center">
                                    <input type="checkbox">
                                    <span class="slider round"></span>
                                </label>

                            </td>
                            <td>2025-06-06 23:45:30</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default"><span
                                            class="glyphicon glyphicon-eye-open"></span></button>
                                    <button type="button" class="btn btn-default"><span
                                            class="glyphicon glyphicon-pencil"></span></button>
                                    <button type="button" class="btn btn-default"><span
                                            class="glyphicon glyphicon-remove"></button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>Angel Revolledo</td>
                            <td>revolledoangel</td>
                            <td>angel.revolledo@algoritmo.digital</td>
                            <td>Administrador</td>
                            <td>
                                <label class="switch text-center">
                                    <input type="checkbox">
                                    <span class="slider round"></span>
                                </label>

                            </td>
                            <td>2025-06-06 23:45:30</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default"><span
                                            class="glyphicon glyphicon-eye-open"></span></button>
                                    <button type="button" class="btn btn-default"><span
                                            class="glyphicon glyphicon-pencil"></span></button>
                                    <button type="button" class="btn btn-default"><span
                                            class="glyphicon glyphicon-remove"></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Estado</th>
                            <th>Último Login</th>
                            <th>Acciones</th>
                        </tr>
                    </tfoot>

                    <style>
                        .switch {
                            position: relative;
                            display: inline-block;
                            width: 42px;
                            /* 60 * 0.7 */
                            height: 23.8px;
                            /* 34 * 0.7 */
                        }

                        .switch input {
                            opacity: 0;
                            width: 0;
                            height: 0;
                        }

                        .slider {
                            position: absolute;
                            cursor: pointer;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            background-color: #ccc;
                            -webkit-transition: .4s;
                            transition: .4s;
                        }

                        .slider:before {
                            position: absolute;
                            content: "";
                            height: 18.2px;
                            /* 26 * 0.7 */
                            width: 18.2px;
                            /* 26 * 0.7 */
                            left: 2.8px;
                            /* 4 * 0.7 */
                            bottom: 2.8px;
                            /* 4 * 0.7 */
                            background-color: white;
                            -webkit-transition: .4s;
                            transition: .4s;
                        }

                        input:checked+.slider {
                            background-color: #2196F3;
                        }

                        input:focus+.slider {
                            box-shadow: 0 0 1px #2196F3;
                        }

                        input:checked+.slider:before {
                            -webkit-transform: translateX(18.2px);
                            /* 26 * 0.7 */
                            -ms-transform: translateX(18.2px);
                            transform: translateX(18.2px);
                        }

                        /* Rounded sliders */
                        .slider.round {
                            border-radius: 23.8px;
                            /* 34 * 0.7 */
                        }

                        .slider.round:before {
                            border-radius: 50%;
                        }
                    </style>
                </table>
            </div>
            <!-- /.box-body -->
        </div>

    </section>
    <!-- /.content -->
</div>

<!-- Modal agregar usuario -->
<div class="modal fade in" id="addUserModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form role="form" method="post" enctype="multipart/form-data">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" style="color:#fff">×</span></button>
                    <h4 class="modal-title">Agregar Usuario</h4>
                </div>
                <div class="modal-body">

                    <div class="box-body">

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-male"></i></span>
                                <input type="text" class="form-control" placeholder="Nombres y Apellidos"
                                    name="newUserName">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-at"></i></span>
                                <input type="email" class="form-control" placeholder="Email" name="newUserEmail">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Nombre de usuario"
                                    name="newUserUsername">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-lock"></i></span>
                                <input type="password" class="form-control" placeholder="Contraseña"
                                    name="newUserPassword">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group" style="higth:34px;">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-gears"></i></span>
                                <select class="form-control select2" style="width: 100%;" name="newUserProfile">
                                    <option value="Analista" selected="selected">Analista</option>
                                    <option value="Administrador">Administrador</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="panel">Subir Foto</div>
                            <input type="file" id="newUserPhoto" name="newUserPhoto">
                            <p class="help-block">Peso máximo de la foto: 200 Mb</p>
                            <img src="views/img/template/usuario-sin-foto.png" class="img-thumbnail" width="100px">
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