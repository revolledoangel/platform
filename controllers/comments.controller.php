<?php

class Comments_Controller
{
    static public function ctrShowComments()
    {
        $url = 'https://algoritmo.digital/backend/public/api/comments';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            return [
                'error' => true,
                'message' => 'No se pudieron obtener los comentarios',
                'status' => $httpCode
            ];
        }
    }

    public function ctrCreateComment()
    {
        if (
            isset($_POST["newCommentClient"]) &&
            isset($_POST["newCommentPlatform"]) &&
            isset($_POST["newCommentPeriod"]) &&
            isset($_POST["newCommentConclusion"]) &&
            isset($_POST["newCommentRecommendation"])
        ) {
            $body = [
                "client_id" => $_POST["newCommentClient"],
                "platform_id" => $_POST["newCommentPlatform"],
                "period_id" => $_POST["newCommentPeriod"],
                "conclusion" => $_POST["newCommentConclusion"],
                "recommendation" => $_POST["newCommentRecommendation"]
            ];

            $jsonData = json_encode($body);

            $ch = curl_init('https://algoritmo.digital/backend/public/api/comments');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode($response, true);

            if ($httpCode === 201) {
                echo '<script>
                        swal({
                            type: "success",
                            title: "Comentario creado correctamente",
                            text: "El comentario fue registrado con éxito.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result)=> {
                            if(result.value){
                                //window.location = "comments";
                            }
                        });
                    </script>';
            } else {
                $errorMsg = $responseData["message"] ?? "Error desconocido";
                echo '<script>
                        swal({
                            type: "error",
                            title: "Error al crear el comentario",
                            text: "' . addslashes($errorMsg) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result)=> {
                            if(result.value){
                                window.location = "comments";
                            }
                        });
                    </script>';
            }
        }
    }

    static public function ctrDeleteComment()
    {
        if (isset($_GET["commentId"])) {
            $commentId = $_GET["commentId"];
            $url = 'https://algoritmo.digital/backend/public/api/comments/' . $commentId;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $respuesta = json_decode($response, true);

            if ($httpCode === 200 && isset($respuesta["success"]) && $respuesta["success"] === true) {
                echo '<script>
                        swal({
                            type: "success",
                            title: "¡Comentario eliminado!",
                            text: "El comentario ha sido eliminado correctamente.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "comments";
                            }
                        });
                    </script>';
            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Error",
                            text: "No se pudo eliminar el comentario. Por favor, inténtelo de nuevo.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "comments";
                            }
                        });
                    </script>';
            }
        }
    }
}
