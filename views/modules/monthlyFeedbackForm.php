<?php
/* =====================================================================
   Feedback Mensual - Formulario publico (sin sesion)
   Accedido por el cliente a traves del link unico (token).
   ===================================================================== */

$token    = trim($_GET['token'] ?? '');
$feedback = null;
$error    = null;

if (empty($token)) {
    $error = 'Link invalido o incompleto.';
} else {
    $feedback = MonthlyFeedback_Controller::ctrGetFeedbackByToken($token);
    if (!$feedback) {
        $error = 'El link no es valido o no existe.';
    }
}

$clientName = $feedback ? htmlspecialchars($feedback['client_name'] ?? 'Cliente') : '';

// Load configured projects (if any)
$feedbackProjects = [];
if ($feedback && !empty($feedback['project_ids'])) {
    $decoded = json_decode($feedback['project_ids'], true);
    if (is_array($decoded)) $feedbackProjects = $decoded;
}

// Fetch periods from API
$allPeriods = [];
$currentPeriodName = '';
if ($feedback) {
    $allPeriods = Periods_controller::ctrShowPeriods();
    if (isset($allPeriods['error'])) $allPeriods = [];
    // Current month/year to pre-select
    $curMonth = (int) date('n');
    $curYear  = (int) date('Y');
    foreach ($allPeriods as $p) {
        if ((int)$p['month_number'] === $curMonth && (int)$p['year'] === $curYear) {
            $currentPeriodName = $p['name'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reporte Mensual de Leads</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --purple: #3B20E8;
    --magenta: #FF00C8;
    --white: #FFFFFF;
    --light: #F2F0FF;
    --dark: #0A0520;
    --gray: #8B82B0;
    --success: #00E5A0;
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--purple);
    min-height: 100vh;
    color: var(--dark);
  }

  .noise {
    position: fixed; inset: 0; pointer-events: none; z-index: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
    opacity: 0.5;
  }

  .header {
    background: var(--dark);
    padding: 24px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative; z-index: 1;
  }

  .logo {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 28px;
    color: var(--magenta);
    letter-spacing: 3px;
  }

  .header-badge {
    background: var(--magenta);
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 20px;
    letter-spacing: 1px;
    text-transform: uppercase;
  }

  .container {
    max-width: 760px;
    margin: 0 auto;
    padding: 40px 24px 80px;
    position: relative; z-index: 1;
  }

  .hero {
    text-align: center;
    margin-bottom: 48px;
  }



  .hero h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(48px, 8vw, 72px);
    color: white;
    line-height: 0.95;
    letter-spacing: 2px;
    margin-bottom: 16px;
  }

  .hero h1 span { color: var(--magenta); }

  .hero p {
    color: rgba(255,255,255,0.65);
    font-size: 15px;
    font-weight: 300;
    max-width: 480px;
    margin: 0 auto;
    line-height: 1.6;
  }

  .progress-bar-wrap {
    background: rgba(255,255,255,0.12);
    border-radius: 10px;
    height: 6px;
    margin-bottom: 40px;
    overflow: hidden;
  }

  .progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--magenta), #FF6BEA);
    border-radius: 10px;
    transition: width 0.5s cubic-bezier(.4,0,.2,1);
    width: 0%;
  }

  .progress-label {
    display: flex;
    justify-content: space-between;
    color: rgba(255,255,255,0.45);
    font-size: 12px;
    margin-bottom: 8px;
  }

  .step { display: none; animation: fadeUp 0.4s ease; }
  .step.active { display: block; }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .card {
    background: white;
    border-radius: 20px;
    padding: 32px;
    margin-bottom: 16px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.15);
  }

  .card-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 22px;
    letter-spacing: 1px;
    color: var(--purple);
    margin-bottom: 6px;
  }

  .card-subtitle {
    font-size: 13px;
    color: var(--gray);
    font-weight: 400;
    margin-bottom: 24px;
  }

  .section-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--magenta);
    margin-bottom: 6px;
    display: block;
  }

  .field-group { margin-bottom: 20px; }

  label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #444;
    margin-bottom: 8px;
  }

  input[type="text"], input[type="number"], input[type="email"], textarea, select {
    width: 100%;
    border: 2px solid #E8E4F5;
    border-radius: 12px;
    padding: 12px 16px;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    color: var(--dark);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: #FAFAFA;
  }

  input:focus, textarea:focus, select:focus {
    border-color: var(--purple);
    box-shadow: 0 0 0 4px rgba(59,32,232,0.08);
    background: white;
  }

  textarea { resize: vertical; min-height: 90px; }

  .two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }

  @media (max-width: 520px) { .two-col { grid-template-columns: 1fr; } }

  /* ── Responsive: mobile fixes ─────────────────────────────── */
  @media (max-width: 600px) {
    .header { padding: 16px 16px; }
    .logo   { font-size: 22px; letter-spacing: 2px; }
    .header-badge { font-size: 10px; padding: 5px 10px; }

    .container { padding: 24px 10px 60px; }
    .card { padding: 18px 14px; border-radius: 14px; }

    .card-title { font-size: 18px; }
    .card-subtitle { font-size: 12px; margin-bottom: 16px; }
    .section-label { font-size: 10px; }

    /* Wrap source table in scrollable area */
    .source-table-wrap {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      margin: 0 -14px;
      padding: 0 14px;
    }

    .source-table { min-width: 420px; }

    .source-table th { padding: 8px 8px; font-size: 10px; }
    .source-table td { padding: 8px 8px; font-size: 13px; }
    .source-table input[type="number"] {
      max-width: 64px !important;
      padding: 6px 6px;
      font-size: 13px;
    }

    .project-block { padding: 16px 12px; border-radius: 12px; }
    .project-block-header { flex-wrap: wrap; }
    .project-block-header select { font-size: 13px; padding: 8px 10px; }

    .add-row-btn { font-size: 12px; padding: 8px 12px; }

    input[type="text"], input[type="number"], input[type="email"], textarea, select {
      font-size: 14px;
      padding: 10px 12px;
    }

    .nav-buttons { gap: 8px; flex-wrap: wrap; }
    .nav-buttons .btn-primary,
    .nav-buttons button { font-size: 14px; padding: 12px 20px; }

    /* Rating chips */
    .rating-scale { gap: 5px; }
    .rating-chip { width: 34px; height: 34px; font-size: 13px; }
  }

  .source-table { width: 100%; border-collapse: collapse; }

  .source-table th {
    background: var(--purple);
    color: white;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 10px 14px;
    text-align: left;
  }

  .source-table th:first-child { border-radius: 10px 0 0 0; }
  .source-table th:last-child { border-radius: 0 10px 0 0; }

  .source-table td {
    padding: 10px 14px;
    border-bottom: 1px solid #EEE;
    font-size: 14px;
    vertical-align: middle;
  }

  .source-table tr:last-child td { border-bottom: none; }
  .source-table tr:nth-child(even) td { background: var(--light); }

  .source-table input[type="number"] {
    padding: 8px 12px;
    font-size: 14px;
    border-radius: 8px;
    max-width: 100px;
  }

  .source-table th:last-child,
  .source-table td:last-child { width: 32px; text-align: center; }

  .source-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
  }

  .dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    display: inline-block;
  }

  .interest-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 0;
    border-bottom: 1px solid #EEE;
  }

  .interest-row:last-child { border-bottom: none; }

  .interest-badge {
    width: 80px;
    text-align: center;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
  }

  .badge-alto { background: #FFE8F0; color: #D4006A; }
  .badge-medio { background: #FFF3E0; color: #E07800; }
  .badge-bajo { background: #F0F0FF; color: #4B3F9C; }

  .interest-label { flex: 1; font-size: 13px; color: #555; line-height: 1.4; }
  .interest-input { width: 80px !important; text-align: center; }

  .district-row {
    display: grid;
    grid-template-columns: 1fr 1.5fr 80px 70px 40px;
    gap: 8px;
    margin-bottom: 8px;
    align-items: center;
  }

  .district-row input { margin: 0; }

  .district-header {
    display: grid;
    grid-template-columns: 1fr 1.5fr 80px 70px 40px;
    gap: 8px;
    margin-bottom: 6px;
  }

  .district-header span {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--gray);
  }

  .add-row-btn {
    background: #fff;
    border: 2px dashed var(--magenta);
    color: var(--magenta);
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 16px;
    border-radius: 10px;
    cursor: pointer;
    width: 100%;
    margin-top: 8px;
    transition: all 0.2s;
  }

  .add-row-btn:hover { background: #f3eaff; }

  /* ── Project block (per-project mode) ── */
  .project-block {
    background: #FFF;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    position: relative;
  }
  .project-block-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
  }
  .project-block-header select {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #E0E0E0;
    border-radius: 10px;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    background: #FAFAFA;
    color: #333;
    appearance: auto;
  }
  .project-block-header select:focus { border-color: var(--magenta); outline: none; }
  .project-block .remove-project-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    background: none;
    border: none;
    cursor: pointer;
    color: #CCC;
    font-size: 20px;
    line-height: 1;
    transition: color 0.2s;
  }
  .project-block .remove-project-btn:hover { color: #e74c3c; }
  .project-block .section-label {
    background: var(--magenta);
    color: white;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 20px;
    display: inline-block;
    margin-bottom: 12px;
  }

  .remove-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #CCC;
    font-size: 18px;
    line-height: 1;
    padding: 2px;
    transition: color 0.2s;
  }
  .remove-btn:hover { color: var(--magenta); }

  .rating-scale { display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; justify-content: center; }

  .rating-chip {
    width: 40px; height: 40px;
    border-radius: 50%;
    border: 2px solid #E0E0E0;
    background: #FAFAFA;
    display: flex; align-items: center; justify-content: center;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px; font-weight: 700;
    color: #999;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
    -webkit-user-select: none;
  }
  .rating-chip:hover {
    border-color: var(--magenta);
    color: var(--magenta);
    transform: scale(1.12);
    box-shadow: 0 2px 8px rgba(255,0,200,0.15);
  }
  .rating-chip.active {
    background: var(--magenta);
    border-color: var(--magenta);
    color: #FFF;
    transform: scale(1.12);
    box-shadow: 0 2px 12px rgba(255,0,200,0.3);
  }

  .rating-labels {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: var(--gray);
    margin-top: 6px;
    padding: 0 4px;
  }

  .rating-value {
    text-align: center;
    margin-top: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--magenta);
    min-height: 20px;
    transition: opacity 0.2s;
  }

  .btn-primary {
    background: var(--magenta);
    color: white;
    border: none;
    border-radius: 14px;
    padding: 16px 36px;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 20px;
    letter-spacing: 2px;
    cursor: pointer;
    width: 100%;
    transition: all 0.2s;
    box-shadow: 0 6px 24px rgba(255,0,200,0.35);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 32px rgba(255,0,200,0.45);
  }

  .btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
  }

  .btn-secondary {
    background: transparent;
    color: rgba(255,255,255,0.65);
    border: none;
    padding: 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    cursor: pointer;
    width: 100%;
    text-align: center;
    transition: color 0.2s;
  }

  .btn-secondary:hover { color: white; }

  .nav-buttons { margin-top: 24px; }

  .success-screen {
    text-align: center;
    padding: 60px 0;
  }

  .success-icon {
    width: 80px; height: 80px;
    background: var(--success);
    border-radius: 50%;
    margin: 0 auto 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
  }

  .success-screen h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 42px;
    color: white;
    letter-spacing: 2px;
    margin-bottom: 12px;
  }

  .success-screen p {
    color: rgba(255,255,255,0.65);
    font-size: 15px;
    max-width: 400px;
    margin: 0 auto 32px;
    line-height: 1.6;
  }

  .summary-card {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 16px;
    padding: 24px;
    text-align: left;
    margin-bottom: 24px;
  }

  .summary-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    font-size: 14px;
  }

  .summary-item:last-child { border-bottom: none; }
  .summary-item .key { color: rgba(255,255,255,0.55); }
  .summary-item .val { color: white; font-weight: 600; }

  .required-star { color: var(--magenta); }

  .helper {
    font-size: 12px;
    color: var(--gray);
    margin-top: 4px;
    font-style: italic;
  }

  .month-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
  }

  .month-btn {
    border: 2px solid #E8E4F5;
    background: #FAFAFA;
    border-radius: 10px;
    padding: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #444;
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
  }

  .month-btn:hover { border-color: var(--purple); color: var(--purple); }
  .month-btn.selected {
    background: var(--purple);
    border-color: var(--purple);
    color: white;
    font-weight: 600;
  }

  .period-btn {
    border: 2px solid #E8E4F5;
    background: #FAFAFA;
    border-radius: 10px;
    padding: 12px 16px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #444;
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
    flex: 1;
  }

  .period-btn:hover { border-color: var(--purple); }
  .period-btn.selected {
    background: var(--purple);
    border-color: var(--purple);
    color: white;
    font-weight: 600;
  }

  .period-row { display: flex; gap: 8px; }

  /* SweetAlert2 overrides (global reset strips its padding) */
  .swal2-popup { padding: 24px 28px !important; border-radius: 16px !important; }
  .swal2-title { padding: 12px 0 0 !important; font-size: 22px !important; }
  .swal2-html-container { padding: 8px 16px 0 !important; font-size: 15px !important; color: var(--gray) !important; }
  .swal2-icon { margin: 20px auto 0 !important; }
  .swal2-actions { padding: 16px 0 4px !important; }
  .swal2-confirm { border-radius: 10px !important; font-size: 15px !important; padding: 10px 28px !important; }
  .swal2-cancel { border-radius: 10px !important; font-size: 15px !important; padding: 10px 28px !important; }
  .swal2-input { margin: 12px auto !important; border-radius: 10px !important; border: 2px solid #E8E4F5 !important; padding: 10px 14px !important; }
  .swal2-input:focus { border-color: var(--purple) !important; box-shadow: 0 0 0 3px rgba(59,32,232,0.1) !important; }
  .swal2-validation-message { padding: 8px 16px !important; margin: 0 !important; border-radius: 8px !important; }

  .error-card {
    text-align: center;
    padding: 60px 0;
  }
  .error-card h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 36px;
    color: white;
    letter-spacing: 2px;
    margin-bottom: 12px;
  }
  .error-card p {
    color: rgba(255,255,255,0.65);
    font-size: 15px;
    max-width: 400px;
    margin: 0 auto;
    line-height: 1.6;
  }
</style>
</head>
<body>
<div class="noise"></div>

<header class="header">
  <div class="logo"><img src="views/img/template/algoritmo-logo-largo.png" alt="Algoritmo Digital" style="height:32px;"></div>
  <div class="header-badge"><?php echo htmlspecialchars($clientName); ?></div>
</header>

<div class="container">

<?php if ($error): ?>

  <div class="error-card">
    <div style="font-size:56px;margin-bottom:16px;">&#x1F517;</div>
    <h2>Link no valido</h2>
    <p><?php echo htmlspecialchars($error); ?></p>
  </div>

<?php else: ?>

  <!-- HERO -->
  <div class="hero">
    <span id="heroPeriod"><?php echo $clientName; ?></span>
    <h1>Feedback<br><span>Mensual</span></h1>
    <p>Completar este formulario toma menos de 5 minutos y nos ayuda a mejorar tus resultados.</p>
  </div>

  <!-- PROGRESS -->
  <div class="progress-label">
    <span id="stepLabel">Paso 1 de 4</span>
    <span id="pct">0%</span>
  </div>
  <div class="progress-bar-wrap">
    <div class="progress-bar-fill" id="progressFill"></div>
  </div>

  <input type="hidden" id="feedbackToken" value="<?php echo htmlspecialchars($token); ?>">

  <!-- STEP 1: IDENTIFICATION -->
  <div class="step active" id="step1">
    <div class="card">
      <span class="section-label">Identificacion</span>
      <div class="card-title">¿Quién reporta?</div>
      <div class="card-subtitle">Datos básicos del responsable y periodo a reportar</div>

      <div class="field-group">
        <label>Nombre del responsable <span class="required-star">*</span></label>
        <input type="text" id="contactName" placeholder="Tu nombre completo">
      </div>

      <div class="field-group">
        <label>Periodo a reportar <span class="required-star">*</span></label>
        <select id="periodSelect" onchange="selectPeriodFromDB(this)">
          <option value="">-- Selecciona un periodo --</option>
          <?php foreach ($allPeriods as $p):
            $sel = ($p['name'] === $currentPeriodName) ? ' selected' : '';
          ?>
          <option value="<?php echo htmlspecialchars($p['name']); ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($p['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field-group">
        <label>Periodo <span class="required-star">*</span></label>
        <div class="period-row">
          <button type="button" class="period-btn" id="p1" onclick="selectPeriod('1-15')">1 &ndash; 15</button>
          <button type="button" class="period-btn" id="p2" onclick="selectPeriod('16-fin')">16 &ndash; fin</button>
          <button type="button" class="period-btn" id="p3" onclick="selectPeriod('completo')">Mes completo</button>
        </div>
      </div>
    </div>

    <div class="nav-buttons">
      <button type="button" class="btn-primary" onclick="nextStep(1)">Siguiente &rarr;</button>
    </div>
  </div>

  <!-- STEP 2: LEADS BY SOURCE -->
  <div class="step" id="step2">

  <?php if (empty($feedbackProjects)): ?>
    <!-- ── SIN proyectos: tabla unica (comportamiento actual) ── -->
    <div class="card">
      <span class="section-label">Contactos recibidos</span>
      <div class="card-title">Leads por Plataforma</div>
      <div class="card-subtitle">Todo es opcional. Completa solo las plataformas que apliquen.</div>

      <div class="source-table-wrap">
      <table class="source-table" id="sourcesTable">
        <thead>
          <tr>
            <th>Plataforma</th>
            <th>Recibidos</th>
            <th>Contestaron</th>
            <th>Son perfil</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr data-platform="Meta">
            <td><span class="source-tag"><span class="dot" style="background:#0081FB"></span> Meta</span></td>
            <td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>
            <td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>
            <td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>
            <td></td>
          </tr>
          <tr data-platform="Google">
            <td><span class="source-tag"><span class="dot" style="background:#4285F4"></span> Google</span></td>
            <td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>
            <td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>
            <td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>
            <td></td>
          </tr>

        </tbody>
      </table>
      </div>
      <button type="button" class="add-row-btn" onclick="addSourceRow()">+ Agregar plataforma</button>

      <div class="field-group" style="margin-top:20px">
        <label>Comentarios sobre los leads</label>
        <textarea id="source_comments" placeholder="Comentarios sobre la calidad de los leads recibidos"></textarea>
      </div>
    </div>

  <?php else: ?>
    <!-- ── CON proyectos: bloques por proyecto ── -->
    <div id="projectBlocksContainer">
      <!-- El primer bloque se genera automaticamente por JS -->
    </div>
    <button type="button" class="add-row-btn" id="addProjectBlockBtn" style="margin-top:12px;" onclick="addProjectBlock()">+ Agregar otro proyecto</button>

  <?php endif; ?>

    <div class="nav-buttons">
      <button type="button" class="btn-primary" onclick="nextStep(2)">Siguiente &rarr;</button>
      <button type="button" class="btn-secondary" onclick="prevStep(2)">&larr; Volver</button>
    </div>
  </div>

  <!-- STEP 3: QUALITY RATING -->
  <div class="step" id="step3">
    <div class="card">
      <span class="section-label">Evaluación general</span>
      <div class="card-title">¿Cómo calificarías la calidad de los leads este periodo?</div>
      <div class="card-subtitle">Tu opinión es la más importante para mejorar las campañas.</div>

      <div class="field-group">
        <label>Calidad general de leads</label>
        <div class="rating-scale" id="ratingScale">
          <div class="rating-chip" onclick="rateQuality(1)">1</div>
          <div class="rating-chip" onclick="rateQuality(2)">2</div>
          <div class="rating-chip" onclick="rateQuality(3)">3</div>
          <div class="rating-chip" onclick="rateQuality(4)">4</div>
          <div class="rating-chip" onclick="rateQuality(5)">5</div>
          <div class="rating-chip" onclick="rateQuality(6)">6</div>
          <div class="rating-chip" onclick="rateQuality(7)">7</div>
          <div class="rating-chip" onclick="rateQuality(8)">8</div>
          <div class="rating-chip" onclick="rateQuality(9)">9</div>
          <div class="rating-chip" onclick="rateQuality(10)">10</div>
        </div>
        <div class="rating-labels">
          <span>Muy mala</span>
          <span>Excelente</span>
        </div>
        <div class="rating-value" id="ratingValue"></div>
      </div>

      <div class="field-group">
        <label>Comentarios adicionales</label>
        <textarea id="free_comment" placeholder="Algo más que quieras compartir, para que podamos plantear mejores optimizaciones…"></textarea>
      </div>

      <div class="field-group">
        <label>Adjuntar archivo (opcional)</label>
        <small style="display:block;color:#999;margin-bottom:6px;">PDF, Excel o Word. Máximo 10 MB.</small>
        <input type="file" id="attachmentFile" accept=".pdf,.xls,.xlsx,.doc,.docx" style="font-size:14px;">
      </div>
    </div>

    <div class="nav-buttons">
      <button type="button" class="btn-primary" id="submitBtn" onclick="submitForm()">Enviar reporte &#10003;</button>
      <button type="button" class="btn-secondary" onclick="prevStep(3)">&larr; Volver</button>
    </div>
  </div>

  <!-- SUCCESS -->
  <div class="step" id="stepSuccess">
    <div class="success-screen">
      <div class="success-icon">&#10003;</div>
      <h2>Reporte enviado!</h2>
      <p>Gracias por completar tu reporte. Revisaremos los datos y te contactaremos si tenemos preguntas.</p>

      <div class="summary-card" id="summaryContent"></div>

      <button type="button" class="btn-primary" onclick="window.print()" style="max-width:300px;margin:0 auto;display:block">Imprimir resumen</button>
    </div>
  </div>

<?php endif; ?>

</div>

<script src="views/bower_components/jquery/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  var currentStep = 1;
  var totalSteps = 3;
  var selectedMonth = document.getElementById('periodSelect') ? document.getElementById('periodSelect').value : '';
  var selectedPeriod = '';
  var ratings = { quality: 0 };
  var hasProjects = <?php echo !empty($feedbackProjects) ? 'true' : 'false'; ?>;
  var availableProjects = <?php echo json_encode($feedbackProjects ?: []); ?>;

  function updateProgress() {
    var pct = Math.round(((currentStep - 1) / totalSteps) * 100);
    document.getElementById('progressFill').style.width = pct + '%';
    document.getElementById('pct').textContent = pct + '%';
    document.getElementById('stepLabel').textContent = 'Paso ' + currentStep + ' de ' + totalSteps;
  }

  function nextStep(from) {
    if (from === 1) {
      if (!document.getElementById('contactName').value.trim()) {
        Swal.fire({icon:'warning',title:'Campo requerido',text:'Por favor ingresa tu nombre.',confirmButtonColor:'#FF00C8'}); return;
      }
      if (!selectedMonth) { Swal.fire({icon:'warning',title:'Campo requerido',text:'Selecciona el periodo a reportar.',confirmButtonColor:'#FF00C8'}); return; }
      if (!selectedPeriod) { Swal.fire({icon:'warning',title:'Campo requerido',text:'Selecciona el periodo.',confirmButtonColor:'#FF00C8'}); return; }
    }
    document.getElementById('step' + from).classList.remove('active');
    currentStep = from + 1;
    document.getElementById('step' + currentStep).classList.add('active');
    updateProgress();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function prevStep(from) {
    document.getElementById('step' + from).classList.remove('active');
    currentStep = from - 1;
    document.getElementById('step' + currentStep).classList.add('active');
    updateProgress();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function selectPeriodFromDB(sel) {
    selectedMonth = sel.value;
    updateHero();
  }

  function selectPeriod(p) {
    ['p1','p2','p3'].forEach(function(id) { document.getElementById(id).classList.remove('selected'); });
    selectedPeriod = p;
    if (p === '1-15') document.getElementById('p1').classList.add('selected');
    else if (p === '16-fin') document.getElementById('p2').classList.add('selected');
    else document.getElementById('p3').classList.add('selected');
    updateHero();
  }

  function updateHero() {
    var txt = '';
    if (selectedMonth && selectedPeriod) {
      var map = {'1-15':'1 al 15', '16-fin':'16 al fin', 'completo':'mes completo'};
      txt = selectedMonth + ' - ' + (map[selectedPeriod] || selectedPeriod);
    } else if (selectedMonth) {
      txt = selectedMonth;
    } else {
      txt = document.getElementById('heroPeriod').dataset.client || '';
    }
    document.getElementById('heroPeriod').textContent = txt;
  }

  // Initialize hero with pre-selected period
  if (selectedMonth) { updateHero(); }

  var ratingLabels = {
    1:'Muy mala', 2:'Mala', 3:'Baja', 4:'Regular', 5:'Aceptable',
    6:'Buena', 7:'Bastante buena', 8:'Muy buena', 9:'Excelente', 10:'Excepcional'
  };

  function rateQuality(val) {
    ratings.quality = val;
    document.querySelectorAll('#ratingScale .rating-chip').forEach(function(chip, i) {
      chip.classList.toggle('active', (i + 1) === val);
    });
    document.getElementById('ratingValue').textContent = val + '/10 — ' + ratingLabels[val];
  }

  function addSourceRow() {
    Swal.fire({
      title: 'Nueva plataforma',
      input: 'text',
      inputPlaceholder: 'Ej: Spotify, TikTok Ads...',
      showCancelButton: true,
      confirmButtonText: 'Agregar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#FF00C8',
      inputValidator: function(val) { if (!val || !val.trim()) return 'Ingresa un nombre'; }
    }).then(function(result) {
      if (!result.isConfirmed) return;
      var name = result.value.trim();
      var tbody = document.querySelector('#sourcesTable tbody');
      var tr = document.createElement('tr');
      tr.setAttribute('data-platform', name);
      tr.innerHTML =
        '<td><span class="source-tag"><span class="dot" style="background:#888"></span> ' + name + '</span></td>' +
        '<td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>' +
        '<td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>' +
        '<td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>' +
        '<td><button type="button" class="remove-btn" onclick="this.closest(\'tr\').remove()">&times;</button></td>';
      tbody.appendChild(tr);
    });
  }

  function collectSources() {
    var sources = [];
    document.querySelectorAll('#sourcesTable tbody tr').forEach(function(tr) {
      var platform = tr.getAttribute('data-platform');
      var inputs = tr.querySelectorAll('input[type=number]');
      var recv = inputs[0].value || '0';
      var replied = inputs[1].value || '0';
      var profile = inputs[2].value || '0';
      if (+recv || +replied || +profile) {
        sources.push({ platform: platform, received: +recv, replied: +replied, profile: +profile });
      }
    });
    return sources;
  }

  /* ── Per-project block functions ── */
  var projectBlockCounter = 0;

  function buildSourceTableHtml(blockId) {
    return '<div class="source-table-wrap">' +
      '<table class="source-table" id="sourcesTable_' + blockId + '">' +
      '<thead><tr><th>Plataforma</th><th>Recibidos</th><th>Contestaron</th><th>Son perfil</th><th></th></tr></thead>' +
      '<tbody>' +
      '<tr data-platform="Meta"><td><span class="source-tag"><span class="dot" style="background:#0081FB"></span> Meta</span></td>' +
      '<td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>' +
      '<td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>' +
      '<td><input type="number" min="0" placeholder="0" style="max-width:80px"></td><td></td></tr>' +
      '<tr data-platform="Google"><td><span class="source-tag"><span class="dot" style="background:#4285F4"></span> Google</span></td>' +
      '<td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>' +
      '<td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>' +
      '<td><input type="number" min="0" placeholder="0" style="max-width:80px"></td><td></td></tr>' +

      '</tbody></table></div>' +
      '<button type="button" class="add-row-btn" onclick="addSourceRowToBlock(\'' + blockId + '\')">+ Agregar plataforma</button>' +
      '<div class="field-group" style="margin-top:16px"><label>Comentarios sobre los leads</label>' +
      '<textarea class="project-comments" placeholder="Comentarios sobre la calidad de los leads de este proyecto"></textarea></div>' +
      '<div class="field-group" style="margin-top:12px"><label>Adjuntar archivo del proyecto (opcional)</label>' +
      '<small style="display:block;color:#999;margin-bottom:6px;">PDF, Excel o Word. M\u00e1ximo 10 MB.</small>' +
      '<input type="file" class="project-attachment" accept=".pdf,.xls,.xlsx,.doc,.docx" style="font-size:14px;"></div>';
  }

  function buildProjectSelectHtml(blockId) {
    var opts = '<option value="">Selecciona un proyecto...</option>';
    availableProjects.forEach(function(p) {
      opts += '<option value="' + p.id + '" data-name="' + p.name.replace(/"/g, '&quot;') + '">' + p.name + '</option>';
    });
    return opts;
  }

  function addProjectBlock() {
    projectBlockCounter++;
    var blockId = 'projBlock_' + projectBlockCounter;
    var container = document.getElementById('projectBlocksContainer');
    var canRemove = container.children.length > 0;
    var div = document.createElement('div');
    div.className = 'project-block';
    div.id = blockId;
    div.innerHTML =
      (canRemove ? '<button type="button" class="remove-project-btn" onclick="removeProjectBlock(\'' + blockId + '\')">&times;</button>' : '') +
      '<span class="section-label">Proyecto ' + (container.children.length + 1) + '</span>' +
      '<div class="project-block-header">' +
      '<select class="project-select" onchange="onProjectSelect(this)">' + buildProjectSelectHtml(blockId) + '</select>' +
      '</div>' +
      '<div class="card-subtitle" style="margin-bottom:12px;">Todo es opcional. Completa solo las plataformas que apliquen.</div>' +
      buildSourceTableHtml(blockId);
    container.appendChild(div);
    updateProjectNumbers();
  }

  function removeProjectBlock(blockId) {
    var el = document.getElementById(blockId);
    if (el) el.remove();
    updateProjectNumbers();
  }

  function updateProjectNumbers() {
    var blocks = document.querySelectorAll('#projectBlocksContainer .project-block');
    blocks.forEach(function(block, i) {
      var label = block.querySelector('.section-label');
      if (label) label.textContent = 'Proyecto ' + (i + 1);
      /* Ensure first block cannot be removed */
      var removeBtn = block.querySelector('.remove-project-btn');
      if (i === 0 && removeBtn) removeBtn.remove();
    });
  }

  function onProjectSelect(sel) {
    /* Warn if this project is already chosen in another block */
    var val = sel.value;
    if (!val) return;
    var count = 0;
    document.querySelectorAll('#projectBlocksContainer .project-select').forEach(function(s) {
      if (s.value === val) count++;
    });
    if (count > 1) {
      Swal.fire({icon:'warning',title:'Proyecto duplicado',text:'Este proyecto ya fue seleccionado en otro bloque.',confirmButtonColor:'#FF00C8'});
      sel.value = '';
    }
  }

  function addSourceRowToBlock(blockId) {
    Swal.fire({
      title: 'Nueva plataforma',
      input: 'text',
      inputPlaceholder: 'Ej: Spotify, TikTok Ads...',
      showCancelButton: true,
      confirmButtonText: 'Agregar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#FF00C8',
      inputValidator: function(val) { if (!val || !val.trim()) return 'Ingresa un nombre'; }
    }).then(function(result) {
      if (!result.isConfirmed) return;
      var name = result.value.trim();
      var tbody = document.querySelector('#sourcesTable_' + blockId + ' tbody');
      if (!tbody) return;
      var tr = document.createElement('tr');
      tr.setAttribute('data-platform', name);
      tr.innerHTML =
        '<td><span class="source-tag"><span class="dot" style="background:#888"></span> ' + name + '</span></td>' +
        '<td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>' +
        '<td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>' +
        '<td><input type="number" min="0" placeholder="0" style="max-width:80px"></td>' +
        '<td><button type="button" class="remove-btn" onclick="this.closest(\'tr\').remove()">&times;</button></td>';
      tbody.appendChild(tr);
    });
  }

  function collectProjectSources() {
    var result = [];
    document.querySelectorAll('#projectBlocksContainer .project-block').forEach(function(block) {
      var sel = block.querySelector('.project-select');
      if (!sel || !sel.value) return;
      var projId = sel.value;
      var projName = sel.options[sel.selectedIndex].getAttribute('data-name') || sel.options[sel.selectedIndex].text;
      var sources = [];
      block.querySelectorAll('.source-table tbody tr').forEach(function(tr) {
        var platform = tr.getAttribute('data-platform');
        var inputs = tr.querySelectorAll('input[type=number]');
        var recv = inputs[0].value || '0';
        var replied = inputs[1].value || '0';
        var profile = inputs[2].value || '0';
        if (+recv || +replied || +profile) {
          sources.push({ platform: platform, received: +recv, replied: +replied, profile: +profile });
        }
      });
      var comments = (block.querySelector('.project-comments') || {}).value || '';
      result.push({ project_id: projId, project_name: projName, sources: sources, comments: comments.trim() });
    });
    return result;
  }

  /* Auto-add first project block on load if in project mode */
  if (hasProjects) {
    addProjectBlock();
  }

  function submitForm() {
    var token = document.getElementById('feedbackToken').value;
    var projectName = '<?php echo addslashes($clientName); ?>';
    var contactName = document.getElementById('contactName').value.trim();

    if (!contactName || !selectedMonth || !selectedPeriod) {
      Swal.fire({icon:'warning',title:'Campos incompletos',text:'Completa todos los campos del paso 1.',confirmButtonColor:'#FF00C8'}); return;
    }

    /* Validate per-project mode */
    var sourcesData, sourceComments, totalRecv = 0;
    if (hasProjects) {
      var projData = collectProjectSources();
      if (projData.length === 0) {
        Swal.fire({icon:'warning',title:'Selecciona un proyecto',text:'Debes seleccionar al menos un proyecto en el paso 2.',confirmButtonColor:'#FF00C8'}); return;
      }
      sourcesData = JSON.stringify(projData);
      sourceComments = '';
      projData.forEach(function(p) { p.sources.forEach(function(s) { totalRecv += s.received; }); });
    } else {
      var sources = collectSources();
      sourcesData = JSON.stringify(sources);
      sourceComments = document.getElementById('source_comments').value.trim();
      sources.forEach(function(s) { totalRecv += s.received; });
    }

    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Enviando...';

    var fd = new FormData();
    fd.append('action', 'submitResponse');
    fd.append('feedbackToken', token);
    fd.append('projectName', projectName);
    fd.append('contactName', contactName);
    fd.append('reportMonth', selectedMonth);
    fd.append('reportPeriod', selectedPeriod);
    fd.append('sources_json', sourcesData);
    fd.append('source_comments', sourceComments);
    fd.append('quality_rating', ratings.quality);
    fd.append('free_comment', document.getElementById('free_comment').value.trim());
    var fileInput = document.getElementById('attachmentFile');
    if (fileInput && fileInput.files.length) fd.append('attachment', fileInput.files[0]);

    /* Per-project attachments */
    if (hasProjects) {
      var projIdx = 0;
      document.querySelectorAll('#projectBlocksContainer .project-block').forEach(function(block) {
        var sel = block.querySelector('.project-select');
        if (!sel || !sel.value) return;
        var fileIn = block.querySelector('.project-attachment');
        if (fileIn && fileIn.files.length) {
          fd.append('proj_attachment_' + projIdx, fileIn.files[0]);
        }
        projIdx++;
      });
    }

    $.ajax({
      url:      'ajax/monthlyFeedback.ajax.php',
      type:     'POST',
      data:     fd,
      dataType: 'json',
      processData: false,
      contentType: false,
      success:  function(res) {
        if (res.success) {
          var summary =
            '<div class="summary-item"><span class="key">Responsable</span><span class="val">' + contactName + '</span></div>' +
            '<div class="summary-item"><span class="key">Periodo</span><span class="val">' + selectedMonth + ' - ' + selectedPeriod + '</span></div>' +
            '<div class="summary-item"><span class="key">Leads recibidos</span><span class="val">' + totalRecv + '</span></div>' +
            '<div class="summary-item"><span class="key">Calidad</span><span class="val">' + (ratings.quality ? ratings.quality + '/10' : '-') + '</span></div>';

          document.getElementById('summaryContent').innerHTML = summary;
          document.getElementById('step3').classList.remove('active');
          document.getElementById('stepSuccess').classList.add('active');
          document.getElementById('progressFill').style.width = '100%';
          document.getElementById('pct').textContent = '100%';
          document.getElementById('stepLabel').textContent = 'Completado!';
          window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
          Swal.fire({icon:'error',title:'Error',text:res.message || 'No se pudo enviar el formulario.',confirmButtonColor:'#FF00C8'});
          btn.disabled = false;
          btn.textContent = 'Enviar reporte';
        }
      },
      error: function() {
        Swal.fire({icon:'error',title:'Error de red',text:'Intenta nuevamente.',confirmButtonColor:'#FF00C8'});
        btn.disabled = false;
        btn.textContent = 'Enviar reporte';
      }
    });
  }

  updateProgress();
</script>
</body>
</html>
<?php exit; ?>
