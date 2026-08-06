<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$accion = isset($_GET['accion']) ? $_GET['accion'] : (isset($_POST['accion']) ? $_POST['accion'] : '');

if ($accion === 'registrar') {

    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    if ($codigo === '' || $nombre === '' || $apellido === '') {
        echo json_encode(array("exito" => false, "mensaje" => "Codigo, nombre y apellido son obligatorios"));
        exit;
    }

    $check = $conexion->prepare("SELECT id FROM pacientes WHERE codigo = ?");
    $check->bind_param("s", $codigo);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(array("exito" => false, "mensaje" => "Ya existe un paciente con ese codigo"));
        $check->close();
        exit;
    }
    $check->close();

    $stmt = $conexion->prepare("INSERT INTO pacientes (codigo, nombre, apellido, telefono, direccion) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $codigo, $nombre, $apellido, $telefono, $direccion);

    if ($stmt->execute()) {
        echo json_encode(array("exito" => true, "mensaje" => "Paciente registrado correctamente"));
    } else {
        echo json_encode(array("exito" => false, "mensaje" => "Error al registrar el paciente"));
    }
    $stmt->close();

} elseif ($accion === 'listar') {

    $resultado = $conexion->query("SELECT codigo, nombre, apellido, telefono FROM pacientes ORDER BY nombre ASC");
    $lista = array();

    while ($fila = $resultado->fetch_assoc()) {
        $lista[] = array(
            "codigo" => $fila['codigo'],
            "nombre" => $fila['nombre'],
            "apellido" => $fila['apellido'],
            "telefono" => $fila['telefono']
        );
    }

    echo json_encode($lista);

} else {
    echo json_encode(array("exito" => false, "mensaje" => "Accion no valida"));
}

$conexion->close();
?>
