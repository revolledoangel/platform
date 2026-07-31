<?php
$host = 'srv1013.hstgr.io';
$port = 3306;
$db   = 'u961992735_plataforma';
$user = 'u961992735_plataforma';
$pass = 'Peru+*963.';
$conn = new mysqli($host, $user, $pass, $db, $port);
$detailsCount = 0;
$clientsCount = 0;
$usersCount = 0;
if (!$conn->connect_error) {
    $res = $conn->query("SELECT COUNT(*) AS total FROM mediamixrealestate_details");
    if ($res && $row = $res->fetch_assoc()) $detailsCount = $row['total'];
    $res = $conn->query("SELECT COUNT(*) AS total FROM clients");
    if ($res && $row = $res->fetch_assoc()) $clientsCount = $row['total'];
    $res = $conn->query("SELECT COUNT(*) AS total FROM users");
    if ($res && $row = $res->fetch_assoc()) $usersCount = $row['total'];
    $conn->close();
}
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Dashboard
      <small>Panel de Control</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i> Home</a></li>
      <li class="active">Dashboard</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-aqua">
          <div class="inner">
            <h3><?php echo $detailsCount; ?></h3>

            <p>Campañas</p>
          </div>
          <div class="icon">
            <i class="ion ion-bag"></i>
          </div>
          <a href="#" class="small-box-footer">Ver más <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green">
          <div class="inner">
            <h3><?php echo $clientsCount; ?></h3>

            <p>Clientes</p>
          </div>
          <div class="icon">
            <i class="ion ion-stats-bars"></i>
          </div>
          <a href="clients" class="small-box-footer">Ver más <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3><?php echo $usersCount; ?></h3>

            <p>Usuarios registrados</p>
          </div>
          <div class="icon">
            <i class="ion ion-person-add"></i>
          </div>
          <a href="users" class="small-box-footer">Ver más <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-red">
          <div class="inner">
            <h3>65</h3>

            <p>Comentarios</p>
          </div>
          <div class="icon">
            <i class="ion ion-pie-graph"></i>
          </div>
          <a href="#" class="small-box-footer">Ver más <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <!-- ./col -->
    </div>
    <!-- /.row -->

    <!-- Main row -->
    <div class="row">


    </div>
    <!-- /.row (main row) -->

  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->