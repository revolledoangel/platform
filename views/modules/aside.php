<?php
$currentRoute = $_GET["route"] ?? "";
$perfil = $_SESSION["perfil"] ?? "";
?>

<aside class="main-sidebar">
    <section class="sidebar">

        <div class="user-panel">
            <div class="pull-left image">
                <?php
                if (!empty($_SESSION["foto"])) {
                    echo '<img src="' . $_SESSION["foto"] . '" class="img-circle" alt="User Image">';
                } else {
                    echo '<img src="views/img/template/usuario-sin-foto.png" class="img-circle" alt="User Image">';
                }
                ?>
            </div>
            <div class="pull-left info">
                <p><?= $_SESSION["nombre"] ?></p>
                <small><?= $_SESSION["perfil"] ?></small>
            </div>
        </div>

        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">MENÚ DE NAVEGACIÓN</li>

            <!-- Inicio -->
            <li class="<?= ($currentRoute == 'home' || $currentRoute == '') ? 'active' : '' ?>">
                <a href="home">
                    <i class="fa fa-home"></i> <span>Inicio</span>
                </a>
            </li>

            <!-- Usuarios: Solo para Super -->
            <?php if ($perfil === "Super"): ?>
                <li class="<?= ($currentRoute == 'users') ? 'active' : '' ?>">
                    <a href="users">
                        <i class="fa fa-users"></i> <span>Usuarios</span>
                        <span class="pull-right-container">
                            <small class="label pull-right bg-green">nuevo</small>
                        </span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Configuración: Para Super y Administrador -->
            <?php if (in_array($perfil, ["Super", "Administrador"])): ?>
                <li
                    class="treeview <?= in_array($currentRoute, ['verticals', 'clients', 'projects', 'platforms', 'formats', 'objectives', 'campaignTypes', 'channels']) ? 'active' : '' ?>">
                    <a href="#">
                        <i class="fa fa-cogs"></i> <span>Configuración</span> <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?= ($currentRoute == 'verticals') ? 'active' : '' ?>"><a href="verticals"><i
                                    class="fa fa-circle-o"></i> Verticales</a></li>
                        <li class="<?= ($currentRoute == 'clients') ? 'active' : '' ?>"><a href="clients"><i
                                    class="ion ion-person-add"></i> Clientes</a></li>
                        <li class="<?= ($currentRoute == 'projects') ? 'active' : '' ?>"><a href="projects"><i
                                    class="fa fa-map"></i> Proyectos</a></li>
                        <li class="<?= ($currentRoute == 'platforms') ? 'active' : '' ?>"><a href="platforms"><i
                                    class="fa fa-chrome"></i> Plataformas</a></li>
                        <li class="<?= ($currentRoute == 'formats') ? 'active' : '' ?>"><a href="formats"><i
                                    class="fa fa-bookmark"></i> Formatos</a></li>
                        <li class="<?= ($currentRoute == 'objectives') ? 'active' : '' ?>"><a href="objectives"><i
                                    class="fa fa-graduation-cap"></i> Objetivos</a></li>

                        <li class="<?= ($currentRoute == 'campaignTypes') ? 'active' : '' ?>"><a href="campaignTypes"><i
                                    class="fa fa-tags"></i> Tipo de Campaña</a></li>
                        <li class="<?= ($currentRoute == 'channels') ? 'active' : '' ?>"><a href="channels"><i
                                    class="fa fa-sitemap"></i> Canales</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Media Mix: Visible for all -->
            <li
                class="treeview <?= in_array($currentRoute, ['mediaMixRealEstate', 'mediaMixEcommerce', 'mediaMixOthers']) ? 'active' : '' ?>">
                <a href="#">
                    <i class="fa fa-random"></i> <span>Mix de Medios</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="<?= ($currentRoute == 'mediaMixRealEstate') ? 'active' : '' ?>">
                        <a href="mediaMixRealEstate"><i class="fa fa-circle-o"></i> Mix Inmobiliario</a>
                    </li>
                    <li class="<?= ($currentRoute == 'mediaMixEcommerce') ? 'active' : '' ?>">
                        <a href="mediaMixEcommerce"><i class="fa fa-circle-o"></i> Mix Ecommerce</a>
                    </li>
                    <li class="<?= ($currentRoute == 'mediaMixOthers') ? 'active' : '' ?>">
                        <a href="mediaMixOthers"><i class="fa fa-circle-o"></i> Mix Otros</a>
                    </li>
                </ul>
            </li>



            <!-- Campañas: Visible para todos -->
            <li class="treeview <?= in_array($currentRoute, ['campaigns', 'urls']) ? 'active' : '' ?>">
                <a href="#">
                    <i class="glyphicon glyphicon-bullhorn"></i> <span>Campañas</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="<?= ($currentRoute == 'campaigns') ? 'active' : '' ?>"><a href="campaigns"><i
                                class="fa fa-circle-o"></i> Campañas</a></li>
                    <li class="<?= ($currentRoute == 'urls') ? 'active' : '' ?>"><a href="urls"><i
                                class="fa fa-circle-o"></i> Generador de UTM's</a></li>
                </ul>
            </li>



            <!-- Comentarios: Visible para todos -->
            <li class="<?= ($currentRoute == 'comments') ? 'active' : '' ?>">
                <a href="comments">
                    <i class="fa fa-comments"></i> <span>Comentarios</span>
                </a>
            </li>

        </ul>
    </section>
</aside>