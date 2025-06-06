<?php
class Users_controller
{
    // Iniciar Sesion
    public function ctrUserStartSesion()
    {
        if (isset($_POST["user"])) {
    
            if (
                preg_match('/^[a-zA-Z0-9*.@]+$/', $_POST["user"]) &&
                preg_match('/^[a-zA-Z0-9*.@]+$/', $_POST["password"])
            ) {
                $username = $_POST['user'] ?? '';
                $password = $_POST['password'] ?? '';

                $user = User::login($username, $password);

                if ($user) {
                    
                    echo '<br><div class="alert alert-success m-1">Bienvenido</div>';

                    $_SESSION["startSession"]=true;
                    $_SESSION["nombre"]= $user["user"]["name"];
                    $_SESSION["foto"]= $user["user"]["photo"];
                    $_SESSION["perfil"]= $user["user"]["profile"];

                    echo '<script>
                        window.location = "home";
                    </script>';

                } else {
                    echo '<br><div class="alert alert-danger m-1">Usuario y/o contraseña inválido</div>';
                }

            }else{
                echo "caracteres raros";
            }

        }else {
            //include 'views/modules/login.php';
        }

    }
}