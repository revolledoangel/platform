<?php
require_once "../controllers/users.controller.php";
require_once "../models/users.model.php";



class AjaxUsers
{
    /*Editar Usuario*/
    public $userId;
    public $userActive;
    static public function ajaxEditUser()
    {
        $url = "https://algoritmo.digital/backend/public/api/users/" . $this->userId;

        $response = file_get_contents($url); // Alternativa: usar cURL si necesitas encabezados

        echo $response; // El frontend lo recibirá como JSON
    }

    static public function ajaxActiveUser()
    {
        Users_controller::ctrCambiarEstadoUsuario($this->userId, $this->userActive);
    }
}
/*editar usuario*/
if (isset($_POST["userId"])) {
    $editar = new AjaxUsers();
    $editar->userId = $_POST["userId"];
    $editar->ajaxEditUser();
}
/*activar o desactivar*/
if (isset($_POST["active"])) {
    $activar = new AjaxUsers();
    $activar->userId = $_POST["id"];
    $activar->userActive = $_POST["active"];
    $activar->ajaxActiveUser();
}
