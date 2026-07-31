<?php

class Platforms_controller
{
    static public function ctrShowPlatforms()
    {
        $url = 'https://algoritmo.digital/backend/public/api/platforms';

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
                'message' => 'No se pudieron obtener los clientes',
                'status' => $httpCode
            ];
        }
    }

    public function ctrCreatePlatform()
    {
        if (isset($_POST["newPlatformName"])) {

            if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["newPlatformName"])) {

                $name = $_POST["newPlatformName"];
                $code = $_POST["newPlatformCode"];

                // Si está vacío, es válido (autogenerar)
                if ($code === "" || preg_match('/^[A-Z]{2}$/', $code)) {

                    $body = [
                        "name" => $name,
                        "code" => $code
                    ];

                    $jsonData = json_encode($body);
                    // Inicializa cURL
                    $ch = curl_init('https://algoritmo.digital/backend/public/api/platforms');
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
                        // Extraer datos de la plataforma
                        $plataforma = $responseData;
                        $name = htmlspecialchars($plataforma["name"]);
                        $code = htmlspecialchars($plataforma["code"]);

                        // Construir texto para el swal
                        $mensaje = "Nombre: $name\nCódigo: $code\n";
                        
                        echo '<script>
                                swal({
                                    type: "success",
                                    title: "Plataforma creada correctamente",
                                    text: ' . json_encode($mensaje) . ',
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then((result)=>{
                                    if(result.value){
                                        window.location = "platforms";
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
                                    title: "Error al crear la Plataforma",
                                    text: "' . addslashes($errorMsg) . '",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then((result)=>{
                                    if(result.value){
                                        window.location = "platforms";
                                    }
                                });
                            </script>';
                    }

                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Validación incorrecta",
                            text: "El código debe tener exactamente 2 letras mayúculas. Usted ingresó: \n' . htmlspecialchars($_POST["newPlatformCode"]) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result)=>{
                            if(result.value){
                                window.location = "platforms";
                            }
                        });
                    </script>';
                }

            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Validación incorrecta",
                            text: "No se permiten caracteres especiales, usted ingresó: ' . htmlspecialchars($_POST["newPlatformName"]) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "platforms";
                            }
                        });
                    </script>';
            }
        }
    }

    public static function ctrCambiarEstadoPlataforma($id, $estado)
    {
        $url = "https://algoritmo.digital/backend/public/api/platforms/" . $id;

        $data = json_encode([
            "active" => $estado
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    static public function ctrDeletePlatform()
    {
        if (isset($_GET["platformId"])) {

            $platformId = $_GET["platformId"];
            
            $url = 'https://algoritmo.digital/backend/public/api/platforms/' . $platformId;

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
                            title: "¡Plataforma eliminada!",
                            text: "La plataforma ha sido eliminado correctamente.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "platforms";
                            }
                        });
                    </script>';
            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Error",
                            text: "No se pudo eliminar la plataforma. Por favor, inténtelo de nuevo.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "platforms";
                            }
                        });
                    </script>';
            }
        }
    }
}