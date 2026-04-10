<?php

/* =====================================================================
   Feedback Mensual �?" Panel principal
   ===================================================================== */

$createFeedback = new MonthlyFeedback_Controller();
$createFeedback->ctrCreateFeedback();

$feedbacks = MonthlyFeedback_Controller::ctrGetFeedbacks();
$clients   = MonthlyFeedback_Controller::ctrGetClients();
$webhookUrl = MonthlyFeedback_Controller::ctrGetConfig('webhook_url');

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
         . '://' . $_SERVER['HTTP_HOST']
         . strtok($_SERVER['REQUEST_URI'], '?');
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Feedback Mensual
            <small>Gestión de formularios de satisfacción de clientes</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Feedback Mensual</li>
        </ol>
    </section>

    <section class="content">

        <!-- ============================================================
             Modal �?" Generar nuevo link de feedback
             ============================================================ -->
        <div class="modal fade" id="addFeedbackModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" autocomplete="off">
                        <div class="modal-header" style="background:#00013b; color:#fff;">
                            <button type="button" class="close" data-dismiss="modal">
                                <span aria-hidden="true">�-</span>
                            </button>
                            <h4 class="modal-title">
                                <i class="fa fa-link"></i> Generar Link de Feedback
                            </h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Cliente <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="newFeedbackClientId"
                                        id="newFeedbackClientId" required style="width:100%;">
                                    <option value="">Selecciona un cliente...</option>
                                    <?php foreach ($clients as $c): ?>
                                        <option value="<?php echo (int)$c['id']; ?>">
                                            <?php echo htmlspecialchars($c['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" style="margin-top:12px;">
                                <label>
                                    <input type="checkbox" id="specifyProjectsCheck" name="specifyProjects" value="1">
                                    Especificar proyectos
                                </label>
                                <small class="text-muted" style="display:block;margin-top:2px;">
                                    Si marcas esta opci&oacute;n, el cliente deber&aacute; elegir un proyecto al llenar el formulario.
                                </small>
                            </div>
                            <div id="projectListContainer" style="display:none;margin-top:8px;">
                                <label>Proyectos del cliente</label>
                                <div id="projectCheckboxes" style="max-height:200px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;padding:8px;">
                                    <p class="text-muted" style="margin:0;">Selecciona un cliente primero&hellip;</p>
                                </div>
                                <small class="text-muted">Marca los proyectos que se incluir&aacute;n en el formulario.</small>
                            </div>
                            <input type="hidden" name="projectIds" id="projectIdsHidden" value="">

                            <!-- Ejecutivos -->
                            <hr style="margin:16px 0 12px;">
                            <div class="form-group">
                                <label><i class="fa fa-users"></i> Ejecutivos (notificaciones)</label>
                                <small class="text-muted" style="display:block;margin-bottom:6px;">Se enviar&aacute;n los datos del feedback al webhook. Agrega los ejecutivos que recibir&aacute;n la notificaci&oacute;n.</small>
                                <div id="executivesContainer">
                                    <div class="exec-row" style="display:flex;gap:6px;margin-bottom:6px;">
                                        <input type="text" class="form-control exec-name" placeholder="Nombre" style="flex:1;">
                                        <input type="email" class="form-control exec-email" placeholder="Correo" style="flex:1;">
                                        <button type="button" class="btn btn-xs btn-danger" onclick="this.closest('.exec-row').remove()" style="align-self:center;"><i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-xs btn-default" id="addExecBtn" style="margin-top:4px;">
                                    <i class="fa fa-plus"></i> Agregar ejecutivo
                                </button>
                            </div>
                            <input type="hidden" name="executives" id="executivesHidden" value="">

                            <div class="alert alert-info" style="margin-bottom:0;margin-top:12px;">
                                <i class="fa fa-info-circle"></i>
                                Se generará un link permanente para el cliente. Cada mes el cliente
                                eleGirá el período al llenar el formulario &mdash; el mismo link sirve siempre.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left"
                                    data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-link"></i> Generar Link
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================================
             Modal �?" Ver respuestas
             ============================================================ -->
        <div class="modal fade" id="viewResponseModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background:#00a65a; color:#fff;">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">�-</span>
                        </button>
                        <h4 class="modal-title">
                            <i class="fa fa-comments"></i> Respuestas del Cliente
                        </h4>
                    </div>
                    <div class="modal-body" id="viewResponseBody">
                        <div class="text-center text-muted">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                            <p>Cargando respuestas...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================
             Modal - Editar feedback
             ============================================================ -->
        <div class="modal fade" id="editFeedbackModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background:#f0ad4e; color:#fff;">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">
                            <i class="fa fa-pencil"></i> Editar Link de Feedback
                        </h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editFeedbackId">
                        <div class="form-group">
                            <label>Cliente</label>
                            <input type="text" class="form-control" id="editClientName" disabled>
                        </div>
                        <div class="form-group" style="margin-top:12px;">
                            <label>
                                <input type="checkbox" id="editSpecifyProjectsCheck">
                                Especificar proyectos
                            </label>
                        </div>
                        <div id="editProjectListContainer" style="display:none;margin-top:8px;">
                            <label>Proyectos del cliente</label>
                            <div id="editProjectCheckboxes" style="max-height:200px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;padding:8px;">
                                <p class="text-muted" style="margin:0;">Cargando...</p>
                            </div>
                        </div>

                        <!-- Ejecutivos (edit) -->
                        <hr style="margin:16px 0 12px;">
                        <div class="form-group">
                            <label><i class="fa fa-users"></i> Ejecutivos</label>
                            <div id="editExecutivesContainer"></div>
                            <button type="button" class="btn btn-xs btn-default" id="addEditExecBtn" style="margin-top:4px;">
                                <i class="fa fa-plus"></i> Agregar ejecutivo
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left"
                                data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-warning" id="btnSaveEditFeedback">
                            <i class="fa fa-save"></i> Guardar cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================
             Configuración global de webhook
             ============================================================ -->
        <div class="box box-default collapsed-box">
            <div class="box-header with-border" style="cursor:pointer;" data-widget="collapse">
                <h3 class="box-title"><i class="fa fa-cog"></i> Configuración</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                </div>
            </div>
            <div class="box-body" style="display:none;">
                <div class="form-group" style="max-width:600px;">
                    <label><i class="fa fa-globe"></i> Webhook URL (Make / Zapier)</label>
                    <small class="text-muted" style="display:block;margin-bottom:6px;">Endpoint donde se enviar&aacute;n los datos del feedback al ser completado. Se aplica a todos los links.</small>
                    <div class="input-group">
                        <input type="url" class="form-control" id="globalWebhookUrl" placeholder="https://hook.us1.make.com/..." value="<?php echo htmlspecialchars($webhookUrl ?? ''); ?>">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-primary" id="btnSaveWebhookUrl">
                                <i class="fa fa-save"></i> Guardar
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================
             Tabla de feedbacks
             ============================================================ -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Links de feedback generados</h3>
                <button class="btn btn-success pull-right" data-toggle="modal"
                        data-target="#addFeedbackModal">
                    <i class="fa fa-plus"></i> Generar Link
                </button>
            </div>
            <div class="box-body">
                <?php if (empty($feedbacks)): ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        Aún no hay links generados. Haz clic en <strong>Generar Link</strong> para crear el primero.
                    </div>
                <?php else: ?>
                    <table id="feedbackTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Respuestas</th>
                                <th>�sltimo feedback</th>
                                <th>Generado</th>
                                <th style="width:240px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedbacks as $i => $f):
                                $formUrl = $baseUrl . '?route=monthlyFeedbackForm&token=' . urlencode($f['token']);
                            ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($f['client_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($f['client_code'] ?? ''); ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo (int)$f['response_count'] > 0 ? 'green' : 'gray'; ?>">
                                        <?php echo (int)$f['response_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($f['last_response']): ?>
                                        <?php echo date('d/m/Y H:i', strtotime($f['last_response'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($f['created_at'])); ?></td>
                                <td style="white-space:nowrap;">
                                    <button class="btn btn-xs btn-info btn-copyFormLink"
                                            title="Copiar link del formulario"
                                            data-link="<?php echo htmlspecialchars($formUrl); ?>">
                                        <i class="fa fa-copy"></i> Copiar link
                                    </button>

                                    <?php if ((int)$f['response_count'] > 0): ?>
                                        <button class="btn btn-xs btn-success btn-viewResponse"
                                                title="Ver respuestas"
                                                data-feedback-id="<?php echo (int)$f['id']; ?>">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    <?php endif; ?>

                                    <button class="btn btn-xs btn-warning btn-editFeedback"
                                            title="Editar"
                                            data-feedback-id="<?php echo (int)$f['id']; ?>"
                                            data-client-id="<?php echo (int)$f['client_id']; ?>"
                                            data-client-name="<?php echo htmlspecialchars($f['client_name']); ?>"
                                            data-project-ids="<?php echo htmlspecialchars($f['project_ids'] ?? ''); ?>"
                                            data-executives="<?php echo htmlspecialchars($f['executives'] ?? ''); ?>">
                                        <i class="fa fa-pencil"></i>
                                    </button>

                                    <button class="btn btn-xs btn-danger btn-deleteFeedback"
                                            title="Eliminar"
                                            data-feedback-id="<?php echo (int)$f['id']; ?>"
                                            data-client="<?php echo htmlspecialchars($f['client_name']); ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </section>
</div>
