<?php
// test_db_connection.php
$host = 'srv1013.hstgr.io';
$port = 3306;
$db   = 'u961992735_plataforma';
$user = 'u961992735_plataforma';
$pass = 'Peru+*963.';

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}

echo 'Conexión exitosa a la base de datos.';
$conn->close();
