<?php

class Verticals_Controller
{
    static public function ctrShowVerticals()
    {
        $url = 'https://algoritmo.digital/backend/public/api/verticals';

        // Inicializa cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            return $responseData;
        } else {
            // Puedes devolver false o un array con un mensaje de error
            return [
                'error' => true,
                'message' => 'No se pudieron obtener los usuarios',
                'status' => $httpCode
            ];
        }
    }

    static public function ctrCreateVertical()
    {
        if (isset($_POST["newVerticalName"])) {

            if (
                preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ][a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]*$/', $_POST["newVerticalName"])
            ) {
                $body = [
                    "name" => $_POST["newVerticalName"],
                ];

                $jsonData = json_encode($body);
                // Inicializa cURL
                $ch = curl_init('https://algoritmo.digital/backend/public/api/verticals');
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
                if ($httpCode === 201 || $httpCode === 200) {
                    echo '<script>
                    swal({
                        type: "success",
                        title: "Vertical creado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then((result)=>{
                        if(result.value){
                            window.location = "verticals";
                        }
                    });
                </script>';
                } else {
                    // Obtener mensaje de error específico
                    $errorMsg = 'Error desconocido';
                    if (isset($responseData['errors'])) {
                        // Extraer el primer mensaje de error de cualquier campo
                        foreach ($responseData['errors'] as $field => $messages) {
                            if (is_array($messages) && count($messages) > 0) {
                                $errorMsg = $messages[0];
                                break;
                            }
                        }
                    } elseif (isset($responseData['message'])) {
                        $errorMsg = $responseData['message'];
                    }
                    echo '<script>
                    swal({
                        type: "error",
                        title: "Error al crear el Vertical",
                        text: "' . addslashes($errorMsg) . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then((result)=>{
                        if(result.value){
                            window.location = "verticals";
                        }
                    });
                </script>';
                }

            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Validación incorrecta",
                            text: "No se permiten caracteres especiales",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result)=>{
                            if(result.value){
                                window.location = "verticals";
                            }
                        });
                    </script>';
            }


        }

    }

    static public function ctrEditVertical()
    {

        if (isset($_POST["editVerticalId"])) {

            if (
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ_-]+$/', $_POST["editVerticalName"])
            ) {

                $body = [
                    "name" => $_POST["editVerticalName"],
                ];

                $jsonData = json_encode($body);

                // 4. ENVÍO DE DATOS VÍA cURL (sin cambios aquí, tu código cURL es correcto)
                $ch = curl_init('https://algoritmo.digital/backend/public/api/verticals/' . $_POST["editVerticalId"]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $responseData = json_decode($response, true);

                // 5. RESPUESTA AL USUARIO (sin cambios aquí)
                if ($httpCode === 200 && isset($responseData["success"]) && $responseData["success"] === true) {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "Vertical actualizado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "verticals";
                            }
                        });
                    </script>';
                } else {
                    $errorMsg = $responseData['message'] ?? 'Error desconocido desde la API.';
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Error al actualizar el Vertical",
                            text: "' . addslashes($errorMsg) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }

            } else {
                // Mensaje de error de validación
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error de validación",
                        text: "Por favor, revisa los campos.No deben contener caracteres inválidos.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    static public function ctrDeleteVertical()
    {
        if (isset($_GET["verticalId"])) {

            $verticalId = $_GET["verticalId"];
            
            $url = 'https://algoritmo.digital/backend/public/api/verticals/' . $verticalId;

            // Iniciar cURL
            $ch = curl_init($url);

            // Configurar cURL para realizar una petición DELETE
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            ));

            // Ejecutar la petición
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Cerrar cURL
            curl_close($ch);

            // Decodificar la respuesta JSON
            $respuesta = json_decode($response, true);

            // Verificar si la respuesta fue exitosa
            if ($httpCode === 200 && isset($respuesta["success"]) && $respuesta["success"] === true) {
                echo '<script>
                        swal({
                            type: "success",
                            title: "¡Vertical eliminado!",
                            text: "El vertical ha sido eliminado correctamente.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "verticals";
                            }
                        });
                    </script>';
            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Error",
                            text: "No se pudo eliminar el vertical. Por favor, inténtelo de nuevo.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "verticals";
                            }
                        });
                    </script>';
            }
        }
    }

}