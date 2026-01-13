<?php
require_once "../controllers/comments.controller.php";

// Función para truncar HTML preservando etiquetas
function truncateHTML($html, $maxLength) {
    $plainText = strip_tags($html);
    
    if (mb_strlen($plainText) <= $maxLength) {
        return $html;
    }
    
    // Truncar el texto plano en la última palabra completa
    $truncated = mb_substr($plainText, 0, $maxLength);
    $lastSpace = mb_strrpos($truncated, ' ');
    if ($lastSpace !== false) {
        $truncated = mb_substr($truncated, 0, $lastSpace);
    }
    
    // Extraer el mismo contenido del HTML original
    $result = '';
    $plainIndex = 0;
    $targetLength = mb_strlen($truncated);
    $inTag = false;
    
    for ($i = 0; $i < mb_strlen($html); $i++) {
        $char = mb_substr($html, $i, 1);
        
        if ($char === '<') {
            $inTag = true;
            $result .= $char;
        } elseif ($char === '>') {
            $inTag = false;
            $result .= $char;
        } elseif ($inTag) {
            $result .= $char;
        } else {
            if ($plainIndex < $targetLength) {
                $result .= $char;
                $plainIndex++;
            } else {
                break;
            }
        }
    }
    
    return $result . '...';
}

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

            $acciones = '<div class="btn-group" style="white-space: nowrap;">
                            <button type="button" class="btn btn-info btn-viewComment" commentId="' . $comment["id"] . '" data-toggle="modal" data-target="#viewCommentModal">
                                <i class="fa fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-warning btn-editComment" commentId="' . $comment["id"] . '" data-toggle="modal" data-target="#editCommentModal">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-deleteComment" commentId="' . $comment["id"] . '">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>';

            // Truncar textos a 180 caracteres
            $clientName = htmlspecialchars($comment["client_name"]);
            $platformName = htmlspecialchars($comment["platform_name"]);
            $periodName = htmlspecialchars($comment["period_name"]);
            
            // Para recommendation y conclusion, permitir HTML pero truncar basado en texto plano
            $recommendation = $comment["recommendation"];
            $conclusion = $comment["conclusion"];
            
            $recommendation = truncateHTML($recommendation, 180);
            $conclusion = truncateHTML($conclusion, 180);

            // Truncar nombres en la última palabra completa
            if (mb_strlen($clientName) > 180) {
                $clientName = mb_substr($clientName, 0, 180);
                $lastSpace = mb_strrpos($clientName, ' ');
                if ($lastSpace !== false) {
                    $clientName = mb_substr($clientName, 0, $lastSpace);
                }
                $clientName .= '...';
            }
            
            if (mb_strlen($platformName) > 180) {
                $platformName = mb_substr($platformName, 0, 180);
                $lastSpace = mb_strrpos($platformName, ' ');
                if ($lastSpace !== false) {
                    $platformName = mb_substr($platformName, 0, $lastSpace);
                }
                $platformName .= '...';
            }
            
            if (mb_strlen($periodName) > 180) {
                $periodName = mb_substr($periodName, 0, 180);
                $lastSpace = mb_strrpos($periodName, ' ');
                if ($lastSpace !== false) {
                    $periodName = mb_substr($periodName, 0, $lastSpace);
                }
                $periodName .= '...';
            }

            $data[] = [
                $clientName,                                    // Columna 0 - Nombre (visible)
                $comment["client_id"],                          // Columna 1 - ID (oculta)
                $platformName,                                  // Columna 2 - Nombre (visible)
                $comment["platform_id"],                        // Columna 3 - ID (oculta)
                $periodName,                                    // Columna 4 - Nombre (visible)
                $comment["period_id"],                          // Columna 5 - ID (oculta)
                $recommendation,                                // Columna 6
                $conclusion,                                    // Columna 7
                $acciones                                       // Columna 8
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
