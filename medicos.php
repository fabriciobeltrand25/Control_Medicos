<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$accion = isset($_GET['accion']) ? $_GET['accion'] : (isset($_POST['accion']) ? $_POST['accion'] : '');

if ($accion === 'registrar') {

    $nombre = trim($_POST['nombre']);
    $especialidad = trim($_POST['especialidad']);

    if ($nombre === '' || $especialidad === '') {
        echo json_encode(array("exito" => false, "mensaje" => "Completa todos los campos"));
        exit;
    }

    $stmt = $conexion->prepare("INSERT INTO medicos (nombre, especialidad) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre, $especialidad);

    if ($stmt->execute()) {
        echo json_encode(array("exito" => true, "mensaje" => "Medico registrado correctamente"));
    } else {
        echo json_encode(array("exito" => false, "mensaje" => "Error al registrar el medico"));
    }
    $stmt->close();

} elseif ($accion === 'listar') {

    $resultado = $conexion->query("SELECT id, nombre, especialidad FROM medicos ORDER BY nombre ASC");
    $lista = array();

    while ($fila = $resultado->fetch_assoc()) {
        $lista[] = array(
            "id" => $fila['id'],
            "nombre" => $fila['nombre'],
            "especialidad" => $fila['especialidad']
        );
    }

    echo json_encode($lista);

} else {
    echo json_encode(array("exito" => false, "mensaje" => "Accion no valida"));
}

$conexion->close();
?>
