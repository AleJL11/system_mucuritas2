<?php
include("./conexion.php");

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehiculoId = $_POST['vehiculo_id'];

    // Iniciar una transacción para asegurar que ambas eliminaciones ocurran
    $conn->begin_transaction();

    try {
        // Eliminar los puestos relacionados con el vehículo
        $deletePuestosQuery = "DELETE FROM puesto WHERE tipo_vehiculo_id = ?";
        $stmtPuestos = $conn->prepare($deletePuestosQuery);
        $stmtPuestos->bind_param('i', $vehiculoId);

        if (!$stmtPuestos->execute()) {
            throw new Exception('Error al eliminar los puestos.');
        }

        // Eliminar el vehículo
        $deleteVehiculoQuery = "DELETE FROM tipo_vehiculo WHERE id = ?";
        $stmtVehiculo = $conn->prepare($deleteVehiculoQuery);
        $stmtVehiculo->bind_param('i', $vehiculoId);

        if (!$stmtVehiculo->execute()) {
            throw new Exception('Error al eliminar el vehículo.');
        }

        // Si todo fue bien, confirma la transacción
        $conn->commit();

        // Ajustar AUTO_INCREMENT para las tablas
        // Para la tabla puesto
        $resetPuestosQuery = "SELECT MAX(id) AS max_id FROM puesto";
        $resultPuestos = $conn->query($resetPuestosQuery);
        $maxIdPuestos = $resultPuestos->fetch_assoc()['max_id'] ?? 0;
        $nextAutoIncrementPuestos = $maxIdPuestos + 1;

        // Ejecutar ALTER TABLE directamente sin parámetros
        $alterPuestosQuery = "ALTER TABLE puesto AUTO_INCREMENT = $nextAutoIncrementPuestos";
        if (!$conn->query($alterPuestosQuery)) {
            throw new Exception('Error al ajustar AUTO_INCREMENT para la tabla puesto: ' . $conn->error);
        }

        // Para la tabla tipo_vehiculo
        $resetVehiculosQuery = "SELECT MAX(id) AS max_id FROM tipo_vehiculo";
        $resultVehiculos = $conn->query($resetVehiculosQuery);
        $maxIdVehiculos = $resultVehiculos->fetch_assoc()['max_id'] ?? 0;
        $nextAutoIncrementVehiculos = $maxIdVehiculos + 1;

        // Ejecutar ALTER TABLE directamente sin parámetros
        $alterVehiculosQuery = "ALTER TABLE tipo_vehiculo AUTO_INCREMENT = $nextAutoIncrementVehiculos";
        if (!$conn->query($alterVehiculosQuery)) {
            throw new Exception('Error al ajustar AUTO_INCREMENT para la tabla tipo_vehiculo: ' . $conn->error);
        }

        echo json_encode(['success' => true, 'message' => 'Vehículo y puesto eliminados exitosamente.']);

    } catch (Exception $e) {
        // En caso de error, deshacer la transacción
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $stmtPuestos->close();
    $stmtVehiculo->close();
    $conn->close();
}
