<?php

class Users_controller
{
    // Iniciar Sesion
    static public function ctrUserStartSesion()
    {
        if (isset($_POST["user"])) {

            if (
                preg_match('/^[a-zA-Z0-9*.@]+$/', $_POST["user"]) &&
                preg_match('/^[a-zA-Z0-9*.@+!#$%&()=]+$/', $_POST["password"])
            ) {
                $username = $_POST['user'] ?? '';
                $password = $_POST['password'] ?? '';

                $body = [
                    "username" => $username,
                    "password" => $password
                ];

                $user = UserModel::login($body);

                if ($user) {

                    echo '<br><div class="alert alert-success m-1">Bienvenido</div>';

                    $_SESSION["startSession"] = true;
                    $_SESSION["id"] = $user["user"]["id"];
                    $_SESSION["email"] = $user["user"]["email"];
                    $_SESSION["nombre"] = $user["user"]["name"];
                    $_SESSION["foto"] = $user["user"]["photo"];
                    $_SESSION["perfil"] = $user["user"]["profile"];

                    echo '<script>
                        window.location = "home";
                    </script>';

                } else {
                    echo '<br><div class="alert alert-danger m-1">Usuario y/o contraseña inválido</div>';
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
                        window.location = "users";
                    }
                });
            </script>';
            }

        } else {
            //include 'views/modules/login.php';
        }

    }

    public function ctrCreateUser()
    {
        if (isset($_POST["newName"])) {

            if (
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["newName"]) &&
                preg_match('/^[a-zA-Z0-9@.]+$/', $_POST["newEmail"]) &&
                preg_match('/^[a-zA-Z0-9@.*#%&+]+$/', $_POST["newPassword"]) &&
                preg_match('/^[a-zA-Z0-9@.*#%&]+$/', $_POST["newUsername"])
            ) {
                /* validar imagen */
                $ruta = "";

                if (isset($_FILES["newPhoto"]["tmp_name"]) && !empty($_FILES["newPhoto"]["tmp_name"])) {

                    //var_dump(getimagesize($_FILES["newPhoto"]["tmp_name"]));
                    list($ancho, $alto) = getimagesize($_FILES["newPhoto"]["tmp_name"]);
                    $nuevoAncho = 500;
                    $nuevoAlto = 500;

                    //crea la carpeta para guardar la foto
                    $carpeta = "views/img/users/" . $_POST["newUsername"];
                    mkdir($carpeta, 0755, true);

                    if ($_FILES["newPhoto"]["type"] == "image/jpeg") {
                        $aleatorio = mt_rand(100, 999);
                        $ruta = "views/img/users/" . $_POST["newUsername"] . "/" . $aleatorio . ".jpg";
                        $origen = imagecreatefromjpeg($_FILES["newPhoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagejpeg($destino, $ruta);
                    }

                    if ($_FILES["newPhoto"]["type"] == "image/png") {
                        $aleatorio = mt_rand(100, 999);
                        $ruta = "views/img/users/" . $_POST["newUsername"] . "/" . $aleatorio . ".png";
                        $origen = imagecreatefrompng($_FILES["newPhoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagepng($destino, $ruta);
                    }
                }
                $body = [
                    "name" => $_POST["newName"],
                    "username" => $_POST["newUsername"],
                    "email" => $_POST["newEmail"],
                    "password" => $_POST["newPassword"],
                    "profile" => $_POST["newProfile"] ?? '',
                    "photo" => $ruta ?? ''
                ];
                $jsonData = json_encode($body);
                // Inicializa cURL
                $ch = curl_init('https://algoritmo.digital/backend/public/api/users');
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
                        title: "Usuario creado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then((result)=>{
                        if(result.value){
                            window.location = "users";
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
                        title: "Error al crear el usuario",
                        text: "' . addslashes($errorMsg) . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then((result)=>{
                        if(result.value){
                            window.location = "users";
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
                        window.location = "users";
                    }
                });
            </script>';
            }
        }
    }

    static public function ctrShowUsers()
    {
        $url = 'https://algoritmo.digital/backend/public/api/users';

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

    static public function ctrCambiarEstadoUsuario($id, $estado)
    {
        $respuesta = UserModel::mdlActualizarEstadoUsuario($id, $estado);
        echo json_encode([
            "success" => true,
            "message" => "Estado actualizado correctamente",
            "response" => $respuesta
        ]);
    }

    public function ctrEditUser()
    {
        // Usar isset($_POST["editId"]) es una mejor comprobación de que el formulario se ha enviado.
        if (isset($_POST["editId"])) {            

            // 1. VALIDACIÓN DE CAMPOS
            // Es mejor validar cada campo por separado para dar mensajes de error más específicos.
            // Pero siguiendo tu estructura, aquí está la validación corregida.

            // Mueve la validación de la contraseña adentro para que no sea obligatoria.
            $passwordValidation = true; // Asumimos que es válida si está vacía
            if (!empty($_POST["newUserPassword"])) {
                $passwordValidation = preg_match('/^[a-zA-Z0-9@.*#%&+]+$/', $_POST["newUserPassword"]);
            }   

            if (
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editName"]) &&
                filter_var($_POST["editEmail"], FILTER_VALIDATE_EMAIL) && // Usando una mejor validación de email
                preg_match('/^[a-zA-Z0-9_.-]+$/', $_POST["editUsername"]) && // Regex más común para usernames
                $passwordValidation // Usando la variable de validación de contraseña
            ) {

                echo "<script>alert('ID de usuario recibido: " . $_POST["editPhoto"] . "');</script>";
                // 2. VALIDACIÓN Y PROCESAMIENTO DE LA IMAGEN
                $ruta = $_POST["currentPhoto"]; // Necesitarás un input hidden para la foto actual

                // Verifica si se subió un nuevo archivo.
                // CORRECCIÓN: Usar "editPhoto", que es el nombre del input file.
                if (isset($_FILES["editPhoto"]["tmp_name"]) && !empty($_FILES["editPhoto"]["tmp_name"])) {

                    list($ancho, $alto) = getimagesize($_FILES["editPhoto"]["tmp_name"]);
                    $nuevoAncho = 500;
                    $nuevoAlto = 500;

                    // CORRECCIÓN: Crear la carpeta usando el "editUsername".
                    $carpeta = "views/img/users/" . $_POST["editUsername"];

                    // Si el usuario no tiene foto, creamos el directorio
                    if (!file_exists($carpeta)) {
                        mkdir($carpeta, 0755, true);
                    } else {
                        // Si ya tenía una foto anterior, la borramos para no acumular archivos.
                        if (!empty($_POST["currentPhoto"])) {
                            unlink($_POST["currentPhoto"]);
                        }
                    }

                    // Procesamiento según el tipo de imagen
                    if ($_FILES["editPhoto"]["type"] == "image/jpeg") {
                        $aleatorio = mt_rand(100, 999);
                        // CORRECCIÓN: Usar "editUsername" para la ruta.
                        $ruta = "views/img/users/" . $_POST["editUsername"] . "/" . $aleatorio . ".jpg";
                        $origen = imagecreatefromjpeg($_FILES["editPhoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagejpeg($destino, $ruta);
                    }

                    if ($_FILES["editPhoto"]["type"] == "image/png") {
                        $aleatorio = mt_rand(100, 999);
                        // CORRECCIÓN: Usar "editUsername" para la ruta.
                        $ruta = "views/img/users/" . $_POST["editUsername"] . "/" . $aleatorio . ".png";
                        $origen = imagecreatefrompng($_FILES["editPhoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagepng($destino, $ruta);
                    }
                }

                // 3. PREPARACIÓN DE DATOS PARA LA API
                $body = [
                    "name" => $_POST["editName"],
                    "username" => $_POST["editUsername"],
                    "email" => $_POST["editEmail"],
                    "profile" => $_POST["editProfile"],
                    "photo" => $ruta // $ruta contendrá la foto nueva o la anterior si no se cambió.
                ];

                // Solo añade la contraseña al body si se proporcionó una nueva.
                if (!empty($_POST["newUserPassword"])) {
                    // IMPORTANTE: La API debe encargarse de hacer el hash de la contraseña.
                    $body["password"] = $_POST["newUserPassword"];
                }

                $jsonData = json_encode($body);

                // 4. ENVÍO DE DATOS VÍA cURL (sin cambios aquí, tu código cURL es correcto)
                $ch = curl_init('https://algoritmo.digital/backend/public/api/users/' . $_POST["editId"]);
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
                            title: "Usuario actualizado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "users";
                            }
                        });
                    </script>';
                } else {
                    $errorMsg = $responseData['message'] ?? 'Error desconocido desde la API.';
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Error al actualizar el usuario",
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
                        text: "Por favor, revisa los campos. El nombre, usuario y email son obligatorios y no deben contener caracteres inválidos.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }




}