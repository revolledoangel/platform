<?php
require_once "../controllers/urls.controller.php";

class AjaxUrls {
    public function ajaxListUrls() {
        $urls = Urls_Controller::ctrShowUrls();

        $data = [];
        foreach ($urls as $url) {
            $fullUrl = $url["url"] . "?" . http_build_query([
                'utm_source' => $url['utm_source'],
                'utm_medium' => $url['utm_medium'],
                'utm_campaign' => $url['utm_campaign'],
                'utm_term' => $url['utm_term'],
                'utm_content' => $url['utm_content'],
            ]);

            $acciones = '<div class="btn-group">
                <button type="button" class="btn btn-default btn-info btn-showUrl"
                    data-url="' . htmlspecialchars($fullUrl) . '"
                    title="Ver URL completa">
                    <i class="fa fa-eye"></i>
                </button>
                <button type="button" class="btn btn-default btn-warning btn-editUrl"
                    urlId="' . $url["id"] . '" data-toggle="modal"
                    data-target="#editUrlModal">
                    <span class="glyphicon glyphicon-pencil"></span>
                </button>
                <button type="button" class="btn btn-default btn-danger btn-deleteUrl"
                    urlId="' . $url["id"] . '">
                    <span class="glyphicon glyphicon-remove"></span>
                </button>
            </div>';

            $data[] = [
                htmlspecialchars($url["url"]),
                htmlspecialchars($url["utm_source"]),
                htmlspecialchars($url["utm_medium"]),
                htmlspecialchars($url["utm_campaign"]),
                htmlspecialchars($url["utm_term"]),
                htmlspecialchars($url["utm_content"]),
                htmlspecialchars($url["campaign_name"] ?: "Sin nombre"),
                $acciones
            ];
        }

        echo json_encode(["data" => $data]);
    }
}

if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listar = new AjaxUrls();
    $listar->ajaxListUrls();
    return;
}