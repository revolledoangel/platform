<?php

class Formats_controller
{
    static public function ctrShowFormats()
    {
        $url = 'https://algoritmo.digital/backend/public/api/formats';

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
                'message' => 'No se pudieron obtener los formatos',
                'status' => $httpCode
            ];
        }
    }

    public function ctrCreateFormat()
    {
        if (isset($_POST["newFormatName"])) {

            if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["newFormatName"])) {

                $name = $_POST["newFormatName"];
                $platform_id = $_POST["newFormatPlatform"];
                $code = $_POST["newFormatCode"];

                // Si está vacío, es válido (autogenerar)
                if ($code === "" || preg_match('/^\d{3}$/', $code)) {

                    $body = [
                        "platform_id" => $platform_id,
                        "name" => $name
                    ];

                    if($code!==""){
                        $body['code'] = $code;
                    }

                    $jsonData = json_encode($body);
                    // Inicializa cURL
                    $ch = curl_init('https://algoritmo.digital/backend/public/api/formats');
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
                        // Extraer datos del cliente
                        $cliente = $responseData;
                        $name = htmlspecialchars($cliente["name"]);
                        $code = htmlspecialchars($cliente["code"]);

                        // Construir texto para el swal
                        $mensaje = "Nombre: $name\nCódigo: $code\n";
                        
                        echo '<script>
                                swal({
                                    type: "success",
                                    title: "Formato creado correctamente",
                                    text: ' . json_encode($mensaje) . ',
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then((result)=>{
                                    if(result.value){
                                        window.location = "formats";
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
                                    title: "Error al crear el Formato",
                                    text: "' . addslashes($errorMsg) . '",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then((result)=>{
                                    if(result.value){
                                        window.location = "formats";
                                    }
                                });
                            </script>';
                    }

                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Validación incorrecta",
                            text: "El formato debe tener exactamente 3 dígitos numéricos. Usted ingresó: \n' . htmlspecialchars($_POST["newFormatCode"]) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result)=>{
                            if(result.value){
                                window.location = "formats";
                            }
                        });
                    </script>';
                }

            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Validación incorrecta",
                            text: "No se permiten caracteres especiales, usted ingresó: ' . htmlspecialchars($_POST["newFormatName"]) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "formats";
                            }
                        });
                    </script>';
            }
        }
    }

    public static function ctrCambiarEstadoFormato($id, $estado)
    {
        $url = "https://algoritmo.digital/backend/public/api/formats/" . $id;

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

    static public function ctrDeleteFormat()
    {
        if (isset($_GET["formatId"])) {

            $formatId = $_GET["formatId"];
            
            $url = 'https://algoritmo.digital/backend/public/api/formats/' . $formatId;

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
                            title: "¡Formato eliminado!",
                            text: "El formato ha sido eliminado correctamente.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "formats";
                            }
                        });
                    </script>';
            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Error",
                            text: "No se pudo eliminar el formato. Por favor, inténtelo de nuevo.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "formats";
                            }
                        });
                    </script>';
            }
        }
    }
}