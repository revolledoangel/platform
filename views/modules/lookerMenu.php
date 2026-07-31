<?php
$perfil = $_SESSION['perfil'] ?? '';
$isAdmin = in_array($perfil, ['Super', 'Administrador']);

// Obtener datos del menú
$menuData     = LookerMenu_Controller::ctrGetMenuData();
$executives   = LookerMenu_Controller::ctrGetExecutives();
$allClients   = $isAdmin ? LookerMenu_Controller::ctrGetAllClients() : [];
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Menú Looker Studio
            <small>Acceso rápido a reportes</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Menú Looker Studio</li>
        </ol>
    </section>

    <section class="content">

        <?php if ($isAdmin): ?>
        <!-- Panel de Administración de Asignaciones -->
        <div class="box box-primary">
            <div class="box-header with-border" style="cursor:pointer;" data-toggle="collapse" data-target="#assignPanel">
                <h3 class="box-title"><i class="fa fa-cog"></i> Administrar Asignaciones</h3>
                <span class="pull-right"><i class="fa fa-chevron-down"></i></span>
            </div>
            <div id="assignPanel" class="collapse">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ejecutivo</label>
                                <select class="form-control select2" id="selectExecutive" style="width:100%;">
                                    <option value="">-- Selecciona un ejecutivo --</option>
                                    <?php foreach ($executives as $exec): ?>
                                        <option value="<?php echo $exec['id']; ?>"><?php echo htmlspecialchars($exec['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Clientes asignados</label>
                                <select class="form-control select2" id="selectClients" multiple="multiple" style="width:100%;" data-placeholder="Selecciona clientes...">
                                    <?php foreach ($allClients as $client): ?>
                                        <option value="<?php echo $client['id']; ?>"
                                            data-has-url="<?php echo !empty($client['looker_url']) ? '1' : '0'; ?>">
                                            <?php echo htmlspecialchars($client['name']); ?>
                                            <?php if (empty($client['looker_url'])): ?> (sin URL Looker)<?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Solo los clientes con URL de Looker configurada aparecerán en el menú público.</small>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-success" id="btnSaveAssignments" disabled>
                        <i class="fa fa-save"></i> Guardar Asignaciones
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Menú visual -->
        <div class="box box-default" style="border-radius:12px; overflow:hidden;">
            <div class="box-header with-border" style="background:linear-gradient(135deg,#6f42c1 0%,#a855f7 100%); color:#fff; padding:18px 24px;">
                <h3 class="box-title" style="font-size:22px; font-weight:700; color:#fff;">
                    <i class="fa fa-bar-chart"></i> Menú Looker Studio
                </h3>
            </div>
            <div class="box-body" style="background:#f8f5ff; padding:24px;">

                <?php if (empty($menuData)): ?>
                    <p class="text-muted text-center" style="padding:40px 0;">
                        <i class="fa fa-info-circle fa-2x"></i><br>
                        No hay ejecutivos con clientes asignados aún.
                        <?php if ($isAdmin): ?>
                            <br><a href="#assignPanel" onclick="$('#assignPanel').collapse('show')">Administrar asignaciones</a>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <!-- Botones de ejecutivos -->
                    <div id="executiveButtonsRow" style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
                        <?php foreach ($menuData as $exec): ?>
                            <button class="btn-executive"
                                    data-user-id="<?php echo $exec['user_id']; ?>"
                                    data-clients='<?php echo htmlspecialchars(json_encode($exec['clients']), ENT_QUOTES); ?>'>
                                <?php echo htmlspecialchars($exec['user_name']); ?>
                                <i class="fa fa-chevron-down" style="margin-left:6px; font-size:11px;"></i>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Panel de clientes (se muestra al hacer click en un ejecutivo) -->
                    <div id="clientsPanel" style="display:none;">
                        <div id="executiveLabel" style="font-size:14px; color:#888; margin-bottom:12px;"></div>
                        <div id="clientButtonsRow" style="display:flex; flex-wrap:wrap; gap:10px;"></div>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </section>
</div>

<!-- Modal link copiado -->
<div class="modal fade" id="lookerCopiedModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document" style="margin-top:20vh;">
        <div class="modal-content" style="border-radius:12px; overflow:hidden;">
            <div class="modal-header" style="background:#6f42c1; color:#fff; border:none; text-align:center; display:block;">
                <i class="fa fa-check-circle fa-3x" style="margin-bottom:8px;"></i>
                <h4 class="modal-title" style="font-weight:700;">¡Link copiado!</h4>
            </div>
            <div class="modal-body text-center" style="padding:20px;">
                <p id="lookerCopiedClientName" style="font-size:16px; font-weight:600; margin-bottom:4px;"></p>
                <p class="text-muted" style="font-size:12px; word-break:break-all;" id="lookerCopiedUrl"></p>
            </div>
            <div class="modal-footer" style="text-align:center; border:none;">
                <a href="#" id="btnGoToLooker" target="_blank" class="btn btn-success">
                    <i class="fa fa-external-link"></i> Ir al Looker
                </a>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
.btn-executive {
    background: #fff;
    border: 2px solid #6f42c1;
    color: #6f42c1;
    border-radius: 24px;
    padding: 8px 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    outline: none;
}
.btn-executive:hover,
.btn-executive.active {
    background: #6f42c1;
    color: #fff;
}
.btn-client-looker {
    background: transparent;
    border: 2px solid #a855f7;
    color: #6f42c1;
    border-radius: 20px;
    padding: 6px 18px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    outline: none;
}
.btn-client-looker:hover {
    background: #a855f7;
    color: #fff;
    border-color: #a855f7;
}
#clientsPanel {
    border-top: 2px dashed #d8b4fe;
    padding-top: 18px;
    animation: fadeIn 0.25s ease;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
