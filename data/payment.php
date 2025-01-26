<?php

include("./conexion.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$saldo  = $_SESSION['user_data']['saldo'];
$id     = $_SESSION['user_data']['id'];
$id_cxc = $_SESSION['user_data']['cxc_id'];
$nombre = $_SESSION['user_data']['nombre_completo'];

session_write_close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name_payment   = $_POST['name_payment'];
    $fecha          = $_POST['date'];
    $bank           = $_POST['bank_select'];
    $nPuesto        = $_POST['nPuesto'];
    $monto          = isset($_POST['money']) ? floatval($_POST['money']) : 0.0;
    $montoBs        = isset($_POST['money_bs']) ? floatval($_POST['money_bs']) : 0.0;
    $ref            = $_POST['ref'];
    $tPuesto        = $_POST['tPuesto'];
    $tPago          = $_POST['tPago'];
    $img_payment    = $_FILES['capture']['tmp_name'];
    $pagoCompleto   = $_POST['pagoCompleto'];

    /*$saldo  = $_SESSION['user_data']['saldo'];
    $id     = $_SESSION['user_data']['id'];
    $id_cxc = $_SESSION['user_data']['cxc_id'];
    $nombre = $_SESSION['user_data']['nombre_completo'];*/

    $debug_msg = [];

    // Convertir el valor de $tPago a "Pago móvil" o "Transferencia" o "Efectivo"
    if ($tPago == "0") {
        $tPago = "Pago movil";
    } elseif ($tPago == "1") {
        $tPago = "Transferencia";
    } elseif ($tPago == "2") {
        $tPago = "Efectivo";
    }

    try {
        $conn->begin_transaction();

        // Inicializar mesesPagados y mesesPagadosStr para el primer insert
        $mesesPagados = [];
        $mesesPagadosStr = '';

        if ($pagoCompleto === "si") {
            $mesesPagados   = $_POST['meses_dashboard'];
            $mesesPagadosStr= implode(', ', $mesesPagados);
        } elseif ($pagoCompleto === "no") {
            $mesesPendientes = isset($_POST['meses_dashboard']) && is_array($_POST['meses_dashboard']) ? $_POST['meses_dashboard'] : [];
            $tarifas = isset($_POST['tarifas']) && is_array($_POST['tarifas']) ? $_POST['tarifas'] : [];
        }

        // Preparar la sentencia para la inserción en la tabla "pagos"
        $stmt1 = $conn->prepare("INSERT INTO pagos (num_referencia, banco_receptor, monto_num, monto_bs, tPago, nombre_completo, meses_pagados, fecha_pago, tPuesto_pago, nPuesto_pago, capture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("ssddsssssss", $ref, $bank, $monto, $montoBs, $tPago, $name_payment, $mesesPagadosStr, $fecha, $tPuesto, $nPuesto, $img_payment);
        $stmt1->execute();
        $id_pago = $conn->insert_id;
        $stmt1->close();

        // Preparar la sentencia para la inserción en la tabla "cxc_pagos_relacion"
        $stmt2 = $conn->prepare("INSERT INTO cxc_pagos_relacion (id_cxc, id_pagos) VALUES (?, ?)");
        $stmt2->bind_param("ii", $id_cxc, $id_pago);
        $stmt2->execute();
        $stmt2->close();

        if ($pagoCompleto === "si") {

            // Calcular el saldo total a pagar
            $total_a_pagar = $monto;

            // Determinar el saldo restante
            $saldo_restante = $saldo - $total_a_pagar;

            // Actualizar el saldo en la tabla cxc
            $stmt5 = $conn->prepare("UPDATE cxc SET saldo = ? WHERE nombre_completo = ?");
            $stmt5->bind_param("ds", $saldo_restante, $nombre);
            $stmt5->execute();
            $stmt5->close();

            // Obtener los meses actuales y la cantidad de meses de la tabla cxc
            $stmt8 = $conn->prepare("SELECT meses, cantidad_meses FROM cxc WHERE id = ?");
            $stmt8->bind_param("i", $id_cxc);
            $stmt8->execute();
            $result = $stmt8->get_result();
            $row = $result->fetch_assoc();
            $meses_actuales = $row['meses'];
            $cantidad_meses_actual = $row['cantidad_meses'];
            $stmt8->close();

            // Descomponer los meses actuales en un array
            $meses_actuales_array = explode('<br>', $meses_actuales);
            $meses_pagados_array = explode(', ', $mesesPagadosStr);

            // Crear un array para los meses actualizados
            $meses_actualizados_array = [];

            foreach ($meses_actuales_array as $mes_anio_actual) {
                list($anio, $meses) = explode(': ', $mes_anio_actual);
                $meses_array = explode(', ', $meses);

                foreach ($meses_pagados_array as $mes_pagado) {
                    list($anio_pagado, $mes) = explode(': ', $mes_pagado);
                    if ($anio == $anio_pagado) {
                        if (($key = array_search($mes, $meses_array)) !== false) {
                            unset($meses_array[$key]);
                        }
                    }
                }

                if (!empty($meses_array)) {
                    $meses_actualizados_array[] = $anio . ': ' . implode(', ', $meses_array);
                }
            }

            // Convertir el array actualizado a una cadena
            $meses_actualizados_str = implode('<br>', $meses_actualizados_array);
            $cantidad_meses_actualizada = 0;

            foreach ($meses_actualizados_array as $mes_anio_actualizado) {
                list($anio, $meses) = explode(': ', $mes_anio_actualizado);
                $meses_array = explode(', ', $meses);
                $cantidad_meses_actualizada += count($meses_array);
            }

            // Actualizar la tabla cxc con los meses y cantidad de meses actualizados
            $stmt9 = $conn->prepare("UPDATE cxc SET meses = ?, cantidad_meses = ? WHERE id = ?");
            $stmt9->bind_param("sii", $meses_actualizados_str, $cantidad_meses_actualizada, $id_cxc);
            $stmt9->execute();
            $stmt9->close();

        } else if ($pagoCompleto === "no") {

            // Verificar si 'mesesPendientes' está definido y es un array
            $mesesPendientes = isset($_POST['mesesPendientes']) && is_array($_POST['mesesPendientes']) ? $_POST['mesesPendientes'] : [];
            $tarifas = isset($_POST['tarifas']) && is_array($_POST['tarifas']) ? $_POST['tarifas'] : [];

            // Log para verificar el contenido de mesesPendientes y tarifas
            error_log("mesesPendientes: " . print_r($mesesPendientes, true));
            error_log("tarifas: " . print_r($tarifas, true));

            $debug_msg[] = "mesesPendientes: " . print_r($mesesPendientes, true);
            $debug_msg[] = "tarifas: " . print_r($tarifas, true);

            $total_tarifas = 0;
        
            // Registrar la fecha del pago en la tabla cxc_detalle
            foreach ($mesesPendientes as $mes) {
                $mes_limpio = preg_replace('/\s*-\s*\d+$/', '', $mes);
                
                foreach ($tarifas as $tarifa_desc) {
                    if (preg_match('/-\s*(\d+(\.\d+)?)/', $tarifa_desc, $matches)) {
                        $tarifa = floatval($matches[1]);
                        $total_tarifas += $tarifa;

                        error_log("Procesando mes: $mes_limpio con tarifa: $tarifa");
                        $debug_msg[] = "Procesando mes: $mes_limpio con tarifa: $tarifa";

                        // Sentencia preparada para encontrar el cxc_id correcto basado en el monto y mes
                        $stmt_id_detalle = $conn->prepare("SELECT id, anio FROM cxc_detalle WHERE cxc_id = ? AND mes = ? AND monto = ? AND fecha_pago IS NULL");
                        $stmt_id_detalle->bind_param("isd", $id_cxc, $mes_limpio, $tarifa);
                        $stmt_id_detalle->execute();
                        $result_id = $stmt_id_detalle->get_result();

                        if ($result_id->num_rows > 0) {
                            while ($row_id = $result_id->fetch_assoc()) {
                                $id_detalle = $row_id['id'];
                                $anio = $row_id['anio'];

                                error_log("Registro encontrado en cxc_detalle para id: $id_detalle");
                                $debug_msg[] = "Registro encontrado en cxc_detalle para id: $id_detalle";

                                // Actualizar el registro con el id encontrado
                                $stmt_detalle = $conn->prepare("UPDATE cxc_detalle SET fecha_pago = ? WHERE id = ?");
                                $stmt_detalle->bind_param("si", $fecha, $id_detalle);
                                $stmt_detalle->execute();

                                if ($stmt_detalle->affected_rows > 0) {
                                    error_log("Actualización exitosa para cxc_detalle id: $id_detalle");
                                    $debug_msg[] = "Actualización exitosa para cxc_detalle id: $id_detalle";
                                } else {
                                    error_log("Falló la actualización para cxc_detalle id: $id_detalle");
                                    $debug_msg[] = "Falló la actualización para cxc_detalle id: $id_detalle";
                                }

                                $stmt_detalle->close();

                                // Verificar si todas las tarifas del mes y año han sido pagadas
                                $stmt_verificar_pago = $conn->prepare("SELECT COUNT(*) AS pendientes FROM cxc_detalle WHERE cxc_id = ? AND mes = ? AND anio = ? AND fecha_pago IS NULL");
                                $stmt_verificar_pago->bind_param("isi", $id_cxc, $mes_limpio, $anio);
                                $stmt_verificar_pago->execute();
                                $result_verificar = $stmt_verificar_pago->get_result();
                                $row_verificar = $result_verificar->fetch_assoc();

                                $debug_msg[] = "Pagos pendientes: " . $row_verificar['pendientes'];

                                if ($row_verificar['pendientes'] == 0) {
                                    // Obtener los meses actuales de la tabla `cxc`
                                    $stmt8 = $conn->prepare("SELECT meses FROM cxc WHERE id = ?");
                                    $stmt8->bind_param("i", $id_cxc);
                                    $stmt8->execute();
                                    $result = $stmt8->get_result();
                                    $row = $result->fetch_assoc();
                                    $stmt8->close();

                                    $meses_actuales_array = explode('<br>', $row['meses']);
                                    $cantidad_meses_actualizada = 0;
                                    $meses_actualizados_array = [];

                                    // Recorrer cada mes y eliminar los que ya han sido pagados completamente
                                    foreach ($meses_actuales_array as $mes_anio_actual) {
                                        list($anio_cxc, $meses_cxc) = explode(': ', $mes_anio_actual);
                                        $meses_array = explode(', ', $meses_cxc);

                                        if ($anio_cxc == $anio && in_array($mes_limpio, $meses_array)) {
                                            $meses_array = array_diff($meses_array, [$mes_limpio]);
                                        }

                                        if (!empty($meses_array)) {
                                            $meses_actualizados_array[] = $anio_cxc . ': ' . implode(', ', $meses_array);
                                            $cantidad_meses_actualizada += count($meses_array);
                                        }
                                    }

                                    // Actualizar el campo 'meses' en la tabla `cxc`
                                    $meses_actualizados_str = implode('<br>', $meses_actualizados_array);
                                    $stmt_update_cxc = $conn->prepare("UPDATE cxc SET meses = ?, cantidad_meses = ? WHERE id = ?");
                                    $stmt_update_cxc->bind_param("sii", $meses_actualizados_str, $cantidad_meses_actualizada, $id_cxc);
                                    $stmt_update_cxc->execute();
                                    $stmt_update_cxc->close();

                                    error_log("Meses actualizados: $meses_actualizados_str - Cantidad de meses actualizada: $cantidad_meses_actualizada");
                                    $debug_msg[] = "Meses actualizados: $meses_actualizados_str - Cantidad de meses actualizada: $cantidad_meses_actualizada";
                                }

                                $stmt_verificar_pago->close();
                            }
                        } else {
                            error_log("No se encontraron registros en cxc_detalle");
                            $debug_msg[] = "No se encontraron registros en cxc_detalle";
                        }
                        $stmt_id_detalle->close();
                    } else {
                        error_log("Formato de tarifa no válido: $tarifa_desc");
                        $debug_msg[] = "Formato de tarifa no válido: $tarifa_desc";
                    }
                }
            }

            if ($total_tarifas > 0) {
                $stmt_saldo_cxc = $conn->prepare("SELECT saldo FROM cxc WHERE id = ?");
                $stmt_saldo_cxc->bind_param("i", $id_cxc);
                $stmt_saldo_cxc->execute();
                $result_saldo = $stmt_saldo_cxc->get_result();

                if ($row_saldo = $result_saldo->fetch_assoc()) {
                    $tarifa_cxc = $row_saldo['saldo'];
                    //$total_tarifa = array_sum($tarifas);
                    $resta = $tarifa_cxc - $total_tarifas;

                    error_log("Tarifa cxc: $tarifa_cxc - Tarifa: $tarifa = Resta: $resta");
                    $debug_msg[] = "Tarifa cxc: $tarifa_cxc - Tarifa: $tarifa = Resta: $resta";

                    $stmt_update_saldo = $conn->prepare("UPDATE cxc SET saldo = ? WHERE id = ?");
                    $stmt_update_saldo->bind_param("di", $resta, $id_cxc);
                    $stmt_update_saldo->execute();

                    if ($stmt_update_saldo->affected_rows > 0) {
                        error_log("Actualización exitosa para cxc id: $id_cxc");
                        $debug_msg[] = "Actualización exitosa para cxc id: $id_cxc";
                    } else {
                        error_log("Falló la actualización para cxc id: $id_cxc");
                        $debug_msg[] = "Falló la actualización para cxc id: $id_cxc";
                    }

                    $stmt_update_saldo->close();
                }

                $stmt_saldo_cxc->close();
            }
        }

        // Obtener el saldo y la cantidad de meses actualizados después de eliminar los meses
        $stmt10 = $conn->prepare("SELECT saldo, cantidad_meses, meses FROM cxc WHERE id = ?");
        $stmt10->bind_param("i", $id_cxc);
        $stmt10->execute();
        $result = $stmt10->get_result();
        $row = $result->fetch_assoc();
        $stmt10->close();

        $response = array(
            "saldo" => $row['saldo'],
            "cnt_meses" => $row['cantidad_meses'],
            "meses" => $row['meses'],
            "status" => "success",
            "message" => "Pago registrado correctamente",
            "formato_meses_cadena" => $mesesPagadosStr,
            "formato_meses" => $mesesPagados,
            "tpago" => $tPago,
            "debug" => $debug_msg
        );
        echo json_encode($response);

        $conn->commit();
        $conn->close();

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(array("message" => "Error en la transacción: " . $e->getMessage()));
    }
} else {
    echo json_encode(array("message" => "Datos no recibidos"));
}
