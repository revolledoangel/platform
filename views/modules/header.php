<header class="main-header">

    <!-- Logo -->

    <a href="home" class="logo">

        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">
            <img src="views/img/template/algoritmo-logo-chico.png" class="img-responsive" style="padding: 10px 5px;" alt="logo algoritmo">
        </span>

        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg">
            <img src="views/img/template/algoritmo-logo-largo.png" class="img-responsive" style="padding: 10px;" alt="logo algoritmo">
        </span>

    </a>

    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">

        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">


                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">

                        <?php
                        if($_SESSION["foto"]!=""){
                            echo '<img src="'.$_SESSION["foto"].'" class="user-image" alt="User Image">';
                        }else{
                            echo '<img src="views/img/template/usuario-sin-foto.png" class="user-image" alt="User Image">';
                        }
                        ?>

                        <span class="hidden-xs"><?php echo $_SESSION["nombre"]; ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">

                            <?php
                            if($_SESSION["foto"]!=""){
                                echo '<img src="'.$_SESSION["foto"].'" class="img-circle" alt="User Image">';
                            }else{
                                echo '<img src="views/img/template/usuario-sin-foto.png" class="img-circle" alt="User Image">';
                            }
                            ?>
                            
                            <p>
                                <?php echo $_SESSION["nombre"]; ?> - <?php echo $_SESSION["perfil"]; ?>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-right">
                                <a href="close" class="btn btn-default btn-flat">Cerrar Sesión</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>