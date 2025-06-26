<?php

class Clients_controller
{
    static public function ctrShowClients()
    {
        $url = 'https://algoritmo.digital/backend/public/api/clients';

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
    public function ctrCreateClient()
    {
        if (isset($_POST["newClientName"])) {

            if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["newClientName"])) {


                $name = $_POST["newClientName"];
                $user_id = null;
                $code = $_POST["newClientCode"];
                $vertical_ids = [];


                // Si está vacío, es válido (autogenerar)
                if ($code === "" || preg_match('/^[A-Z]{2}$/', $code)) {

                    if (!empty($_POST["newClientVerticals"])) {
                        $vertical_ids = $_POST["newClientVerticals"];
                    }

                    if (!empty($_POST["newClientUser"])) {

                        $user_id = $_POST["newClientUser"];
                        echo "el id user es : " . $user_id;
                    }

                    $body = [
                        "name" => $name,
                        "user_id" => $user_id,
                        "code" => $code,
                        "vertical_ids" => $vertical_ids
                    ];

                    $jsonData = json_encode($body);
                    // Inicializa cURL
                    $ch = curl_init('https://algoritmo.digital/backend/public/api/clients');
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
                        // Extraer datos del cliente
                        $cliente = $responseData["client"];
                        $name = htmlspecialchars($cliente["name"]);
                        $code = htmlspecialchars($cliente["code"]);

                        // Construir texto para el swal
                        $mensaje = "Nombre: $name\nCódigo: $code\n";
                        
                        echo '<script>
                                swal({
                                    type: "success",
                                    title: "Cliente creado correctamente",
                                    text: ' . json_encode($mensaje) . ',
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then((result)=>{
                                    if(result.value){
                                        window.location = "clients";
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
                                    title: "Error al crear el Cliente",
                                    text: "' . addslashes($errorMsg) . '",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then((result)=>{
                                    if(result.value){
                                        window.location = "clients";
                                    }
                                });
                            </script>';
                    }



                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Validación incorrecta",
                            text: "El código debe tener exactamente dos letras mayúsculas (sin tildes, números ni espacios), o dejarse vacío. Usted ingresó: \n' . htmlspecialchars($_POST["newClientCode"]) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result)=>{
                            if(result.value){
                                window.location = "clients";
                            }
                        });
                    </script>';
                }



            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Validación incorrecta",
                            text: "No se permiten caracteres especiales, usted ingresó: ' . htmlspecialchars($_POST["newClientName"]) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "clients";
                            }
                        });
                    </script>';
            }



        }
    }

    static public function ctrEditClient()
    {
        if (isset($_POST["editClientId"])) {

            if (
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s_-]+$/', $_POST["editClientName"]) &&
                preg_match('/^[a-zA-Z0-9_-]+$/', $_POST["editClientCode"])
            ) {

                // Construir el body con campos obligatorios
                $body = [
                    "name" => $_POST["editClientName"],
                    "code" => $_POST["editClientCode"]
                ];

                // Agregar user_id si fue enviado
                if (!empty($_POST["editClientUser"])) {
                    $body["user_id"] = (int) $_POST["editClientUser"];
                }

                // Agregar vertical_ids si vienen seleccionadas
                if (!empty($_POST["editClientVerticals"]) && is_array($_POST["editClientVerticals"])) {
                    // Convertir cada valor a int
                    $body["vertical_ids"] = array_map('intval', $_POST["editClientVerticals"]);
                }

                $jsonData = json_encode($body);

                $ch = curl_init('https://algoritmo.digital/backend/public/api/clients/' . $_POST["editClientId"]);
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

                if ($httpCode === 201 && isset($responseData["success"]) && $responseData["success"] === true) {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "Cliente actualizado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "clients";
                            }
                        });
                    </script>';
                } else {
                    $errorMsg = $responseData['message'] ?? 'Error desconocido desde la API.';
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Error al actualizar el cliente",
                            text: "' . addslashes($errorMsg) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }

            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error de validación",
                        text: "Revisa los campos: no deben contener caracteres inválidos.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }


    static public function ctrCambiarEstadoCliente($id, $estado)
    {
        
        
        $url = "https://algoritmo.digital/backend/public/api/clients/" . $id;

        $data = json_encode([
            "active" => $estado
        ]);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        echo json_encode([
            "success" => true,
            "message" => "Estado actualizado correctamente",
            "response" => $response
        ]);
    }

    static public function ctrDeleteClient()
    {
        if (isset($_GET["clientId"])) {

            $clientId = $_GET["clientId"];
            
            $url = 'https://algoritmo.digital/backend/public/api/clients/' . $clientId;

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
                            title: "¡Cliente eliminado!",
                            text: "El cliente ha sido eliminado correctamente.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "clients";
                            }
                        });
                    </script>';
            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Error",
                            text: "No se pudo eliminar el cliente. Por favor, inténtelo de nuevo.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "clients";
                            }
                        });
                    </script>';
            }
        }
    }

}