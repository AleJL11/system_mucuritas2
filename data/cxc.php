<?php

include("./conexion.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$postdata = file_get_contents("php://input");

if (isset($postdata) && !empty($postdata)) {
    $data = json_decode($postdata, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(array("message" => "Error decoding JSON data"));
        exit;
    }

    $id = $data['id'];

    $mesesSeleccionadosArray = $data['meses'];
    $total = $data['total'];
    $tarifasSeleccionadas = $data['tarifas'];

    try {
        $conn->begin_transaction();

        // Obtener el saldo actual y los meses registrados
        $stmt = $conn->prepare("SELECT saldo, meses FROM cxc WHERE personas_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $saldoActual = $row['saldo'];
        $mesesRegistrados = $row['meses'];
        $stmt->close();

        // Convertir los meses registrados a un array
        $mesesRegistradosArray = explode('<br>', $mesesRegistrados);
        $mesesRegistradosMap = [];
        foreach ($mesesRegistradosArray as $registro) {
            if (!empty(trim($registro))) {
                list($year, $meses) = explode(': ', $registro);
                $mesesRegistradosMap[trim($year)] = array_map('trim', explode(', ', $meses));
            }
        }

        // Comparar y añadir solo los nuevos meses que no están registrados
        foreach ($mesesSeleccionadosArray as $year => $meses) {
            $mesesArray = array_map('trim', explode(', ', $meses));
            if (isset($mesesRegistradosMap[$year])) {
                $mesesRegistradosMap[$year] = array_unique(array_merge($mesesRegistradosMap[$year], $mesesArray));
            } else {
                $mesesRegistradosMap[$year] = $mesesArray;
            }
        }

        // Convertir el array actualizado a la cadena de formato deseado
        $mesesActualizadosConAnio = [];
        foreach ($mesesRegistradosMap as $year => $meses) {
            $mesesActualizadosConAnio[] = $year . ': ' . implode(', ', $meses);
        }
        $mesesActualizadosporAnio = implode(' <br>', $mesesActualizadosConAnio);
        $cantidadMesesActualizados = array_reduce($mesesRegistradosMap, function ($carry, $meses) {
            return $carry + count($meses);
        }, 0);

        // Calcular el nuevo saldo
        $nuevoSaldo = $saldoActual + $total;

        // Actualizar los datos en la tabla cxc
        $stmt = $conn->prepare("UPDATE cxc SET meses = ?, cantidad_meses = ?, saldo = ? WHERE personas_id = ?");
        $stmt->bind_param("siii", $mesesActualizadosporAnio, $cantidadMesesActualizados, $nuevoSaldo, $id);
        $stmt->execute();

        // Eliminar las relaciones existentes en la tabla de relación
        $stmt = $conn->prepare("DELETE FROM cxc_tarifas_relacion WHERE id_cxc = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Insertar las nuevas relaciones en la tabla de relación
        foreach ($tarifasSeleccionadas as $tarifa) {
            $tipoPuesto = $tarifa['tipoPuesto'];
            $tarifaMonto = $tarifa['tarifa'];

            // Verificar que la tarifa exista en la tabla 'tarifas'
            $stmt = $conn->prepare("SELECT id FROM tarifas WHERE tipo = ? AND tarifa = ?");
            $stmt->bind_param("sd", $tipoPuesto, $tarifaMonto);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $tarifaId = $result->fetch_assoc()['id'];
                
                // Insertar en la tabla de relación
                $stmt = $conn->prepare("INSERT INTO cxc_tarifas_relacion (id_cxc, id_tarifa) VALUES (?, ?)");
                $stmt->bind_param("ii", $id, $tarifaId);
                $stmt->execute();
            } else {
                $conn->rollback();
                echo json_encode(array("message" => "Error: Tarifa con tipo '$tipoPuesto' y monto '$tarifaMonto' no existe en la tabla 'tarifas'"));
                exit;
            }
        }

        if (isset($data['detalles']) && is_array($data['detalles'])) {
            foreach ($data['detalles'] as $detalle) {
                $anio = $detalle['anio'];
                $mes = $detalle['mes'];
                $tarifa_id = $detalle['tarifa_id'];
                $monto = $detalle['monto'];

                $stmt = $conn->prepare("INSERT INTO cxc_detalle (cxc_id, mes, anio, tarifa_id, monto) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isiii", $id, $mes, $anio, $tarifa_id, $monto);
                $stmt->execute();
            }
        }

        // Actualizar el estado de la cuenta por cobrar
        $stmt8 = $conn->prepare("SELECT * FROM cxc WHERE personas_id = ?");
        $stmt8->bind_param("i", $id);
        $stmt8->execute();
        $result = $stmt8->get_result();
        $row = $result->fetch_assoc();
        $stmt8->close();

        $conn->commit();
        $stmt->close();
        $conn->close();

        echo json_encode(array(
            "message" => "Cuenta por cobrar registrada correctamente",
            "cnt_meses" => $row['cantidad_meses'],
            "meses" => $row['meses'],
            "saldo" => $row['saldo'],
            "total" => $total,
            "tarifasSeleccionadas" => $tarifasSeleccionadas,
            "mesesSeleccionadosArray" => $mesesActualizadosporAnio,
            "id" => $id,
            "status" => "success"
        ));

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(array("message" => "Error en la transacción: " . $e->getMessage()));
        exit;
    }
} else {
    echo json_encode(array("message" => "Datos no recibidos"));
}
