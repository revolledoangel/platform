<?php
require_once "controllers/users.controller.php";
?>

<div class="login-box">

    <div class="login-logo">
        <img src="views/img/template/algoritmo-logo-largo.png" class="img-responsive" style="padding:30px 100px 0 100px"
            alt="Logo de Algoritmo">
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body" style="border-radius:15px">
        <p class="login-box-msg">Ingresar al sistema</p>
        
        <form method="post">

            <div class="form-group has-feedback">
                <input type="text" class="form-control" placeholder="Email o usuario" name="user" required autocomplete="off">
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>

            <div class="form-group has-feedback">
                <input type="password" class="form-control" placeholder="Contraseña" name="password" required autocomplete="off">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>

            <div class="row">


                <div class="col-xs-12">

                    <button type="submit" class="btn btn-warning btn-block btn-flat"> Ingresar</button>

                </div>


            </div>

            <?php
            $login =  new Users_Controller();
            $login -> ctrUserStartSesion();

            ?>

        </form>

    </div>

</div>

