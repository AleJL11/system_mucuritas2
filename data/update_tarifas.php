<?php
include("./conexion.php");

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    if ($data) {
        $success = true;
        $conn->begin_transaction(); // Iniciar una transacción
        
        try {
            for ($i = 1; $i <= 4; $i++) {
                $tarifa_id = $data['id_tarifa_' . $i];
                $tarifa_value = $data['tarifa_' . $i];

                // Preparar la consulta SQL para actualizar la tarifa
                $sql = "UPDATE tarifas SET tarifa = ? WHERE id = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("di", $tarifa_value, $tarifa_id);

                    // Ejecutar la consulta
                    if (!$stmt->execute()) {
                        $success = false;
                        break;
                    }

                    $stmt->close();
                } else {
                    $success = false;
                    break;
                }
            }

            // Verificar si la operación fue exitosa
            if ($success) {
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Tarifas actualizadas correctamente']);
            } else {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Error al actualizar las tarifas']);
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Excepción: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos o vacíos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
