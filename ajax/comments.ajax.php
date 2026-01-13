<?php
require_once "../controllers/comments.controller.php";

class AjaxComments
{
    public $commentId;

    public function ajaxEditComment()
    {
        $url = "https://algoritmo.digital/backend/public/api/comments/" . $this->commentId;
        $response = file_get_contents($url);
        echo $response;
    }

    public function ajaxListComments()
    {
        $comments = Comments_Controller::ctrShowComments();

        $data = [];

        foreach ($comments as $comment) {

            $acciones = '<div class="btn-group">
                            <button type="button" class="btn btn-warning btn-editComment" commentId="' . $comment["id"] . '" data-toggle="modal" data-target="#editCommentModal">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </button>
                            <button type="button" class="btn btn-danger btn-deleteComment" commentId="' . $comment["id"] . '">
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                        </div>';

            $data[] = [
                htmlspecialchars($comment["client_name"]),      // Columna 0 - Nombre (visible)
                $comment["client_id"],                          // Columna 1 - ID (oculta)
                htmlspecialchars($comment["platform_name"]),    // Columna 2 - Nombre (visible)
                $comment["platform_id"],                        // Columna 3 - ID (oculta)
                htmlspecialchars($comment["period_name"]),      // Columna 4 - Nombre (visible)
                htmlspecialchars($comment["recommendation"]),   // Columna 5
                htmlspecialchars($comment["conclusion"]),       // Columna 6
                $acciones                                       // Columna 7
            ];
        }

        echo json_encode(["data" => $data]);
    }
}

// Acción: listar
if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listar = new AjaxComments();
    $listar->ajaxListComments();
    return;
}

// Acción: editar
if (isset($_POST["commentId"])) {
    $editar = new AjaxComments();
    $editar->commentId = $_POST["commentId"];
    $editar->ajaxEditComment();
}
