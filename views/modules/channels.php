<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Canales
            <small>Administrar Canales</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Canales</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addChannelModal">
                    Agregar Canal
                </button>
            </div>

            <div class="box-body">
                <div class="table-responsive">
                    <table id="channelsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>Nombre</th>
                                <th style="max-width:150px">Acciones</th>
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

<div class="modal fade in" id="addChannelModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Agregar Canal</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i style="width: 25px;" class="fa fa-sitemap"></i></span>
                                <input type="text" class="form-control" placeholder="Nombre del Canal" name="newChannelName" required autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <?php
                $newChannel = new Channels_Controller();
                $newChannel->ctrCreateChannel();
                ?>
            </form>
        </div>
    </div>
</div>

<div class="modal fade in" id="editChannelModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editChannelForm" autocomplete="off">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Editar Canal</h4>
                </div>
                <input type="hidden" name="editChannelId">
                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i style="width: 25px;" class="fa fa-sitemap"></i>
                                </span>
                                <input type="text" class="form-control" name="editChannelName" placeholder="Nombre del canal" required autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$deleteChannel = new Channels_Controller();
$deleteChannel->ctrDeleteChannel();
?>