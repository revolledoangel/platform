<?php
// Incluir los controladores necesarios al principio
require_once "controllers/mediaMixRealEstate.controller.php";
require_once "controllers/mediaMixRealEstateDetails.controller.php";

// 1. VALIDACIÓN INICIAL
if (!isset($_GET['mediaMixId']) || !is_numeric($_GET['mediaMixId'])) {
    echo '<script>window.location = "mediaMixRealEstate";</script>';
    return;
}

// 2. VERIFICAR QUE EL MEDIA MIX EXISTE
$mediaMix = MediaMixRealEstate_Controller::ctrShowMediaMixRealEstateById($_GET['mediaMixId']);

if (!$mediaMix) {
    echo '<script>
        swal({
            type: "error",
            title: "Error",
            text: "¡Mix de Medios no encontrado!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
        }).then((result) => {
            if (result.value) {
                window.location = "mediaMixRealEstate";
            }
        });
    </script>';
    return;
}

// Lógica del CRUD para los detalles
$createDetail = new MediaMixRealEstateDetails_Controller();
$createDetail->ctrCreateDetail();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Detalles del Mix de Medios: 
            <small><?php echo htmlspecialchars($mediaMix['name']); ?></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="mediaMixRealEstate">Mix de Medios Inmobiliario</a></li>
            <li class="active">Detalles</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addDetailModal">
                    Agregar Detalle
                </button>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="detailsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Proyecto</th>
                                <th>Plataforma</th>
                                <th>Objetivo(s)</th>
                                <th>AON</th>
                                <th>Tipo Campaña</th>
                                <th>Canal</th>
                                <th>Segmentación</th>
                                <th>Formato(s)</th>
                                <th>Inversión</th>
                                <th>Distribución</th>
                                <th>Meta Proyectada</th>
                                <th>Tipo Resultado</th>
                                <th>CPR</th>
                                <th>Tipo de CRP</th>
                                <th>Comentarios</th>
                                <th>Estado</th>
                                <th style="width:100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade in" id="addDetailModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form role="form" method="post" autocomplete="off">
                <input type="hidden" name="newMediaMixRealEstateId" value="<?php echo $_GET['mediaMixId']; ?>">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <h4 class="modal-title">Agregar Detalle al Mix de Medios</h4>
                </div>
                <div class="modal-body">
                    <p>Aquí se colocarán los campos para agregar un nuevo detalle...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Detalle</button>
                </div>
            </form>
        </div>
    </div>
</div>