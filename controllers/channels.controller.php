<?php

class Channels_Controller
{
    static public function ctrShowChannels()
    {
        $url = 'https://algoritmo.digital/backend/public/api/channels';

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
            return [];
        }
    }

    public function ctrCreateChannel()
    {
        if (isset($_POST["newChannelName"])) {
            if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ \-\/\(\),.]+$/', $_POST["newChannelName"])) {

                $name = $_POST["newChannelName"];

                $body = [
                    "name" => $name,
                ];

                $jsonData = json_encode($body);

                $ch = curl_init('https://algoritmo.digital/backend/public/api/channels');
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
                    $channel = $responseData;
                    $name = htmlspecialchars($channel["name"]);
                    
                    $mensaje = "Nombre: $name\n";

                    echo '<script>
                            swal({
                                type: "success",
                                title: "Canal creado correctamente",
                                text: ' . json_encode($mensaje) . ',
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then((result)=>{
                                if(result.value){
                                    window.location = "channels";
                                }
                            });
                        </script>';
                } else {
                    $errorMsg = 'Error desconocido';
                    if (isset($responseData['message'])) {
                        $errorMsg = $responseData['message'];
                    }
                    echo '<script>
                            swal({
                                type: "error",
                                title: "Error al crear el Canal",
                                text: "' . addslashes($errorMsg) . '",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then((result)=>{
                                if(result.value){
                                    window.location = "channels";
                                }
                            });
                        </script>';
                }

            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Validación incorrecta",
                            text: "El nombre no puede llevar caracteres especiales.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "channels";
                            }
                        });
                    </script>';
            }
        }
    }

    static public function ctrDeleteChannel()
    {
        if (isset($_GET["channelId"])) {
            $channelId = $_GET["channelId"];
            $url = 'https://algoritmo.digital/backend/public/api/channels/' . $channelId;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            ));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 || $httpCode === 204) {
                echo '<script>
                        swal({
                            type: "success",
                            title: "¡Canal eliminado!",
                            text: "El canal ha sido eliminado correctamente.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "channels";
                            }
                        });
                    </script>';
            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Error",
                            text: "No se pudo eliminar el canal. Por favor, inténtelo de nuevo.",
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "channels";
                            }
                        });
                    </script>';
            }
        }
    }
}