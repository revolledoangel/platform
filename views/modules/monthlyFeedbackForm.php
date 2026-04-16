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

// Load zones with districts for the district table
$zonesWithDistricts = [];
if ($feedback) {
    $zonesWithDistricts = MonthlyFeedback_Controller::ctrGetZonesWithDistricts();
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
<style>
  :root {
    --purple: #4614FF;
    --magenta: #A400F6;
    --white: #FFFFFF;
    --light: #F0EDFF;
    --dark: #0A0520;
    --gray: #8B82B0;
    --success: #00E5A0;
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  html, body {
    font-family: 'Helvetica Neue', Arial, sans-serif;
    background-color: #00013b;
    min-height: 100vh;
    color: var(--dark);
    position: relative;
  }

  body::before {
    content: '';
    position: fixed;
    inset: 0;
    z-index: 0;
    background-color: #00013b;
    background-image:
      linear-gradient(180deg, rgba(0,1,59,0.55) 0%, rgba(44,2,138,0.4) 100%),
      url('https://algoritmo.digital/wp-content/uploads/2023/10/BANNER-TRAMA-1.webp');
    background-position: center center;
    background-repeat: no-repeat;
    background-size: cover, auto 100vh;
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
    font-family: 'Helvetica Neue', Arial, sans-serif;
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
    font-family: 'Helvetica Neue', Arial, sans-serif;
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
    background: linear-gradient(90deg, var(--magenta), #C266FF);
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
    font-family: 'Helvetica Neue', Arial, sans-serif;
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
    font-family: 'Helvetica Neue', Arial, sans-serif;
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

  /* ── Unified table base ── */
  .fb-table { width: 100%; border-collapse: collapse; font-size: 13px; }
  .fb-table th {
    color: #fff; font-size: 11px; font-weight: 700;
    letter-spacing: 1px; text-transform: uppercase;
    padding: 10px 12px; text-align: left;
  }
  .fb-table th:first-child { border-radius: 10px 0 0 0; }
  .fb-table th:last-child  { border-radius: 0 10px 0 0; }
  .fb-table td {
    padding: 10px 12px; border-bottom: 1px solid #EEE;
    font-size: 13px; vertical-align: middle;
  }
  .fb-table tr:last-child td { border-bottom: none; }
  .fb-table tr:nth-child(even) td { background: var(--light); }
  .fb-table input[type="number"] {
    font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 13px;
    padding: 6px 10px; border: 1px solid #E0E0E0;
    border-radius: 8px; background: #FAFAFA; max-width: 90px;
  }
  .fb-table input:focus, .fb-table select:focus { border-color: var(--magenta); outline: none; }
  .fb-table select {
    font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 13px;
    padding: 6px 8px; border: 1px solid #E0E0E0;
    border-radius: 8px; background: #FAFAFA;
  }
  .fb-table tfoot td { font-style: italic; color: #999; }
  .fb-table tfoot tr:last-child td {
    font-weight: 700; font-style: normal; color: #333;
    background: #F9F9F9; border-top: 2px solid #DDD;
  }

  /* Color variants */
  .fb-table--purple th { background: var(--purple); }
  .fb-table--violet th { background: linear-gradient(135deg, #4B0082, #6A0DAD); }
  .fb-table--magenta th { background: linear-gradient(135deg, #A400F6, #C266FF); }

  /* Source-table specifics */
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

  /* ── District table specifics ── */
  .district-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin-top: 20px;
  }
  .district-table { table-layout: fixed; }
  .district-table th:nth-child(1) { width: 26%; }
  .district-table th:nth-child(3) { width: 80px; }
  .district-table th:nth-child(4) { width: 56px; }
  .district-table th:nth-child(5) { width: 38px; }
  .district-table th:nth-child(3),
  .district-table th:nth-child(4) { text-align: center; }
  .district-table td:nth-child(3),
  .district-table td:nth-child(4) { text-align: center; }
  .district-table td:nth-child(5) { text-align: center; }
  .district-table td { vertical-align: top; }
  .district-table .district-zone-select { width: 100%; min-width: 0; }
  .district-table .district-multi-wrap {
    display: flex; flex-wrap: wrap; gap: 4px; position: relative;
  }
  .district-multi-wrap .dm-tag {
    display: inline-flex; align-items: center; gap: 4px;
    background: var(--light); color: var(--purple); font-size: 12px;
    padding: 3px 8px; border-radius: 12px; font-weight: 600;
  }
  .district-multi-wrap .dm-tag .dm-remove {
    cursor: pointer; font-size: 14px; color: var(--magenta); line-height: 1;
  }
  .district-multi-wrap .dm-add-input {
    border: 1px dashed #CCC; border-radius: 8px; padding: 3px 8px;
    font-size: 12px; width: 100%; min-width: 0; box-sizing: border-box; background: #fff;
  }
  .district-multi-wrap .dm-dropdown {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    z-index: 999;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    max-height: 180px;
    overflow-y: auto;
    width: 220px;
    margin-top: 2px;
  }
  .district-multi-wrap .dm-add-input:focus { border-color: var(--magenta); outline: none; }
  .district-pct { font-weight: 600; color: var(--magenta); }

  /* ── Sales table specifics ── */
  .sales-table-wrap { margin-top: 20px; }
  .sales-table th:nth-child(2),
  .sales-table th:nth-child(3) { text-align: center; }
  .sales-table td { text-align: center; }

  /* ── Lead quality per project (3 stars) ── */
  .lead-quality-wrap {
    margin-top: 20px;
  }
  .lead-quality-wrap .card-subtitle {
    font-weight: 600; color: #333; margin-bottom: 10px;
  }
  .star-rating {
    display: inline-flex; align-items: center; gap: 6px;
  }
  .star-rating .star {
    font-size: 32px; cursor: pointer; color: #DDD;
    transition: color 0.15s ease, transform 0.15s ease;
    line-height: 1; user-select: none;
  }
  .star-rating .star:hover { transform: scale(1.15); }
  .star-rating .star.filled { color: #F5A623; }
  .star-rating-label {
    display: inline-block; margin-left: 10px;
    font-size: 13px; font-weight: 700; letter-spacing: 0.5px;
    vertical-align: middle;
  }

  .add-row-btn {
    background: #fff;
    border: 2px dashed var(--magenta);
    color: var(--magenta);
    font-family: 'Helvetica Neue', Arial, sans-serif;
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
    font-family: 'Helvetica Neue', Arial, sans-serif;
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
    font-family: 'Helvetica Neue', Arial, sans-serif;
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
    font-family: 'Helvetica Neue', Arial, sans-serif;
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
    font-family: 'Helvetica Neue', Arial, sans-serif;
    font-size: 20px;
    letter-spacing: 2px;
    cursor: pointer;
    width: 100%;
    transition: all 0.2s;
    box-shadow: 0 6px 24px rgba(255,0,200,0.35);
    -webkit-tap-highlight-color: transparent;
    -webkit-appearance: none;
  }

  .btn-primary:hover {
    background: #8B00D0;
    transform: translateY(-2px);
    box-shadow: 0 10px 32px rgba(164,0,246,0.45);
  }

  .btn-primary:focus,
  .btn-primary:active,
  .btn-primary:focus-visible,
  .btn-primary:active:focus {
    background: #8B00D0 !important;
    outline: none !important;
    box-shadow: 0 6px 24px rgba(164,0,246,0.35) !important;
    border-color: transparent !important;
    color: white !important;
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
    font-family: 'Helvetica Neue', Arial, sans-serif;
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
    font-family: 'Helvetica Neue', Arial, sans-serif;
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
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 16px;
    padding: 24px;
    text-align: left;
    margin-bottom: 24px;
    color: #222;
  }

  .summary-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
    font-size: 14px;
  }

  .summary-item:last-child { border-bottom: none; }
  .summary-item .key { color: #888; }
  .summary-item .val { color: #222; font-weight: 600; }

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
    font-family: 'Helvetica Neue', Arial, sans-serif;
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
    font-family: 'Helvetica Neue', Arial, sans-serif;
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
    font-family: 'Helvetica Neue', Arial, sans-serif;
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
      <table class="source-table fb-table fb-table--purple" id="sourcesTable">
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

      <button type="button" class="btn-primary" onclick="downloadPDF()" style="max-width:340px;margin:0 auto 24px;display:block">&#11015; Descargar reporte en PDF</button>

      <div class="summary-card" id="summaryContent"></div>
    </div>
  </div>

<?php endif; ?>

</div>

<script src="views/bower_components/jquery/dist/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
        Swal.fire({icon:'warning',title:'Campo requerido',text:'Por favor ingresa tu nombre.',confirmButtonColor:'#A400F6'}); return;
      }
      if (!selectedMonth) { Swal.fire({icon:'warning',title:'Campo requerido',text:'Selecciona el periodo a reportar.',confirmButtonColor:'#A400F6'}); return; }
      if (!selectedPeriod) { Swal.fire({icon:'warning',title:'Campo requerido',text:'Selecciona el periodo.',confirmButtonColor:'#A400F6'}); return; }
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
  }

  function selectPeriod(p) {
    ['p1','p2','p3'].forEach(function(id) { document.getElementById(id).classList.remove('selected'); });
    selectedPeriod = p;
    if (p === '1-15') document.getElementById('p1').classList.add('selected');
    else if (p === '16-fin') document.getElementById('p2').classList.add('selected');
    else document.getElementById('p3').classList.add('selected');
  }



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
      confirmButtonColor: '#A400F6',
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

  /* Zones data for districts */
  var zonesData = <?php echo json_encode($zonesWithDistricts, JSON_UNESCAPED_UNICODE); ?>;

  function buildZoneOptions() {
    var opts = '<option value="">Selecciona zona...</option>';
    zonesData.forEach(function(z) {
      opts += '<option value="' + z.id + '">' + z.name + '</option>';
    });
    return opts;
  }

  function buildSourceTableHtml(blockId) {
    return '<div class="source-table-wrap">' +
      '<table class="source-table fb-table fb-table--purple" id="sourcesTable_' + blockId + '">' +
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

      /* ── Districts table ── */
      '<div class="card-subtitle" style="margin-top:24px;margin-bottom:8px;font-weight:600;color:#333;"><i style="color:var(--magenta);">&#9679;</i> Distritos de los leads</div>' +
      '<div class="district-table-wrap">' +
      '<table class="district-table fb-table fb-table--violet" id="districtTable_' + blockId + '">' +
      '<thead><tr><th>Zona</th><th>Distrito(s)</th><th>Cantidad</th><th>%</th><th></th></tr></thead>' +
      '<tbody></tbody>' +
      '<tfoot>' +
      '<tr class="district-others-row"><td colspan="2" style="text-align:right;padding-right:12px;">OTROS</td><td id="districtOthers_' + blockId + '" style="text-align:center;">0</td><td></td><td></td></tr>' +
      '<tr class="district-total-row"><td colspan="2" style="text-align:right;padding-right:12px;font-weight:700;">TOTAL</td><td id="districtTotal_' + blockId + '" style="text-align:center;font-weight:700;">0</td><td></td><td></td></tr>' +
      '</tfoot>' +
      '</table></div>' +
      '<button type="button" class="add-row-btn" onclick="addDistrictRow(\'' + blockId + '\')" style="margin-top:8px;">+ Agregar zona / distrito</button>' +

      /* ── Sales table (Ventas / Separaciones) ── */
      '<div class="sales-table-wrap">' +
      '<div class="card-subtitle" style="margin-bottom:8px;font-weight:600;color:#333;"><i style="color:var(--magenta);">&#9679;</i> Ventas y Separaciones</div>' +
      '<table class="sales-table fb-table fb-table--magenta" id="salesTable_' + blockId + '">' +
      '<thead><tr><th></th><th>Ventas</th><th>Separaciones</th></tr></thead>' +
      '<tbody><tr><td></td>' +
      '<td><input type="number" min="0" placeholder="0" class="sales-ventas"></td>' +
      '<td><input type="number" min="0" placeholder="0" class="sales-separaciones"></td>' +
      '</tr></tbody>' +
      '</table></div>' +

      /* ── Lead quality per project (3 stars) ── */
      '<div class="lead-quality-wrap">' +
      '<div class="card-subtitle"><i style="color:var(--magenta);">&#9679;</i> Calidad de los leads</div>' +
      '<div class="star-rating" id="starRating_' + blockId + '" data-value="0">' +
      '<span class="star" data-star="1" onclick="rateStars(\'' + blockId + '\', 1)">&#9733;</span>' +
      '<span class="star" data-star="2" onclick="rateStars(\'' + blockId + '\', 2)">&#9733;</span>' +
      '<span class="star" data-star="3" onclick="rateStars(\'' + blockId + '\', 3)">&#9733;</span>' +
      '</div>' +
      '<span class="star-rating-label" id="starLabel_' + blockId + '"></span>' +
      '</div>' +

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
      '<select class="project-select" onchange="onProjectSelect(this, \'' + blockId + '\')">' + buildProjectSelectHtml(blockId) + '</select>' +
      '</div>' +
      '<div class="project-block-body" id="body_' + blockId + '" style="display:none;">' +
      '<div class="card-subtitle" style="margin-bottom:12px;">Todo es opcional. Completa solo las plataformas que apliquen.</div>' +
      buildSourceTableHtml(blockId) +
      '</div>';
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

  function onProjectSelect(sel, blockId) {
    var val = sel.value;
    /* Show/hide body */
    var body = document.getElementById('body_' + blockId);
    if (body) body.style.display = val ? 'block' : 'none';
    if (!val) return;
    /* Warn if this project is already chosen in another block */
    var count = 0;
    document.querySelectorAll('#projectBlocksContainer .project-select').forEach(function(s) {
      if (s.value === val) count++;
    });
    if (count > 1) {
      Swal.fire({icon:'warning',title:'Proyecto duplicado',text:'Este proyecto ya fue seleccionado en otro bloque.',confirmButtonColor:'#A400F6'});
      sel.value = '';
      if (body) body.style.display = 'none';
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
      confirmButtonColor: '#A400F6',
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

  /* ── District row functions ── */
  var districtRowCounter = 0;

  function addDistrictRow(blockId) {
    districtRowCounter++;
    var rowId = 'drow_' + districtRowCounter;
    var tbody = document.querySelector('#districtTable_' + blockId + ' tbody');
    if (!tbody) return;
    var tr = document.createElement('tr');
    tr.id = rowId;
    tr.setAttribute('data-block', blockId);
    tr.innerHTML =
      '<td><select class="district-zone-select" onchange="onZoneChange(this, \'' + rowId + '\', \'' + blockId + '\')">' + buildZoneOptions() + '</select></td>' +
      '<td><div class="district-multi-wrap" id="dmw_' + rowId + '">' +
      '<input type="text" class="dm-add-input" placeholder="Selecciona zona primero" disabled>' +
      '</div></td>' +
      '<td><input type="number" min="0" placeholder="0" class="district-qty" style="width:70px;text-align:center;" onchange="recalcDistrictPct(\'' + blockId + '\')" oninput="recalcDistrictPct(\'' + blockId + '\')" value=""></td>' +
      '<td class="district-pct">0%</td>' +
      '<td><button type="button" class="remove-btn" onclick="removeDistrictRow(\'' + rowId + '\', \'' + blockId + '\')">&times;</button></td>';
    tbody.appendChild(tr);
  }

  function removeDistrictRow(rowId, blockId) {
    var el = document.getElementById(rowId);
    if (el) el.remove();
    recalcDistrictPct(blockId);
  }

  function onZoneChange(sel, rowId, blockId) {
    var zoneId = sel.value;
    var wrap = document.getElementById('dmw_' + rowId);
    if (!wrap) return;
    /* Clear existing tags */
    wrap.querySelectorAll('.dm-tag').forEach(function(t) { t.remove(); });
    var input = wrap.querySelector('.dm-add-input');
    if (!zoneId) {
      input.placeholder = 'Selecciona zona primero';
      input.disabled = true;
      return;
    }
    input.disabled = false;
    input.placeholder = 'Escribir distrito + Enter';
    /* Build dropdown list for this zone */
    var zone = zonesData.find(function(z) { return String(z.id) === String(zoneId); });
    var districts = zone ? zone.districts : [];

    /* Show a dropdown suggestions on focus */
    input.setAttribute('data-zone-id', zoneId);
    input.setAttribute('data-row-id', rowId);

    /* Remove old dropdown if any */
    var oldDrop = wrap.querySelector('.dm-dropdown');
    if (oldDrop) oldDrop.remove();

    if (districts.length) {
      var dd = document.createElement('div');
      dd.className = 'dm-dropdown';
      dd.style.display = 'none';
      districts.forEach(function(d) {
        var opt = document.createElement('div');
        opt.textContent = d.name;
        opt.setAttribute('data-district-name', d.name.toLowerCase());
        opt.style.cssText = 'padding:8px 12px;cursor:pointer;font-size:13px;';
        opt.onmouseenter = function() { this.style.background = '#F0EDFF'; };
        opt.onmouseleave = function() { this.style.background = '#fff'; };
        opt.onclick = function() {
          addDistrictTag(wrap, d.name, rowId);
          dd.style.display = 'none';
          input.value = '';
        };
        dd.appendChild(opt);
      });
      wrap.appendChild(dd);

      function filterDropdown() {
        var query = input.value.trim().toLowerCase();
        var anyVisible = false;
        var items = dd.querySelectorAll('[data-district-name]');
        items.forEach(function(item) {
          var match = !query || item.getAttribute('data-district-name').indexOf(query) !== -1;
          item.style.display = match ? '' : 'none';
          if (match) anyVisible = true;
        });
        dd.style.display = anyVisible ? 'block' : 'none';
      }

      input.onfocus = function() { filterDropdown(); };
      input.oninput = function() { filterDropdown(); };
      input.onblur = function() { setTimeout(function() { dd.style.display = 'none'; }, 200); };
    }

    /* Allow typing custom district + Enter */
    input.onkeydown = function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        var val = this.value.trim();
        if (val) { addDistrictTag(wrap, val, rowId); this.value = ''; }
        var dd2 = wrap.querySelector('.dm-dropdown');
        if (dd2) dd2.style.display = 'none';
      }
    };
  }

  function addDistrictTag(wrap, name, rowId) {
    /* Avoid duplicates in same row */
    var existing = wrap.querySelectorAll('.dm-tag');
    for (var i = 0; i < existing.length; i++) {
      if (existing[i].getAttribute('data-name').toLowerCase() === name.toLowerCase()) return;
    }
    var tag = document.createElement('span');
    tag.className = 'dm-tag';
    tag.setAttribute('data-name', name);
    tag.innerHTML = name + ' <span class="dm-remove" onclick="this.parentElement.remove()">&times;</span>';
    var input = wrap.querySelector('.dm-add-input');
    wrap.insertBefore(tag, input);
  }

  function recalcDistrictPct(blockId) {
    var rows = document.querySelectorAll('#districtTable_' + blockId + ' tbody tr');
    var total = 0;
    rows.forEach(function(tr) {
      var inp = tr.querySelector('.district-qty');
      total += parseInt(inp.value) || 0;
    });
    /* Get total leads received from source table */
    var sourceTotal = 0;
    document.querySelectorAll('#sourcesTable_' + blockId + ' tbody tr').forEach(function(tr) {
      var inputs = tr.querySelectorAll('input[type=number]');
      sourceTotal += parseInt(inputs[0] ? inputs[0].value : 0) || 0;
    });
    /* Others = sourceTotal - districtTotal */
    var others = Math.max(0, sourceTotal - total);
    var othersEl = document.getElementById('districtOthers_' + blockId);
    var totalEl  = document.getElementById('districtTotal_' + blockId);
    if (othersEl) othersEl.textContent = others;
    if (totalEl)  totalEl.textContent = total + others;
    var grandTotal = total + others;
    /* Update percentages */
    rows.forEach(function(tr) {
      var qty = parseInt(tr.querySelector('.district-qty').value) || 0;
      var pctCell = tr.querySelector('.district-pct');
      pctCell.textContent = grandTotal > 0 ? Math.round(qty / grandTotal * 100) + '%' : '0%';
    });
  }

  function rateStars(blockId, val) {
    var wrap = document.getElementById('starRating_' + blockId);
    var label = document.getElementById('starLabel_' + blockId);
    if (!wrap) return;
    wrap.setAttribute('data-value', val);
    wrap.querySelectorAll('.star').forEach(function(s) {
      var n = parseInt(s.getAttribute('data-star'));
      if (n <= val) s.classList.add('filled'); else s.classList.remove('filled');
    });
    var labels = {1: 'Bajo', 2: 'Medio', 3: 'Alto'};
    var colors = {1: '#E74C3C', 2: '#F39C12', 3: '#27AE60'};
    if (label) { label.textContent = labels[val] || ''; label.style.color = colors[val] || '#999'; }
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
      /* Collect districts */
      var districts = [];
      block.querySelectorAll('.district-table tbody tr').forEach(function(tr) {
        var zoneSel = tr.querySelector('.district-zone-select');
        var zoneName = zoneSel && zoneSel.value ? zoneSel.options[zoneSel.selectedIndex].text : '';
        var tags = tr.querySelectorAll('.dm-tag');
        var districtNames = [];
        tags.forEach(function(t) { districtNames.push(t.getAttribute('data-name')); });
        var qty = parseInt(tr.querySelector('.district-qty').value) || 0;
        var pct = tr.querySelector('.district-pct').textContent;
        if (zoneName && qty > 0) {
          districts.push({ zone: zoneName, districts: districtNames, quantity: qty, pct: pct });
        }
      });
      /* Collect sales */
      var ventasInput = block.querySelector('.sales-ventas');
      var separacionesInput = block.querySelector('.sales-separaciones');
      var ventas = ventasInput ? (parseInt(ventasInput.value) || 0) : 0;
      var separaciones = separacionesInput ? (parseInt(separacionesInput.value) || 0) : 0;
      /* Collect lead quality */
      var starWrap = block.querySelector('.star-rating');
      var starVal = starWrap ? parseInt(starWrap.getAttribute('data-value')) || 0 : 0;
      var leadQuality = starVal === 3 ? 'alto' : (starVal === 2 ? 'medio' : (starVal === 1 ? 'bajo' : ''));
      var comments = (block.querySelector('.project-comments') || {}).value || '';
      result.push({ project_id: projId, project_name: projName, sources: sources, districts: districts, ventas: ventas, separaciones: separaciones, lead_quality: leadQuality, comments: comments.trim() });
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
      Swal.fire({icon:'warning',title:'Campos incompletos',text:'Completa todos los campos del paso 1.',confirmButtonColor:'#A400F6'}); return;
    }

    /* Validate per-project mode */
    var sourcesData, sourceComments, totalRecv = 0;
    if (hasProjects) {
      var projData = collectProjectSources();
      if (projData.length === 0) {
        Swal.fire({icon:'warning',title:'Selecciona un proyecto',text:'Debes seleccionar al menos un proyecto en el paso 2.',confirmButtonColor:'#A400F6'}); return;
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
          /* ── Build full report ── */
          var h = '';
          h += '<div style="font-family:Helvetica Neue,Arial,sans-serif;color:#222;">';
          h += '<div style="text-align:center;margin-bottom:20px;">';
          h += '<h2 style="color:#4614FF;margin:0 0 4px;">Reporte Mensual de Leads</h2>';
          h += '<p style="color:#666;font-size:13px;margin:0;">'+projectName+'</p>';
          h += '</div>';

          /* Header info */
          h += '<table style="width:100%;font-size:13px;margin-bottom:16px;border-collapse:collapse;">';
          h += '<tr><td style="padding:6px 0;color:#888;width:140px;">Responsable</td><td style="padding:6px 0;font-weight:600;">'+contactName+'</td></tr>';
          h += '<tr><td style="padding:6px 0;color:#888;">Periodo</td><td style="padding:6px 0;font-weight:600;">'+selectedMonth+' - '+selectedPeriod+'</td></tr>';
          h += '<tr><td style="padding:6px 0;color:#888;">Total leads recibidos</td><td style="padding:6px 0;font-weight:600;">'+totalRecv+'</td></tr>';
          h += '</table>';

          if (hasProjects) {
            projData.forEach(function(proj) {
              h += '<div style="border:1px solid #E0E0E0;border-radius:10px;padding:16px;margin-bottom:14px;">';
              h += '<h3 style="color:#4614FF;font-size:15px;margin:0 0 10px;border-bottom:2px solid #4614FF;padding-bottom:6px;">'+proj.project_name+'</h3>';

              /* Sources table */
              if (proj.sources && proj.sources.length) {
                h += '<p style="font-weight:700;font-size:12px;color:#555;margin:0 0 4px;">Plataformas</p>';
                h += '<table style="width:100%;border-collapse:collapse;font-size:12px;margin-bottom:10px;">';
                h += '<tr style="background:#4614FF;color:#fff;"><th style="padding:6px 8px;text-align:left;">Plataforma</th><th style="padding:6px 8px;text-align:center;">Recibidos</th><th style="padding:6px 8px;text-align:center;">Contestaron</th><th style="padding:6px 8px;text-align:center;">Son perfil</th></tr>';
                var tR=0,tC=0,tP=0;
                proj.sources.forEach(function(s){
                  var r=s.received||0, c=s.replied||0, p=s.profile||0; tR+=r;tC+=c;tP+=p;
                  h+='<tr style="border-bottom:1px solid #eee;"><td style="padding:5px 8px;">'+s.platform+'</td><td style="padding:5px 8px;text-align:center;">'+r+'</td><td style="padding:5px 8px;text-align:center;">'+c+'</td><td style="padding:5px 8px;text-align:center;">'+p+'</td></tr>';
                });
                h += '<tr style="font-weight:700;background:#f4f4f4;"><td style="padding:5px 8px;">Total</td><td style="padding:5px 8px;text-align:center;">'+tR+'</td><td style="padding:5px 8px;text-align:center;">'+tC+'</td><td style="padding:5px 8px;text-align:center;">'+tP+'</td></tr>';
                h += '</table>';
              }

              /* Districts */
              if (proj.districts && proj.districts.length) {
                h += '<p style="font-weight:700;font-size:12px;color:#555;margin:8px 0 4px;">Distritos</p>';
                h += '<table style="width:100%;border-collapse:collapse;font-size:12px;margin-bottom:10px;">';
                h += '<tr style="background:#6A0DAD;color:#fff;"><th style="padding:6px 8px;text-align:left;">Zona</th><th style="padding:6px 8px;text-align:left;">Distrito(s)</th><th style="padding:6px 8px;text-align:center;">Cantidad</th><th style="padding:6px 8px;text-align:center;">%</th></tr>';
                proj.districts.forEach(function(dd){
                  h+='<tr style="border-bottom:1px solid #eee;"><td style="padding:4px 8px;">'+(dd.zone||'')+'</td><td style="padding:4px 8px;">'+(dd.districts||[]).join(', ')+'</td><td style="padding:4px 8px;text-align:center;">'+(dd.quantity||0)+'</td><td style="padding:4px 8px;text-align:center;">'+(dd.pct||'')+'</td></tr>';
                });
                h += '</table>';
              }

              /* Sales */
              if (proj.ventas || proj.separaciones) {
                h += '<p style="font-weight:700;font-size:12px;color:#555;margin:8px 0 4px;">Ventas y Separaciones</p>';
                h += '<table style="width:100%;border-collapse:collapse;font-size:12px;margin-bottom:10px;">';
                h += '<tr style="background:#A400F6;color:#fff;"><th style="padding:6px 8px;text-align:center;">Ventas</th><th style="padding:6px 8px;text-align:center;">Separaciones</th></tr>';
                h += '<tr><td style="padding:5px 8px;text-align:center;">'+(proj.ventas||0)+'</td><td style="padding:5px 8px;text-align:center;">'+(proj.separaciones||0)+'</td></tr></table>';
              }

              /* Lead quality */
              if (proj.lead_quality) {
                var qLabels={alto:'Alto',medio:'Medio',bajo:'Bajo'};
                var qColors={alto:'#27AE60',medio:'#F39C12',bajo:'#E74C3C'};
                var qStars={alto:'\u2605\u2605\u2605',medio:'\u2605\u2605\u2606',bajo:'\u2605\u2606\u2606'};
                h += '<p style="font-size:12px;margin:6px 0;"><strong>Calidad de los leads:</strong> <span style="color:'+(qColors[proj.lead_quality]||'#999')+';font-weight:700;">'+(qStars[proj.lead_quality]||'')+' '+(qLabels[proj.lead_quality]||proj.lead_quality)+'</span></p>';
              }

              /* Comments */
              if (proj.comments) {
                h += '<p style="font-size:12px;color:#666;margin:4px 0;"><em>Comentario: '+proj.comments+'</em></p>';
              }
              h += '</div>';
            });
          } else {
            /* Non-project mode */
            var sources = collectSources();
            if (sources.length) {
              h += '<p style="font-weight:700;font-size:12px;color:#555;margin:8px 0 4px;">Plataformas</p>';
              h += '<table style="width:100%;border-collapse:collapse;font-size:12px;margin-bottom:10px;">';
              h += '<tr style="background:#4614FF;color:#fff;"><th style="padding:6px 8px;text-align:left;">Plataforma</th><th style="padding:6px 8px;text-align:center;">Recibidos</th><th style="padding:6px 8px;text-align:center;">Contestaron</th><th style="padding:6px 8px;text-align:center;">Son perfil</th></tr>';
              sources.forEach(function(s){
                h+='<tr style="border-bottom:1px solid #eee;"><td style="padding:5px 8px;">'+s.platform+'</td><td style="padding:5px 8px;text-align:center;">'+(s.received||0)+'</td><td style="padding:5px 8px;text-align:center;">'+(s.replied||0)+'</td><td style="padding:5px 8px;text-align:center;">'+(s.profile||0)+'</td></tr>';
              });
              h += '</table>';
            }
            if (sourceComments) h += '<p style="font-size:12px;color:#666;"><em>'+sourceComments+'</em></p>';
          }

          /* General quality + free comment */
          if (ratings.quality) h += '<p style="font-size:13px;margin:10px 0 4px;"><strong>Calidad general:</strong> '+ratings.quality+'/10</p>';
          var freeComment = document.getElementById('free_comment').value.trim();
          if (freeComment) h += '<p style="font-size:13px;margin:4px 0;"><strong>Comentario adicional:</strong> '+freeComment+'</p>';

          h += '<p style="text-align:center;color:#999;font-size:10px;margin-top:20px;">Generado el '+ new Date().toLocaleDateString('es-PE') +'</p>';
          h += '</div>';

          document.getElementById('summaryContent').innerHTML = h;

          document.getElementById('step3').classList.remove('active');
          document.getElementById('stepSuccess').classList.add('active');
          document.getElementById('progressFill').style.width = '100%';
          document.getElementById('pct').textContent = '100%';
          document.getElementById('stepLabel').textContent = 'Completado!';
          window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
          Swal.fire({icon:'error',title:'Error',text:res.message || 'No se pudo enviar el formulario.',confirmButtonColor:'#A400F6'});
          btn.disabled = false;
          btn.textContent = 'Enviar reporte';
        }
      },
      error: function() {
        Swal.fire({icon:'error',title:'Error de red',text:'Intenta nuevamente.',confirmButtonColor:'#A400F6'});
        btn.disabled = false;
        btn.textContent = 'Enviar reporte';
      }
    });
  }

  function downloadPDF() {
    var el = document.getElementById('summaryContent');
    var clientLabel = document.getElementById('projectName') ? document.getElementById('projectName').textContent : 'Reporte';

    /* Temporarily force explicit white bg & ensure element is scrolled into view */
    window.scrollTo({ top: el.offsetTop - 20, behavior: 'instant' });

    var opt = {
      margin:       [8, 8, 8, 8],
      filename:     'Reporte_Leads_' + clientLabel.replace(/[^a-zA-Z0-9]/g,'_') + '.pdf',
      image:        { type: 'png', quality: 1 },
      html2canvas:  { scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false, scrollY: 0, scrollX: 0 },
      jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
      pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
    };

    html2pdf().set(opt).from(el).save();
  }

  updateProgress();
</script>
</body>
</html>
<?php exit; ?>
