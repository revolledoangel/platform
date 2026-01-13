<?php

$deleteComment = new Comments_Controller();
$deleteComment->ctrDeleteComment();

?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Comentarios
            <small>Administrar comentarios</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Comentarios</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">

            <div class="box-header with-border">

                <div class="form-group pull-left" style="width:300px; margin-bottom:20px;">
                    <label for="filterClient">Filtrar por Cliente:</label>
                    <select id="filterClient" class="form-control select2" style="width:100%;">
                        <option value="">-- Todos los clientes --</option>
                        <?php
                        $clientes = Clients_controller::ctrShowClients();
                        foreach ($clientes as $cliente) {
                            echo '<option value="' . htmlspecialchars($cliente["id"]) . '">' . htmlspecialchars($cliente["name"]) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group pull-left" style="width:300px; margin-left:20px; margin-bottom:20px;">
                    <label for="filterPlatform">Filtrar por Plataforma:</label>
                    <select id="filterPlatform" class="form-control select2" style="width:100%;">
                        <option value="">-- Todas las plataformas --</option>
                        <?php
                        $platforms = Platforms_controller::ctrShowPlatforms();
                        foreach ($platforms as $platform) {
                            echo '<option value="' . htmlspecialchars($platform["id"]) . '">' . htmlspecialchars($platform["name"]) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group pull-left" style="width:300px; margin-left:20px; margin-bottom:20px;">
                    <label for="filterPeriod">Filtrar por Periodo:</label>
                    <select id="filterPeriod" class="form-control select2" style="width:100%;">
                        <option value="">-- Todos los periodos --</option>
                        <?php
                        $periods = Periods_controller::ctrShowPeriods();
                        foreach ($periods as $period) {
                            echo '<option value="' . htmlspecialchars($period["id"]) . '">' . htmlspecialchars($period["name"]) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addCommentModal">
                    Agregar Comentarios
                </button>

            </div>
            <div class="box-body">
                <style>
                    #commentsTable td:nth-child(6),
                    #commentsTable td:nth-child(7) {
                        max-width: 250px;
                        word-wrap: break-word;
                    }
                    #commentsTable td:nth-child(8) {
                        white-space: nowrap !important;
                        min-width: 130px;
                        width: 130px;
                    }
                    #commentsTable .btn-group {
                        display: flex !important;
                        flex-direction: row !important;
                        white-space: nowrap !important;
                    }
                    #commentsTable .btn-group .btn {
                        flex-shrink: 0;
                        padding: 6px 10px;
                        font-size: 12px;
                    }
                </style>
                <table class="table table-bordered table-striped dt-responsive" id="commentsTable" width="100%">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th style="display:none;">Client ID</th>
                            <th>Plataforma</th>
                            <th style="display:none;">Platform ID</th>
                            <th>Periodo</th>
                            <th style="display:none;">Period ID</th>
                            <th>Hallazgos - Recomendaciones</th>
                            <th>Comentario - Conclusiones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Modal para agregar comentario -->
<div class="modal fade" id="addCommentModal" tabindex="-1" role="dialog" aria-labelledby="addCommentLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <form role="form" method="post" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <h4 class="modal-title">Nuevo Comentario</h4>
                </div>

                <div class="modal-body">
                    <div class="box-body">

                        <!-- Seleccionar Periodo -->
                        <div class="form-group">

                            <label for="newCommentPeriod">Periodo</label>
                            <select class="form-control select2" id="newCommentPeriod" name="newCommentPeriod" required
                                style="width:100%;">
                                <?php
                                $periodsJson = @file_get_contents('https://algoritmo.digital/backend/public/api/periods');
                                $periods = json_decode($periodsJson, true);

                                if (!is_array($periods)) {
                                    echo '<option value="">Error al cargar periodos</option>';
                                } else {
                                    $currentMonth = (int) date("n");
                                    $currentYear = (int) date("Y");
                                    $selectedFound = false;

                                    echo '<option value="">-- Selecciona un periodo --</option>';

                                    foreach ($periods as $period) {
                                        $selected = '';
                                        if ((int) $period["month_number"] === $currentMonth && (int) $period["year"] === $currentYear) {
                                            $selected = ' selected';
                                            $selectedFound = true;
                                        }
                                        echo '<option value="' . htmlspecialchars($period["id"]) . '"' . $selected . '>' . htmlspecialchars($period["name"]) . '</option>';
                                    }

                                    if (!$selectedFound) {
                                        echo '<!-- ⚠ No se encontró periodo para el mes actual -->';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Seleccionar Cliente -->
                        <div class="form-group">
                            <label for="newCommentClient">Cliente</label>
                            <select class="form-control select2" id="newCommentClient" name="newCommentClient" required
                                style="width:100%;">
                                <option value="">-- Selecciona un cliente --</option>
                                <?php
                                $clientes = Clients_controller::ctrShowClients();
                                foreach ($clientes as $cliente) {
                                    echo '<option value="' . $cliente["id"] . '">' . htmlspecialchars($cliente["name"]) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Seleccionar Plataforma -->
                        <div class="form-group">
                            <label for="newCommentPlatform">Plataforma</label>
                            <select class="form-control select2" id="newCommentPlatform" name="newCommentPlatform"
                                required style="width:100%;">
                                <option value="">-- Selecciona una plataforma --</option>
                                <?php
                                $platforms = Platforms_controller::ctrShowPlatforms();
                                foreach ($platforms as $platform) {
                                    echo '<option value="' . $platform["id"] . '">' . htmlspecialchars($platform["name"]) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="commentRecommendation">Hallazgos - Recomendaciones</label>
                            <textarea id="commentRecommendation" name="newCommentRecommendation" class="form-control ckeditor"
                                rows="3" placeholder="Ej. Se sugiere optimizar audiencias..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="commentConclusion">Comentario - Conclusiones</label>
                            <textarea id="commentConclusion" name="newCommentConclusion" class="form-control ckeditor" rows="3"
                                placeholder="Ej. Se concluye que el rendimiento fue superior..." required></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar comentario</button>
                </div>
            </div>

            <?php
            $createComment = new Comments_Controller();
            $createComment->ctrCreateComment();
            ?>

        </form>
    </div>
</div><!-- Modal para editar comentario -->
<div class="modal fade" id="editCommentModal" tabindex="-1" role="dialog" aria-labelledby="editCommentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form role="form" method="post" id="editCommentForm" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header" style="background:#00013b;color:#fff">
                    <h4 class="modal-title">Editar Comentario</h4>
                </div>

                <div class="modal-body">
                    <div class="box-body">
                        <input type="hidden" name="editCommentId">

                        <!-- Seleccionar Periodo -->
                        <div class="form-group">
                            <label for="editCommentPeriod">Periodo</label>
                            <select class="form-control select2" id="editCommentPeriod" name="editCommentPeriod" required style="width:100%;">
                                <option value="">-- Selecciona un periodo --</option>
                                <?php
                                $periodsJson = @file_get_contents('https://algoritmo.digital/backend/public/api/periods');
                                $periods = json_decode($periodsJson, true);
                                if (is_array($periods)) {
                                    foreach ($periods as $period) {
                                        echo '<option value="' . htmlspecialchars($period["id"]) . '">' . htmlspecialchars($period["name"]) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Seleccionar Cliente -->
                        <div class="form-group">
                            <label for="editCommentClient">Cliente</label>
                            <select class="form-control select2" id="editCommentClient" name="editCommentClient" required style="width:100%;">
                                <option value="">-- Selecciona un cliente --</option>
                                <?php
                                $clientes = Clients_controller::ctrShowClients();
                                foreach ($clientes as $cliente) {
                                    echo '<option value="' . $cliente["id"] . '">' . htmlspecialchars($cliente["name"]) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Seleccionar Plataforma -->
                        <div class="form-group">
                            <label for="editCommentPlatform">Plataforma</label>
                            <select class="form-control select2" id="editCommentPlatform" name="editCommentPlatform" required style="width:100%;">
                                <option value="">-- Selecciona una plataforma --</option>
                                <?php
                                $platforms = Platforms_controller::ctrShowPlatforms();
                                foreach ($platforms as $platform) {
                                    echo '<option value="' . $platform["id"] . '">' . htmlspecialchars($platform["name"]) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="editCommentRecommendation">Hallazgos - Recomendaciones</label>
                            <textarea id="editCommentRecommendation" name="editCommentRecommendation" class="form-control ckeditor" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="editCommentConclusion">Comentario - Conclusiones</label>
                            <textarea id="editCommentConclusion" name="editCommentConclusion" class="form-control ckeditor" rows="3" required></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar comentario</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal para ver comentario -->
<div class="modal fade" id="viewCommentModal" tabindex="-1" role="dialog" aria-labelledby="viewCommentLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="viewCommentLabel">Ver Comentario</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Cliente</label>
                            <p id="viewCommentClient" class="form-control-static"></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Plataforma</label>
                            <p id="viewCommentPlatform" class="form-control-static"></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Periodo</label>
                            <p id="viewCommentPeriod" class="form-control-static"></p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Hallazgos - Recomendaciones</label>
                    <div id="viewCommentRecommendation" class="form-control-static" style="border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9; min-height: 50px;"></div>
                </div>

                <div class="form-group">
                    <label>Comentario - Conclusiones</label>
                    <div id="viewCommentConclusion" class="form-control-static" style="border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9; min-height: 50px;"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
