<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$accion = isset($_GET['accion']) ? $_GET['accion'] : (isset($_POST['accion']) ? $_POST['accion'] : '');

// =====================================================
// ASIGNAR CONSULTA
// =====================================================
if ($accion === 'asignar') {

    $codigo_paciente = trim($_POST['codigo_paciente']);
    $id_medico = trim($_POST['id_medico']);
    $fecha_consulta = trim($_POST['fecha_consulta']);
    $valor_consulta = trim($_POST['valor_consulta']);

    if ($codigo_paciente === '' || $id_medico === '' || $fecha_consulta === '' || $valor_consulta === '') {
        echo json_encode(array("exito" => false, "mensaje" => "Completa todos los campos"));
        exit;
    }

    // Buscar paciente
    $stmtP = $conexion->prepare("SELECT id FROM pacientes WHERE codigo = ?");
    $stmtP->bind_param("s", $codigo_paciente);
    $stmtP->execute();
    $resP = $stmtP->get_result();

    if ($resP->num_rows === 0) {
        echo json_encode(array("exito" => false, "mensaje" => "No existe un paciente con ese codigo"));
        $stmtP->close();
        exit;
    }
    $id_paciente = $resP->fetch_assoc()['id'];
    $stmtP->close();

    // Validar medico
    $stmtM = $conexion->prepare("SELECT id FROM medicos WHERE id = ?");
    $stmtM->bind_param("i", $id_medico);
    $stmtM->execute();
    if ($stmtM->get_result()->num_rows === 0) {
        echo json_encode(array("exito" => false, "mensaje" => "No existe un medico con ese ID"));
        $stmtM->close();
        exit;
    }
    $stmtM->close();

    // Regla: el paciente NO puede tener otra consulta activa
    $stmtCheck = $conexion->prepare("SELECT id FROM consultas WHERE id_paciente = ? AND estado = 'activa'");
    $stmtCheck->bind_param("i", $id_paciente);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows > 0) {
        echo json_encode(array("exito" => false, "mensaje" => "Este paciente ya tiene una consulta activa"));
        $stmtCheck->close();
        exit;
    }
    $stmtCheck->close();

    $stmt = $conexion->prepare("INSERT INTO consultas (id_paciente, id_medico, fecha_consulta, valor_consulta, estado) VALUES (?, ?, ?, ?, 'activa')");
    $stmt->bind_param("iisd", $id_paciente, $id_medico, $fecha_consulta, $valor_consulta);

    if ($stmt->execute()) {
        echo json_encode(array("exito" => true, "mensaje" => "Consulta asignada correctamente"));
    } else {
        echo json_encode(array("exito" => false, "mensaje" => "Error al asignar la consulta"));
    }
    $stmt->close();

// =====================================================
// BUSCAR CONSULTA ACTIVA DE UN PACIENTE
// =====================================================
} elseif ($accion === 'buscar_activa') {

    $codigo_paciente = isset($_GET['codigo_paciente']) ? trim($_GET['codigo_paciente']) : '';

    $sql = "SELECT c.id, m.nombre AS nombre_medico, c.fecha_consulta, c.valor_consulta
            FROM consultas c
            INNER JOIN pacientes p ON c.id_paciente = p.id
            INNER JOIN medicos m ON c.id_medico = m.id
            WHERE p.codigo = ? AND c.estado = 'activa'
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $codigo_paciente);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode(array("exito" => false, "mensaje" => "Este paciente no tiene consulta activa"));
    } else {
        $fila = $resultado->fetch_assoc();
        echo json_encode(array(
            "exito" => true,
            "id_consulta" => strval($fila['id']),
            "nombre_medico" => $fila['nombre_medico'],
            "fecha_consulta" => $fila['fecha_consulta'],
            "valor_consulta" => $fila['valor_consulta']
        ));
    }
    $stmt->close();

// =====================================================
// DAR DE BAJA (cierra la consulta, habilita el cobro)
// =====================================================
} elseif ($accion === 'dar_baja') {

    $id_consulta = trim($_POST['id_consulta']);

    $stmt = $conexion->prepare("UPDATE consultas SET estado = 'pendiente_pago', fecha_baja = NOW() WHERE id = ? AND estado = 'activa'");
    $stmt->bind_param("i", $id_consulta);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(array("exito" => true, "mensaje" => "Consulta dada de baja. Cobro pendiente generado."));
    } else {
        echo json_encode(array("exito" => false, "mensaje" => "No se pudo dar de baja (verifica que siga activa)"));
    }
    $stmt->close();

} else {
    echo json_encode(array("exito" => false, "mensaje" => "Accion no valida"));
}

$conexion->close();
?>
