<?php
session_start();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Algoritmo Plataforma</title>

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="icon" href="views/img/template/algoritmo-icon.png">

    <!--========================================================================
    PLUGINS DE css
    ========================================================================-->
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="views/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="views/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="views/bower_components/Ionicons/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="views/dist/css/AdminLTE.css">
    <!-- AdminLTE Skins -->
    <link rel="stylesheet" href="views/dist/css/skins/_all-skins.css">
    <!-- Google Font -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <!-- Select2 -->
    <link rel="stylesheet" href="views/bower_components/select2/dist/css/select2.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="views/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="views/bower_components/datatables.net-bs/css/responsive.bootstrap.min.css">


    <!-- SweetAlert2 -->
    <script src="views/plugins/sweetalert2/sweetalert2.all.js"></script>



    <link rel="stylesheet" href="views/css/style.css">



</head>

<body class="hold-transition skin-blue sidebar-mini login-page sidebar-collapse">

    <?php

    if (isset($_SESSION["startSession"]) && $_SESSION["startSession"] == true) {

        echo '<div class="wrapper">';
        include "modules/header.php";
        include "modules/aside.php";

        if (isset($_GET["route"])) {

            $allowedRoutes = [
                "home",
                "campaigns",
                "urls",
                "comments",
                "close",
                // ✅ New Media Mix routes
                "mediaMixRealEstate",
                "mediaMixRealEstateDetails",
                "mediaMixEcommerce",
                "mediaMixEcommerceDetails",
                "mediaMixOthers"
            ];

            $perfil = $_SESSION["perfil"] ?? "";

            if ($perfil === "Super") {
                $allowedRoutes = array_merge($allowedRoutes, [
                    "users",
                    "verticals",
                    "clients",
                    "projects",
                    "platforms",
                    "formats",
                    "objectives",
                    "campaignTypes",
                    "channels"
                ]);
            } elseif ($perfil === "Administrador") {
                $allowedRoutes = array_merge($allowedRoutes, [
                    "verticals",
                    "clients",
                    "projects",
                    "platforms",
                    "formats",
                    "objectives",
                    "campaignTypes",
                    "channels"
                ]);
            }

            if (in_array($_GET["route"], $allowedRoutes)) {
                include "modules/" . $_GET["route"] . ".php";
            } else {
                include "modules/404.php";
            }

        } else {
            include "modules/home.php";
        }

        include "modules/footer.php";
        echo '</div>';

    } else {
        include "modules/login.php";
    }

    ?>
</body>



<!--========================================================================
    PLUGINS DE javascript
    ========================================================================-->

<!-- jQuery 3 -->
<script src="views/bower_components/jquery/dist/jquery.min.js"></script>

<!-- Bootstrap 3.3.7 -->
<script src="views/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

<!-- FastClick -->
<script src="views/bower_components/fastclick/lib/fastclick.js"></script>

<!-- AdminLTE App -->
<script src="views/dist/js/adminlte.min.js"></script>

<!-- DataTables -->
<script src="views/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="views/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<!--<script src="views/bower_components/datatables.net-bs/js/dataTables.responsive.min.js"></script>
    <script src="views/bower_components/datatables.net-bs/js/responsive.bootstrap.min.js"></script>-->

<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<!-- Botones CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>


<!-- Select2 -->
<script src="views/bower_components/select2/dist/js/select2.full.js"></script>

<script src="views/js/template.js"></script>

<script src="views/js/users.js"></script>

<script src="views/js/verticals.js"></script>

<script src="views/js/clients.js"></script>

<script src="views/js/projects.js"></script>

<script src="views/js/platforms.js"></script>

<script src="views/js/formats.js"></script>

<script src="views/js/objectives.js"></script>

<script src="views/js/campaignTypes.js"></script>

<script src="views/js/channels.js"></script>

<script src="views/js/mediaMixRealEstate.js"></script>

<script src="views/js/mediaMixRealEstateDetails.js"></script>

<script src="views/js/mediaMixEcommerce.js"></script>

<script src="views/js/mediaMixEcommerceDetails.js"></script>

<script src="views/js/campaigns.js"></script>

<script src="views/js/urls.js"></script>

<script src="views/js/comments.js"></script>


</html>