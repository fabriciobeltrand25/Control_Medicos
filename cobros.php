<?php
header('Content-Type: application/json');
require_once 'conexion.php';

define("MORA_POR_DIA", 20.00);

$accion = isset($_GET['accion']) ? $_GET['accion'] : (isset($_POST['accion']) ? $_POST['accion'] : '');

// =====================================================
// BUSCAR DEUDA PENDIENTE (consulta + mora calculada)
// =====================================================
if ($accion === 'buscar') {

    $codigo_paciente = isset($_GET['codigo_paciente']) ? trim($_GET['codigo_paciente']) : '';

    $sql = "SELECT c.id, c.valor_consulta, c.fecha_baja
            FROM consultas c
            INNER JOIN pacientes p ON c.id_paciente = p.id
            WHERE p.codigo = ? AND c.estado = 'pendiente_pago'
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $codigo_paciente);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode(array("exito" => false, "mensaje" => "No hay cobros pendientes para ese paciente"));
        $stmt->close();
        $conexion->close();
        exit;
    }

    $fila = $resultado->fetch_assoc();
    $stmt->close();

    $valor_consulta = floatval($fila['valor_consulta']);
    $fecha_baja = new DateTime($fila['fecha_baja']);
    $ahora = new DateTime();

    $diferencia = $fecha_baja->diff($ahora);
    $dias_atraso = intval($diferencia->days);

    $mora = $dias_atraso * MORA_POR_DIA;
    $total = $valor_consulta + $mora;

    echo json_encode(array(
        "exito" => true,
        "id_consulta" => strval($fila['id']),
        "valor_consulta" => $valor_consulta,
        "dias_atraso" => $dias_atraso,
        "mora" => $mora,
        "total" => $total
    ));

// =====================================================
// PROCESAR PAGO
// =====================================================
} elseif ($accion === 'pagar') {

    $id_consulta = trim($_POST['id_consulta']);
    $monto_pagado = floatval($_POST['monto_pagado']);

    $sql = "SELECT valor_consulta, fecha_baja FROM consultas WHERE id = ? AND estado = 'pendiente_pago'";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_consulta);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode(array("exito" => false, "mensaje" => "Esta consulta no tiene un cobro pendiente"));
        $stmt->close();
        $conexion->close();
        exit;
    }

    $fila = $resultado->fetch_assoc();
    $stmt->close();

    $valor_consulta = floatval($fila['valor_consulta']);
    $fecha_baja = new DateTime($fila['fecha_baja']);
    $ahora = new DateTime();
    $dias_atraso = intval($fecha_baja->diff($ahora)->days);
    $mora = $dias_atraso * MORA_POR_DIA;
    $total = $valor_consulta + $mora;

    // Regla: no se permite pagar menos del total
    if ($monto_pagado < $total) {
        echo json_encode(array("exito" => false, "mensaje" => "El monto pagado (L " . number_format($monto_pagado, 2) . ") es menor al total (L " . number_format($total, 2) . ")"));
        $conexion->close();
        exit;
    }

    $cambio = round($monto_pagado - $total, 2);

    $conexion->begin_transaction();
    try {
        $stmtInsert = $conexion->prepare("INSERT INTO cobros (id_consulta, valor_consulta, dias_atraso, mora, total_pagado, monto_recibido, cambio) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtInsert->bind_param("ididdd", $id_consulta, $valor_consulta, $dias_atraso, $mora, $total, $monto_pagado, $cambio);
        $stmtInsert->execute();
        $stmtInsert->close();

        $stmtUpdate = $conexion->prepare("UPDATE consultas SET estado = 'pagada' WHERE id = ?");
        $stmtUpdate->bind_param("i", $id_consulta);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        $conexion->commit();

        echo json_encode(array(
            "exito" => true,
            "mensaje" => "Pago procesado correctamente",
            "cambio" => $cambio
        ));
    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(array("exito" => false, "mensaje" => "Error al procesar el pago"));
    }

} else {
    echo json_encode(array("exito" => false, "mensaje" => "Accion no valida"));
}

$conexion->close();
?>
