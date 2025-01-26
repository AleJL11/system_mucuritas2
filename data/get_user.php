<?php

include("./conexion.php");

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$response = array('success' => false);

if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    try {
        // Consulta SQL para obtener vehículos y puestos
        $sql = "SELECT
            u.id AS usuario_id,
            u.usuario,
            p.nombre_completo,
            p.correo,
            tv.id AS vehiculo_id,
            tv.vehiculo AS tipo_vehiculo,
            pu.id AS puesto_id,
            pu.tipo_puesto AS tipo_puesto,
            pu.n_puesto AS n_puesto
        FROM
            usuarios u
        INNER JOIN
            personas p ON u.personas_id = p.id
        LEFT JOIN
            tipo_vehiculo tv ON p.id = tv.personas_id
        LEFT JOIN
            puesto pu ON pu.personas_id = p.id
            AND pu.id = tv.id
        WHERE
            u.id = ?
        ORDER BY
            tv.id, pu.tipo_puesto, pu.n_puesto;";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = array(
                'id' => null,
                'usuario' => null,
                'nombre_completo' => null,
                'correo' => null,
                'vehiculos' => array()
            );

            while ($row = $result->fetch_assoc()) {
                if ($user['id'] === null) {
                    $user['id'] = $row['usuario_id'];
                    $user['usuario'] = $row['usuario'];
                    $user['nombre_completo'] = $row['nombre_completo'];
                    $user['correo'] = $row['correo'];
                }

                if (!isset($user['vehiculos'][$row['vehiculo_id']])) {
                    $user['vehiculos'][$row['vehiculo_id']] = array(
                        'vehiculo_id' => $row['vehiculo_id'], 
                        'tipo_vehiculo' => $row['tipo_vehiculo'],
                        'puestos' => array()
                    );
                }

                if ($row['tipo_puesto'] !== null || $row['n_puesto'] !== null) {
                    $user['vehiculos'][$row['vehiculo_id']]['puestos'][] = array(
                        'puesto_id' => $row['puesto_id'],
                        'tipo_puesto' => $row['tipo_puesto'],
                        'n_puesto' => $row['n_puesto']
                    );
                }
            }
            
            $user['vehiculos'] = array_values($user['vehiculos']); // Convertir el array asociativo a un array indexado
            
            $response['success'] = true;
            $response['user'] = $user;
        } else {
            $response['message'] = 'Usuario no encontrado.';
        }

        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ID de usuario no proporcionado.';
}

echo json_encode($response);
