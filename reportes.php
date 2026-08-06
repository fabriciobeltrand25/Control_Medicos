<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$lista = array();

// =====================================================
// 1) Pacientes con consulta activa
// =====================================================
if ($tipo === 'consulta_activa') {

    $sql = "SELECT p.codigo, p.nombre, p.apellido, m.nombre AS medico, c.fecha_consulta
            FROM consultas c
            INNER JOIN pacientes p ON c.id_paciente = p.id
            INNER JOIN medicos m ON c.id_medico = m.id
            WHERE c.estado = 'activa'
            ORDER BY c.fecha_consulta ASC";
    $resultado = $conexion->query($sql);
    while ($fila = $resultado->fetch_assoc()) {
        $lista[] = array(
            "linea1" => $fila['codigo'] . " - " . $fila['nombre'] . " " . $fila['apellido'],
            "linea2" => "Medico: " . $fila['medico'] . " | Fecha: " . $fila['fecha_consulta']
        );
    }

// =====================================================
// 2) Pacientes en mora (deben y tienen dias de atraso > 0)
// =====================================================
} elseif ($tipo === 'mora') {

    $sql = "SELECT p.codigo, p.nombre, p.apellido, c.valor_consulta, c.fecha_baja
            FROM consultas c
            INNER JOIN pacientes p ON c.id_paciente = p.id
            WHERE c.estado = 'pendiente_pago'";
    $resultado = $conexion->query($sql);

    while ($fila = $resultado->fetch_assoc()) {
        $fecha_baja = new DateTime($fila['fecha_baja']);
        $ahora = new DateTime();
        $dias = intval($fecha_baja->diff($ahora)->days);

        if ($dias > 0) {
            $mora = $dias * 20.00;
            $total = floatval($fila['valor_consulta']) + $mora;
            $lista[] = array(
                "linea1" => $fila['codigo'] . " - " . $fila['nombre'] . " " . $fila['apellido'],
                "linea2" => $dias . " dias de atraso | Total: L " . number_format($total, 2)
            );
        }
    }

// =====================================================
// 3) Medicos por especialidad
// =====================================================
} elseif ($tipo === 'medicos_especialidad') {

    $sql = "SELECT nombre, especialidad FROM medicos ORDER BY especialidad ASC, nombre ASC";
    $resultado = $conexion->query($sql);
    while ($fila = $resultado->fetch_assoc()) {
        $lista[] = array(
            "linea1" => $fila['nombre'],
            "linea2" => "Especialidad: " . $fila['especialidad']
        );
    }

// =====================================================
// 4) Pacientes con mas consultas
// =====================================================
} elseif ($tipo === 'pacientes_top') {

    $sql = "SELECT p.codigo, p.nombre, p.apellido, COUNT(c.id) AS total
            FROM consultas c
            INNER JOIN pacientes p ON c.id_paciente = p.id
            GROUP BY p.id
            ORDER BY total DESC
            LIMIT 20";
    $resultado = $conexion->query($sql);
    while ($fila = $resultado->fetch_assoc()) {
        $lista[] = array(
            "linea1" => $fila['codigo'] . " - " . $fila['nombre'] . " " . $fila['apellido'],
            "linea2" => "Total de consultas: " . $fila['total']
        );
    }

// =====================================================
// 5) Medicos con mas consultas
// =====================================================
} elseif ($tipo === 'medicos_top') {

    $sql = "SELECT m.nombre, m.especialidad, COUNT(c.id) AS total
            FROM consultas c
            INNER JOIN medicos m ON c.id_medico = m.id
            GROUP BY m.id
            ORDER BY total DESC
            LIMIT 20";
    $resultado = $conexion->query($sql);
    while ($fila = $resultado->fetch_assoc()) {
        $lista[] = array(
            "linea1" => $fila['nombre'] . " (" . $fila['especialidad'] . ")",
            "linea2" => "Total de consultas: " . $fila['total']
        );
    }

// =====================================================
// 6) Cobros por consulta (historial de pagos)
// =====================================================
} elseif ($tipo === 'cobros_consulta') {

    $sql = "SELECT p.codigo, p.nombre, p.apellido, co.total_pagado, co.mora, co.fecha_pago
            FROM cobros co
            INNER JOIN consultas c ON co.id_consulta = c.id
            INNER JOIN pacientes p ON c.id_paciente = p.id
            ORDER BY co.fecha_pago DESC
            LIMIT 50";
    $resultado = $conexion->query($sql);
    while ($fila = $resultado->fetch_assoc()) {
        $lista[] = array(
            "linea1" => $fila['codigo'] . " - " . $fila['nombre'] . " " . $fila['apellido'] . " | L " . number_format($fila['total_pagado'], 2),
            "linea2" => "Mora: L " . number_format($fila['mora'], 2) . " | " . $fila['fecha_pago']
        );
    }

// =====================================================
// 7) Total recaudado por dia
// =====================================================
} elseif ($tipo === 'total_dia') {

    $sql = "SELECT DATE(fecha_pago) AS dia, SUM(total_pagado) AS total, COUNT(*) AS cantidad
            FROM cobros
            GROUP BY DATE(fecha_pago)
            ORDER BY dia DESC
            LIMIT 60";
    $resultado = $conexion->query($sql);
    while ($fila = $resultado->fetch_assoc()) {
        $lista[] = array(
            "linea1" => $fila['dia'],
            "linea2" => "Total: L " . number_format($fila['total'], 2) . " (" . $fila['cantidad'] . " cobros)"
        );
    }

}

echo json_encode($lista);
$conexion->close();
?>
