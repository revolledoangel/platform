<?php
header('Content-Type: application/json');

require_once "../controllers/metrics.controller.php";

class AjaxMetrics
{
    public $metricId;

    public function ajaxListMetrics()
    {
        $metrics = Metrics_Controller::ctrShowMetrics();

        if (empty($metrics)) {
            echo json_encode(["data" => []]);
            return;
        }

        // Fetch requires_event for all metrics from local DB
        $host = 'srv1013.hstgr.io';
        $port = 3306;
        $db   = 'u961992735_plataforma';
        $user = 'u961992735_plataforma';
        $pass = 'Peru+*963.';
        $requiresEventMap = [];
        $conn = new mysqli($host, $user, $pass, $db, $port);
        if (!$conn->connect_error) {
            $result = $conn->query("SELECT id, requires_event FROM metrics");
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $requiresEventMap[intval($row['id'])] = intval($row['requires_event']);
                }
            }
            $conn->close();
        }

        $data = [];

        foreach ($metrics as $metric) {
            $id = (int)($metric['id'] ?? 0);
            $name = htmlspecialchars($metric['name'] ?? '');
            $code = htmlspecialchars($metric['code'] ?? '');
            $active = !empty($metric['active']);

            $platformNames = [];

            if (!empty($metric['platforms']) && is_array($metric['platforms'])) {
                foreach ($metric['platforms'] as $platform) {
                    if (!empty($platform['name'])) {
                        $platformNames[] = htmlspecialchars($platform['name']);
                    }
                }
            } else {
                $platforms = Metrics_Controller::ctrShowMetricPlatforms($id);
                if (is_array($platforms)) {
                    foreach ($platforms as $platform) {
                        if (!empty($platform['name'])) {
                            $platformNames[] = htmlspecialchars($platform['name']);
                        }
                    }
                }
            }

            $platformsText = !empty($platformNames)
                ? implode(', ', $platformNames)
                : '<span class="text-muted">Sin plataformas</span>';

            $activeBadge = $active
                ? '<span class="label label-success">Activo</span>'
                : '<span class="label label-default">Inactivo</span>';

            $requiresEvent = isset($requiresEventMap[$id]) ? $requiresEventMap[$id] : 0;
            $requiresEventBadge = $requiresEvent
                ? '<span class="label label-warning">Sí</span>'
                : '<span class="label label-default">No</span>';

            $actions = '<div class="btn-group">'
                . '<button class="btn btn-warning btn-editMetric" data-metricid="' . $id . '" data-toggle="modal" data-target="#editMetricModal">'
                . '<i class="fa fa-pencil"></i></button>'
                . '<button class="btn btn-danger btn-deleteMetric" data-metricid="' . $id . '">'
                . '<i class="fa fa-trash"></i></button>'
                . '</div>';

            $data[] = [
                $name,
                $code,
                $activeBadge,
                $platformsText,
                $requiresEventBadge,
                $actions
            ];
        }

        echo json_encode(["data" => $data]);
    }

    public function ajaxEditMetric()
    {
        $metric = Metrics_Controller::ctrShowMetricById($this->metricId);
        echo json_encode($metric);
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'list') {
    $listMetrics = new AjaxMetrics();
    $listMetrics->ajaxListMetrics();
    exit;
}

if (isset($_GET['metricId'])) {
    $editMetric = new AjaxMetrics();
    $editMetric->metricId = $_GET['metricId'];
    $editMetric->ajaxEditMetric();
    exit;
}

// Save requires_event locally for a metric
if (isset($_POST['save_requires_event'])) {
    $host = 'srv1013.hstgr.io';
    $port = 3306;
    $db   = 'u961992735_plataforma';
    $user = 'u961992735_plataforma';
    $pass = 'Peru+*963.';
    $metric_id = intval($_POST['save_requires_event']);
    $requires_event = isset($_POST['requires_event']) && $_POST['requires_event'] ? 1 : 0;
    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) { echo json_encode(['success' => false]); exit; }
    // Update if exists, otherwise insert a minimal row (in case the API metric isn't locally mirrored)
    $check = $conn->query("SELECT id FROM metrics WHERE id = $metric_id");
    if ($check && $check->num_rows > 0) {
        $conn->query("UPDATE metrics SET requires_event = $requires_event WHERE id = $metric_id");
    } else {
        $conn->query("INSERT INTO metrics (id, requires_event) VALUES ($metric_id, $requires_event)");
    }
    $conn->close();
    echo json_encode(['success' => true]);
    exit;
}

// Get requires_event for a metric
if (isset($_GET['get_requires_event'])) {
    $host = 'srv1013.hstgr.io';
    $port = 3306;
    $db   = 'u961992735_plataforma';
    $user = 'u961992735_plataforma';
    $pass = 'Peru+*963.';
    $metric_id = intval($_GET['get_requires_event']);
    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) { echo json_encode(['requires_event' => 0]); exit; }
    $result = $conn->query("SELECT requires_event FROM metrics WHERE id = $metric_id LIMIT 1");
    $value = 0;
    if ($result && $row = $result->fetch_assoc()) {
        $value = intval($row['requires_event']);
    }
    $conn->close();
    echo json_encode(['requires_event' => $value]);
    exit;
}

