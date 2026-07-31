<?php
// Lógica para procesar la creación y eliminación, movida al inicio para mayor claridad y corrección.
// Esta sección se ejecuta solo si se envía un formulario con los datos correspondientes.

if (isset($_POST['newCampaignTypeName'])) {
    $createCampaignType = new CampaignTypes_Controller();
    $createCampaignType->ctrCreateCampaignType();
}

if (isset($_GET['deleteCampaignTypeId'])) {
    $deleteCampaignType = new CampaignTypes_Controller();
    $deleteCampaignType->ctrDeleteCampaignType();
}

?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Tipos de Campaña
            <small>Administrar Tipos de Campaña</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Tipos de Campaña</li>
        </ol>
    </section>

    <section class="content">

        <div class="box">
            <div class="box-header with-border">
                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addCampaignTypeModal">
                    Agregar Tipo de Campaña
                </button>
            </div>

            <div class="box-body">
                <div class="table-responsive">
                    <table id="campaignTypesTable" class="table table-bordered table-striped dt-responsive">
                        <thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>Nombre</th>
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

<div class="modal fade" id="addCampaignTypeModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Agregar Tipo de Campaña</h4>
                </div>

                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-tags"></i></span>
                                <input type="text" class="form-control" placeholder="Nombre del Tipo de Campaña" name="newCampaignTypeName" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCampaignTypeModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" id="editCampaignTypeForm" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Editar Tipo de Campaña</h4>
                </div>

                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-tags"></i></span>
                                <input type="text" class="form-control" name="editCampaignTypeName" id="editCampaignTypeName" required>
                                <input type="hidden" name="campaignTypeId" id="campaignTypeId">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <?php
                    // La lógica para editar se manejará vía AJAX (JavaScript)
                    // pero si se necesita procesar aquí, se haría de forma similar a la creación:
                    // if (isset($_POST['campaignTypeId'])) {
                    //     $editCampaignType = new CampaignTypes_Controller();
                    //     $editCampaignType->ctrEditCampaignType();
                    // }
                ?>
            </form>
        </div>
    </div>
</div>