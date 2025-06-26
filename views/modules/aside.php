<?php

// Obtenemos la ruta actual de la URL para saber qué página está activa.
$currentRoute = "";
if (isset($_GET["route"])) {
    $currentRoute = $_GET["route"];
}

?>

<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">

                <?php
                if ($_SESSION["foto"] != "") {
                    echo '<img src="' . $_SESSION["foto"] . '" class="img-circle" alt="User Image">';
                } else {
                    echo '<img src="views/img/template/usuario-sin-foto.png" class="img-circle" alt="User Image">';
                }
                ?>

            </div>
            <div class="pull-left info">
                <p><?php echo $_SESSION["nombre"] ?></p>
                <small><?php echo $_SESSION["perfil"] ?></small>
            </div>
        </div>



        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree">

            <li class="header">MENÚ DE NAVEGACIÓN</li>

            <li class="<?php if ($currentRoute == 'home' || $currentRoute == '')
                echo 'active'; ?>">
                <a href="home">
                    <i class="fa fa-home"></i> <span>Inicio</span>
                </a>
            </li>

            <li class="<?php if ($currentRoute == 'users')
                echo 'active'; ?>">
                <a href="users">
                    <i class="fa fa-users"></i> <span>Usuarios</span>
                    <span class="pull-right-container">
                        <small class="label pull-right bg-green">nuevo</small>
                    </span>
                </a>
            </li>

            <li class="treeview <?php if    ($currentRoute == 'clients' 
                                        || $currentRoute == 'projects'
                                        || $currentRoute == 'platforms'
                                        || $currentRoute == 'formats'
                                        || $currentRoute == 'objetives')
                echo 'active'; ?>">
                <a href="#">
                    <i class="fa fa-share"></i> <span>Configuración</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if ($currentRoute == 'verticals')
                        echo 'active'; ?>">
                        <a href="verticals">
                            <i class="fa fa-circle-o"></i> Verticales
                        </a>
                    </li>
                    <li class="<?php if ($currentRoute == 'clients')
                        echo 'active'; ?>">
                        <a href="clients">
                            <i class="ion ion-person-add"></i> Clientes
                        </a>
                    </li>
                    <li class="<?php if ($currentRoute == 'projects')
                        echo 'active'; ?>">
                        <a href="projects">
                            <i class="fa fa-map"></i> Proyectos
                        </a>
                    </li>
                    <li class="<?php if ($currentRoute == 'platforms')
                        echo 'active'; ?>">
                        <a href="platforms">
                            <i class="fa fa-chrome"></i> Plataformas
                        </a>
                    </li>
                    <li class="<?php if ($currentRoute == 'formats')
                        echo 'active'; ?>">
                        <a href="formats">
                            <i class="fa fa-bookmark"></i> Formatos
                        </a>
                    </li>
                    <li class="<?php if ($currentRoute == 'objetives')
                        echo 'active'; ?>">
                        <a href="objetives">
                            <i class="fa fa-graduation-cap"></i> Objetivos
                        </a>
                    </li>
                </ul>
            </li>

            <li class="treeview <?php if ($currentRoute == 'campanas' || $currentRoute == 'generador-utm')
                echo 'active'; ?>">
                <a href="#">
                    <i class="glyphicon glyphicon-bullhorn"></i> <span>Campañas</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if ($currentRoute == 'campanas')
                        echo 'active'; ?>">
                        <a href="campanas">
                            <i class="fa fa-circle-o"></i> Campañas
                        </a>
                    </li>
                    <li class="<?php if ($currentRoute == 'generador-utm')
                        echo 'active'; ?>">
                        <a href="generador-utm">
                            <i class="fa fa-circle-o"></i> Generador de UTM's
                        </a>
                    </li>
                </ul>
            </li>

        </ul>
    </section>
    <!-- /.sidebar -->
</aside>