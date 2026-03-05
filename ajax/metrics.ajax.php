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
