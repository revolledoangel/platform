<?php

class Projects_controller
{
    static public function ctrShowProjects()
    {
        $url = 'https://algoritmo.digital/backend/public/api/projects';

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

    public function ctrCreateProject()
    {
        if (isset($_POST["newProjectName"])) {

            if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ +\-:_.\/,"\'()]+$/', $_POST["newProjectName"])) {


                $name = $_POST["newProjectName"];
                $client_id = $_POST["newProjectClient"];
                $code = $_POST["newProjectCode"];
                $group = $_POST["newProjectGroup"];

                // Si está vacío, es válido (autogenerar)
                if ($code === "" || preg_match('/^\d{3}$/', $code)) {

                    $body = [
                        "client_id" => $client_id,
                        "name" => $name,
                        "group" => $group,
                        "code" => $code
                    ];

                    $jsonData = json_encode($body);
                    // Inicializa cURL
                    $ch = curl_init('https://algoritmo.digital/backend/public/api/projects');
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
                                    title: "Cliente creado correctamente",
                                    text: ' . json_encode($mensaje) . ',
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then((result)=>{
                                    if(result.value){
                                        window.location = "projects";
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
                                    title: "Error al crear el Proyecto",
                                    text: "' . addslashes($errorMsg) . '",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then((result)=>{
                                    if(result.value){
                                        window.location = "projects";
                                    }
                                });
                            </script>';
                    }

                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Validación incorrecta",
                            text: "El código debe tener exactamente 3 dígitos numéricos. Usted ingresó: \n' . htmlspecialchars($_POST["newProjectCode"]) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result)=>{
                            if(result.value){
                                window.location = "projects";
                            }
                        });
                    </script>';
                }

            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Validación incorrecta",
                            text: "No se permiten caracteres especiales, usted ingresó: ' . htmlspecialchars($_POST["newProjectName"]) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "projects";
                            }
                        });
                    </script>';
            }
        }
    }

    public static function ctrCambiarEstadoProyecto($id, $estado)
    {
        $url = "https://algoritmo.digital/backend/public/api/projects/" . $id;

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

    static public function ctrDeleteProject()
    {
        if (isset($_GET["projectId"])) {

            $projectId = $_GET["projectId"];

            $url = 'https://algoritmo.digital/backend/public/api/projects/' . $projectId;

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
                            title: "¡Proyecto eliminado!",
                            text: "El proyecto ha sido eliminado correctamente.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "projects";
                            }
                        });
                    </script>';
            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Error",
                            text: "No se pudo eliminar el proyecto. Por favor, inténtelo de nuevo.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "projects";
                            }
                        });
                    </script>';
            }
        }
    }

    static public function ctrShowProjectsByClient($clientId)
    {
        // Construimos la URL del API con el ID del cliente
        $url = 'https://algoritmo.digital/backend/public/api/clients/' . $clientId . '/projects';

        $ch = curl_init($url);

        // Hacemos la petición con el método POST, como lo requiere la API
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            // Devolvemos únicamente el array 'projects' que está dentro de la respuesta
            return $responseData['projects'] ?? [];
        } else {
            // Si hay un error, devolvemos un array vacío para no romper el modal
            return [];
        }
    }
}