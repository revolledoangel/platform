<?php
class MediaMixEcommerce_Controller
{
    static public function ctrShowMediaMixEcommerce()
    {
        $url = 'https://algoritmo.digital/backend/public/api/mmecommerces';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode === 200) ? json_decode($response, true) : [];
    }

    public function ctrCreateMediaMixEcommerce()
    {
        if (isset($_POST["newPeriodId"])) {
            if ($_POST["newName"] === "" || preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ \-.,()]+$/', $_POST["newName"])) {
                $body = [
                    "name" => $_POST["newName"],
                    "period_id" => $_POST["newPeriodId"],
                    "client_id" => $_POST["newClientId"],
                    "currency" => $_POST["newCurrency"],
                    "fee" => $_POST["newFee"],
                    "fee_type" => $_POST["newFeeType"],
                    "igv" => $_POST["newIgv"],
                ];
                $jsonData = json_encode($body);

                $ch = curl_init('https://algoritmo.digital/backend/public/api/mmecommerces');
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
                                title: "Registro creado correctamente",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then((result)=>{
                                if(result.value){ window.location = "mediaMixEcommerce"; }
                            });
                        </script>';
                } else {
                    $errorMsg = $responseData['message'] ?? 'Error desconocido';
                    echo '<script>
                            swal({
                                type: "error",
                                title: "Error al crear el registro",
                                text: "' . addslashes($errorMsg) . '",
                            });
                        </script>';
                }
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error de validación",
                        text: "El nombre no puede llevar caracteres especiales.",
                    });
                </script>';
            }
        }
    }

    public function ctrEditMediaMixEcommerce()
    {
        if (isset($_POST["editMediaMixId"])) {
            if ($_POST["editName"] === "" || preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ \-.,()]+$/', $_POST["editName"])) {
                $mediaMixId = $_POST["editMediaMixId"];
                $url = 'https://algoritmo.digital/backend/public/api/mmecommerces/' . $mediaMixId;

                $body = [
                    "name" => $_POST["editName"],
                    "period_id" => $_POST["editPeriodId"],
                    "client_id" => $_POST["editClientId"],
                    "currency" => $_POST["editCurrency"],
                    "fee" => $_POST["editFee"],
                    "fee_type" => $_POST["editFeeType"],
                    "igv" => $_POST["editIgv"],
                ];
                $jsonData = json_encode($body);

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    echo '<script>
                            swal({
                                type: "success",
                                title: "Registro actualizado correctamente",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then((result)=>{
                                if(result.value){ window.location = "mediaMixEcommerce"; }
                            });
                        </script>';
                } else {
                    $responseData = json_decode($response, true);
                    $errorMsg = $responseData['message'] ?? 'Error desconocido';
                    echo '<script>
                            swal({
                                type: "error",
                                title: "Error al actualizar el registro",
                                text: "' . addslashes($errorMsg) . '",
                            });
                        </script>';
                }
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error de validación",
                        text: "El nombre no puede llevar caracteres especiales.",
                    });
                </script>';
            }
        }
    }

    static public function ctrDeleteMediaMixEcommerce()
    {
        if (isset($_GET["mediaMixId"])) {
            $mediaMixId = $_GET["mediaMixId"];
            $url = 'https://algoritmo.digital/backend/public/api/mmecommerces/' . $mediaMixId;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 || $httpCode === 204) {
                echo '<script>
                        swal({
                            type: "success",
                            title: "¡Registro eliminado!",
                            text: "El registro ha sido eliminado correctamente.",
                        }).then((result) => {
                            if (result.value) { window.location = "mediaMixEcommerce"; }
                        });
                    </script>';
            } else {
                echo '<script>
                        swal({
                            type: "error",
                            title: "Error",
                            text: "No se pudo eliminar el registro.",
                        }).then((result) => {
                            if (result.value) { window.location = "mediaMixEcommerce"; }
                        });
                    </script>';
            }
        }
    }

    static public function ctrShowMediaMixEcommerceById($id)
    {
        $url = 'https://algoritmo.digital/backend/public/api/mmecommerces/' . $id;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = ($httpCode === 200) ? json_decode($response, true) : null;
        
        // Asegurar que fee_type tenga un valor por defecto si no existe
        if ($data && !isset($data['fee_type'])) {
            $data['fee_type'] = 'percentage';
        }
        
        return $data;
    }
}
