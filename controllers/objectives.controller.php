<?php

class Objectives_Controller
{
    static public function ctrShowObjectives()
    {
        $url = 'https://algoritmo.digital/backend/public/api/objectives';

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
            return [
                'error' => true,
                'message' => 'No se pudieron obtener los objetivos',
                'status' => $httpCode
            ];
        }
    }

    public function ctrCreateObjective()
    {
        if (isset($_POST["newObjectiveName"])) {

            if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ \-\/\(\)]+$/', $_POST["newObjectiveName"])) {

                $name = $_POST["newObjectiveName"];
                $code = $_POST["newObjectiveCode"];
                $default_result = $_POST["newObjectiveDefaultResult"];

                // Validar: vacío o exactamente 3 dígitos numéricos
                if ($code === "" || preg_match('/^\d{3}$/', $code)) {

                    $body = [
                        "name" => $name,
                        "code" => $code,
                        "default_result" => $default_result
                    ];

                    $jsonData = json_encode($body);

                    $ch = curl_init('https://algoritmo.digital/backend/public/api/objectives');
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
                        $objetivo = $responseData;
                        $name = htmlspecialchars($objetivo["name"]);
                        $default_result = htmlspecialchars($objetivo["default_result"]);
                        $code = htmlspecialchars($objetivo["code"]);

                        $mensaje = "Nombre: $name\nResultado por defecto: $default_result\nCódigo: $code\n";
                        
                        echo '<script>
                                swal({
                                    type: "success",
                                    title: "Objetivo creado correctamente",
                                    text: ' . json_encode($mensaje) . ',
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then((result)=>{
                                    if(result.value){
                                        window.location = "objectives";
                                    }
                                });
                            </script>';
                    } else {
                        $errorMsg = 'Error desconocido';
                        if (isset($responseData['errors'])) {
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
                                    title: "Error al crear el Objetivo",
                                    text: "' . addslashes($errorMsg) . '",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then((result)=>{
                                    if(result.value){
                                        window.location = "objectives";
                                    }
                                });
                            </script>';
                    }

                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Validación incorrecta",
                            text: "El código debe contener exactamente 3 números. Usted ingresó: \n' . htmlspecialchars($code) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result)=>{
                            if(result.value){
                                window.location = "objectives";
                            }
                        });
                    </script>';
                }

            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Validación incorrecta",
                            text: "No se permiten caracteres especiales, usted ingresó: ' . htmlspecialchars($_POST["newObjectiveName"]) . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "objectives";
                            }
                        });
                    </script>';
            }
        }
    }

    static public function ctrDeleteObjective()
    {
        if (isset($_GET["objectiveId"])) {

            $objectiveId = $_GET["objectiveId"];
            
            $url = 'https://algoritmo.digital/backend/public/api/objectives/' . $objectiveId;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            ));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $respuesta = json_decode($response, true);

            if ($httpCode === 200 && isset($respuesta["success"]) && $respuesta["success"] === true) {
                echo '<script>
                        swal({
                            type: "success",
                            title: "¡Objetivo eliminado!",
                            text: "El objetivo ha sido eliminado correctamente.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "objectives";
                            }
                        });
                    </script>';
            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Error",
                            text: "No se pudo eliminar el objetivo. Por favor, inténtelo de nuevo.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "objectives";
                            }
                        });
                    </script>';
            }
        }
    }
}
