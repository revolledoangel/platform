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
                            <th style="max-width:100px">Nombre</th>
                            <th style="max-width:50px">Usuario</th>
                            <th style="max-width:80px">E-mail</th>
                            <th style="max-width:100px">Perfil</th>
                            <th style="max-width:40px">Estado</th>
                            <th style="max-width:100px">Último Login</th>
                            <th style="max-width:70px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $usuarios = Users_controller::ctrShowUsers();

                        foreach ($usuarios as $usuario) {
                            echo '<tr>';

                            // Nombre
                            echo '<td>' . htmlspecialchars($usuario["name"]) . '</td>';

                            // Usuario
                            echo '<td>' . htmlspecialchars($usuario["username"]) . '</td>';

                            // Email
                            echo '<td>' . htmlspecialchars($usuario["email"]) . '</td>';

                            // Perfil
                            echo '<td>' . htmlspecialchars($usuario["profile"]) . '</td>';

                            // Estado (activo/inactivo)
                            $checked = $usuario["active"] == 1 ? "checked" : "";
                            echo '<td>
                                    <label class="switch text-center">
                                        <input type="checkbox" class="toggle-active" data-id="' . $usuario["id"] . '" ' . $checked . '>
                                        <span class="slider round"></span>
                                    </label>
                                </td>';

                            // Último login
                            echo '<td>' . ($usuario["last_login"] ?? 'Sin registro') . '</td>';

                            // Botones de acción
                            echo '<td>
                                    <div class="btn-group">

                                        <button type="button" class="btn btn-default btn-warning btn-editUser" userId="' . $usuario["id"] . '" data-toggle="modal" data-target="#editUserModal">
                                            <span class="glyphicon glyphicon-pencil"></span>
                                        </button>

                                        <button type="button" class="btn btn-default btn-danger">
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

            <form role="form" method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Agregar Usuario</h4>
                </div>
                <div class="modal-body">

                    <div class="box-body">

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-male"></i></span>
                                <input type="text" class="form-control" placeholder="Nombres y Apellidos" name="newName"
                                    required autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-at"></i></span>
                                <input type="email" class="form-control" placeholder="Email" name="newEmail" required
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Nombre de usuario"
                                    name="newUsername" required autocomplete="new-username">

                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-lock"></i></span>
                                <input type="password" class="form-control"
                                    placeholder="Contraseña (mínimo 5 caracteres)" name="newPassword" required
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group" style="higth:34px;">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-gears"></i></span>
                                <select class="form-control select2" style="width: 100%;" name="newProfile" required>
                                    <option value="Analista" selected="selected">Analista</option>
                                    <option value="Administrador">Administrador</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="panel">Subir Foto</div>
                            <input type="file" class="newPhoto" name="newPhoto">
                            <p class="help-block">Peso máximo de la foto: 2Mb</p>
                            <img src="views/img/template/usuario-sin-foto.png" class="img-thumbnail preview"
                                width="100px">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <?php
                $createUser = new Users_Controller();
                $createUser->ctrCreateUser();
                ?>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<!-- Modal editar usuario -->
<div class="modal fade in" id="editUserModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form role="form" method="post" enctype="multipart/form-data" autocomplete="off">

                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Editar Usuario</h4>
                </div>

                <input type="hidden" name="editId">
                <input type="hidden" name="currentPhoto">

                <div class="modal-body">

                    <div class="box-body">

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-male"></i></span>
                                <input type="text" class="form-control" value="" name="editName" required
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-at"></i></span>
                                <input type="email" class="form-control" value="" name="editEmail" required
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-user"></i></span>
                                <input type="text" class="form-control" value="" name="editUsername" required
                                    autocomplete="edit-username">

                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-lock"></i></span>
                                <input type="password" class="form-control"
                                    placeholder="Nueva Contraseña (mínimo 5 caracteres)" name="newUserPassword"
                                    id="newUserPassword" autocomplete="new-password">

                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group" style="higth:34px;">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-gears"></i></span>
                                <select class="form-control select2" style="width: 100%;" name="editProfile" required>
                                    <option value="" id="editProfile"></option>
                                    <option value="Analista">Analista</option>
                                    <option value="Administrador">Administrador</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="panel">Subir Foto</div>
                            <input type="file" class="newPhoto" name="editPhoto">
                            <p class="help-block">Peso máximo de la foto: 2Mb</p>
                            <img src="views/img/template/usuario-sin-foto.png" class="img-thumbnail preview"
                                width="100px">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <?php
                $editUser = new Users_Controller();
                $editUser->ctrEditUser();
                ?>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>