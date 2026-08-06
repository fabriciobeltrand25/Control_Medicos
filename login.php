<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($usuario === '' || $password === '') {
    echo json_encode(array("exito" => false, "mensaje" => "Completa todos los campos"));
    exit;
}

$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ? AND password = ?");
$stmt->bind_param("ss", $usuario, $password);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    echo json_encode(array("exito" => true, "mensaje" => "Bienvenido"));
} else {
    echo json_encode(array("exito" => false, "mensaje" => "Usuario o contrasena incorrectos"));
}

$stmt->close();
$conexion->close();
?>
