<?php
include("./conexion.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debugInfo = [];
    $debugInfo[] = 'Datos POST recibidos: ' . print_r($_POST, true);

    // Datos básicos
    $userId = $_POST['id'];
    $usuario = $_POST['usuario'];
    $nombre_completo = $_POST['nombre_completo'];
    $correo = $_POST['correo'];
    
    // Actualizar la tabla personas
    $updatePersonasQuery = "UPDATE personas SET nombre_completo = ?, correo = ?, actualizacion = NOW() WHERE id = ?";
    $stmtPersonas = $conn->prepare($updatePersonasQuery);
    $stmtPersonas->bind_param('ssi', $nombre_completo, $correo, $userId);
    if (!$stmtPersonas->execute()) {
        echo json_encode(['success' => false, 'message_denied' => 'Error al actualizar la tabla personas: ' . $stmtPersonas->error]);
        exit();
    }

    // Actualizar la tabla usuarios
    $updateUsuariosQuery = "UPDATE usuarios SET usuario = ?, actualizacion = NOW() WHERE personas_id = ?";
    $stmtUsuarios = $conn->prepare($updateUsuariosQuery);
    $stmtUsuarios->bind_param('si', $usuario, $userId);
    if (!$stmtUsuarios->execute()) {
        echo json_encode(['success' => false, 'message_denied' => 'Error al actualizar la tabla usuarios: ' . $stmtUsuarios->error]);
        exit();
    }

    // Determinar el índice más alto de vehículos existentes
    $maxVehicleIndex = 0;
    foreach ($_POST as $key => $value) {
        if (preg_match('/vehiculo_(\d+)_id/', $key, $matches)) {
            $index = (int)$matches[1];
            if ($index > $maxVehicleIndex) {
                $maxVehicleIndex = $index;
            }
        }
    }

    // Ciclo para actualizar vehículos existentes
    $vehicleIndex = 0;
    while (isset($_POST["vehiculo_{$vehicleIndex}_id"])) {
        $vehiculoId = $_POST["vehiculo_{$vehicleIndex}_id"];
        $vehiculoTipo = $_POST["vehiculo_{$vehicleIndex}_tipo"];
        
        // Actualizar vehículo existente
        $debugInfo[] = "Actualizando vehículo con ID: $vehiculoId";

        $updateVehiculoQuery = "UPDATE tipo_vehiculo SET vehiculo = ? WHERE id = ?";
        $stmtUpdateVehiculo = $conn->prepare($updateVehiculoQuery);
        $stmtUpdateVehiculo->bind_param('si', $vehiculoTipo, $vehiculoId);
        if (!$stmtUpdateVehiculo->execute()) {
            $debugInfo[] = 'Error al actualizar el vehículo: ' . $stmtUpdateVehiculo->error;
            echo json_encode(['success' => false, 'message_denied' => $debugInfo]);
            exit();
        }

        // Ciclo para actualizar puestos asociados a vehículos existentes
        $puestoIndex = 0;
        while (isset($_POST["vehiculo_{$vehicleIndex}_puesto_{$puestoIndex}_id"])) {
            $puestoId = $_POST["vehiculo_{$vehicleIndex}_puesto_{$puestoIndex}_id"];
            $puestoTipo = $_POST["vehiculo_{$vehicleIndex}_puesto_{$puestoIndex}_tipo"];
            $puestoNumero = $_POST["vehiculo_{$vehicleIndex}_puesto_{$puestoIndex}_numero"];

            // Actualizar puesto existente
            $debugInfo[] = "Actualizando puesto con ID: $puestoId para el vehículo con ID: $vehiculoId";
            $updatePuestoQuery = "UPDATE puesto SET tipo_puesto = ?, n_puesto = ? WHERE id = ?";
            $stmtUpdatePuesto = $conn->prepare($updatePuestoQuery);
            $stmtUpdatePuesto->bind_param('ssi', $puestoTipo, $puestoNumero, $puestoId);
            if (!$stmtUpdatePuesto->execute()) {
                $debugInfo[] = 'Error al actualizar el puesto: ' . $stmtUpdatePuesto->error;
                echo json_encode(['success' => false, 'message_denied' => $debugInfo]);
                exit();
            }

            $puestoIndex++;
        }

        $vehicleIndex++;
    }

    // Ciclo para insertar nuevos vehículos
    $debugInfo[] = 'Indice más alto de vehículos existentes: ' . $maxVehicleIndex;
    $vehicleIndex = $maxVehicleIndex + 2;
    $debugInfo[] = 'Indice del nuevo vehículo: ' . $vehicleIndex;

    while (isset($_POST["vehiculo_{$vehicleIndex}_tipo_nuevo"])) {
        $vehiculoTipoNuevo = $_POST["vehiculo_{$vehicleIndex}_tipo_nuevo"];
        
        // Insertar nuevo vehículo
        $debugInfo[] = "Insertando nuevo vehículo: $vehiculoTipoNuevo";
        $insertVehiculoQuery = "INSERT INTO tipo_vehiculo (vehiculo, personas_id) VALUES (?, ?)";
        $stmtInsertVehiculo = $conn->prepare($insertVehiculoQuery);
        $stmtInsertVehiculo->bind_param('si', $vehiculoTipoNuevo, $userId);
        if (!$stmtInsertVehiculo->execute()) {
            $debugInfo[] = 'Error al insertar el vehículo: ' . $stmtInsertVehiculo->error;
            echo json_encode(['success' => false, 'message_denied' => $debugInfo]);
            exit();
        }
        $vehiculoId = $stmtInsertVehiculo->insert_id; // Obtener el ID del nuevo vehículo
        $debugInfo[] = "Nuevo vehículo insertado con ID: $vehiculoId";

        // Ciclo para insertar nuevos puestos asociados al nuevo vehículo
        $puestoIndex = 1;
        while (isset($_POST["vehiculo_{$vehicleIndex}_puesto_{$puestoIndex}_tipo_nuevo"])) {
            $puestoTipoNuevo = $_POST["vehiculo_{$vehicleIndex}_puesto_{$puestoIndex}_tipo_nuevo"];
            $puestoNumeroNuevo = $_POST["vehiculo_{$vehicleIndex}_puesto_{$puestoIndex}_numero_nuevo"];

            // Insertar nuevo puesto
            $debugInfo[] = "Insertando nuevo puesto: $puestoTipoNuevo, Número: $puestoNumeroNuevo, para el vehículo con ID: $vehiculoId";
            $insertPuestoQuery = "INSERT INTO puesto (tipo_puesto, n_puesto, personas_id, tipo_vehiculo_id) VALUES (?, ?, ?, ?)";
            $stmtInsertPuesto = $conn->prepare($insertPuestoQuery);
            $stmtInsertPuesto->bind_param('ssii', $puestoTipoNuevo, $puestoNumeroNuevo, $userId, $vehiculoId);
            if (!$stmtInsertPuesto->execute()) {
                $debugInfo[] = 'Error al insertar el puesto: ' . $stmtInsertPuesto->error;
                echo json_encode(['success' => false, 'message_denied' => $debugInfo]);
                exit();
            }

            $puestoIndex++;
        }

        $vehicleIndex++;
    }

    // Añadir la información al debugInfo
    $debugInfo[] = 'Proceso finalizado.';

    // Respuesta en caso de éxito
    echo json_encode([
        'success' => true,
        'message_success' => 'Usuario y/o vehículos actualizados correctamente.',
        'debugInfo' => $debugInfo
    ]);
} else {
    echo json_encode(['success' => false, 'message_denied' => 'Método de solicitud no permitido.']);
}
