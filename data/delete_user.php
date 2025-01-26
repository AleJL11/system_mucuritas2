<?php
    include("./conexion.php");

    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener el ID del usuario
        $userID = $_POST['user_id'];

        try {
            $conn ->begin_transaction();

            // 1. Consultar datos del usuario para obtener detalles necesarios para eliminar en otras tablas
            $stmt = $conn->prepare("SELECT
                tVehiculo_id,
                tPuesto_id,
                personas_id
                FROM usuarios
                WHERE id = ?");
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
            $stmt->close();

            if ($userData) {
                $tVehiculoId    = $userData['tVehiculo_id'];
                $tPuestoId      = $userData['tPuesto_id'];
                $personasId     = $userData['personas_id'];

                // 2. Eliminar el usuario
                $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->bind_param("i", $userID);
                $stmt->execute();
                $stmt->close();

                // 3. Eliminar los pagos si existen
                $stmt = $conn->prepare("DELETE FROM pagos WHERE id IN (SELECT id_pagos FROM cxc_pagos_relacion WHERE id_cxc IN (SELECT id FROM cxc WHERE personas_id = ?))");
                $stmt->bind_param("i", $personasId);
                $stmt->execute();
                $stmt->close();

                // 4. Eliminar las relaciones en `cxc_pagos_relacion` si existen
                $stmt = $conn->prepare("DELETE FROM cxc_pagos_relacion WHERE id_cxc IN (SELECT id FROM cxc WHERE personas_id = ?)");
                $stmt->bind_param("i", $personasId);
                $stmt->execute();
                $stmt->close();
    
                // 5. Eliminar las relaciones en `cxc_tarifas_relacion` si existen
                $stmt = $conn->prepare("DELETE FROM cxc_tarifas_relacion WHERE id_cxc IN (SELECT id FROM cxc WHERE personas_id = ?)");
                $stmt->bind_param("i", $personasId);
                $stmt->execute();
                $stmt->close();

                // 6. Eliminar los datos del `cxc`
                $stmt = $conn->prepare("DELETE FROM cxc WHERE personas_id = ?");
                $stmt->bind_param("i", $personasId);
                $stmt->execute();
                $stmt->close();
    
                // 7. Eliminar los datos del `puesto`
                if ($tPuestoId) {
                    $stmt = $conn->prepare("DELETE FROM puesto WHERE id = ?");
                    $stmt->bind_param("i", $tPuestoId);
                    $stmt->execute();
                    $stmt->close();
                }

                // 8. Eliminar los datos del `tipo_vehiculo` si existe
                if ($tVehiculoId) {
                    $stmt = $conn->prepare("DELETE FROM tipo_vehiculo WHERE id = ?");
                    $stmt->bind_param("i", $tVehiculoId);
                    $stmt->execute();
                    $stmt->close();
                }
    
                // 9. Eliminar los datos del `personas`
                $stmt = $conn->prepare("DELETE FROM personas WHERE id = ?");
                $stmt->bind_param("i", $personasId);
                $stmt->execute();
                $stmt->close();
            }
    
            // Confirmar la transacción
            $conn->commit();

            // Ajustar AUTO_INCREMENT para las tablas
            // 1. Para la tabla usuarios
            $resetPuestosQuery = "SELECT MAX(id) AS max_id FROM usuarios";
            $resultPuestos = $conn->query($resetPuestosQuery);
            $maxIdPuestos = $resultPuestos->fetch_assoc()['max_id'] ?? 0;
            $nextAutoIncrementPuestos = $maxIdPuestos + 1;

            $alterPuestosQuery = "ALTER TABLE usuarios AUTO_INCREMENT = $nextAutoIncrementPuestos";
            if (!$conn->query($alterPuestosQuery)) {
                throw new Exception('Error al ajustar AUTO_INCREMENT para la tabla usuarios: ' . $conn->error);
            }

            // 2. Para la tabla tipo_vehiculo
            $resetVehiculosQuery = "SELECT MAX(id) AS max_id FROM tipo_vehiculo";
            $resultVehiculos = $conn->query($resetVehiculosQuery);
            $maxIdVehiculos = $resultVehiculos->fetch_assoc()['max_id'] ?? 0;
            $nextAutoIncrementVehiculos = $maxIdVehiculos + 1;

            // Ejecutar ALTER TABLE directamente sin parámetros
            $alterVehiculosQuery = "ALTER TABLE tipo_vehiculo AUTO_INCREMENT = $nextAutoIncrementVehiculos";
            if (!$conn->query($alterVehiculosQuery)) {
                throw new Exception('Error al ajustar AUTO_INCREMENT para la tabla tipo_vehiculo: ' . $conn->error);
            }

            // 3. Para la tabla pagos
            $resetVehiculosQuery = "SELECT MAX(id) AS max_id FROM pagos";
            $resultVehiculos = $conn->query($resetVehiculosQuery);
            $maxIdVehiculos = $resultVehiculos->fetch_assoc()['max_id'] ?? 0;
            $nextAutoIncrementVehiculos = $maxIdVehiculos + 1;

            // Ejecutar ALTER TABLE directamente sin parámetros
            $alterVehiculosQuery = "ALTER TABLE pagos AUTO_INCREMENT = $nextAutoIncrementVehiculos";
            if (!$conn->query($alterVehiculosQuery)) {
                throw new Exception('Error al ajustar AUTO_INCREMENT para la tabla pagos: ' . $conn->error);
            }

            // 4. Para la tabla cxc
            $resetVehiculosQuery = "SELECT MAX(id) AS max_id FROM cxc";
            $resultVehiculos = $conn->query($resetVehiculosQuery);
            $maxIdVehiculos = $resultVehiculos->fetch_assoc()['max_id'] ?? 0;
            $nextAutoIncrementVehiculos = $maxIdVehiculos + 1;

            // Ejecutar ALTER TABLE directamente sin parámetros
            $alterVehiculosQuery = "ALTER TABLE cxc AUTO_INCREMENT = $nextAutoIncrementVehiculos";
            if (!$conn->query($alterVehiculosQuery)) {
                throw new Exception('Error al ajustar AUTO_INCREMENT para la tabla cxc: ' . $conn->error);
            }

            // 5. Para la tabla puesto
            $resetPuestosQuery = "SELECT MAX(id) AS max_id FROM puesto";
            $resultPuestos = $conn->query($resetPuestosQuery);
            $maxIdPuestos = $resultPuestos->fetch_assoc()['max_id'] ?? 0;
            $nextAutoIncrementPuestos = $maxIdPuestos + 1;

            // Ejecutar ALTER TABLE directamente sin parámetros
            $alterPuestosQuery = "ALTER TABLE puesto AUTO_INCREMENT = $nextAutoIncrementPuestos";
            if (!$conn->query($alterPuestosQuery)) {
                throw new Exception('Error al ajustar AUTO_INCREMENT para la tabla puesto: ' . $conn->error);
            }

            // 6. Para la tabla personas
            $resetPersonasQuery = "SELECT MAX(id) AS max_id FROM personas";
            $resultPersonas = $conn->query($resetPersonasQuery);
            $maxIdPersonas = $resultPersonas->fetch_assoc()['max_id'] ?? 0;
            $nextAutoIncrementPersonas = $maxIdPersonas + 1;

            // Ejecutar ALTER TABLE directamente sin parámetros
            $alterPersonasQuery = "ALTER TABLE personas AUTO_INCREMENT = $nextAutoIncrementPersonas";
            if (!$conn->query($alterPersonasQuery)) {
                throw new Exception('Error al ajustar AUTO_INCREMENT para la tabla personas: ' . $conn->error);
            }

            $response = array(
                "success" => true,
                "message_success" => "Usuario eliminado correctamente",
                "message_denied" => "No se pudo eliminar el usuario"
            );
            echo json_encode($response);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(array("message" => "Ocurrió un error inesperado:" . $e->getMessage()));
        } 

    } else {
        echo json_encode(array("message" => "Datos no recibidos"));
    }
