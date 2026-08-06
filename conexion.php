<?php
$servidor = "localhost";
$usuario_db = "root";
$password_db = "";
$base_datos = "control_pacientes";

$conexion = new mysqli($servidor, $usuario_db, $password_db, $base_datos);

if ($conexion->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(array("exito" => false, "mensaje" => "Error de conexion a la base de datos"));
    exit;
}

$conexion->set_charset("utf8");
?>
