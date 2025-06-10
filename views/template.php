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

    

</head>

<body class="hold-transition skin-blue sidebar-mini login-page">

    <?php

    if (isset($_SESSION["startSession"]) && $_SESSION["startSession"] == true) {

        echo '<div class="wrapper">';
        include "modules/header.php";
        include "modules/aside.php";

        if (isset($_GET["route"])) {

            if (
                $_GET["route"] == "home" ||
                $_GET["route"] == "users" ||
                $_GET["route"] == "close"
            ) {
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

    

    <!-- Select2 -->
    <script src="views/bower_components/select2/dist/js/select2.full.js"></script>

    <script src="views/js/template.js"></script>

    <script src="views/js/users.js"></script>

</html>